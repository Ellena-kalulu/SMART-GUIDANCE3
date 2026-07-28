@extends('layouts.dashboard')

@section('title', 'System Analytics')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.analytics.index') }}" class="text-black/50 hover:text-blue-600">Analytics</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">System Analytics</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">⚙️ System Analytics</h1>
        <p class="text-black/30">System performance, usage metrics, and engagement analytics</p>
    </div>

    <!-- Engagement Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Avg Sessions/User</p>
                <p class="text-3xl font-bold text-black/80">{{ number_format($engagementMetrics['avg_sessions_per_user'], 1) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Avg Assessments/Student</p>
                <p class="text-3xl font-bold text-black/80">{{ number_format($engagementMetrics['avg_assessments_per_student'], 1) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Avg Recommendations/Student</p>
                <p class="text-3xl font-bold text-black/80">{{ number_format($engagementMetrics['avg_recommendations_per_student'], 1) }}</p>
            </div>
        </div>
    </div>

    <!-- Daily Active Users & Page Views -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">📊 Daily Active Users (Last 30 Days)</h3>
            <canvas id="dailyActiveChart" height="250"></canvas>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">👁️ Daily Page Views (Last 30 Days)</h3>
            <canvas id="dailyViewsChart" height="250"></canvas>
        </div>
    </div>

    <!-- Peak Usage Hours & Popular Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">⏰ Peak Usage Hours</h3>
            <canvas id="peakHoursChart" height="250"></canvas>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">🔥 Most Popular Actions</h3>
            <canvas id="actionsChart" height="250"></canvas>
            <div class="mt-4 space-y-2">
                @foreach($popularActions->take(5) as $action)
                <div class="flex justify-between items-center">
                    <span class="text-sm capitalize">{{ str_replace('_', ' ', $action->action) }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($action->count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Feature Usage -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
        <h3 class="font-bold text-black/80 mb-4">🎯 Feature Usage Breakdown</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div class="text-center p-4 bg-blue-50 rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($featureUsage['assessments']) }}</div>
                <div class="text-xs text-black/50 mt-1">Assessments</div>
            </div>
            <div class="text-center p-4 text-blue-50 rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($featureUsage['recommendations_viewed']) }}</div>
                <div class="text-xs text-black/50 mt-1">Recommendations Viewed</div>
            </div>
            <div class="text-center p-4 text-blue-50 rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($featureUsage['recommendations_saved']) }}</div>
                <div class="text-xs text-black/50 mt-1">Recommendations Saved</div>
            </div>
            <div class="text-center p-4 text-blue-50 rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($featureUsage['reports_downloaded']) }}</div>
                <div class="text-xs text-black/50 mt-1">Reports Downloaded</div>
            </div>
            <div class="text-center p-4 text-blue-50 rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($featureUsage['profile_updates']) }}</div>
                <div class="text-xs text-black/50 mt-1">Profile Updates</div>
            </div>
        </div>
    </div>

    <!-- Most Active Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 bg-white">
            <h3 class="font-bold text-black/80">⭐ Top 10 Most Active Users</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Activity Count</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @foreach($mostActiveUsers as $index => $user)
                    <tr class="hover:bg-white">
                        <td class="px-6 py-4 text-black/50">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-black/80">{{ $user->name }}</td>
                        <td class="px-6 py-4 capitalize">{{ $user->role }}</td>
                        <td class="px-6 py-4 font-bold text-blue-600">{{ number_format($user->activity_logs_count) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Active Users Chart
    new Chart(document.getElementById('dailyActiveChart'), {
        type: 'line',
        data: {
            labels: @json($dailyActiveUsers->pluck('date')),
            datasets: [{ label: 'Active Users', data: @json($dailyActiveUsers->pluck('count')), borderColor: '#3b82f6', fill: true, backgroundColor: 'rgba(59, 130, 246, 0.1)', tension: 0.4 }]
        },
        options: { responsive: true }
    });

    // Daily Views Chart
    new Chart(document.getElementById('dailyViewsChart'), {
        type: 'line',
        data: {
            labels: @json($dailyViews->pluck('date')),
            datasets: [{ label: 'Page Views', data: @json($dailyViews->pluck('count')), borderColor: '#2563eb', fill: true, backgroundColor: 'rgba(37, 99, 235, 0.1)', tension: 0.4 }]
        },
        options: { responsive: true }
    });

    // Peak Hours Chart
    new Chart(document.getElementById('peakHoursChart'), {
        type: 'bar',
        data: {
            labels: @json($peakHours->pluck('hour')),
            datasets: [{ label: 'Activities', data: @json($peakHours->pluck('count')), backgroundColor: '#000000', borderRadius: 4 }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Popular Actions Chart
    new Chart(document.getElementById('actionsChart'), {
        type: 'bar',
        data: {
            labels: @json($popularActions->pluck('action')->map(function($a) { return str_replace('_', ' ', $a); })),
            datasets: [{ data: @json($popularActions->pluck('count')), backgroundColor: '#2563eb', borderRadius: 8 }]
        },
        options: { responsive: true, indexAxis: 'y', scales: { x: { beginAtZero: true } }, plugins: { legend: { display: false } } }
    });
});
</script>
@endpush
@endsection
