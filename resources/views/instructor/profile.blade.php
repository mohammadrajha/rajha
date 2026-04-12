@extends('layouts.app')

@section('title', __('instructor.profile_title'))

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('instructor.profile_title') }}</h1>

    @if(!$user->instructor_name)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-6">
            {{ __('instructor.name_required_notice') }}
        </div>
    @endif

    <form method="POST" action="{{ route('instructor.profile.update') }}" class="bg-white rounded-xl shadow p-6">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('instructor.account_email') }}</label>
            <input type="text" value="{{ $user->email }}" disabled class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-500">
            <p class="text-xs text-gray-400 mt-1">{{ __('instructor.email_readonly') }}</p>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('instructor.your_name') }} *</label>
            <input type="text" name="instructor_name" value="{{ old('instructor_name', $user->instructor_name) }}" required
                class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 text-lg"
                placeholder="{{ __('instructor.name_placeholder') }}" dir="auto">
            @error('instructor_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-400 mt-1">{{ __('instructor.name_must_match') }}</p>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('table.save') }}</button>
        </div>
    </form>
</div>
@endsection
