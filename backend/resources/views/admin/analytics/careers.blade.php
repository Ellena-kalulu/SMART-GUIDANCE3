@extends('layouts.dashboard')

@section('title', 'Career Analytics')


@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.analytics.index') }}" class="text-black/50 hover:text-blue-600">Analytics</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Career Analytics</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Career Analytics</h1>
        <p class="text-blue-100">Career interest trends and recommendation insights</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Total Career Matches</p>
                <p class="text-3xl font-bold text-black/80">{{ number_format($topCareers->sum('count')) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Avg Match Score</p>
                <p class="text-3xl font-bold text-black/80">{{ number_format($topCareers->avg('avg_score'), 1) }}%</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Acceptance Rate</p>
                <p class="text-3xl font-bold text-black/80">{{ $acceptanceRate['acceptance_percentage'] }}%</p>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="text-center">
                <p class="text-black/50 text-sm">Total Universities</p>
                <p class="text-3xl font-bold text-black/80">{{ number_format($topUniversities->sum('count')) }}</p>
            </div>
        </div>
    </div>

    <!-- Top Careers & Score Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Top 10 Career Matches</h3>
            <div class="space-y-3">
                @foreach($topCareers as $career)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $career->recommended->title ?? 'Unknown' }}</span>
                        <span class="text-black/50">{{ number_format($career->count) }} matches ({{ round($career->avg_score) }}% avg)</span>
                    </div>
                    <div class="w-full h-2 bg-black/[0.05] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ min(100, ($career->count / $topCareers->first()->count) * 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Score Distribution</h3>
            <canvas id="scoreDistChart" height="200"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <p class="text-xs text-black/50">Excellent (80%+)</p>
                    <p class="font-bold text-blue-600">{{ number_format($scoreDistribution['excellent']) }}</p>
                </div>
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <p class="text-xs text-black/50">Good (60-79%)</p>
                    <p class="font-bold text-blue-600">{{ number_format($scoreDistribution['good']) }}</p>
                </div>
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <p class="text-xs text-black/50">Average (40-59%)</p>
                    <p class="font-bold text-blue-600">{{ number_format($scoreDistribution['average']) }}</p>
                </div>
                <div class="text-center p-2 bg-blue-50 rounded-lg">
                    <p class="text-xs text-black/50">Low (<40%)</p>
                    <p class="font-bold text-blue-600">{{ number_format($scoreDistribution['low']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Career Category & Match Score by Form -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Career Categories</h3>
            <canvas id="categoryChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach($careerCategories as $category => $count)
                <div class="flex justify-between items-center px-3 py-2 bg-white rounded-lg">
                    <span class="text-sm font-medium text-black/60">{{ $category }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Match Score by Form Level</h3>
            <canvas id="matchScoreChart" height="250"></canvas>
            <div class="mt-4 space-y-2">
                @foreach($matchScoreByForm as $form => $data)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span>{{ $form }}</span>
                        <span>{{ number_format($data['avg_score'], 1) }}%</span>
                    </div>
                    <div class="w-full h-2 bg-black/[0.05] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $data['avg_score'] }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Subject Combinations & Universities -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Top Subject Combinations</h3>
            <div class="space-y-3">
                @foreach($topCombinations as $combo)
                <div class="flex justify-between items-center p-3 bg-white rounded-lg">
                    <span class="font-medium">{{ $combo->recommended->name ?? 'Unknown' }}</span>
                    <span class="text-sm text-black/50">{{ number_format($combo->count) }} recommendations</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Top University Programs</h3>
            <div class="space-y-3">
                @foreach($topUniversities as $uni)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $uni->recommended->name ?? 'Unknown' }}</span>
                        <span class="text-black/50">{{ round($uni->avg_score) }}% avg match</span>
                    </div>
                    <div class="w-full h-2 bg-black/[0.05] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-600 rounded-full" style="width: {{ $uni->avg_score }}%"></div>
                    </div>
                    <p class="text-xs text-black/40 mt-1">{{ $uni->recommended->university ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Score Distribution Chart
    new Chart(document.getElementById('scoreDistChart'), {
        type: 'bar',
        data: {
            labels: ['Excellent (80%+)', 'Good (60-79%)', 'Average (40-59%)', 'Low (<40%)'],
            datasets: [{ data: @json(array_values($scoreDistribution)), backgroundColor: ['#2563eb', '#3b82f6', '#000000', '#2563eb'], borderRadius: 8 }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } }, plugins: { legend: { display: false } } }
    });

    // Category Chart
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: @json(array_keys($careerCategories)),
            datasets: [{ data: @json(array_values($careerCategories)), backgroundColor: '#2563eb', borderRadius: 8 }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } }, indexAxis: 'y' }
    });

    // Match Score Chart
    new Chart(document.getElementById('matchScoreChart'), {
        type: 'bar',
        data: {
            labels: @json(array_keys($matchScoreByForm)),
            datasets: [{ label: 'Average Match Score (%)', data: @json(collect($matchScoreByForm)->pluck('avg_score')), backgroundColor: '#2563eb', borderRadius: 8 }]
        },
        options: { responsive: true, scales: { y: { max: 100, beginAtZero: true } } }
    });
});
</script>
@endpush
@endsection
