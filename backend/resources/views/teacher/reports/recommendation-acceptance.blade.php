@extends('layouts.dashboard')
@section('title', 'Recommendation Acceptance Rate')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Recommendation Acceptance</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Recommendation Acceptance Rate</h1>
        <p class="text-black/50 text-sm mt-1">How students are responding to career and programme recommendations.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['Total', $acceptanceData['total'], 'text-blue-600'],
            ['Accepted', $acceptanceData['accepted'], 'text-green-600'],
            ['Dismissed', $acceptanceData['dismissed'], 'text-red-500'],
            ['Pending', $acceptanceData['pending'], 'text-yellow-600'],
        ] as [$label, $val, $color])
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-black {{ $color }}">{{ $val }}</p>
            <p class="text-xs text-black/50 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-black/10 p-6 space-y-4">
        <h2 class="font-semibold text-black/80">Acceptance Rate</h2>
        <div class="flex items-center gap-4">
            <div class="text-4xl font-black text-green-600">{{ $acceptanceData['acceptance_rate'] }}%</div>
            <div class="flex-1">
                <div class="h-4 bg-black/5 rounded-full overflow-hidden">
                    <div class="h-full bg-green-500 rounded-full" style="width: {{ $acceptanceData['acceptance_rate'] }}%"></div>
                </div>
                <p class="text-xs text-black/40 mt-1">{{ $acceptanceData['accepted'] }} of {{ $acceptanceData['total'] }} recommendations accepted</p>
            </div>
        </div>

        @if($acceptanceData['total'] > 0)
        <div class="grid grid-cols-3 gap-3 pt-3 border-t border-black/5">
            @foreach([
                ['Accepted', $acceptanceData['accepted'], $acceptanceData['total'], 'bg-green-500'],
                ['Pending',  $acceptanceData['pending'],  $acceptanceData['total'], 'bg-yellow-400'],
                ['Dismissed',$acceptanceData['dismissed'],$acceptanceData['total'], 'bg-red-400'],
            ] as [$label, $val, $total, $color])
            @php $p = $total > 0 ? round(($val / $total) * 100) : 0; @endphp
            <div class="text-center">
                <p class="text-sm font-bold text-black/70">{{ $p }}%</p>
                <p class="text-xs text-black/40">{{ $label }}</p>
                <div class="mt-1 h-1.5 bg-black/5 rounded-full overflow-hidden">
                    <div class="h-full {{ $color }} rounded-full" style="width: {{ $p }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
