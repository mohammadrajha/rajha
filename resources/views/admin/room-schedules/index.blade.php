@extends('layouts.app')

@section('title', __('nav.rooms'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('nav.rooms') }}</h1>
    <a href="{{ route('admin.sync-rooms.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
        {{ __('rooms.sync_now') }}
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow p-4 mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <input type="text" name="room_no" value="{{ request('room_no') }}"
            placeholder="{{ __('rooms.room_no') }}"
            class="border rounded-lg px-3 py-2 text-sm">
        <input type="text" name="instructor_name" value="{{ request('instructor_name') }}"
            placeholder="{{ __('rooms.instructor_name') }}"
            class="border rounded-lg px-3 py-2 text-sm">
        <div class="flex space-x-2 rtl:space-x-reverse">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">{{ __('filters.filter') }}</button>
            @if(request()->hasAny(['room_no', 'instructor_name']))
                <a href="{{ route('admin.room-schedules.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
            @endif
        </div>
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.room_no') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.room_desc') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.day') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.start_time') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.end_time') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.semester') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.dept_no') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.course_name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('rooms.instructor_name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($schedules as $schedule)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-bold">
                        {{ $schedule->room_no }}
                        @if($schedule->room_id)
                            <div class="text-[10px] font-normal text-gray-400">ID: {{ $schedule->room_id }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $schedule->room_desc ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $schedule->day }}</td>
                    <td class="px-4 py-3 font-mono text-gray-600">{{ $schedule->start_time }}</td>
                    <td class="px-4 py-3 font-mono text-gray-600">{{ $schedule->end_time }}</td>
                    <td class="px-4 py-3">{{ $schedule->semester }}</td>
                    <td class="px-4 py-3">{{ $schedule->dept_no ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $schedule->course_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $schedule->instructor_name ?? '-' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ route('admin.room-schedules.edit', $schedule) }}" class="text-indigo-600 hover:underline text-xs mr-3 rtl:ml-3 rtl:mr-0">{{ __('table.edit') }}</a>
                        <form method="POST" action="{{ route('admin.room-schedules.destroy', $schedule) }}" class="inline"
                              onsubmit="return confirm('{{ __('rooms.confirm_delete') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-xs">{{ __('table.delete') }}</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-4 py-8 text-center text-gray-400">{{ __('table.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $schedules->links() }}</div>
@endsection
