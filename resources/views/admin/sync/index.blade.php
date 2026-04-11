@extends('layouts.app')

@section('title', __('nav.sync'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">{{ __('nav.sync') }}</h1>
    <form method="POST" action="{{ route('admin.sync.now') }}">
        @csrf
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">
            {{ __('stats.sync_now') }}
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('sync.type') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.status') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('sync.synced') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('sync.failed') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('sync.started') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('sync.completed') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('sync.error') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium capitalize">{{ $log->type }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $log->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $log->status === 'partial' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        ">{{ $log->status }}</span>
                    </td>
                    <td class="px-4 py-3">{{ $log->records_synced }}</td>
                    <td class="px-4 py-3">{{ $log->records_failed }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->started_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->completed_at?->format('Y-m-d H:i') }}</td>
                    <td class="px-4 py-3 text-red-500 text-xs">{{ Str::limit($log->error_message, 50) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">{{ __('table.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $logs->links() }}</div>
@endsection
