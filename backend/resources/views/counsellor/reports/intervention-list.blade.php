@extends('layouts.dashboard')
@section('title', 'Student Intervention Priority List')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Intervention Priority List</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-white text-xs font-semibold uppercase tracking-widest mb-1">Report #25</p>
                <h1 class="text-2xl font-bold">Student Intervention Priority List</h1>
                <p class="text-white text-sm mt-1">Ranked list of students needing immediate career guidance</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.intervention-list'])
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @php
            $highCount   = collect($students)->where('priority', 'High')->count();
            $mediumCount = collect($students)->where('priority', 'Medium')->count();
            $noAssess    = collect($students)->where('has_assessment', false)->count();
        @endphp
        <div class="bg-white rounded-2xl border border-blue-100 p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $highCount }}</p>
                <p class="text-xs text-black/50">High Priority</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $mediumCount }}</p>
                <p class="text-xs text-black/50">Medium Priority</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-black/[0.03] rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-black/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-black/80">{{ $noAssess }}</p>
                <p class="text-xs text-black/50">No Assessment Yet</p>
            </div>
        </div>
    </div>

    {{-- Student Table --}}
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <div>
                <h2 class="font-bold text-black/80">Priority List</h2>
                <p class="text-xs text-black/50 mt-0.5">{{ count($students) }} students requiring guidance</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Form</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Stream</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-black/50 uppercase">Assessment</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-black/50 uppercase">Best Match</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-black/50 uppercase">Priority</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Recommended Action</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-black/50 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse($students as $i => $student)
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-3 text-sm text-black/40">{{ $i + 1 }}</td>
                        <td class="px-6 py-3">
                            <p class="font-medium text-black/80">{{ $student['name'] }}</p>
                        </td>
                        <td class="px-6 py-3 text-sm text-black/60">{{ $student['form'] }}</td>
                        <td class="px-6 py-3 text-sm text-black/60">{{ $student['stream'] }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($student['has_assessment'])
                                <span class="px-2 py-1 bg-blue-50 bg-blue-700 rounded-lg text-xs font-medium">Done</span>
                            @else
                                <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center font-semibold {{ $student['best_match_score'] >= 60 ? 'text-blue-600' : ($student['best_match_score'] >= 40 ? 'text-blue-600' : 'text-blue-500') }}">
                            {{ $student['best_match_score'] }}%
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold {{ $student['priority'] === 'High' ? 'bg-blue-100 text-blue-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $student['priority'] }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-black/60">{{ $student['recommended_action'] }}</td>
                        <td class="px-6 py-3 text-center">
                            <a href="{{ route('counsellor.students') }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-6 py-8 text-center text-black/40 text-sm">All students are on track — no interventions needed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
