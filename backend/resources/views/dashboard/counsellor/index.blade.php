@extends('layouts.dashboard')

@section('title', 'Counsellor Dashboard')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 60%)"></div>
        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Counsellor Portal</p>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="text-blue-100 text-sm max-w-xl">Guide your students toward successful career paths with data-driven insights and scheduled counselling sessions.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('counsellor.sessions') }}"
                   class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-semibold transition-colors">
                    Schedule Session
                </a>
                <a href="{{ route('counsellor.students') }}"
                   class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-semibold transition-colors">
                    View Students
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stats-card title="Total Students" :value="$totalStudents" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>' color="blue" />
        <x-stats-card title="Assessments Completed" :value="$assessmentsCompleted" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>' color="green" />
        <x-stats-card title="Career Pathways" :value="$careerPathways" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>' color="purple" />
        <x-stats-card title="Assessment Rate" :value="$successRate . '%'" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' color="orange" />
    </div>

    <!-- Alert + quick metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('counsellor.sessions') }}" class="bg-amber-50 border border-amber-200 rounded-2xl p-5 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Pending Requests</p>
            <p class="text-3xl font-bold text-amber-800 mt-1">{{ $pendingSessions }}</p>
            <p class="text-xs text-amber-600 mt-1">Student session requests awaiting your response</p>
        </a>
        <a href="{{ route('counsellor.students') }}?status=no_assessment" class="bg-red-50 border border-red-100 rounded-2xl p-5 hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">No Assessment Yet</p>
            <p class="text-3xl font-bold text-red-700 mt-1">{{ $studentsWithoutAssessment }}</p>
            <p class="text-xs text-red-500 mt-1">Students who have not completed the career assessment</p>
        </a>
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Avg Career Match</p>
            <p class="text-3xl font-bold text-blue-700 mt-1">{{ round($avgMatchScore) }}%</p>
            <p class="text-xs text-blue-500 mt-1">Average confidence score across all students</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/10">
            <h2 class="text-lg font-bold text-black/80 mb-4">Career Interests Distribution</h2>
            <canvas id="careerChart" height="250"></canvas>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/10">
            <h2 class="text-lg font-bold text-black/80 mb-4">Assessment Completion Trend</h2>
            <canvas id="trendChart" height="250"></canvas>
        </div>
    </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Upcoming Sessions -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-black/80">Upcoming Sessions</h2>
                <p class="text-xs text-black/50 mt-0.5">Confirmed appointments on your calendar</p>
            </div>
            <a href="{{ route('counsellor.sessions') }}" class="text-xs text-blue-600 hover:underline font-medium">Manage</a>
        </div>
        <div class="divide-y divide-black/10">
            @forelse($upcomingSessions as $session)
            <div class="px-6 py-4 flex items-center gap-4">
                <div class="shrink-0 w-12 text-center bg-blue-50 rounded-xl py-1.5">
                    <p class="text-[10px] font-semibold text-blue-400 uppercase">{{ $session->scheduled_at->format('M') }}</p>
                    <p class="text-lg font-bold text-blue-700 leading-none">{{ $session->scheduled_at->format('d') }}</p>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-black/80 text-sm truncate">
                        {{ $session->isForAll() ? 'All Students' : ($session->student->name ?? 'Student') }}
                    </p>
                    <p class="text-xs text-black/50">{{ $session->scheduled_at->format('l, H:i') }}{{ $session->venue ? ' · ' . $session->venue : '' }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-black/40 text-sm">
                No upcoming sessions. <a href="{{ route('counsellor.sessions') }}" class="text-blue-600 hover:underline">Schedule one</a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Students Who Submitted Assessments -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-black/80">Recent Assessment Submissions</h2>
                <p class="text-xs text-black/50 mt-0.5">Students who completed the career assessment</p>
            </div>
            <a href="{{ route('counsellor.students') }}" class="text-xs text-blue-600 hover:underline font-medium shrink-0">View all</a>
        </div>
        <div class="divide-y divide-black/10">
            @forelse($studentsNeedingGuidance as $student)
            <div class="px-6 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold shrink-0">
                        {{ strtoupper(substr($student->name, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-black/80 truncate">{{ $student->name }}</p>
                        <p class="text-xs text-black/50">{{ $student->studentProfile?->form_level ?? 'Unknown form' }}</p>
                    </div>
                </div>
                <div class="flex-1 mx-2 min-w-0 hidden sm:block">
                    <p class="text-sm text-black/60 truncate">{{ $student->guidance_reason }}</p>
                    @if($student->assessment_completed_at)
                    <p class="text-xs text-black/40">Submitted {{ \Carbon\Carbon::parse($student->assessment_completed_at)->diffForHumans() }}</p>
                    @endif
                </div>
                <a href="{{ route('counsellor.student.detail', $student) }}"
                   class="shrink-0 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition-colors">
                    Reach Out
                </a>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-black/40 text-sm">
                No students have completed an assessment yet.
            </div>
            @endforelse
        </div>
    </div>
  </div>

    <!-- Quick links -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Analytics', route('counsellor.analytics'), 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['Reports', route('counsellor.reports.index'), 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['Career Library', route('counsellor.careers'), 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['Career Profiles', route('counsellor.career-profiles'), 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
        ] as [$label, $url, $icon])
        <a href="{{ $url }}" class="bg-white border border-black/10 rounded-2xl p-4 hover:shadow-md hover:border-blue-200 transition-all flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-black/70">{{ $label }}</span>
        </a>
        @endforeach
    </div>
</div>

@push('scripts')
@php
    $chartCareer = $chartData['career_interests'] ?? [];
    $chartLabels = $chartData['trend_labels'] ?? [];
    $chartCounts = $chartData['trend_data'] ?? [];
@endphp
<script>
    const careerLabels = @json(array_keys($chartCareer));
    const careerValues = @json(array_values($chartCareer));
    const chartColors = ['#3b82f6', '#2563eb', '#1d4ed8', '#60a5fa', '#93c5fd', '#1e40af'];

    new Chart(document.getElementById('careerChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: careerLabels,
            datasets: [{
                data: careerValues,
                backgroundColor: chartColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Assessments Completed',
                data: @json($chartCounts),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true } },
            plugins: { legend: { display: false } }
        }
    });
</script>
@endpush
@endsection
