@extends('layouts.dashboard')

@section('title', 'My Students')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Teacher</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">My Students</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-black/80">My Students</h1>
            <p class="text-black/50 text-sm mt-1">Monitor academic performance and career guidance progress.</p>
        </div>
        <a href="{{ route('teacher.reports.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700
                  text-white rounded-xl font-semibold text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export Report
        </a>
    </div>

    <!-- Attention Alert -->
    @if(($attentionCount ?? 0) > 0)
    <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 flex items-center gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <p class="text-sm text-red-700">
            <span class="font-bold">{{ $attentionCount }} {{ Str::plural('student', $attentionCount) }} require{{ $attentionCount === 1 ? 's' : '' }} attention</span>
            — poor performance, missing assessment, or no clear career path.
            <button onclick="document.getElementById('needsFilter').value='attention'; filterStudents()"
                    class="ml-2 underline font-semibold hover:text-red-900">Show only</button>
        </p>
    </div>
    @endif

    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Search students by name…"
                   class="w-full pl-10 pr-4 py-2.5 border border-black/15 rounded-xl text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   oninput="filterStudents()">
        </div>
        <select id="statusFilter" onchange="filterStudents()"
                class="px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Status</option>
            <option value="completed">Assessment Completed</option>
            <option value="pending">Assessment Pending</option>
        </select>
        <select id="needsFilter" onchange="filterStudents()"
                class="px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Students</option>
            <option value="attention">Needs Attention</option>
        </select>
        <select id="formFilter" onchange="filterStudents()"
                class="px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Forms</option>
            @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
            <option value="{{ $f }}">{{ $f }}</option>
            @endforeach
        </select>
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full" id="studentsTable">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Form</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Assessment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Top Career Match</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Match Score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-black/50">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10" id="studentRows">
                    @forelse($students ?? [] as $student)
                    @php
                        $attempt    = $student->assessmentAttempts->where('status','completed')->first();
                        $topRec     = $student->top_career ?? $student->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
                        $score      = $topRec ? round($topRec->confidence_score) : null;
                        $statusDone = (bool)$attempt;
                        $flags      = $student->attention_flags ?? [];
                        $needsAttn  = count($flags) > 0;
                    @endphp
                    <tr class="hover:bg-white transition-colors student-row"
                        data-name="{{ strtolower($student->name) }}"
                        data-status="{{ $statusDone ? 'completed' : 'pending' }}"
                        data-form="{{ $student->studentProfile->form_level ?? '' }}"
                        data-attention="{{ $needsAttn ? 'attention' : 'ok' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center
                                            text-blue-700 font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-black/80">{{ $student->name }}</p>
                                    <p class="text-xs text-black/40">{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-black/60 text-sm">{{ $student->studentProfile->form_level ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $statusDone ? 'bg-blue-100 text-blue-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $statusDone ? 'Completed' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/70">
                            {{ $topRec?->recommended?->title ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($score)
                            <div class="flex items-center gap-2">
                                <div class="w-20 h-2 bg-black/[0.03] rounded-full overflow-hidden">
                                    <div class="h-full rounded-full
                                        {{ $score >= 70 ? 'bg-blue-600' : ($score >= 50 ? 'bg-blue-500' : 'bg-blue-400') }}"
                                         style="width:{{ $score }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-blue-600">
                                    {{ $score }}%
                                </span>
                            </div>
                            @else
                            <span class="text-black/40 text-sm">Not started</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($needsAttn)
                                <div class="flex flex-col gap-1">
                                    @foreach($flags as $flag)
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-semibold rounded-full whitespace-nowrap">{{ $flag }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded-full">On Track</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('teacher.student.detail', $student) }}"
                               class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                                View →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-black/40 text-sm">
                            No students found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterStudents() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const status  = document.getElementById('statusFilter').value;
    const form    = document.getElementById('formFilter').value;
    const needs   = document.getElementById('needsFilter').value;

    document.querySelectorAll('.student-row').forEach(row => {
        const ok = row.dataset.name.includes(search)
            && (!status || row.dataset.status === status)
            && (!form   || row.dataset.form === form)
            && (!needs  || row.dataset.attention === needs);
        row.style.display = ok ? '' : 'none';
    });
}
</script>
@endpush
@endsection
