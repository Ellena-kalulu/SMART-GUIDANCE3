@extends('layouts.dashboard')
@section('title', 'Career Fair Recommendations')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Career Fair Recommendations</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Report #29</p>
                <h1 class="text-2xl font-bold">Career Fair Recommendations</h1>
                <p class="text-blue-100 text-sm mt-1">Suggested career clusters to feature based on student interest data</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.career-fair'])
            </div>
        </div>
    </div>

    {{-- Intro note --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-blue-800">These career clusters are ranked by student interest frequency from assessment data. Prioritise inviting speakers and exhibitors from the top-ranked categories.</p>
        </div>
    </div>

    {{-- Career Clusters --}}
    @php $colors = ['text-blue']; @endphp
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @forelse($recommendations as $category => $info)
        @php $color = $colors[$loop->index % count($colors)]; @endphp
        <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-{{ $color }}-100 flex items-center justify-center text-{{ $color }}-700 font-bold text-sm">
                        {{ $loop->iteration }}
                    </div>
                    <h3 class="font-bold text-black/80">{{ $category }}</h3>
                </div>
                <span class="px-3 py-1 bg-{{ $color }}-50 text-{{ $color }}-700 rounded-full text-xs font-semibold">
                    {{ number_format($info['count']) }} students
                </span>
            </div>
            <div class="p-5">
                <p class="text-xs font-semibold text-black/50 uppercase tracking-wide mb-3">Top Careers to Feature</p>
                <div class="space-y-2">
                    @foreach($info['careers'] as $career)
                    <div class="flex items-center justify-between py-2 border-b border-black/5 last:border-0">
                        <span class="text-sm text-black/70">{{ $career['title'] }}</span>
                        <span class="text-xs font-semibold text-{{ $color }}-600">{{ $career['avg_score'] }}% avg match</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-white rounded-2xl border border-black/10 p-8 text-center text-black/40 text-sm">
            No recommendation data available yet.
        </div>
        @endforelse
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
