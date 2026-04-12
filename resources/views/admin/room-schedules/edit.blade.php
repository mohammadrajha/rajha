@extends('layouts.app')

@section('title', __('rooms.edit_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('rooms.edit_title') }} #{{ $schedule->id }}</h1>

    <form method="POST" action="{{ route('admin.room-schedules.update', $schedule) }}" class="bg-white rounded-xl shadow p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.room_no') }}</label>
                <input type="text" name="room_no" value="{{ old('room_no', $schedule->room_no) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono" required>
                <p class="text-xs text-gray-500 mt-1">{{ __('rooms.room_no_hint') }}</p>
                @error('room_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.room_id') }}</label>
                <input type="number" name="room_id" value="{{ old('room_id', $schedule->room_id) }}"
                    class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-500 font-mono" readonly>
                <p class="text-xs text-gray-500 mt-1">{{ __('rooms.room_id_hint') }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.room_desc') }}</label>
                <input type="text" name="room_desc" value="{{ old('room_desc', $schedule->room_desc) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                @error('room_desc') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.day') }}</label>
                <input type="text" name="day" value="{{ old('day', $schedule->day) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500" required>
                @error('day') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.start_time') }}</label>
                <input type="text" name="start_time" value="{{ old('start_time', $schedule->start_time) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono" required>
                @error('start_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.end_time') }}</label>
                <input type="text" name="end_time" value="{{ old('end_time', $schedule->end_time) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono" required>
                @error('end_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.semester') }}</label>
                <input type="number" name="semester" value="{{ old('semester', $schedule->semester) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500" required>
                @error('semester') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.dept_no') }}</label>
                <input type="number" name="dept_no" value="{{ old('dept_no', $schedule->dept_no) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                @error('dept_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.course_name') }}</label>
                <input type="text" name="course_name" value="{{ old('course_name', $schedule->course_name) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                @error('course_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('rooms.instructor_name') }}</label>
                <input type="text" name="instructor_name" value="{{ old('instructor_name', $schedule->instructor_name) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                @error('instructor_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end space-x-3 rtl:space-x-reverse mt-6">
            <a href="{{ route('admin.room-schedules.index') }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('table.save') }}</button>
        </div>
    </form>
</div>
@endsection
