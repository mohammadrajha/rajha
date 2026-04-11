@extends('layouts.app')

@section('title', __('nav.admin_dashboard'))

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('nav.admin_dashboard') }}</h1>

<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow p-5">
        <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_instructors'] }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ __('stats.instructors') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_classrooms'] }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ __('stats.classrooms') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_departments'] }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ __('stats.departments') }}</div>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <div class="text-3xl font-bold text-indigo-600">{{ $stats['total_schedules'] }}</div>
        <div class="text-sm text-gray-500 mt-1">{{ __('stats.active_schedules') }}</div>
    </div>
</div>

<!-- Today's Summary -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-green-50 border border-green-200 rounded-xl p-5">
        <div class="text-3xl font-bold text-green-600">{{ $stats['today_present'] }}</div>
        <div class="text-sm text-green-700 mt-1">{{ __('stats.today_present') }}</div>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5">
        <div class="text-3xl font-bold text-yellow-600">{{ $stats['today_late'] }}</div>
        <div class="text-sm text-yellow-700 mt-1">{{ __('stats.today_late') }}</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-5">
        <div class="text-3xl font-bold text-red-600">{{ $stats['today_missed'] }}</div>
        <div class="text-sm text-red-700 mt-1">{{ __('stats.today_missed') }}</div>
    </div>
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-5">
        <div class="text-3xl font-bold text-orange-600">{{ $stats['today_wrong'] }}</div>
        <div class="text-sm text-orange-700 mt-1">{{ __('stats.today_wrong') }}</div>
    </div>
</div>

<!-- Weekly Chart -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('stats.weekly_overview') }}</h2>
    <canvas id="weeklyChart" height="100"></canvas>
</div>

<!-- Sync Status -->
@if($lastSync)
<div class="bg-white rounded-xl shadow p-4 mb-8 flex items-center justify-between">
    <div>
        <span class="text-sm text-gray-500">{{ __('stats.last_sync') }}:</span>
        <span class="text-sm font-medium {{ $lastSync->status === 'success' ? 'text-green-600' : 'text-red-600' }}">
            {{ $lastSync->completed_at?->diffForHumans() ?? '-' }} ({{ $lastSync->status }})
        </span>
    </div>
    <form method="POST" action="{{ route('admin.sync.now') }}">
        @csrf
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">{{ __('stats.sync_now') }}</button>
    </form>
</div>
@endif

<!-- Recent Logs -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">{{ __('stats.recent_activity') }}</h2>
        <a href="{{ route('admin.attendance.index') }}" class="text-indigo-600 text-sm hover:underline">{{ __('stats.view_all') }}</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.instructor') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.course') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.classroom') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.status') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.time') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentLogs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $log->instructor->name }}</td>
                    <td class="px-4 py-3">{{ $log->schedule?->course_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $log->classroom->name }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $log->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $log->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $log->status === 'missed' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $log->status === 'wrong_classroom' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $log->status === 'no_lecture' ? 'bg-gray-100 text-gray-700' : '' }}
                        ">
                            {{ $log->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->scanned_at->format('H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">{{ __('table.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($weeklyStats, 'date')) !!},
        datasets: [
            {
                label: '{{ __("stats.present") }}',
                data: {!! json_encode(array_column($weeklyStats, 'present')) !!},
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
            },
            {
                label: '{{ __("stats.late") }}',
                data: {!! json_encode(array_column($weeklyStats, 'late')) !!},
                backgroundColor: 'rgba(234, 179, 8, 0.8)',
            },
            {
                label: '{{ __("stats.missed") }}',
                data: {!! json_encode(array_column($weeklyStats, 'missed')) !!},
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
            }
        ]
    },
    options: {
        responsive: true,
        scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
@endpush
