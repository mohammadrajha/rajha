@extends('layouts.app')

@section('title', __('scan.result_title'))

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
        @php
            $status = $result['status'] ?? 'error';
            $colors = [
                'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'icon' => '✅'],
                'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => '⏱'],
                'wrong_classroom' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => '❌'],
                'no_lecture' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'icon' => '📭'],
                'duplicate' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'icon' => 'ℹ️'],
                'error' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'icon' => '⚠️'],
            ];
            $c = $colors[$status] ?? $colors['error'];
        @endphp

        <div class="{{ $c['bg'] }} {{ $c['text'] }} p-8 rounded-xl">
            <div class="text-5xl mb-4">{{ $c['icon'] }}</div>
            <div class="text-xl font-bold">{{ $result['message'] ?? __('scan.error') }}</div>

            @if(isset($result['correct_classroom']))
                <div class="mt-3 text-base">
                    {{ __('scan.correct_classroom') }}: <strong>{{ $result['correct_classroom'] }}</strong>
                </div>
            @endif

            @if(isset($result['delay_minutes']))
                <div class="mt-2 text-base">
                    {{ __('scan.delay') }}: {{ $result['delay_minutes'] }} {{ __('scan.minutes') }}
                </div>
            @endif
        </div>

        @if($classroom)
            <div class="mt-6 text-sm text-gray-500">
                {{ __('scan.scanned_classroom') }}: {{ $classroom->fullName() }}
            </div>
        @endif

        <div class="mt-8">
            <a href="{{ route('scan.page') }}"
                class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition">
                {{ __('scan.scan_again') }}
            </a>
        </div>
    </div>
</div>
@endsection
