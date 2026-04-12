@extends('layouts.app')

@section('title', __('instructor_emails.title'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('instructor_emails.title') }}</h1>
    <a href="{{ route('admin.instructor-emails.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
        {{ __('instructor_emails.add_new') }}
    </a>
</div>

<p class="text-gray-500 text-sm mb-4">{{ __('instructor_emails.description') }}</p>

<!-- Filter -->
<div class="bg-white rounded-xl shadow p-4 mb-6">
    <form method="GET" class="flex space-x-2 rtl:space-x-reverse">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="{{ __('instructor_emails.search_placeholder') }}"
            class="flex-1 border rounded-lg px-3 py-2 text-sm">
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">{{ __('filters.filter') }}</button>
        @if(request('search'))
            <a href="{{ route('admin.instructor-emails.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">{{ __('table.cancel') }}</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('instructor_emails.account_name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('instructor_emails.email') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('instructor_emails.instructor_name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($instructors as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $user->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @if($user->instructor_name)
                            {{ $user->instructor_name }}
                        @else
                            <span class="text-red-400">{{ __('instructor_emails.not_linked') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-3 rtl:space-x-reverse">
                            <a href="{{ route('admin.instructor-emails.edit', $user) }}"
                               class="text-indigo-600 hover:underline text-xs">{{ __('table.edit') }}</a>
                            <form method="POST" action="{{ route('admin.instructor-emails.destroy', $user) }}"
                                  onsubmit="return confirm('{{ __('instructor_emails.confirm_delete') }}');"
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">{{ __('table.delete') }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">{{ __('instructor_emails.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $instructors->links() }}</div>
@endsection
