@extends('layouts.dashboard')
@section('title', 'Form Level Comparison')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Form Level Comparison</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Form Level Comparison</h1>
        <p class="text-black/50 text-sm mt-1">Performance metrics compared across Form 1–4.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($comparisonData as $form => $stats)
        <div class="bg-white rounded-2xl border border-black/10 p-5 space-y-3">
            <h2 class="font-bold text-blue-700 text-sm uppercase tracking-wide">{{ $form }}</h2>
            <div class="text-3xl font-black text-black/80">{{ $stats['total_students'] }}</div>
            <p class="text-xs text-black/40">Students</p>
            <div class="space-y-2 pt-2 border-t border-black/5">
                @foreach([
                    ['Completion Rate', $stats['completion_rate'] . '%', 'text-green-600'],
                    ['Avg Career Match', $stats['avg_match_score'] . '%', 'text-purple-600'],
                    ['Avg Academic', $stats['avg_academic_score'] . '%', 'text-blue-600'],
                ] as [$label, $val, $color])
                <div class="flex items-center justify-between text-xs">
                    <span class="text-black/50">{{ $label }}</span>
                    <span class="font-bold {{ $color }}">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-black/10">
            <h2 class="font-semibold text-black/80">Summary Table</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Form</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Students</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Completion Rate</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Avg Career Match</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Avg Academic Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @foreach($comparisonData as $form => $stats)
                <tr class="hover:bg-black/[0.01]">
                    <td class="px-5 py-3 font-semibold text-black/80">{{ $form }}</td>
                    <td class="px-5 py-3 text-black/70">{{ $stats['total_students'] }}</td>
                    <td class="px-5 py-3 font-semibold text-green-600">{{ $stats['completion_rate'] }}%</td>
                    <td class="px-5 py-3 font-semibold text-purple-600">{{ $stats['avg_match_score'] }}%</td>
                    <td class="px-5 py-3 font-semibold text-blue-600">{{ $stats['avg_academic_score'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
