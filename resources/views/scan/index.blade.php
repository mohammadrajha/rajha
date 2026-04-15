@extends('layouts.app')

@section('title', __('scan.title'))

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 text-center">
        <div class="text-5xl mb-3">📱</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-1">{{ __('scan.title') }}</h1>
        <p class="text-gray-500 mb-6">{{ __('scan.direct_hint') }}</p>

        {{-- Manual fallback: instructors only land here if they typed /scan directly.
             The printed room QR encodes an absolute URL to /scan/{roomNo}, so normal
             scanning with any camera app skips this page entirely. --}}
        <div class="max-w-xs mx-auto">
            <label for="manual-room" class="block text-sm font-medium text-gray-600 mb-1">
                {{ __('scan.manual_entry') }}
            </label>
            <form id="manual-form" action="{{ url('/scan') }}" method="get"
                  class="flex space-x-2 rtl:space-x-reverse">
                <input type="text" id="manual-room" name="room_no"
                       inputmode="numeric" autocomplete="off"
                       pattern="[A-Za-z0-9_\-]+"
                       placeholder="{{ __('scan.room_number_placeholder') }}"
                       class="flex-1 border rounded-lg px-4 py-3 text-center text-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono">
                <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    {{ __('scan.go') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Rewrite the manual form submit into a direct GET /scan/{roomNo} so it
// lands on the exact same direct-scan route the printed QR uses. This
// keeps the codebase with a single attendance entry point.
document.addEventListener('DOMContentLoaded', function () {
    const form  = document.getElementById('manual-form');
    const input = document.getElementById('manual-room');
    if (!form || !input) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const raw = (input.value || '').trim();
        // Match the server-side route constraint so a typo never 404s.
        const code = raw.replace(/[^A-Za-z0-9_\-]/g, '');
        if (code === '') return;
        window.location.href = '{{ url('/scan') }}/' + encodeURIComponent(code);
    });
});
</script>
@endpush
