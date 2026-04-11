@extends('layouts.app')

@section('title', $classroom->name)

@section('content')
<div class="flex justify-between items-start mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $classroom->name }}</h1>
        @if($classroom->name_ar)
            <p class="text-gray-500">{{ $classroom->name_ar }}</p>
        @endif
    </div>
    <div class="flex space-x-2 rtl:space-x-reverse">
        <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200">{{ __('table.edit') }}</a>
        <a href="{{ route('admin.classrooms.print-qr', $classroom) }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-purple-700">{{ __('classrooms.print_qr') }}</a>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Info -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-semibold text-gray-800 mb-4">{{ __('classrooms.details') }}</h2>
        <dl class="space-y-3 text-sm">
            <div><dt class="text-gray-500">{{ __('classrooms.building') }}</dt><dd class="font-medium">{{ $classroom->building ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('classrooms.floor') }}</dt><dd class="font-medium">{{ $classroom->floor ?? '-' }}</dd></div>
            <div><dt class="text-gray-500">{{ __('classrooms.capacity') }}</dt><dd class="font-medium">{{ $classroom->capacity ?? '-' }}</dd></div>
            <div>
                <dt class="text-gray-500">{{ __('classrooms.status') }}</dt>
                <dd><span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $classroom->is_active ? __('classrooms.active') : __('classrooms.inactive') }}
                </span></dd>
            </div>
            @if($classroom->latitude && $classroom->longitude)
            <div><dt class="text-gray-500">{{ __('classrooms.gps') }}</dt><dd class="font-medium text-xs">{{ $classroom->latitude }}, {{ $classroom->longitude }}</dd></div>
            @endif
        </dl>

        <div class="mt-4 pt-4 border-t">
            <form method="POST" action="{{ route('admin.classrooms.regenerate-token', $classroom) }}"
                onsubmit="return confirm('{{ __('classrooms.regenerate_confirm') }}')">
                @csrf
                <button class="text-sm text-red-600 hover:underline">{{ __('classrooms.regenerate_token') }}</button>
            </form>
        </div>
    </div>

    <!-- QR Code -->
    <div class="bg-white rounded-xl shadow p-6 text-center">
        <h2 class="font-semibold text-gray-800 mb-4">{{ __('classrooms.qr_code') }}</h2>
        <div class="inline-block p-4 bg-white border-2 rounded-lg">
            {!! $qrSvg !!}
        </div>
    </div>

    <!-- Schedules -->
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="font-semibold text-gray-800 mb-4">{{ __('classrooms.assigned_schedules') }}</h2>
        @forelse($classroom->schedules as $schedule)
            <div class="border-b last:border-0 py-2 text-sm">
                <div class="font-medium">{{ $schedule->course_name }}</div>
                <div class="text-gray-500">{{ $schedule->instructor->name }}</div>
                <div class="text-gray-400 text-xs">
                    {{ \App\Models\Schedule::dayName($schedule->day_of_week) }}
                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                </div>
            </div>
        @empty
            <p class="text-gray-400 text-sm">{{ __('classrooms.no_schedules') }}</p>
        @endforelse
    </div>
</div>
@endsection
