@extends('layouts.dashboard')
@section('title', 'Career Path Tracking')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Career Path Tracking</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-black/30 text-xs font-semibold uppercase tracking-widest mb-1">Report #32</p>
                <h1 class="text-2xl font-bold">Career Path Tracking</h1>
                <p class="text-black/30 text-sm mt-1">Longitudinal tracking of students' career preference changes over time</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.career-tracking'])
            </div>
        </div>
    </div>

    {{-- Summary --}}
    @php
        $improving = collect($trackingData)->where('trend', 'Improving ↑')->count();
        $stable    = collect($trackingData)->where('trend', 'Stable →')->count();
        $declining = collect($trackingData)->where('trend', 'Declining ↓')->count();
    @endphp
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $improving }}</p>
            <p class="text-xs text-black/50 mt-1">Improving</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-black/60">{{ $stable }}</p>
            <p class="text-xs text-black/50 mt-1">Stable</p>
        </div>
        <div class="bg-white rounded-2xl border text-blue-100 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $declining }}</p>
            <p class="text-xs text-black/50 mt-1">Declining</p>
        </div>
    </div>

    {{-- Tracking Table --}}
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-6 py-4 bg-blue-600 border-black/10">
            <h2 class="font-bold text-white">Student Career Trajectories</h2>
            <p class="text-xs text-white mt-0.5">{{ count($trackingData) }} students with trackable career history</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Latest Career</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-black/50 uppercase">Assessments</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-black/50 uppercase">Trend</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">History</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse($trackingData as $record)
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-3 font-medium text-black/80">{{ $record['student_name'] }}</td>
                        <td class="px-6 py-3 text-sm text-black/70">{{ $record['latest_career'] }}</td>
                        <td class="px-6 py-3 text-center text-sm text-black/60">{{ count($record['history']) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="px-2 py-1 rounded-lg text-xs font-semibold {{
                                str_contains($record['trend'], '↑') ? 'bg-blue-50 text-blue-700' :
                                (str_contains($record['trend'], '↓') ? 'bg-blue-50 bg-blue-600' :
                                'bg-black/[0.03] text-black/60')
                            }}">{{ $record['trend'] }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($record['history'] as $h)
                                <span class="px-2 py-0.5 bg-black/[0.03] text-black/70 rounded text-xs" title="{{ $h['date'] }}">
                                    {{ $h['career'] }} ({{ $h['score'] }}%)
                                </span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-black/40 text-sm">
                            No students have taken the assessment more than once — longitudinal data will appear when they retake it.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
