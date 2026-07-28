@extends('layouts.dashboard')

@section('title', 'Reports')

@section('sidebar')
   @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Teacher</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Reports</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-black/80">Student Reports</h1>
            <p class="text-black/50 text-sm mt-1">View and export student progress and career guidance reports.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.reports.export', ['format' => 'pdf']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700
                      text-white rounded-xl font-semibold text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('teacher.reports.export', ['format' => 'csv']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-white border border-black/15
                      text-black/70 rounded-xl font-semibold text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @foreach([
            ['Total Students', $reportData['total_students'] ?? 0, 'bg-blue', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['Assessments Completed', $reportData['completed_assessments'] ?? 0, 'green', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Pending Assessments', $reportData['pending_assessments'] ?? 0, 'orange', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as [$label, $value, $color, $icon])
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5 flex items-center gap-4">
            <div class="w-12 h-12 bg-{{ $color }}-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-{{ $color }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $value }}</p>
                <p class="text-xs text-black/50">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Progress Report Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">Student Progress Report</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Form</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Avg Score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Assessment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Career Match</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">University Eligible</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-black/50">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($students ?? [] as $student)
                    @php
                        $done    = $student->assessmentAttempts->where('status','completed')->isNotEmpty();
                        $topRec  = $student->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
                        $uniElig = $student->recommendations->where('type','university_program')->where('confidence_score','>=',80)->count();
                        $avg     = $student->academicResults->avg('score');
                    @endphp
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center
                                            text-blue-700 font-bold text-xs">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <span class="font-medium text-black/80 text-sm">{{ $student->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/60">{{ $student->studentProfile->form_level ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-blue-600">
                            {{ $avg ? round($avg) . '%' : '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $done ? 'bg-blue-100 text-blue-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $done ? 'Done' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/70">{{ $topRec?->recommended?->title ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($uniElig > 0)
                                <span class="text-blue-600 font-semibold">{{ $uniElig }} programme(s)</span>
                            @else
                                <span class="text-black/40">None yet</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('teacher.student.detail', $student->id) }}"
                                   class="text-blue-600 hover:text-blue-700 font-medium text-sm">View</a>
                                <a href="{{ route('teacher.student.report', $student->id) }}"
                                   class="inline-flex items-center gap-1 text-xs text-black/50 hover:text-blue-600 transition-colors"
                                   title="Download PDF Report Card">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    </svg>
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-black/40 text-sm">No report data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
