@extends('layouts.app')

@section('title', __('nav.classrooms'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('nav.classrooms') }}</h1>
    <a href="{{ route('admin.classrooms.create') }}"
        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm">
        + {{ __('classrooms.add') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('classrooms.name') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('classrooms.building') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('classrooms.capacity') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('classrooms.schedules') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('classrooms.status') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($classrooms as $classroom)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $classroom->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $classroom->building ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $classroom->capacity ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $classroom->schedules_count }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $classroom->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $classroom->is_active ? __('classrooms.active') : __('classrooms.inactive') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex space-x-2 rtl:space-x-reverse">
                            <a href="{{ route('admin.classrooms.show', $classroom) }}" class="text-indigo-600 hover:underline text-xs">{{ __('table.view') }}</a>
                            <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="text-gray-600 hover:underline text-xs">{{ __('table.edit') }}</a>
                            <a href="{{ route('admin.classrooms.print-qr', $classroom) }}" class="text-purple-600 hover:underline text-xs">{{ __('classrooms.qr') }}</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">{{ __('table.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $classrooms->links() }}</div>
@endsection
