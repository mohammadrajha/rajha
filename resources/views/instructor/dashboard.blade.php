@extends('layouts.app')

@section('title', __('nav.instructor_dashboard'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ __('messages.welcome') }}, {{ $instructor->localizedName() }}</h1>
        <p class="text-gray-500">{{ now()->translatedFormat('l, F j, Y') }}</p>
    </div>
    <a href="{{ route('scan.page') }}"
        class="bg-indigo-600 text-white px-6 py-3 rounded-xl text-lg font-semibold hover:bg-indigo-700 shadow-lg">
        📱 {{ __('nav.scan') }}
    </a>
</div>

<!-- Monthly Stats -->
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-green-600">{{ $monthlyStats['present'] }}</div>
        <div class="text-sm text-green-700 mt-1">{{ __('stats.present') }}</div>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-yellow-600">{{ $monthlyStats['late'] }}</div>
        <div class="text-sm text-yellow-700 mt-1">{{ __('stats.late') }}</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-red-600">{{ $monthlyStats['missed'] }}</div>
        <div class="text-sm text-red-700 mt-1">{{ __('stats.missed') }}</div>
    </div>
</div>

<!-- Today's Schedule -->
<div class="bg-white rounded-xl shadow mb-8">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('instructor.today_schedule') }}</h2>
    </div>
    <div class="p-4">
        @forelse($todaySchedules as $schedule)
            <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b' : '' }}">
                <div class="flex-1">
                    <div class="font-medium text-gray-800">{{ $schedule->localizedCourseName() }}</div>
                    <div class="text-sm text-gray-500">
                        {{ $schedule->classroom->fullName() }}
                    </div>
                    <div class="text-sm text-gray-400">
                        {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                    </div>
                </div>
                <div>
                    @if(in_array($schedule->id, $todayAttendance))
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-700">
                            ✅ {{ __('attendance.present') }}
                        </span>
                    @elseif($schedule->isNow())
                        <a href="{{ route('scan.page') }}"
                            class="inline-flex px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 animate-pulse">
                            {{ __('instructor.scan_now') }}
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">{{ __('instructor.upcoming') }}</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-center py-4">{{ __('instructor.no_classes_today') }}</p>
        @endforelse
    </div>
</div>

<!-- Recent Activity -->
<div class="bg-white rounded-xl shadow">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('instructor.recent_activity') }}</h2>
    </div>
    <div class="divide-y">
        @forelse($recentLogs as $log)
            <div class="px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium">{{ $log->schedule?->course_name ?? '-' }}</div>
                    <div class="text-xs text-gray-400">{{ $log->classroom->name }} - {{ $log->scanned_at->format('M d, H:i') }}</div>
                </div>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                    {{ $log->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $log->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $log->status === 'missed' ? 'bg-red-100 text-red-700' : '' }}
                ">
                    {{ $log->statusLabel() }}
                </span>
            </div>
        @empty
            <p class="text-gray-400 text-center py-4">{{ __('table.no_records') }}</p>
        @endforelse
    </div>
</div>
@endsection
