@extends('layouts.dashboard')
@section('title', 'Assessment Engagement Report')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Assessment Engagement</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="bg-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Report #31</p>
                <h1 class="text-2xl font-bold">Assessment Engagement Report</h1>
                <p class="text-blue-100 text-sm mt-1">Completion rates by form, stream, and monthly trends</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.assessment-engagement'])
            </div>
        </div>
    </div>

    {{-- Top Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-black/80">{{ number_format($data['total_students']) }}</p>
            <p class="text-xs text-black/50 mt-1">Total Students</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ number_format($data['completed_assessments']) }}</p>
            <p class="text-xs text-black/50 mt-1">Completed Assessments</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $data['completion_rate'] }}%</p>
            <p class="text-xs text-black/50 mt-1">Overall Completion Rate</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- By Form --}}
        <div class="bg-white rounded-2xl border border-black/10 p-6">
            <h2 class="font-bold text-black/80 mb-5">Completion by Form Level</h2>
            <div class="space-y-4">
                @forelse($data['by_form'] as $form => $stats)
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="font-medium text-black/70">{{ $form }}</span>
                        <span class="text-black/50">{{ $stats['completed'] }}/{{ $stats['total'] }} ({{ $stats['rate'] }}%)</span>
                    </div>
                    <div class="w-full h-3 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $stats['rate'] >= 70 ? 'bg-blue-500' : ($stats['rate'] >= 40 ? 'bg-blue-600' : 'bg-blue-500') }}"
                             style="width:{{ $stats['rate'] }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-black/40 text-sm">No data</p>
                @endforelse
            </div>
        </div>

        {{-- By Stream --}}
        <div class="bg-white rounded-2xl border border-black/10 p-6">
            <h2 class="font-bold text-black/80 mb-5">Completion by Stream</h2>
            <div class="space-y-4">
                @forelse($data['by_stream'] as $stream => $stats)
                <div>
                    <div class="flex justify-between text-sm mb-1.5">
                        <span class="font-medium text-black/70">{{ $stream }}</span>
                        <span class="text-black/50">{{ $stats['completed'] }}/{{ $stats['total'] }} ({{ $stats['rate'] }}%)</span>
                    </div>
                    <div class="w-full h-3 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $stats['rate'] >= 70 ? 'bg-blue-500' : ($stats['rate'] >= 40 ? 'bg-blue-600' : 'bg-blue-500') }}"
                             style="width:{{ $stats['rate'] }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-black/40 text-sm">No data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Monthly Trend Chart --}}
    @if($data['monthly_trends']->count())
    <div class="bg-white rounded-2xl border border-black/10 p-6">
        <h2 class="font-bold text-black/80 mb-5">Monthly Completion Trend</h2>
        <canvas id="trendChart" height="100"></canvas>
    </div>
    @endif

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>

@push('scripts')
@if($data['monthly_trends']->count())
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($data['monthly_trends']->pluck('month')),
            datasets: [{
                label: 'Assessments Completed',
                data: @json($data['monthly_trends']->pluck('count')),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
@endif
@endpush
@endsection
