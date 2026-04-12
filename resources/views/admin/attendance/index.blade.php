@extends('layouts.app')

@section('title', __('nav.attendance'))

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('nav.attendance') }}</h1>

<!-- Filters -->
<div class="bg-white rounded-xl shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <select name="status" class="border rounded-lg px-3 py-2 text-sm">
            <option value="">{{ __('filters.all_statuses') }}</option>
            <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>{{ __('attendance.present') }}</option>
            <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>{{ __('attendance.late') }}</option>
            <option value="missed" {{ request('status') === 'missed' ? 'selected' : '' }}>{{ __('attendance.missed') }}</option>
            <option value="wrong_classroom" {{ request('status') === 'wrong_classroom' ? 'selected' : '' }}>{{ __('attendance.wrong_classroom') }}</option>
        </select>

        <input type="text" name="instructor_name" value="{{ request('instructor_name') }}" placeholder="{{ __('filters.instructor_name') }}" class="border rounded-lg px-3 py-2 text-sm">
        <input type="number" name="room_no" value="{{ request('room_no') }}" placeholder="{{ __('scan.room_number_placeholder') }}" class="border rounded-lg px-3 py-2 text-sm">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded-lg px-3 py-2 text-sm">
        <div class="flex space-x-2 rtl:space-x-reverse">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded-lg px-3 py-2 text-sm flex-1">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">{{ __('filters.filter') }}</button>
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.instructor') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.course') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('scan.room') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.status') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.delay') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $log->instructor_name }}</td>
                    <td class="px-4 py-3">{{ $log->course_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $log->room_no }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $log->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $log->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $log->status === 'missed' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $log->status === 'wrong_classroom' ? 'bg-orange-100 text-orange-700' : '' }}
                        ">{{ $log->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->delay_minutes > 0 ? $log->delay_minutes . ' min' : '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->scanned_at->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">{{ __('table.no_records') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
