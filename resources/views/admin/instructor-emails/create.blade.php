@extends('layouts.app')

@section('title', __('instructor_emails.create_title'))

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('instructor_emails.create_title') }}</h1>

    <form method="POST" action="{{ route('admin.instructor-emails.store') }}" class="bg-white rounded-xl shadow p-6">
        @csrf

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('instructor_emails.account_name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500" required>
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('instructor_emails.email') }}</label>
            <input type="email" name="email" value="{{ old('email') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono text-sm" required>
            <p class="text-xs text-gray-500 mt-1">{{ __('instructor_emails.email_hint') }}</p>
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('instructor_emails.instructor_name') }}</label>
            <input type="text" name="instructor_name" value="{{ old('instructor_name') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                placeholder="{{ __('instructor_emails.instructor_name_placeholder') }}">
            <p class="text-xs text-gray-500 mt-1">{{ __('instructor_emails.instructor_name_hint') }}</p>
            @error('instructor_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('instructor_emails.password') }}</label>
            <input type="text" name="password" value="{{ old('password') }}"
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 font-mono text-sm"
                placeholder="{{ __('instructor_emails.password_placeholder') }}">
            <p class="text-xs text-gray-500 mt-1">{{ __('instructor_emails.password_hint') }}</p>
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end space-x-3 rtl:space-x-reverse">
            <a href="{{ route('admin.instructor-emails.index') }}" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('table.save') }}</button>
        </div>
    </form>
</div>
@endsection
