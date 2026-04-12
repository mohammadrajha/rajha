@extends('layouts.app')

@section('title', __('departments.edit_title'))

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('departments.edit_title') }} - #{{ $department->dept_no }}</h1>

    <form method="POST" action="{{ route('admin.departments.update', $department) }}" class="bg-white rounded-xl shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.dept_no') }}</label>
            <input type="text" value="{{ $department->dept_no }}" disabled class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-500">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.dept_name') }}</label>
            <input type="text" name="dept_name" value="{{ old('dept_name', $department->dept_name) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('departments.dept_name_placeholder') }}">
            @error('dept_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.head_name') }}</label>
            <input type="text" name="head_name" value="{{ old('head_name', $department->head_name) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('departments.head_name_placeholder') }}">
            @error('head_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.head_email') }}</label>
            <input type="email" name="head_email" value="{{ old('head_email', $department->head_email) }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('departments.head_email_placeholder') }}">
            @error('head_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end space-x-3 rtl:space-x-reverse">
            <a href="{{ route('admin.departments.index') }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('table.save') }}</button>
        </div>
    </form>
</div>
@endsection
