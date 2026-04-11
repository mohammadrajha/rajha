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

        <!-- Manual room number input -->
        <div class="mb-6 max-w-xs mx-auto">
            <label class="block text-sm font-medium text-gray-600 mb-1">{{ __('scan.manual_entry') }}</label>
            <div class="flex space-x-2 rtl:space-x-reverse">
                <input type="number" id="manual-room" min="1" placeholder="{{ __('scan.room_number_placeholder') }}"
                    class="flex-1 border rounded-lg px-4 py-3 text-center text-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
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

    // ── Extract room number from QR text ──
    function extractRoomNo(text) {
        const trimmed = text.trim();

        // Pure number
        if (/^\d+$/.test(trimmed)) return parseInt(trimmed, 10);

        // URL containing /rooms/123 or /room/123
        const urlMatch = trimmed.match(/\/rooms?\/(\d+)/i);
        if (urlMatch) return parseInt(urlMatch[1], 10);

        // "Room 20", "room_20", "Room-20", etc.
        const labelMatch = trimmed.match(/room[\s_\-:#]*(\d+)/i);
        if (labelMatch) return parseInt(labelMatch[1], 10);

        return null;
    }

    // ── Fetch room schedule from our backend ──
    function fetchRoomSchedule(roomNo) {
        statusEl.innerHTML = `<div class="flex items-center justify-center space-x-2 rtl:space-x-reverse text-indigo-600">
            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="font-semibold">{{ __('scan.loading_schedule') }}</span>
        </div>`;
        resultsEl.classList.add('hidden');

        fetch('{{ route("room.schedule") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ room_no: roomNo })
        })
        .then(r => r.json())
        .then(data => {
            statusEl.innerHTML = '';

            if (data.status === 'error') {
                statusEl.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
                    <div class="text-2xl mb-1">&#9888;&#65039;</div>
                    <div class="font-semibold">${data.message}</div>
                </div>`;
                scanning = true;
                return;
            }

            renderSchedule(roomNo, data.schedules || []);
            scanning = true;
        })
        .catch(err => {
            statusEl.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
                <div class="font-semibold">{{ __('scan.error') }}</div>
            </div>`;
            scanning = true;
        });
    }

    // ── Render schedule table ──
    function renderSchedule(roomNo, schedules) {
        if (schedules.length === 0) {
            resultsEl.innerHTML = `
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <div class="text-4xl mb-3">&#128237;</div>
                    <h2 class="text-xl font-bold text-gray-700">{{ __('scan.room') }} ${roomNo}</h2>
                    <p class="text-gray-500 mt-2">{{ __('scan.no_schedule_found') }}</p>
                </div>`;
            resultsEl.classList.remove('hidden');
            return;
        }

        let rows = '';
        schedules.forEach(function(s) {
            rows += `
            <tr class="hover:bg-gray-50 border-b last:border-0">
                <td class="px-4 py-3">
                    <div class="font-semibold text-gray-800">${s.course_name}</div>
                    <div class="text-sm text-gray-500 mt-0.5">${s.instructor_name}</div>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-block bg-indigo-50 text-indigo-700 text-sm font-medium px-2.5 py-1 rounded-full">${s.day}</span>
                </td>
                <td class="px-4 py-3 text-center font-mono text-sm">
                    ${s.start_time} - ${s.end_time}
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-500">${s.dept_no}</td>
            </tr>`;
        });

        resultsEl.innerHTML = `
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-indigo-600 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">{{ __('scan.room') }} ${roomNo}</h2>
                        <p class="text-indigo-200 text-sm">{{ __('scan.semester') }}: 20252 &middot; ${schedules.length} {{ __('scan.lectures') }}</p>
                    </div>
                    <div class="text-3xl">&#127979;</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-start font-medium">{{ __('scan.course_instructor') }}</th>
                                <th class="px-4 py-3 text-center font-medium">{{ __('scan.day') }}</th>
                                <th class="px-4 py-3 text-center font-medium">{{ __('scan.time') }}</th>
                                <th class="px-4 py-3 text-center font-medium">{{ __('scan.dept') }}</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>`;
        resultsEl.classList.remove('hidden');
    }

    // ── QR scan callback ──
    function onScanSuccess(decodedText) {
        if (!scanning) return;
        scanning = false;

        const roomNo = extractRoomNo(decodedText);
        if (!roomNo) {
            statusEl.innerHTML = `<div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-lg">
                <div class="font-semibold">{{ __('scan.invalid_room_qr') }}</div>
                <div class="text-sm mt-1">{{ __('scan.scanned_value') }}: <code class="bg-yellow-100 px-1 rounded">${decodedText}</code></div>
            </div>`;
            setTimeout(() => { scanning = true; }, 3000);
            return;
        }

        manualInput.value = roomNo;
        fetchRoomSchedule(roomNo);
    }

    // ── Manual entry ──
    manualBtn.addEventListener('click', function() {
        const val = parseInt(manualInput.value, 10);
        if (!val || val < 1) return;
        fetchRoomSchedule(val);
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
