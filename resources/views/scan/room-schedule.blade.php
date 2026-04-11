@extends('layouts.app')

@section('title', __('scan.room') . ' ' . $roomNo)

@section('content')
<div class="max-w-2xl mx-auto">
    @if($error)
        <div class="bg-red-50 border border-red-200 text-red-700 p-6 rounded-2xl text-center">
            <div class="text-3xl mb-2">&#9888;&#65039;</div>
            <div class="font-semibold text-lg">{{ $error }}</div>
        </div>
    @elseif(count($schedules) === 0)
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            <div class="text-4xl mb-3">&#128237;</div>
            <h2 class="text-xl font-bold text-gray-700">{{ __('scan.room') }} {{ $roomNo }}</h2>
            <p class="text-gray-500 mt-2">{{ __('scan.no_schedule_found') }}</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="bg-indigo-600 text-white px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold">{{ __('scan.room') }} {{ $roomNo }}</h2>
                    <p class="text-indigo-200 text-sm">{{ __('scan.semester') }}: 20252 &middot; {{ count($schedules) }} {{ __('scan.lectures') }}</p>
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
                    <tbody>
                        @foreach($schedules as $s)
                        <tr class="hover:bg-gray-50 border-b last:border-0">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800">{{ $s['course_name'] }}</div>
                                <div class="text-sm text-gray-500 mt-0.5">{{ $s['instructor_name'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block bg-indigo-50 text-indigo-700 text-sm font-medium px-2.5 py-1 rounded-full">{{ $s['day'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-mono text-sm">
                                {{ $s['start_time'] }} - {{ $s['end_time'] }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-500">{{ $s['dept_no'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-6 text-center">
        <a href="{{ route('scan.page') }}"
            class="inline-block bg-indigo-600 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-indigo-700 transition">
            {{ __('scan.scan_again') }}
        </a>
    </div>
</div>
@endsection
