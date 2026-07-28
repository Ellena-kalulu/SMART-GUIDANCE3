@extends('layouts.dashboard')

@section('title', 'All Students')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Counsellor</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">All Students</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-black/80">All Students</h1>
        <p class="text-black/50 text-sm mt-1">Review student profiles, assessment results, and career recommendations.</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="searchInput" placeholder="Search by name or email…"
                   class="w-full pl-10 pr-4 py-2.5 border border-black/15 rounded-xl text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500"
                   oninput="filterRows()">
        </div>
        <select id="formFilter" onchange="filterRows()"
                class="px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Forms</option>
            @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
            <option>{{ $f }}</option>
            @endforeach
        </select>
        <select id="needsFilter" onchange="filterRows()"
                class="px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Priority</option>
            <option value="needs-attention">Needs Attention</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Form</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Assessment</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Top Career</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Match Score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Priority</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-black/50">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($students ?? [] as $student)
                    @php
                        $done    = $student->assessmentAttempts->where('status','completed')->isNotEmpty();
                        $topRec  = $student->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
                        $score   = $topRec ? round($topRec->confidence_score) : null;
                        $needsAttention = !$done || ($score !== null && $score < 50);
                    @endphp
                    <tr class="hover:bg-white transition-colors student-row"
                        data-name="{{ strtolower($student->name) }} {{ strtolower($student->email) }}"
                        data-form="{{ $student->studentProfile->form_level ?? '' }}"
                        data-attention="{{ $needsAttention ? 'needs-attention' : 'ok' }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full {{ $needsAttention ? 'bg-blue-100 bg-blue-700' : 'bg-blue-100 text-blue-700' }}
                                            flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($student->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-black/80 text-sm">{{ $student->name }}</p>
                                    <p class="text-xs text-black/40">{{ $student->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/60">{{ $student->studentProfile->form_level ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $done ? 'text-blue-100 text-blue-700' : 'text-blue-100 text-blue-700' }}">
                                {{ $done ? 'Completed' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/70">{{ $topRec?->recommended?->title ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($score)
                            <span class="font-bold {{ $score >= 70 ? 'text-blue-600' : ($score >= 50 ? 'text-blue-600' : 'text-blue-600') }}">
                                {{ $score }}%
                            </span>
                            @else
                            <span class="text-black/40 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($needsAttention)
                            <span class="px-2 py-1 bg-blue-100 bg-blue-700 text-xs font-semibold rounded-full">Needs Attention</span>
                            @else
                            <span class="px-2 py-1 text-blue-100 bg-blue-700 text-xs font-semibold rounded-full">On Track</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('counsellor.student.detail', $student) }}"
                               class="text-blue-600 hover:text-blue-700 font-medium text-sm">View →</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-black/40 text-sm">No students found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterRows() {
    const search  = document.getElementById('searchInput').value.toLowerCase();
    const form    = document.getElementById('formFilter').value;
    const needs   = document.getElementById('needsFilter').value;
    document.querySelectorAll('.student-row').forEach(r => {
        const ok = r.dataset.name.includes(search)
            && (!form  || r.dataset.form === form)
            && (!needs || r.dataset.attention === needs);
        r.style.display = ok ? '' : 'none';
    });
}
</script>
@endpush
@endsection
