@extends('layouts.app')

@section('title', __('nav.departments'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('nav.departments') }}</h1>
    <a href="{{ route('admin.departments.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
        + {{ __('departments.add') }}
    </a>
</div>

<p class="text-gray-500 text-sm mb-4">{{ __('departments.description') }}</p>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('departments.dept_no') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('departments.dept_name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('departments.head_name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('departments.head_email') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($departments as $dept)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-bold">{{ $dept->dept_no }}</td>
                    <td class="px-4 py-3">{{ $dept->dept_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $dept->head_name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($dept->head_email)
                            <span class="text-green-600">{{ $dept->head_email }}</span>
                        @else
                            <span class="text-red-400">{{ __('departments.not_set') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.departments.edit', $dept) }}" class="text-indigo-600 hover:underline text-xs">{{ __('table.edit') }}</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ __('departments.empty') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
