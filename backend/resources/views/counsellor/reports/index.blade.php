@extends('layouts.dashboard')
@section('title', 'Guidance & Analytics Reports')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Reports</span>
</nav>
@endsection

@section('content')
@php
$reports = [
    [
        'value'       => 'career_analytics',
        'route'       => 'counsellor.reports.career-analytics',
        'number'      => 24,
        'title'       => 'School-Wide Career Analytics',
        'description' => 'Comprehensive analysis of career interests across all students with demographic breakdowns.',
        'accent'      => 'blue',
        'icon'        => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    ],
    [
        'value'       => 'intervention_list',
        'route'       => 'counsellor.reports.intervention-list',
        'number'      => 25,
        'title'       => 'Student Intervention Priority List',
        'description' => 'Ranked list of students needing immediate career guidance based on match scores and assessment status.',
        'accent'      => 'blue',
        'icon'        => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    ],
    [
        'value'       => 'session_log',
        'route'       => 'counsellor.reports.session-log',
        'number'      => 26,
        'title'       => 'Counselling Session Log',
        'description' => 'Complete history of counselling sessions with notes, outcomes, and follow-up dates.',
        'accent'      => 'blue',
        'icon'        => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
    ],
    [
        'value'       => 'career_library',
        'route'       => 'counsellor.reports.career-library',
        'number'      => 27,
        'title'       => 'Career Library Report',
        'description' => 'Complete catalog of all careers with subject requirements, university pathways, and growth projections.',
        'accent'      => 'blue',
        'icon'        => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    ],
    [
        'value'       => 'university_mapping',
        'route'       => 'counsellor.reports.university-mapping',
        'number'      => 28,
        'title'       => 'University Program Mapping',
        'description' => 'Comprehensive list of all university programs with entry requirements and feeder careers.',
        'accent'      => 'blue',
        'icon'        => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    ],
    [
        'value'       => 'career_fair',
        'route'       => 'counsellor.reports.career-fair',
        'number'      => 29,
        'title'       => 'Career Fair Recommendation',
        'description' => 'Suggested career clusters to feature at events based on student interest data.',
        'accent'      => 'blue',
        'icon'        => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
    ],
    [
        'value'       => 'success_stories',
        'route'       => 'counsellor.reports.success-stories',
        'number'      => 30,
        'title'       => 'Student Success Stories',
        'description' => 'Students who achieved high career match scores and actively followed recommendations.',
        'accent'      => 'blue',
        'icon'        => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
    ],
    [
        'value'       => 'assessment_engagement',
        'route'       => 'counsellor.reports.assessment-engagement',
        'number'      => 31,
        'title'       => 'Assessment Engagement Report',
        'description' => 'Analysis of assessment completion rates by form, stream, and demographic factors.',
        'accent'      => 'blue',
        'icon'        => 'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
    ],
    [
        'value'       => 'career_tracking',
        'route'       => 'counsellor.reports.career-tracking',
        'number'      => 32,
        'title'       => 'Career Path Tracking',
        'description' => 'Longitudinal tracking of students\' career preference changes over multiple assessments.',
        'accent'      => 'blue',
        'icon'        => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
    ],
];

$accentBg  = ['blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100','blue'=>'bg-blue-100'];
$accentTxt = ['blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600','blue'=>'text-blue-600'];
$accentBdr = ['blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300','blue'=>'hover:border-blue-300'];
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Guidance & Analytics Reports</h1>
        </div>
        <p class="text-blue-200 text-sm ml-13">9 reports available &mdash; select one below to view or export</p>
    </div>

    {{-- Report Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($reports as $report)
        @php
            $bg  = $accentBg[$report['accent']];
            $txt = $accentTxt[$report['accent']];
            $bdr = $accentBdr[$report['accent']];
        @endphp
        <a href="{{ route($report['route']) }}"
           class="group bg-white rounded-2xl border border-black/10 p-5 hover:shadow-md {{ $bdr }} transition-all">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 {{ $bg }} rounded-xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 {{ $txt }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $report['icon'] }}"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="font-semibold text-black/80 group-hover:{{ $txt }} transition-colors text-sm leading-snug">
                            {{ $report['title'] }}
                        </h3>
                        <span class="text-xs text-black/40 shrink-0">#{{ $report['number'] }}</span>
                    </div>
                    <p class="text-xs text-black/50 mt-1 leading-relaxed">{{ $report['description'] }}</p>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-1 {{ $txt }} text-xs font-semibold">
                Open report
                <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Quick Stats summary row --}}
    <div class="bg-white rounded-2xl border border-black/10 p-5">
        <p class="text-xs font-semibold text-black/50 uppercase tracking-wide mb-4">Quick Snapshot</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['Total Students',       \App\Models\User::where('role','student')->count(),                                                    'blue'],
                ['Assessments Done',     \App\Models\AssessmentAttempt::where('status','completed')->distinct('student_id')->count('student_id'),'blue'],
                ['Careers in Library',   \App\Models\Career::count(),                                                                           'blue'],
                ['University Programs',  \App\Models\UniversityProgram::count(),                                                                'blue'],
            ] as [$label, $val, $color])
            <div class="text-center p-4 bg-{{ $color }}-50 rounded-xl">
                <p class="text-2xl font-bold text-{{ $color }}-700">{{ number_format($val) }}</p>
                <p class="text-xs text-black/50 mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
