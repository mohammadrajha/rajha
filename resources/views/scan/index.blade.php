@extends('layouts.app')

@section('title', __('scan.title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 text-center">
        <div class="text-5xl mb-3">📱</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ __('scan.title') }}</h1>
        <p class="text-gray-500 mb-6">{{ __('scan.instructions') }}</p>

        <!-- Camera QR Scanner -->
        <div id="qr-reader" class="mb-6 rounded-lg overflow-hidden border-2 border-dashed border-gray-300 mx-auto" style="max-width: 400px; min-height: 300px;"></div>

        <!-- Manual room code input -->
        <div class="mb-6 max-w-xs mx-auto">
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('scan.manual_entry') }}</label>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <input type="text" id="manual-room" inputmode="numeric" autocomplete="off"
                    placeholder="{{ __('scan.room_number_placeholder') }}"
                    class="flex-1 border rounded-lg px-4 py-3 text-center text-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                <button id="manual-submit" class="bg-indigo-600 text-white px-5 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    {{ __('scan.go') }}
                </button>
            </div>
        </div>

        <!-- Loading / Status -->
        <div id="scan-status" class="mt-4"></div>

        <p class="text-xs text-gray-400 mt-4">{{ __('scan.camera_permission') }}</p>
    </div>

    <!-- Schedule results (filled by JS) -->
    <div id="schedule-results" class="mt-6 hidden"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusEl = document.getElementById('scan-status');
    const resultsEl = document.getElementById('schedule-results');
    const manualInput = document.getElementById('manual-room');
    const manualBtn = document.getElementById('manual-submit');
    let scanning = true;

    // Status-code → color/icon table matching resources/views/scan/result.blade.php.
    const STATUS_STYLES = {
        present:         { bg: 'bg-green-100',  text: 'text-green-700',  icon: '\u2705' },
        late:            { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: '\u23F1' },
        wrong_classroom: { bg: 'bg-red-100',    text: 'text-red-700',    icon: '\u274C' },
        no_lecture:      { bg: 'bg-gray-100',   text: 'text-gray-700',   icon: '\uD83D\uDCED' },
        duplicate:       { bg: 'bg-blue-100',   text: 'text-blue-700',   icon: '\u2139\uFE0F' },
        error:           { bg: 'bg-red-100',    text: 'text-red-700',    icon: '\u26A0\uFE0F' },
    };

    // ── Extract room code from QR text (preserves leading zeros and non-digits) ──
    function extractRoomCode(text) {
        if (text === null || text === undefined) return null;
        const trimmed = String(text).trim();
        if (trimmed === '') return null;

        // Plain code: QR payload is the ROOM_CODE itself (our generator emits exactly this).
        if (/^[A-Za-z0-9_\-]+$/.test(trimmed)) return trimmed;

        // URL containing /rooms/<code> or /room/<code> (defensive for older QRs).
        const urlMatch = trimmed.match(/\/rooms?\/([A-Za-z0-9_\-]+)/i);
        if (urlMatch) return urlMatch[1];

        // "Room 10018", "room_10018", "Room-10018", etc.
        const labelMatch = trimmed.match(/room[\s_\-:#]*([A-Za-z0-9_\-]+)/i);
        if (labelMatch) return labelMatch[1];

        return null;
    }

    // ── Post the scanned room code to scan.mark and render the attendance result ──
    function markAttendance(roomCode) {
        statusEl.innerHTML = `<div class="flex items-center justify-center space-x-2 rtl:space-x-reverse text-indigo-600">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="font-semibold">{{ __('scan.processing') }}</span>
        </div>`;
        resultsEl.classList.add('hidden');

        fetch('{{ route("scan.mark") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ room_no: roomCode })
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            return { ok: r.ok, status: r.status, data };
        })
        .then(({ ok, data }) => {
            statusEl.innerHTML = '';
            const payload = data || {};
            const status = payload.status || (ok ? 'error' : 'error');
            renderAttendanceResult(roomCode, status, payload);
            // Re-enable scanning after a short cooldown so the instructor can retry if needed.
            setTimeout(() => { scanning = true; }, 2000);
        })
        .catch(() => {
            statusEl.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
                <div class="font-semibold">{{ __('scan.error') }}</div>
            </div>`;
            scanning = true;
        });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    // ── Render the attendance-mark result card (same shape as scan/result.blade.php) ──
    function renderAttendanceResult(roomCode, status, payload) {
        const style = STATUS_STYLES[status] || STATUS_STYLES.error;
        const message = payload.message || '{{ __('scan.error') }}';

        let extra = '';
        if (payload.correct_room) {
            extra += `<div class="mt-3 text-base">
                {{ __('scan.correct_classroom') }}:
                <strong>{{ __('scan.room') }} ${escapeHtml(payload.correct_room)}</strong>
            </div>`;
        }
        if (typeof payload.delay_minutes !== 'undefined' && payload.delay_minutes !== null) {
            extra += `<div class="mt-2 text-base">
                {{ __('scan.delay') }}: ${escapeHtml(payload.delay_minutes)} {{ __('scan.minutes') }}
            </div>`;
        }

        const instructorEmail = payload.instructor_email
            ? `<div class="mt-2 text-xs text-gray-500 font-mono">${escapeHtml(payload.instructor_email)}</div>`
            : '';

        resultsEl.innerHTML = `
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="${style.bg} ${style.text} p-8 rounded-xl">
                    <div class="text-5xl mb-4">${style.icon}</div>
                    <div class="text-xl font-bold">${escapeHtml(message)}</div>
                    ${extra}
                </div>
                <div class="mt-6 text-sm text-gray-500">
                    {{ __('scan.scanned_classroom') }}:
                    <span class="font-mono">{{ __('scan.room') }} ${escapeHtml(roomCode)}</span>
                </div>
                ${instructorEmail}
            </div>`;
        resultsEl.classList.remove('hidden');
    }

    // ── QR scan callback ──
    function onScanSuccess(decodedText) {
        if (!scanning) return;
        scanning = false;

        const roomCode = extractRoomCode(decodedText);
        if (!roomCode) {
            statusEl.innerHTML = `<div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-lg">
                <div class="font-semibold">{{ __('scan.invalid_room_qr') }}</div>
                <div class="text-sm mt-1">{{ __('scan.scanned_value') }}: <code class="bg-yellow-100 px-1 rounded">${escapeHtml(decodedText)}</code></div>
            </div>`;
            setTimeout(() => { scanning = true; }, 3000);
            return;
        }

        manualInput.value = roomCode;
        markAttendance(roomCode);
    }

    // ── Manual entry ──
    manualBtn.addEventListener('click', function() {
        const val = (manualInput.value || '').trim();
        if (val === '') return;
        scanning = false;
        markAttendance(val);
    });
    manualInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            manualBtn.click();
        }
    });

    // ── Start camera scanner ──
    const html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        onScanSuccess
    ).catch(err => {
        document.getElementById('qr-reader').innerHTML =
            '<div class="p-8 text-gray-500">{{ __("scan.camera_error") }}</div>';
    });
});
</script>
@endpush
