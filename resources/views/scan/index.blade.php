@extends('layouts.app')

@section('title', __('scan.title'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
        <div class="text-6xl mb-4">📱</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('scan.title') }}</h1>
        <p class="text-gray-500 mb-8">{{ __('scan.instructions') }}</p>

        <!-- Camera QR Scanner -->
        <div id="qr-reader" class="mb-6 rounded-lg overflow-hidden border-2 border-dashed border-gray-300" style="min-height: 300px;"></div>

        <!-- Manual payload input (fallback) -->
        <form method="POST" action="{{ route('scan.submit') }}" id="scan-form" class="hidden">
            @csrf
            <input type="hidden" name="payload" id="payload-input">
            <input type="hidden" name="latitude" id="lat-input">
            <input type="hidden" name="longitude" id="lng-input">
        </form>

        <!-- Status -->
        <div id="scan-status" class="mt-4 text-lg font-semibold"></div>

        <div class="mt-6">
            <p class="text-xs text-gray-400">{{ __('scan.camera_permission') }}</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusEl = document.getElementById('scan-status');
    let scanning = true;

    // Get GPS location
    let latitude = null, longitude = null;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            latitude = pos.coords.latitude;
            longitude = pos.coords.longitude;
        }, function() {
            console.log('GPS not available');
        });
    }

    function onScanSuccess(decodedText) {
        if (!scanning) return;
        scanning = false;

        statusEl.innerHTML = '<span class="text-indigo-600">{{ __("scan.processing") }}</span>';

        // Extract payload from URL or use directly
        let payload = decodedText;
        const urlMatch = decodedText.match(/\/scan\/(.+)$/);
        if (urlMatch) {
            payload = urlMatch[1];
        }

        // Submit via fetch
        fetch('{{ route("scan.submit") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payload: payload,
                latitude: latitude,
                longitude: longitude,
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'present') {
                statusEl.innerHTML = `<div class="bg-green-100 text-green-700 p-4 rounded-lg">
                    <div class="text-3xl mb-2">✅</div>
                    <div class="text-xl font-bold">${data.message}</div>
                </div>`;
            } else if (data.status === 'late') {
                statusEl.innerHTML = `<div class="bg-yellow-100 text-yellow-700 p-4 rounded-lg">
                    <div class="text-3xl mb-2">⏱</div>
                    <div class="text-xl font-bold">${data.message}</div>
                </div>`;
            } else if (data.status === 'wrong_classroom') {
                statusEl.innerHTML = `<div class="bg-red-100 text-red-700 p-4 rounded-lg">
                    <div class="text-3xl mb-2">❌</div>
                    <div class="text-xl font-bold">${data.message}</div>
                    ${data.correct_classroom ? '<div class="mt-2">{{ __("scan.correct_classroom") }}: ' + data.correct_classroom + '</div>' : ''}
                </div>`;
            } else if (data.status === 'duplicate') {
                statusEl.innerHTML = `<div class="bg-blue-100 text-blue-700 p-4 rounded-lg">
                    <div class="text-3xl mb-2">ℹ️</div>
                    <div class="text-xl font-bold">${data.message}</div>
                </div>`;
            } else {
                statusEl.innerHTML = `<div class="bg-gray-100 text-gray-700 p-4 rounded-lg">
                    <div class="text-3xl mb-2">⚠️</div>
                    <div class="text-xl font-bold">${data.message}</div>
                </div>`;
            }

            // Allow re-scan after 5 seconds
            setTimeout(() => { scanning = true; }, 5000);
        })
        .catch(err => {
            statusEl.innerHTML = '<span class="text-red-600">{{ __("scan.error") }}</span>';
            scanning = true;
        });
    }

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
