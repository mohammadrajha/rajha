@extends('layouts.app')

@section('title', $classroom->exists ? __('classrooms.edit_title') : __('classrooms.create_title'))

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">
        {{ $classroom->exists ? __('classrooms.edit_title') : __('classrooms.create_title') }}
    </h1>

    <form method="POST"
        action="{{ $classroom->exists ? route('admin.classrooms.update', $classroom) : route('admin.classrooms.store') }}"
        class="bg-white rounded-xl shadow p-6">
        @csrf
        @if($classroom->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $classroom->name) }}" required
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.name_ar') }}</label>
                <input type="text" name="name_ar" value="{{ old('name_ar', $classroom->name_ar) }}" dir="rtl"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.building') }}</label>
                <input type="text" name="building" value="{{ old('building', $classroom->building) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.floor') }}</label>
                <input type="text" name="floor" value="{{ old('floor', $classroom->floor) }}"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.capacity') }}</label>
                <input type="number" name="capacity" value="{{ old('capacity', $classroom->capacity) }}" min="1"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.gps_radius') }}</label>
                <input type="number" name="gps_radius_meters" value="{{ old('gps_radius_meters', $classroom->gps_radius_meters ?? 100) }}" min="10" max="1000"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.latitude') }}</label>
                <input type="number" name="latitude" value="{{ old('latitude', $classroom->latitude) }}" step="any"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('classrooms.longitude') }}</label>
                <input type="number" name="longitude" value="{{ old('longitude', $classroom->longitude) }}" step="any"
                    class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        @if($classroom->exists)
        <div class="mt-4">
            <label class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $classroom->is_active ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 mr-2 rtl:ml-2 rtl:mr-0">
                <span class="text-sm text-gray-700">{{ __('classrooms.active') }}</span>
            </label>
        </div>
        @endif

        <div class="mt-6 flex justify-end space-x-3 rtl:space-x-reverse">
            <a href="{{ route('admin.classrooms.index') }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('table.save') }}</button>
        </div>
    </form>
</div>
@endsection
