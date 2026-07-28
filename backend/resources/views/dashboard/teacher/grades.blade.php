@extends('layouts.dashboard')

@section('title', 'Student Grades')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Student Grades</span>
</nav>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Page Header --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-6 text-white relative overflow-hidden shadow-md">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,#fff 0%,transparent 60%)"></div>
        <div class="relative flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-0.5">Grade Management</p>
                <h1 class="text-xl font-bold">Student Grades</h1>
                <p class="text-white/65 text-sm mt-0.5">Upload, browse and filter academic results across all students.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('teacher.grades.template') }}"
                   class="px-3 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV Template
                </a>
                <a href="{{ route('teacher.grades.export') }}"
                   class="px-3 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-xl text-xs font-bold transition-colors flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export All
                </a>
            </div>
        </div>
    </div>

    {{-- ═══ UPLOAD SECTION ════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50/50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
            </div>
            <div>
                <h2 class="font-bold text-blue-900">Upload Grades</h2>
                <p class="text-xs text-blue-600">Import Excel or CSV — updates student records immediately.</p>
            </div>
        </div>
        <div class="p-6">
            @if(session('success'))
            <div class="mb-4 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 font-semibold">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('import_skip_summary') && count(session('import_skip_summary')) > 0)
            <div class="mb-4 bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                <p class="font-bold mb-1.5">{{ session('import_skipped_total') }} row(s) skipped:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach(session('import_skip_summary') as $line)
                    <li>{{ $line }}</li>
                    @endforeach
                </ul>

                @if(collect(session('import_skip_summary'))->contains(fn($l) => str_contains($l, 'Missing score')))
                <p class="mt-3 pt-3 border-t border-amber-200 font-semibold">
                    It looks like the Score column was left blank for these rows. Open the file, type each student's score into the Score column, save it, then upload it again.
                </p>
                @endif

                @if(session('import_skipped') && count(session('import_skipped')) > 0)
                <details class="mt-3">
                    <summary class="cursor-pointer font-semibold">Show row-by-row detail (first {{ count(session('import_skipped')) }} of {{ session('import_skipped_total') }})</summary>
                    <ul class="list-disc list-inside space-y-0.5 max-h-40 overflow-y-auto mt-2">
                        @foreach(session('import_skipped') as $reason)
                        <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                </details>
                @endif
            </div>
            @endif

            {{-- Blank fill-in template per form --}}
            <div class="flex flex-wrap gap-2 mb-4 pb-4 border-b border-blue-50">
                <div class="w-full mb-1">
                    <p class="text-xs font-semibold text-blue-700">Blank Template (recommended for new grades):</p>
                    <p class="text-[11px] text-black/50 font-semibold">⚠ Download it, open it in Excel, TYPE A SCORE FOR EACH ROW in the "score" column, save the file — then come back and upload it. Uploading it with the score column still empty will skip every row.</p>
                </div>
                @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
                <a href="{{ route('teacher.grades.blank-template', ['form_level' => $f]) }}"
                   class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 rounded-lg text-xs font-semibold text-white transition-colors">
                    {{ $f }} Blank Template
                </a>
                @endforeach
            </div>

            {{-- Export by form --}}
            <div class="flex flex-wrap gap-2 mb-5 pb-5 border-b border-blue-50">
                <p class="text-xs font-semibold text-black/50 self-center mr-1">Export Existing (for edits):</p>
                @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
                <a href="{{ route('teacher.grades.export-form', ['form_level' => $f, 'term' => 'Term 3']) }}"
                   class="px-3 py-1.5 bg-blue-50 border border-blue-200 hover:bg-blue-100 rounded-lg text-xs font-semibold text-blue-700 transition-colors">
                    {{ $f }} CSV
                </a>
                @endforeach
            </div>

            <form action="{{ route('teacher.import.grades.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-blue-700 mb-1.5">Term</label>
                        <select name="term" required
                                class="w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                            @foreach($terms as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-700 mb-1.5">Form</label>
                        <select name="form_level" required
                                class="w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                            @foreach($forms as $f)
                            <option value="{{ $f }}">{{ $f }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-blue-700 mb-1.5">File (.xlsx, .csv)</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                               class="w-full text-sm border border-blue-200 rounded-xl px-3 py-2 bg-white text-black/60 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>
                    <button type="submit"
                            class="h-10 px-5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload Grades
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ STATS BAR ══════════════════════════════════════════════════════════ --}}
    @if($results->total() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        @foreach([
            ['Total Records', number_format($stats['total']), 'bg-blue-600', 'text-white'],
            ['Average Score', $stats['avg'].'%', 'bg-white', ($stats['avg'] >= 70 ? 'text-blue-700' : ($stats['avg'] >= 50 ? 'text-amber-600' : 'text-red-500'))],
            ['Highest Score', $stats['highest'].'%', 'bg-white', 'text-blue-700'],
            ['Lowest Score', $stats['lowest'].'%', 'bg-white', 'text-black/60'],
        ] as [$label, $val, $bg, $textColor])
        <div class="{{ $bg }} rounded-2xl border border-blue-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-black {{ $textColor }}">{{ $val }}</p>
            <p class="text-xs font-medium {{ $bg === 'bg-blue-600' ? 'text-blue-100' : 'text-black/40' }} mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══ FILTERS ════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-6 py-3 bg-blue-50/50 border-b border-blue-100 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <h3 class="font-bold text-blue-900 text-sm">Filter Results</h3>
        </div>
        <form method="GET" action="{{ route('teacher.grades') }}" class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-blue-700 mb-1">Student name / number</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search by name or student number…"
                               class="w-full pl-9 pr-4 py-2.5 border border-blue-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                    </div>
                </div>

                {{-- Form level --}}
                <div>
                    <label class="block text-xs font-bold text-blue-700 mb-1">Form</label>
                    <select name="form_level" class="w-full border border-blue-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">All Forms</option>
                        @foreach($forms as $f)
                        <option value="{{ $f }}" {{ ($formLevel ?? '') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Term --}}
                <div>
                    <label class="block text-xs font-bold text-blue-700 mb-1">Term</label>
                    <select name="term" class="w-full border border-blue-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">All Terms</option>
                        @foreach($terms as $t)
                        <option value="{{ $t }}" {{ ($term ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-xs font-bold text-blue-700 mb-1">Subject</label>
                    <select name="subject_id" class="w-full border border-blue-200 rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ ($subjectId ?? '') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-blue-50">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold transition-colors shadow-sm flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Apply Filters
                </button>
                <a href="{{ route('teacher.grades') }}"
                   class="px-5 py-2 border border-blue-200 text-blue-600 rounded-xl text-sm hover:bg-blue-50 transition-colors">
                    Clear Filters
                </a>
                @if($search || $formLevel || $term || $subjectId)
                <span class="text-xs text-blue-600 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full font-medium">
                    Filters active
                </span>
                @endif
            </div>
        </form>
    </div>

    {{-- ═══ RESULTS TABLE ══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">

        @if($results->isEmpty())
        <div class="p-14 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="text-black/50 font-semibold">No grade records found{{ $search || $formLevel || $term || $subjectId ? ' for the selected filters' : '' }}.</p>
            @if($search || $formLevel || $term || $subjectId)
            <a href="{{ route('teacher.grades') }}" class="text-blue-600 text-sm hover:underline mt-2 inline-block font-medium">Clear all filters</a>
            @endif
        </div>
        @else

        <div class="px-5 py-3 border-b border-blue-50 bg-blue-50/30 flex items-center justify-between">
            <p class="text-xs font-semibold text-blue-700">
                Showing {{ $results->firstItem() }}–{{ $results->lastItem() }} of {{ number_format($results->total()) }} records
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-blue-50/50 border-b border-blue-100">
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Student No.</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Form</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Term</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Score</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider whitespace-nowrap">Grade</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-blue-700 uppercase tracking-wider">Remarks</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($results as $result)
                    @php
                        $score = (float) $result->score;
                        $gc = $score >= 80 ? 'blue-700' : ($score >= 70 ? 'blue-600' : ($score >= 60 ? 'blue-500' : ($score >= 50 ? 'amber-600' : 'red-500')));
                        $barColor = $score >= 80 ? 'bg-blue-700' : ($score >= 70 ? 'bg-blue-600' : ($score >= 60 ? 'bg-blue-400' : ($score >= 50 ? 'bg-amber-400' : 'bg-red-400')));
                        $gradeBg = match($result->grade ?? '') {
                            'A'     => 'bg-blue-100 text-blue-800 border-blue-200',
                            'B'     => 'bg-blue-50 text-blue-700 border-blue-100',
                            'C'     => 'bg-sky-50 text-sky-700 border-sky-100',
                            'D'     => 'bg-amber-50 text-amber-700 border-amber-100',
                            default => 'bg-red-50 text-red-600 border-red-100',
                        };
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-[10px] shrink-0 border border-blue-200">
                                    {{ strtoupper(substr($result->student?->name ?? 'N/A', 0, 2)) }}
                                </div>
                                <span class="font-semibold text-black/80">{{ $result->student?->name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-black/50 text-xs font-mono">
                            {{ $result->student?->studentProfile?->student_number ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold border border-blue-100">{{ $result->form_level }}</span>
                        </td>
                        <td class="px-4 py-3 text-black/60 text-xs font-medium">{{ $result->term }}</td>
                        <td class="px-4 py-3 font-semibold text-black/75">{{ $result->subject?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-blue-50 rounded-full overflow-hidden shrink-0 border border-blue-100">
                                    <div class="{{ $barColor }} h-full rounded-full transition-all" style="width:{{ min($score, 100) }}%"></div>
                                </div>
                                <span class="font-black text-{{ $gc }} shrink-0 text-sm">{{ number_format($score, 1) }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-black {{ $gradeBg }} border">
                                {{ $result->grade ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-black/40 text-xs max-w-[160px] truncate">
                            {{ $result->teacher_remarks ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('teacher.student.detail', $result->student) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-bold transition-colors border border-blue-200">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($results->hasPages())
        <div class="px-5 py-4 border-t border-blue-50 bg-blue-50/20">
            {{ $results->appends(request()->query())->links() }}
        </div>
        @endif

        @endif
    </div>

</div>
@endsection
