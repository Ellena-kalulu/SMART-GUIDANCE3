@extends('layouts.dashboard')
@section('title', 'Student Success Stories')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Success Stories</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="bg-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Report #30</p>
                <h1 class="text-2xl font-bold">Student Success Stories</h1>
                <p class="text-blue-100 text-sm mt-1">Students with high career match scores who followed recommendations</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.success-stories'])
            </div>
        </div>
    </div>

    {{-- Stories Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse($stories as $story)
        <div class="bg-white rounded-2xl border border-black/10 p-6">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl border-blue-100 flex items-center justify-center bg-blue-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($story['name'], 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h3 class="font-bold text-black/80">{{ $story['name'] }}</h3>
                    <p class="text-xs text-black/50">{{ $story['form'] }} &mdash; {{ $story['stream'] }}</p>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-black/50">Top Career Path</span>
                            <span class="text-xs font-semibold text-black/80">{{ $story['top_career'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-black/50">Match Score</span>
                            <span class="text-sm font-bold text-blue-600">{{ $story['match_score'] }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-black/50">Saved Recommendations</span>
                            <span class="text-xs font-semibold text-blue-700">{{ $story['saved_recommendations'] }}</span>
                        </div>
                    </div>
                    <div class="mt-3 w-full h-2 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-blue-500 rounded-full" style="width:{{ $story['match_score'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white rounded-2xl border border-black/10 p-12 text-center">
            <svg class="w-12 h-12 text-black/20 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            <p class="text-black/50 font-medium">No success stories yet</p>
            <p class="text-black/40 text-sm mt-1">Students with a match score of 80%+ and 2+ saved recommendations will appear here.</p>
        </div>
        @endforelse
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
