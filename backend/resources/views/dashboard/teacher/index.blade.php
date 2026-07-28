@extends('layouts.dashboard')

@section('title', 'Teacher Dashboard')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Dashboard</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Teacher Portal</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-blue-700 to-blue-800 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #93c5fd 0%, transparent 60%)"></div>
        <div class="relative flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Teacher Portal</p>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="text-blue-100 text-sm">Monitor your students' progress and upload grades to guide recommendations.</p>
            </div>
            <a href="{{ route('teacher.import.grades') }}" class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-sm font-semibold transition-colors">
                Upload Grades
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-stats-card title="Total Students" value="{{ $totalStudents }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>' color="blue" />
        <x-stats-card title="Assessments Done" value="{{ $assessmentsCompleted }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>' color="green" />
        <x-stats-card title="Avg. Match Score" value="{{ $avgMatchScore }}%" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>' color="purple" />
        <x-stats-card title="Need Attention" value="{{ $attentionCount }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>' color="orange" />
    </div>

    <!-- Attention Alert -->
    @if($attentionCount > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
        <p class="text-sm text-amber-900 font-medium">
            {{ $attentionCount }} {{ Str::plural('student', $attentionCount) }} may need your support.
            <a href="{{ route('teacher.students') }}" class="ml-1 text-blue-600 font-semibold hover:underline">View list</a>
        </p>
    </div>
    @endif

    <!-- Extended Analytics Row -->
    @php
        $allStudentsData = \App\Models\User::where('role','student')
            ->with(['academicResults','assessmentAttempts','recommendations'])
            ->get();
        $avgAll  = round($allStudentsData->flatMap->academicResults->avg('score') ?? 0, 1);
        $abovePassing = $allStudentsData->filter(fn($s) => $s->academicResults->avg('score') >= 50)->count();
        $belowPassing = $allStudentsData->filter(fn($s) => ($s->academicResults->count() > 0) && $s->academicResults->avg('score') < 50)->count();
        $noGrades     = $allStudentsData->filter(fn($s) => $s->academicResults->count() === 0)->count();
        $uploadCount  = \App\Models\AcademicResult::count();
        $lastUpload   = \App\Models\AcademicResult::latest('updated_at')->first()?->updated_at;
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
            <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-1">Class Average Score</p>
            <p class="text-3xl font-black text-blue-800">{{ $avgAll }}%</p>
            <div class="mt-3 h-2 bg-blue-200 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 rounded-full" style="width:{{ min(100, $avgAll) }}%"></div>
            </div>
            <p class="text-xs text-blue-600 mt-2">{{ $uploadCount }} total grade records</p>
        </div>

        <div class="bg-white border border-black/10 rounded-2xl p-5">
            <p class="text-xs font-bold text-black/50 uppercase tracking-wide mb-3">Grade Distribution</p>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-black/60">Passing (≥50%)</span>
                    <span class="font-black text-blue-600">{{ $abovePassing }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-black/60">Below Pass (&lt;50%)</span>
                    <span class="font-black text-red-500">{{ $belowPassing }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-black/60">No Grades Yet</span>
                    <span class="font-black text-black/40">{{ $noGrades }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-black/10 rounded-2xl p-5">
            <p class="text-xs font-bold text-black/50 uppercase tracking-wide mb-3">Grade Upload Status</p>
            <p class="text-2xl font-black text-blue-700">{{ $uploadCount }}</p>
            <p class="text-xs text-black/50">total grade entries</p>
            @if($lastUpload)
            <p class="text-xs text-black/40 mt-2">Last upload: <span class="font-semibold">{{ $lastUpload->diffForHumans() }}</span></p>
            @else
            <p class="text-xs text-amber-600 mt-2 font-semibold">No grades uploaded yet</p>
            @endif
            <a href="{{ route('teacher.import.grades') }}" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">
                Upload More
            </a>
        </div>
    </div>

    <!-- Students Performance Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <h2 class="text-lg font-bold text-black/80">Student Performance Overview</h2>
            <a href="{{ route('teacher.reports.index') }}"
               class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors">
                Reports
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Form</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Assessment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Career Match</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Avg Score</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-black/50">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($recentStudents as $student)
                    @php
                        $done    = $student->assessmentAttempts->where('status','completed')->isNotEmpty();
                        $topRec  = $student->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
                        $avg     = $student->academicResults->avg('score');
                        $avgPct  = $avg ? round($avg) : null;
                        $avgColor = $avgPct >= 70 ? 'green' : ($avgPct >= 50 ? 'yellow' : 'red');
                    @endphp
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-black/80 text-sm">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-black/60 text-sm">{{ $student->studentProfile->form_level ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $done ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $done ? 'Done' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/70">{{ $topRec?->recommended?->title ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($avgPct !== null)
                            <span class="text-sm font-bold text-{{ $avgColor }}-600">{{ $avgPct }}%</span>
                            @else
                            <span class="text-black/30 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('teacher.student.detail', $student) }}"
                               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors">View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-black/40 text-sm">No students yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-black/5 text-right">
            <a href="{{ route('teacher.students') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all students →</a>
        </div>
    </div>
</div>
@endsection
