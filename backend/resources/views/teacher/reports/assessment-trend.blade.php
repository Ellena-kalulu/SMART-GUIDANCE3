@extends('layouts.dashboard')
@section('title', 'Assessment Completion Trend')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Assessment Trend</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Assessment Completion Trend</h1>
        <p class="text-black/50 text-sm mt-1">Monthly completions over the last 6 months.</p>
    </div>

    @php
        $labels = $trendData['labels'] ?? collect();
        $data   = $trendData['data']   ?? collect();
        $maxVal = $data->max() ?: 1;
    @endphp

    @if($labels->isEmpty())
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <p class="text-black/40">No assessment completion data available yet.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-black/10 p-6">
        <div class="flex items-end gap-3 h-40">
            @foreach($labels as $i => $label)
            @php $val = $data[$i] ?? 0; $pct = round(($val / $maxVal) * 100); @endphp
            <div class="flex-1 flex flex-col items-center gap-1">
                <span class="text-xs font-bold text-blue-600">{{ $val }}</span>
                <div class="w-full bg-blue-100 rounded-t-lg relative" style="height: {{ max($pct, 4) }}%">
                    <div class="absolute inset-0 bg-blue-500 rounded-t-lg"></div>
                </div>
                <span class="text-xs text-black/50 text-center leading-tight">{{ $label }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-black/10">
            <h2 class="font-semibold text-black/80">Monthly Data</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Month</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Completions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @foreach($labels as $i => $label)
                <tr class="hover:bg-black/[0.01]">
                    <td class="px-5 py-3 text-black/70">{{ $label }}</td>
                    <td class="px-5 py-3 font-semibold text-blue-600">{{ $data[$i] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
