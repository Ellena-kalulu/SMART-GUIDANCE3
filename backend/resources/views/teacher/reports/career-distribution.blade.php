@extends('layouts.dashboard')
@section('title', 'Career Interest Distribution')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Career Interest Distribution</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Career Interest Distribution</h1>
        <p class="text-black/50 text-sm mt-1">{{ $distribution['total_students'] }} students — top career interests by category.</p>
    </div>

    @php $dist = $distribution['distribution'] ?? []; arsort($dist); @endphp

    @if(empty($dist))
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <p class="text-black/40">No career recommendation data available yet.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-black/10">
            <h2 class="font-semibold text-black/80">Career Categories</h2>
        </div>
        <div class="p-5 space-y-3">
            @php $max = max($dist); @endphp
            @foreach($dist as $category => $count)
            @php $pct = $distribution['total_students'] > 0 ? round(($count / $distribution['total_students']) * 100) : 0; @endphp
            <div>
                <div class="flex items-center justify-between mb-1 text-sm">
                    <span class="font-medium text-black/70">{{ $category }}</span>
                    <span class="text-black/50">{{ $count }} student{{ $count !== 1 ? 's' : '' }} ({{ $pct }}%)</span>
                </div>
                <div class="h-3 bg-black/5 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full transition-all" style="width: {{ $max > 0 ? round(($count/$max)*100) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
