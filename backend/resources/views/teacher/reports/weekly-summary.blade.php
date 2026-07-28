@extends('layouts.dashboard')
@section('title', 'Weekly Progress Summary')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Weekly Summary</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Weekly Progress Summary</h1>
        <p class="text-black/50 text-sm mt-1">Week of {{ $summary['week'] }} — {{ $summary['form_level'] }}</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach([
            ['Total Students',        $summary['total_students'],              'text-blue-600'],
            ['Completion Rate',        $summary['completion_rate'] . '%',       'text-green-600'],
            ['Avg Career Match',       $summary['avg_match_score'] . '%',       'text-purple-600'],
            ['New Assessments (7d)',   $summary['new_assessments'],             'text-orange-600'],
            ['Need Attention',         $summary['students_needing_attention'],  'text-red-600'],
        ] as [$label, $val, $color])
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-black {{ $color }}">{{ $val }}</p>
            <p class="text-xs text-black/50 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-black/10 p-6">
        <h2 class="font-semibold text-black/80 mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('teacher.reports.attention-list') }}"
               class="px-4 py-2 bg-red-50 text-red-700 rounded-xl text-sm font-semibold hover:bg-red-100 transition-colors">
                View Students Needing Attention
            </a>
            <a href="{{ route('teacher.reports.class-performance') }}"
               class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-semibold hover:bg-blue-100 transition-colors">
                View Class Performance
            </a>
            <a href="{{ route('teacher.reports.assessment-trend') }}"
               class="px-4 py-2 bg-green-50 text-green-700 rounded-xl text-sm font-semibold hover:bg-green-100 transition-colors">
                View Assessment Trend
            </a>
        </div>
    </div>
</div>
@endsection
