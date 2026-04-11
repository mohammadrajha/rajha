@extends('layouts.app')

@section('title', $instructor->name . ' - ' . __('hod.report'))

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $instructor->localizedName() }}</h1>
        <p class="text-gray-500">{{ $instructor->email }}</p>
    </div>
    <a href="{{ route('hod.dashboard') }}" class="text-indigo-600 hover:underline text-sm">&larr; {{ __('table.back') }}</a>
</div>

<!-- Stats -->
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-green-600">{{ $stats['present'] }}</div>
        <div class="text-sm text-green-700 mt-1">{{ __('stats.present') }}</div>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-yellow-600">{{ $stats['late'] }}</div>
        <div class="text-sm text-yellow-700 mt-1">{{ __('stats.late') }}</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-red-600">{{ $stats['missed'] }}</div>
        <div class="text-sm text-red-700 mt-1">{{ __('stats.missed') }}</div>
    </div>
</div>

<!-- Chart -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('hod.attendance_breakdown') }}</h2>
    <canvas id="pieChart" height="80"></canvas>
</div>

<!-- Logs -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.course') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.classroom') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.status') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.delay') }}</th>
                    <th class="px-4 py-3 text-start font-medium text-gray-600">{{ __('table.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $log->schedule?->course_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $log->classroom->name }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $log->status === 'present' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $log->status === 'late' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $log->status === 'missed' ? 'bg-red-100 text-red-700' : '' }}
                        ">
                            {{ $log->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->delay_minutes > 0 ? $log->delay_minutes . ' min' : '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $log->scanned_at->format('Y-m-d H:i') }}</td>
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

<div class="mt-4">{{ $logs->links() }}</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('pieChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['{{ __("stats.present") }}', '{{ __("stats.late") }}', '{{ __("stats.missed") }}'],
        datasets: [{
            data: [{{ $stats['present'] }}, {{ $stats['late'] }}, {{ $stats['missed'] }}],
            backgroundColor: ['rgba(34, 197, 94, 0.8)', 'rgba(234, 179, 8, 0.8)', 'rgba(239, 68, 68, 0.8)'],
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
@endpush
