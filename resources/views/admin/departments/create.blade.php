@extends('layouts.app')

@section('title', __('departments.add'))

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('departments.add') }}</h1>

    <form method="POST" action="{{ route('admin.departments.store') }}" class="bg-white rounded-xl shadow p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.dept_no') }} *</label>
            <input type="number" name="dept_no" value="{{ old('dept_no') }}" required min="1"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
            @error('dept_no') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.dept_name') }}</label>
            <input type="text" name="dept_name" value="{{ old('dept_name') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('departments.dept_name_placeholder') }}">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.head_name') }}</label>
            <input type="text" name="head_name" value="{{ old('head_name') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('departments.head_name_placeholder') }}">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('departments.head_email') }}</label>
            <input type="email" name="head_email" value="{{ old('head_email') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('departments.head_email_placeholder') }}">
        </div>

        <div class="flex justify-end space-x-3 rtl:space-x-reverse">
            <a href="{{ route('admin.departments.index') }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('table.save') }}</button>
        </div>
    </form>
</div>
@endsection
