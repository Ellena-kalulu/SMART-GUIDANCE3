@extends('layouts.dashboard')
@section('title', 'University Placement Prediction')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">University Placement Prediction</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-black/80">University Placement Prediction</h1>
            <p class="text-black/50 text-sm mt-1">{{ $formLevel ?? 'All Forms' }} — eligibility forecasts based on academic performance.</p>
        </div>
        <form method="GET" action="{{ route('teacher.reports.placement-prediction') }}" class="flex gap-2">
            <select name="form_level" class="border border-black/15 rounded-xl px-3 py-2 text-sm">
                <option value="">All Forms</option>
                @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
                <option value="{{ $f }}" {{ ($formLevel ?? '') === $f ? 'selected' : '' }}>{{ $f }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold">Filter</button>
        </form>
    </div>

    @if(empty($predictions))
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <p class="text-black/40">No prediction data available.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">#</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Student</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Academic Avg</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Top University</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Eligible Programmes</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Likelihood</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @foreach($predictions as $i => $p)
                @php
                    $lc = $p['placement_likelihood'] === 'High' ? 'green' : ($p['placement_likelihood'] === 'Medium' ? 'yellow' : 'red');
                @endphp
                <tr class="hover:bg-black/[0.01]">
                    <td class="px-5 py-3 text-black/40">{{ $i + 1 }}</td>
                    <td class="px-5 py-3 font-semibold text-black/80">{{ $p['student_name'] }}</td>
                    <td class="px-5 py-3 font-semibold {{ $p['academic_avg'] >= 70 ? 'text-green-600' : ($p['academic_avg'] >= 50 ? 'text-yellow-600' : 'text-red-500') }}">
                        {{ $p['academic_avg'] }}%
                    </td>
                    <td class="px-5 py-3 text-black/60">{{ $p['top_university'] }}</td>
                    <td class="px-5 py-3 text-black/50 text-xs">{{ $p['eligible_programs'] ?: 'None' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $lc }}-50 text-{{ $lc }}-700">
                            {{ $p['placement_likelihood'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
