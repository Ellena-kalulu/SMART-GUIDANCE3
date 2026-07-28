@extends('layouts.dashboard')

@section('title', 'Admin Dashboard')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <span class="text-black/50">Admin</span>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="font-semibold text-black/80">Dashboard</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Welcome Banner --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 80% 20%, #fff 0%, transparent 60%)"></div>
        <div class="relative flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Admin Portal</p>
                <h1 class="text-2xl md:text-3xl font-bold mb-1">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="text-white/70 text-sm">System overview · {{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-semibold transition-colors">+ Add User</a>
                <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-bold transition-colors shadow-sm">View Reports</a>
            </div>
        </div>
    </div>

    {{-- ═══ KEY METRICS ROW ════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $metrics = [
                ['Total Users',       $totalUsers,          'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'bg-blue-600'],
                ['Active Students',   $students,            'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z',                                                              'bg-blue-500'],
                ['Career Pathways',   $careers,             'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01',     'bg-blue-700'],
                ['University Progs',  $universityPrograms,  'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                     'bg-blue-800'],
            ];
        @endphp
        @foreach($metrics as [$label, $val, $icon, $bg])
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-blue-100 flex items-center gap-4">
            <div class="w-12 h-12 {{ $bg }} rounded-xl flex items-center justify-center shrink-0 shadow-md">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-black text-blue-700">{{ number_format($val) }}</p>
                <p class="text-xs text-black/50 font-medium">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ═══ ANALYTICS SECTION ══════════════════════════════════════════════════ --}}
    <div class="bg-blue-50 rounded-2xl border border-blue-100 px-6 py-4">
        <h2 class="text-lg font-bold text-blue-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            System Analytics
        </h2>
        <p class="text-xs text-blue-600 mt-0.5">Key performance indicators across the platform</p>
    </div>

    {{-- Analytics Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- User Distribution --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <h3 class="font-bold text-blue-900 text-sm mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                </span>
                User Distribution
            </h3>
            @php
                $roles = ['student','teacher','counsellor','parent','admin'];
                $roleColors = ['student'=>'bg-blue-500','teacher'=>'bg-blue-400','counsellor'=>'bg-blue-600','parent'=>'bg-blue-300','admin'=>'bg-blue-700'];
                $roleLabels = ['student'=>'Students','teacher'=>'Teachers','counsellor'=>'Counsellors','parent'=>'Parents','admin'=>'Admins'];
                $roleCounts = \App\Models\User::selectRaw('role, count(*) as cnt')->groupBy('role')->pluck('cnt','role');
                $total = $roleCounts->sum() ?: 1;
            @endphp
            <div class="space-y-3">
                @foreach($roles as $role)
                @php $cnt = $roleCounts[$role] ?? 0; $pct = round(($cnt / $total) * 100); @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-semibold text-black/70">{{ $roleLabels[$role] }}</span>
                        <span class="font-bold text-blue-700">{{ $cnt }}</span>
                    </div>
                    <div class="h-2 bg-blue-50 rounded-full overflow-hidden border border-blue-100">
                        <div class="{{ $roleColors[$role] }} h-full rounded-full transition-all" style="width:{{ min($pct, 100) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Assessment Activity --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <h3 class="font-bold text-blue-900 text-sm mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
                Assessment Activity
            </h3>
            @php
                $totalAssessments = \App\Models\AssessmentAttempt::count();
                $completedAssessments = \App\Models\AssessmentAttempt::where('status','completed')->count();
                $pendingAssessments = $totalAssessments - $completedAssessments;
                $completionRate = $totalAssessments > 0 ? round(($completedAssessments / $totalAssessments) * 100) : 0;
                $studentsAssessed = \App\Models\AssessmentAttempt::where('status','completed')->distinct('student_id')->count('student_id');
                $studentsNotAssessed = max(0, $students - $studentsAssessed);
            @endphp
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <span class="text-sm font-medium text-blue-800">Completion Rate</span>
                    <span class="text-xl font-black text-blue-700">{{ $completionRate }}%</span>
                </div>
                <div class="h-3 bg-blue-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full" style="width:{{ $completionRate }}%"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <p class="text-lg font-black text-blue-700">{{ $studentsAssessed }}</p>
                        <p class="text-[10px] text-blue-600 font-medium">Assessed</p>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                        <p class="text-lg font-black text-black/60">{{ $studentsNotAssessed }}</p>
                        <p class="text-[10px] text-black/40 font-medium">Pending</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recommendations Overview --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <h3 class="font-bold text-blue-900 text-sm mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                Recommendations
            </h3>
            @php
                $totalRecs = \App\Models\Recommendation::count();
                $careerRecs = \App\Models\Recommendation::where('type','career')->count();
                $subjectRecs = \App\Models\Recommendation::where('type','subject_combination')->count();
                $uniRecs = \App\Models\Recommendation::where('type','university_program')->count();
                $avgConfidence = \App\Models\Recommendation::avg('confidence_score');
            @endphp
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b border-blue-50">
                    <span class="text-sm text-black/60">Total Generated</span>
                    <span class="font-black text-blue-700 text-lg">{{ number_format($totalRecs) }}</span>
                </div>
                @foreach([['Career', $careerRecs, 'bg-blue-600'],['Subjects', $subjectRecs, 'bg-blue-400'],['University', $uniRecs, 'bg-blue-700']] as [$lbl, $cnt, $clr])
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 {{ $clr }} rounded-full shrink-0"></div>
                    <span class="text-xs text-black/60 flex-1">{{ $lbl }}</span>
                    <span class="text-xs font-bold text-blue-700">{{ number_format($cnt) }}</span>
                </div>
                @endforeach
                @if($avgConfidence)
                <div class="mt-2 pt-2 border-t border-blue-50 flex justify-between items-center">
                    <span class="text-xs text-black/50">Avg Confidence</span>
                    <span class="font-bold text-blue-600">{{ round($avgConfidence) }}%</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══ SECOND ROW ANALYTICS ═══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $gradesCount = \App\Models\AcademicResult::count();
            $avgGrade = \App\Models\AcademicResult::avg('score');
            $activeUsers = \App\Models\User::where('is_active', true)->count();
            $sessionsCount = \App\Models\CounsellingSession::count() ?? 0;
        @endphp
        @foreach([
            ['Grade Records', number_format($gradesCount), 'Academic data points in system', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['Avg Grade Score', round($avgGrade ?? 0).'%', 'Across all uploaded results', 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z'],
            ['Active Accounts', number_format($activeUsers), 'Currently active users', 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['Counselling Sessions', number_format($sessionsCount), 'Sessions recorded', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ] as [$label, $val, $desc, $icon])
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-black text-blue-700">{{ $val }}</p>
            <p class="text-xs font-semibold text-black/70 mt-0.5">{{ $label }}</p>
            <p class="text-[10px] text-black/40 mt-0.5">{{ $desc }}</p>
        </div>
        @endforeach
    </div>

    {{-- ═══ EXTENDED ANALYTICS ROW ════════════════════════════════════════ --}}
    @php
        $totalStudents   = \App\Models\User::where('role','student')->count();
        $inactiveUsers   = \App\Models\User::where('is_active', false)->count();
        $inactiveRate    = $totalUsers > 0 ? round(($inactiveUsers / $totalUsers) * 100) : 0;
        $subjectCombos   = \App\Models\SubjectCombination::count();
        $totalSessions   = \App\Models\CounsellingSession::count() ?? 0;
        $pendingSessions = \App\Models\CounsellingSession::where('status','pending')->count() ?? 0;
        $teacherCount    = \App\Models\User::where('role','teacher')->count();
        $gradesPerTeacher = $teacherCount > 0 ? round(\App\Models\AcademicResult::count() / $teacherCount) : 0;
        $recsPerStudent   = $totalStudents > 0 ? round(\App\Models\Recommendation::count() / $totalStudents, 1) : 0;
        $topCareers = \App\Models\Recommendation::where('type','career')
            ->select('recommended_id', \Illuminate\Support\Facades\DB::raw('count(*) as cnt'))
            ->groupBy('recommended_id')->orderByDesc('cnt')->take(5)
            ->with('recommended')->get();
        $formDistribution = \App\Models\StudentProfile::selectRaw('form_level, count(*) as cnt')
            ->groupBy('form_level')->pluck('cnt','form_level');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Top Career Categories --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 lg:col-span-2">
            <h3 class="font-bold text-blue-900 text-sm mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                Top Career Recommendations
            </h3>
            <div class="space-y-3">
                @forelse($topCareers as $i => $rec)
                @php $pct = $totalStudents > 0 ? round(($rec->cnt / $totalStudents) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-semibold text-black/70">{{ $rec->recommended?->title ?? 'Unknown' }}</span>
                        <span class="font-bold text-blue-700">{{ $rec->cnt }} students</span>
                    </div>
                    <div class="h-2 bg-blue-50 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-{{ 700 - $i * 100 }} rounded-full" style="width:{{ max($pct, 3) }}%;background:{{ ['#1d4ed8','#2563eb','#3b82f6','#60a5fa','#93c5fd'][$i] ?? '#93c5fd' }}"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-black/40 text-center py-4">No career recommendations generated yet</p>
                @endforelse
            </div>
        </div>

        {{-- System Health --}}
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <h3 class="font-bold text-blue-900 text-sm mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </span>
                System Health
            </h3>
            <div class="space-y-3">
                @foreach([
                    ['Active Users', ($totalUsers - $inactiveUsers) . ' / ' . $totalUsers, $inactiveRate < 20 ? 'text-blue-600' : 'text-amber-600'],
                    ['Inactive Rate', $inactiveRate . '%',                                   $inactiveRate < 20 ? 'text-blue-600' : 'text-amber-600'],
                    ['Pending Sessions', $pendingSessions,                                   $pendingSessions < 5 ? 'text-blue-600' : 'text-amber-600'],
                    ['Avg Recs/Student', $recsPerStudent,                                    $recsPerStudent >= 1 ? 'text-blue-600' : 'text-amber-600'],
                    ['Grades/Teacher', $gradesPerTeacher,                                    $gradesPerTeacher > 0 ? 'text-blue-600' : 'text-amber-600'],
                ] as [$label, $val, $color])
                <div class="flex justify-between items-center py-1 border-b border-blue-50 last:border-0">
                    <span class="text-xs text-black/55">{{ $label }}</span>
                    <span class="text-sm font-black {{ $color }}">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Students by Form + Disability breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <h3 class="font-bold text-blue-900 text-sm mb-4">Students by Form Level</h3>
            <div class="grid grid-cols-2 gap-3">
                @foreach(['Form 1','Form 2','Form 3','Form 4'] as $form)
                @php $cnt = $formDistribution[$form] ?? 0; @endphp
                <div class="bg-blue-50 rounded-xl p-3 text-center border border-blue-100">
                    <p class="text-xl font-black text-blue-700">{{ $cnt }}</p>
                    <p class="text-xs text-black/50">{{ $form }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5">
            <h3 class="font-bold text-blue-900 text-sm mb-4">Accessibility Usage</h3>
            @php
                $disabilityStats = \App\Models\StudentProfile::selectRaw('disability_type, count(*) as cnt')
                    ->groupBy('disability_type')->pluck('cnt','disability_type');
                $disabilityLabels = [
                    'none'                => 'None / Standard',
                    'visual_impairment'   => 'Visual Impairment',
                    'hearing_impairment'  => 'Hearing Impairment',
                    'physical_disability' => 'Physical Disability',
                    'learning_disability' => 'Learning Disability',
                ];
            @endphp
            <div class="space-y-2">
                @foreach($disabilityLabels as $key => $label)
                @php $cnt = $disabilityStats[$key] ?? 0; @endphp
                <div class="flex justify-between items-center py-1 border-b border-blue-50 last:border-0">
                    <span class="text-xs text-black/55">{{ $label }}</span>
                    <span class="text-xs font-black text-blue-700">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ QUICK ACTIONS ═══════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['Add User', route('admin.users.create'), 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'from-blue-600 to-blue-700'],
            ['Manage Careers', route('admin.careers'), 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'from-blue-500 to-blue-600'],
            ['Generate Report', route('admin.reports.index'), 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'from-blue-700 to-blue-800'],
            ['Settings', route('admin.settings'), 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'from-blue-800 to-blue-900'],
        ] as [$label, $url, $icon, $gradient])
        <a href="{{ $url }}" class="group flex items-center gap-3 p-4 bg-white rounded-xl shadow-sm border border-blue-100 hover:shadow-md hover:border-blue-300 hover:bg-blue-50/50 transition-all">
            <div class="w-10 h-10 bg-gradient-to-br {{ $gradient }} rounded-xl flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-black/70 group-hover:text-blue-700">{{ $label }}</span>
        </a>
        @endforeach
    </div>

    {{-- ═══ RECENT USERS TABLE ═══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-blue-50 flex items-center justify-between bg-blue-50/50">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h2 class="text-base font-bold text-blue-900">Recent Users</h2>
            </div>
            <a href="{{ route('admin.users') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold">View All →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-blue-50/30 border-b border-blue-50">
                    <tr>
                        @foreach(['User','Role','Email','Status','Joined'] as $h)
                        <th class="px-5 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @forelse($recentUsers as $u)
                    @php
                        $roleColors = [
                            'student'    => 'bg-blue-100 text-blue-700',
                            'teacher'    => 'bg-blue-50 text-blue-600',
                            'counsellor' => 'bg-blue-200 text-blue-800',
                            'parent'     => 'bg-sky-100 text-sky-700',
                            'admin'      => 'bg-blue-900 text-white',
                        ];
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs border border-blue-200">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <span class="font-semibold text-black/80 text-sm">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $roleColors[$u->role] ?? 'bg-blue-50 text-blue-600' }}">
                                {{ ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-black/55 text-sm">{{ $u->email }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $u->is_active ? 'bg-blue-100 text-blue-700' : 'bg-red-50 text-red-600' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $u->is_active ? 'bg-blue-500' : 'bg-red-400' }}"></span>
                                {{ $u->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-black/40 text-sm">{{ $u->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-black/35 text-sm">No users registered yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
