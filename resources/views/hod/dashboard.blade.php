@extends('layouts.app')

@section('title', __('nav.hod_dashboard'))

@section('content')
<h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('nav.hod_dashboard') }}</h1>
<p class="text-gray-500 mb-6">{{ $department->localizedName() }}</p>

<!-- Today Stats -->
<div class="grid grid-cols-3 gap-4 mb-8">
    <div class="bg-green-50 border border-green-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-green-600">{{ $todayStats['present'] }}</div>
        <div class="text-sm text-green-700 mt-1">{{ __('stats.today_present') }}</div>
    </div>
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-yellow-600">{{ $todayStats['late'] }}</div>
        <div class="text-sm text-yellow-700 mt-1">{{ __('stats.today_late') }}</div>
    </div>
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 text-center">
        <div class="text-3xl font-bold text-red-600">{{ $todayStats['missed'] }}</div>
        <div class="text-sm text-red-700 mt-1">{{ __('stats.today_missed') }}</div>
    </div>
</div>

<!-- Weekly Chart -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('stats.weekly_overview') }}</h2>
    <canvas id="weeklyChart" height="100"></canvas>
</div>

<!-- Monthly Summary -->
<div class="bg-white rounded-xl shadow p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">{{ __('stats.monthly_summary') }}</h2>
    <div class="grid grid-cols-3 gap-4 text-center">
        <div>
            <div class="text-2xl font-bold text-green-600">{{ $monthlyStats['present'] }}</div>
            <div class="text-sm text-gray-500">{{ __('stats.present') }}</div>
        </div>
        <div>
            <div class="text-2xl font-bold text-yellow-600">{{ $monthlyStats['late'] }}</div>
            <div class="text-sm text-gray-500">{{ __('stats.late') }}</div>
        </div>
        <div>
            <div class="text-2xl font-bold text-red-600">{{ $monthlyStats['missed'] }}</div>
            <div class="text-sm text-gray-500">{{ __('stats.missed') }}</div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Instructors -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('hod.instructors') }} ({{ $instructors->count() }})</h2>
        </div>
        <div class="divide-y max-h-96 overflow-y-auto">
            @foreach($instructors as $inst)
                <a href="{{ route('hod.instructor.report', $inst) }}" class="block px-4 py-3 hover:bg-gray-50">
                    <div class="font-medium text-gray-800">{{ $inst->localizedName() }}</div>
                    <div class="text-xs text-gray-400">{{ $inst->email }}</div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Recent Alerts -->
    <div class="bg-white rounded-xl shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold text-gray-800">{{ __('hod.recent_alerts') }}</h2>
        </div>
        <div class="divide-y max-h-96 overflow-y-auto">
            @forelse($recentAlerts as $alert)
                <div class="px-4 py-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-sm font-medium">{{ $alert->instructor->name }}</div>
                            <div class="text-xs text-gray-500">{{ $alert->schedule?->course_name ?? '-' }} - {{ $alert->classroom->name }}</div>
                        </div>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            {{ $alert->status === 'late' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700' }}">
                            {{ $alert->statusLabel() }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        {{ $alert->scanned_at->format('M d, H:i') }}
                        @if($alert->delay_minutes > 0)
                            ({{ $alert->delay_minutes }} min)
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-center py-4">{{ __('hod.no_alerts') }}</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($weeklyData, 'date')) !!},
        datasets: [
            {
                label: '{{ __("stats.present") }}',
                data: {!! json_encode(array_column($weeklyData, 'present')) !!},
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                fill: true,
                tension: 0.3,
            },
            {
                label: '{{ __("stats.late") }}',
                data: {!! json_encode(array_column($weeklyData, 'late')) !!},
                borderColor: 'rgb(234, 179, 8)',
                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                fill: true,
                tension: 0.3,
            },
            {
                label: '{{ __("stats.missed") }}',
                data: {!! json_encode(array_column($weeklyData, 'missed')) !!},
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.3,
            }
        ]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
@endpush
