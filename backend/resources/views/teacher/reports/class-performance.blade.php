@extends('layouts.dashboard')
@section('title', 'Class Performance')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Class Performance</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-black/80">Class Performance Overview</h1>
            <p class="text-black/50 text-sm mt-1">{{ $formLevel ?? 'All Forms' }}{{ $stream ? ' — ' . $stream . ' Stream' : '' }}</p>
        </div>
        <form method="GET" action="{{ route('teacher.reports.class-performance') }}" class="flex gap-2">
            <select name="form_level" class="border border-black/15 rounded-xl px-3 py-2 text-sm">
                <option value="">All Forms</option>
                @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
                <option value="{{ $f }}" {{ ($formLevel ?? '') === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold">Filter</button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['Total Students', $performanceData['total_students'], 'text-blue-600'],
            ['Completion Rate', $performanceData['completion_rate'] . '%', 'text-green-600'],
            ['Avg Career Match', $performanceData['avg_match_score'] . '%', 'text-purple-600'],
            ['Avg Academic Score', $performanceData['avg_academic_score'] . '%', 'text-orange-600'],
        ] as [$label, $val, $color])
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-2xl font-black {{ $color }}">{{ $val }}</p>
            <p class="text-xs text-black/50 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-black/10 flex items-center justify-between">
            <h2 class="font-semibold text-black/80">Student List</h2>
            <a href="{{ route('teacher.reports.class-performance') }}?form_level={{ $formLevel }}&format=csv"
               class="text-xs text-blue-600 hover:underline font-semibold">Export CSV</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Student</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Form</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Assessment</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Top Career Match</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @forelse($performanceData['students'] as $student)
                <tr class="hover:bg-black/[0.01]">
                    <td class="px-5 py-3 font-semibold text-black/80">{{ $student->name }}</td>
                    <td class="px-5 py-3 text-black/60">{{ $student->studentProfile?->form_level ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @php $done = $student->assessmentAttempts?->where('status','completed')->isNotEmpty() ?? false; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $done ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                            {{ $done ? 'Done' : 'Pending' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @php $best = $student->recommendations?->where('type','career')->max('confidence_score') ?? 0; @endphp
                        <span class="font-semibold {{ $best >= 60 ? 'text-green-600' : 'text-red-500' }}">{{ round($best) }}%</span>
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('teacher.student.detail', $student->id) }}" class="text-blue-600 hover:underline text-xs font-semibold">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-black/40">No students found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
