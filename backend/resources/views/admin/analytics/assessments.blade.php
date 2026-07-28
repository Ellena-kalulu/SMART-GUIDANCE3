@extends('layouts.dashboard')

@section('title', 'Assessment Analytics')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.analytics.index') }}" class="text-black/50 hover:text-blue-600">Analytics</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Assessment Analytics</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-600 rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">📝 Assessment Analytics</h1>
        <p class="text-blue-100">Track assessment completion, trends, and student engagement</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Completion Rate</p>
                    <p class="text-3xl font-bold text-black/80">{{ $completionRate }}%</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Completed</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($assessmentStatus['completed']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">In Progress</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($assessmentStatus['in_progress']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Not Started</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($assessmentStatus['not_started']) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Status Pie & Daily Completions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">📊 Assessment Status</h3>
            <canvas id="statusPieChart" height="250"></canvas>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm">✅ Completed</span>
                    <span class="font-bold text-blue-600">{{ number_format($assessmentStatus['completed']) }} ({{ round(($assessmentStatus['completed'] / $totalStudents) * 100) }}%)</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm">🔄 In Progress</span>
                    <span class="font-bold text-blue-600">{{ number_format($assessmentStatus['in_progress']) }} ({{ round(($assessmentStatus['in_progress'] / $totalStudents) * 100) }}%)</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm">⏸️ Not Started</span>
                    <span class="font-bold text-blue-600">{{ number_format($assessmentStatus['not_started']) }} ({{ round(($assessmentStatus['not_started'] / $totalStudents) * 100) }}%)</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">📅 Daily Completions (Last 30 Days)</h3>
            <canvas id="dailyChart" height="250"></canvas>
        </div>
    </div>

    <!-- Completion by Form & Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">📚 Completion Rate by Form Level</h3>
            <canvas id="formCompletionChart" height="250"></canvas>
            <div class="mt-4 space-y-2">
                @foreach($completionByForm as $item)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $item->form_level ?? 'Not Set' }}</span>
                        <span>{{ $item->completion_rate }}% ({{ $item->completed }}/{{ $item->total }})</span>
                    </div>
                    <div class="w-full h-2 bg-black/[0.05] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $item->completion_rate }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">🎓 Completion Rate by Stream</h3>
            <canvas id="streamCompletionChart" height="250"></canvas>
            <div class="mt-4 space-y-2">
                @foreach($completionByStream as $item)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $item->stream ?? 'Not Set' }}</span>
                        <span>{{ $item->completion_rate }}% ({{ $item->completed }}/{{ $item->total }})</span>
                    </div>
                    <div class="w-full h-2 bg-black/[0.05] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $item->completion_rate }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Assessment Trend Over Time -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
        <h3 class="font-bold text-black/80 mb-4">📈 Assessment Trend Over Time</h3>
        <canvas id="trendChart" height="250"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Pie Chart
    new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'In Progress', 'Not Started'],
            datasets: [{ data: @json(array_values($assessmentStatus)), backgroundColor: ['#2563eb', '#000000', '#2563eb'], borderWidth: 0 }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Daily Completions Chart
    new Chart(document.getElementById('dailyChart'), {
        type: 'line',
        data: {
            labels: @json($dailyCompletions->pluck('date')),
            datasets: [{ label: 'Completions', data: @json($dailyCompletions->pluck('count')), borderColor: '#3b82f6', fill: true, backgroundColor: 'rgba(59, 130, 246, 0.1)', tension: 0.4 }]
        },
        options: { responsive: true }
    });

    // Form Completion Chart
    new Chart(document.getElementById('formCompletionChart'), {
        type: 'bar',
        data: {
            labels: @json($completionByForm->pluck('form_level')),
            datasets: [
                { label: 'Completion Rate (%)', data: @json($completionByForm->pluck('completion_rate')), backgroundColor: '#3b82f6', borderRadius: 8 }
            ]
        },
        options: { responsive: true, scales: { y: { max: 100, beginAtZero: true } } }
    });

    // Stream Completion Chart
    new Chart(document.getElementById('streamCompletionChart'), {
        type: 'bar',
        data: {
            labels: @json($completionByStream->pluck('stream')),
            datasets: [
                { label: 'Completion Rate (%)', data: @json($completionByStream->pluck('completion_rate')), backgroundColor: '#2563eb', borderRadius: 8 }
            ]
        },
        options: { responsive: true, scales: { y: { max: 100, beginAtZero: true } } }
    });

    // Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($assessmentTrends->pluck('month')),
            datasets: [
                { label: 'Total Assessments', data: @json($assessmentTrends->pluck('total')), borderColor: '#3b82f6', tension: 0.4, fill: false },
                { label: 'Unique Students', data: @json($assessmentTrends->pluck('unique_students')), borderColor: '#2563eb', tension: 0.4, fill: false }
            ]
        },
        options: { responsive: true }
    });
});
</script>
@endpush
@endsection
