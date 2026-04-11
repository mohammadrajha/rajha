@extends('layouts.app')

@section('title', __('attendance.details'))

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('attendance.details') }}</h1>

    <div class="bg-white rounded-xl shadow p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">{{ __('table.instructor') }}</dt>
                <dd class="font-medium text-lg">{{ $log->instructor->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('table.status') }}</dt>
                <dd>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full
                        {{ $log->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $log->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $log->status === 'missed' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $log->status === 'wrong_classroom' ? 'bg-orange-100 text-orange-700' : '' }}
                    ">
                        {{ $log->statusLabel() }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('table.course') }}</dt>
                <dd class="font-medium">{{ $log->schedule?->course_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('table.classroom') }}</dt>
                <dd class="font-medium">{{ $log->classroom->name }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('table.time') }}</dt>
                <dd class="font-medium">{{ $log->scanned_at->format('H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('table.date') }}</dt>
                <dd class="font-medium">{{ $log->scanned_at->format('Y-m-d') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">{{ __('table.delay') }}</dt>
                <dd class="font-medium">{{ $log->delay_minutes }} {{ __('scan.minutes') }}</dd>
            </div>
            @if($log->gps_valid !== null)
            <div>
                <dt class="text-gray-500">{{ __('attendance.gps_valid') }}</dt>
                <dd class="font-medium">{{ $log->gps_valid ? '✅' : '❌' }}</dd>
            </div>
            @endif
            @if($log->notes)
            <div class="col-span-2">
                <dt class="text-gray-500">{{ __('attendance.notes') }}</dt>
                <dd class="font-medium">{{ $log->notes }}</dd>
            </div>
            @endif
            <div>
                <dt class="text-gray-500">IP</dt>
                <dd class="font-medium text-xs text-gray-400">{{ $log->ip_address ?? '-' }}</dd>
            </div>
        </dl>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.attendance.index') }}" class="text-indigo-600 hover:underline text-sm">&larr; {{ __('table.back') }}</a>
    </div>
</div>
@endsection
