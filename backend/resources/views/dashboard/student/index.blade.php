@extends('layouts.dashboard')

@section('title', 'Student Dashboard')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
    <span class="text-sm text-black/80 font-medium">Dashboard</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 60%)"></div>
        <div class="relative flex items-start justify-between gap-4">
            <div>
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">{{ __('student.student_portal') }}</p>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">{{ __('student.welcome_back') }}, {{ $user->name }}</h1>
                @if($assessmentsCompleted > 0)
                    <p class="text-white/70 text-sm mb-4">{{ __($assessmentsCompleted > 1 ? 'student.assessment_done_text_plural' : 'student.assessment_done_text', ['count' => $assessmentsCompleted]) }}</p>
                    <a href="{{ route('student.recommendations') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-500 hover:bg-blue-400 text-white rounded-xl text-sm font-bold transition-colors shadow-md">
                        {{ __('student.view_recommendations') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @else
                    <p class="text-white/70 text-sm mb-4">{{ __('student.no_assessment_text') }}</p>
                    <a href="{{ route('student.assessment') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-bold transition-colors shadow-sm">
                        {{ __('student.start_assessment') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                @endif
            </div>
            @if($profile)
            <div class="hidden md:block text-right shrink-0">
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">{{ __('student.profile') }}</p>
                <p class="text-white font-semibold">{{ $profile->form_level }}</p>
                @if($profile->stream)
                    <p class="text-blue-200 text-sm">{{ $profile->stream }} {{ __('student.stream') }}</p>
                @endif
                @if($profile->student_number)
                    <p class="text-blue-300 text-xs mt-1">{{ $profile->student_number }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $assessmentsCompleted }}</p>
                <p class="text-xs text-black/50">{{ __('student.stat_assessments') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $careerMatches }}</p>
                <p class="text-xs text-black/50">{{ __('student.stat_career_matches') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $subjectMatches }}</p>
                <p class="text-xs text-black/50">{{ __('student.stat_subject_combos') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $universityMatches }}</p>
                <p class="text-xs text-black/50">{{ __('student.stat_uni_matches') }}</p>
            </div>
        </div>
    </div>

    @if(isset($careerGoal) && $careerGoal && ($careerGoal->career || $careerGoal->universityProgram))
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-6 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">{{ $trackingStatus['label'] ?? __('student.my_career_path') }}</p>
                <h3 class="font-bold text-lg">{{ $careerGoal->career?->title }} @if($careerGoal->universityProgram)→ {{ $careerGoal->universityProgram->name }}@endif</h3>
                <p class="text-blue-100 text-sm mt-1 line-clamp-2">{{ $careerGoal->tracking_message }}</p>
            </div>
            <div class="shrink-0 flex items-center gap-4">
                <div class="text-center">
                    <div class="text-3xl font-black">{{ number_format($careerGoal->last_match_score ?? 0, 0) }}%</div>
                    <p class="text-xs text-blue-200">Readiness</p>
                </div>
                <a href="{{ route('student.career.path') }}" class="px-4 py-2 bg-white text-blue-700 rounded-xl text-sm font-bold hover:bg-blue-50">{{ __('student.track_progress') }}</a>
            </div>
        </div>
    </div>
    @endif

    @if(isset($analysis) && $analysis)
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 p-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-black/80">{{ __('student.intelligent_insights') }}</h2>
            <a href="{{ route('student.subject.combinations') }}" class="text-sm text-blue-600 font-semibold">{{ __('student.full_analysis') }}</a>
        </div>
        @if($analysis->top_combination)
        <p class="text-sm text-black/60">
            {{ __('student.top_combination') }}: <strong>{{ $analysis->top_combination['combination'] ?? $analysis->top_combination['label'] }}</strong>
            ({{ number_format($analysis->top_combination['score'] ?? 0, 0) }}%)
        </p>
        @endif
        @if(!empty($analysis->subject_conflicts))
        <p class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2 mt-2">⚠ Subject combination warning — review your subject choices.</p>
        @endif
    </div>
    @endif

    {{-- Feature Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('student.assessment') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.self_assessment') }}</p>
                <p class="text-xs text-black/50 mt-1">{{ __('student.card_assessment_desc') }}</p>
            </div>
        </a>
        <a href="{{ route('student.recommendations') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.my_recommendations') }}</p>
                <p class="text-xs text-black/50 mt-1">{{ __('student.card_recommendations_desc', ['count' => $careerMatches]) }}</p>
            </div>
        </a>
        <a href="{{ route('student.subject.combinations') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-white border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <div class="min-w-0">
                <p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.subject_combination') }}</p>
                <p class="text-xs text-black/50 mt-1">{{ __('student.card_subjects_desc') }}</p>
            </div>
        </a>
        <a href="{{ route('student.progress') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-gradient-to-br from-white to-blue-50/50 border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 shadow-md shadow-blue-600/30"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
            <div class="min-w-0"><p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.academic_progress') }}</p><p class="text-xs text-black/50 mt-1">{{ __('student.card_progress_desc') }}</p></div>
        </a>
        <a href="{{ route('student.universities') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-gradient-to-br from-white to-blue-50/50 border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 shadow-md shadow-blue-600/30"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg></div>
            <div class="min-w-0"><p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.university_programs') }}</p><p class="text-xs text-black/50 mt-1">{{ __('student.card_universities_desc', ['count' => $universityMatches]) }}</p></div>
        </a>
        <a href="{{ route('student.career.path') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-gradient-to-br from-white to-blue-50/50 border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 shadow-md shadow-blue-600/30"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg></div>
            <div class="min-w-0"><p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.my_career_path') }}</p><p class="text-xs text-black/50 mt-1">{{ __('student.card_career_path_desc') }}</p></div>
        </a>
        <a href="{{ route('student.appointments') }}" class="group flex items-start gap-4 p-5 rounded-2xl bg-gradient-to-br from-white to-blue-50/50 border border-blue-100 shadow-sm hover:border-blue-400 hover:shadow-md hover:-translate-y-0.5 transition-all">
            <div class="w-11 h-11 rounded-xl bg-blue-600 flex items-center justify-center shrink-0 shadow-md shadow-blue-600/30"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
            <div class="min-w-0"><p class="font-bold text-black/80 text-sm group-hover:text-blue-700">{{ __('student.book_a_session') }}</p><p class="text-xs text-black/50 mt-1">{{ __('student.card_session_desc') }}</p></div>
        </a>
    </div>
    <!-- Career & Subject Recommendations -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Top Career Recommendations -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-black/80">{{ __('student.top_career_matches') }}</h2>
                <a href="{{ route('student.recommendations') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">{{ __('student.view_all') }}</a>
            </div>
            @forelse($topCareers as $rec)
            <div class="flex items-center justify-between py-3 border-b border-black/5 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-black/80">{{ $rec->recommended->title ?? 'Career' }}</p>
                    <p class="text-xs text-black/50">{{ $rec->recommended->category ?? '' }}</p>
                    @if($rec->recommended?->universityPrograms?->isNotEmpty())
                    <p class="text-[10px] text-black/40 mt-0.5 truncate">{{ $rec->recommended->universityPrograms->first()->university ?? '' }}</p>
                    @endif
                </div>
                <span class="ml-3 shrink-0 text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">{{ __('student.interest') }}</span>
            </div>
            @empty
            <div class="py-8 text-center">
                <p class="text-black/40 text-sm mb-3">{{ __('student.no_career_matches') }}</p>
                <a href="{{ route('student.assessment') }}" class="text-sm text-blue-600 hover:underline">{{ __('student.take_assessment_hint') }}</a>
            </div>
            @endforelse
        </div>

        <!-- Recommended Subject Combinations -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-black/80">{{ __('student.recommended_subjects') }}</h2>
                <a href="{{ route('student.recommendations') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">{{ __('student.view_all') }}</a>
            </div>
            @forelse($topSubjects as $rec)
            <div class="flex items-center justify-between py-2.5 border-b border-black/5 last:border-0">
                <div>
                    <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->name ?? 'Combination' }}</p>
                    <p class="text-xs text-black/40 line-clamp-1">{{ $rec->reason }}</p>
                </div>
                <span class="ml-3 shrink-0 text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">
                    {{ round($rec->confidence_score) }}%
                </span>
            </div>
            @empty
            <div class="py-8 text-center">
                <p class="text-black/40 text-sm mb-3">{{ __('student.no_subjects_yet') }}</p>
                <a href="{{ route('student.assessment') }}" class="text-sm text-blue-600 hover:underline">{{ __('student.complete_assessment') }}</a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- University Programs -->
    @if($topUniversities->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <h2 class="text-lg font-bold text-black/80">{{ __('student.uni_programs_heading') }}</h2>
            <a href="{{ route('student.recommendations') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">{{ __('student.view_all') }}</a>
        </div>
        <div class="divide-y divide-black/10">
            @foreach($topUniversities as $rec)
            @php $eligible = $rec->confidence_score >= 80; @endphp
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-black/80">{{ $rec->recommended->name ?? 'Programme' }}</p>
                    <p class="text-sm text-black/50">{{ $rec->recommended->university ?? '' }}
                        @if($rec->recommended->minimum_points)
                            &nbsp;·&nbsp; Min. {{ $rec->recommended->minimum_points }} pts
                        @endif
                    </p>
                </div>
                <span class="ml-4 shrink-0 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                    {{ $eligible ? __('student.eligible') : __('student.conditional') }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-blue-100 text-center">
        <svg class="w-10 h-10 text-black/30 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <p class="text-black/50 text-sm">{{ __('student.no_uni_matches') }}</p>
        <a href="{{ route('student.assessment') }}" class="inline-block mt-3 text-sm text-blue-600 hover:underline font-medium">{{ __('student.take_the_assessment') }}</a>
    </div>
    @endif

    <!-- Recent Activity -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100">
        <h2 class="text-lg font-bold text-black/80 mb-4">{{ __('student.recent_activity') }}</h2>
        @forelse($recentActivities as $activity)
        <div class="flex items-center gap-3 py-2.5 border-b border-black/5 last:border-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-black/70">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</p>
                <p class="text-xs text-black/40">{{ $activity->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <p class="text-sm text-black/40 text-center py-4">{{ __('student.no_activity') }}</p>
        @endforelse
    </div>
</div>
@endsection
