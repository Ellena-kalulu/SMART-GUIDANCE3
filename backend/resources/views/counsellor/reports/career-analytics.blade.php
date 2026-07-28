@extends('layouts.dashboard')
@section('title', 'School-Wide Career Analytics')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Career Analytics</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-600 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Report #24</p>
                <h1 class="text-2xl font-bold">School-Wide Career Analytics</h1>
                <p class="text-blue-100 text-sm mt-1">Comprehensive career interest analysis with demographic breakdowns</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.career-analytics'])
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-black/15 p-5">
        <form method="GET" action="{{ route('counsellor.reports.career-analytics') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-black/60 mb-1">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="px-3 py-2 border border-black/15 rounded-lg text-sm focus:outline-none focus:border-blue-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-black/60 mb-1">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="px-3 py-2 border border-black/15 rounded-lg text-sm focus:outline-none focus:border-blue-400">
            </div>
            <div>
                <label class="block text-xs font-medium text-black/60 mb-1">Form Level</label>
                <select name="form_level" class="px-3 py-2 border border-black/15 rounded-lg text-sm focus:outline-none focus:border-blue-400">
                    <option value="">All Forms</option>
                    @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
                    <option value="{{ $f }}" {{ request('form_level') == $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-black/60 mb-1">Stream</label>
                <select name="stream" class="px-3 py-2 border border-black/15 rounded-lg text-sm focus:outline-none focus:border-blue-400">
                    <option value="">All Streams</option>
                    @foreach(['Sciences','Humanities','Languages','Commerce','General'] as $s)
                    <option value="{{ $s }}" {{ request('stream') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">Apply Filters</button>
            <a href="{{ route('counsellor.reports.career-analytics') }}" class="px-5 py-2 border border-black/15 text-black/60 rounded-lg text-sm font-medium hover:bg-white transition-colors">Reset</a>
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Students',        $data['total_students'],        'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['Completed Assessments', $data['completed_assessments'], 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['Completion Rate',       $data['completion_rate'].'%',  'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['Career Categories',     count($data['career_interests']),'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ] as [$label, $val, $icon])
        <div class="bg-white rounded-2xl border border-blue-100 p-5 flex items-center gap-4">
            <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-blue-700">{{ $val }}</p>
                <p class="text-xs text-black/50">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Career Interests Distribution --}}
        <div class="bg-white rounded-2xl border border-black/10 p-6">
            <h2 class="font-bold text-black/80 mb-5">Career Interest Distribution</h2>
            <div class="space-y-3">
                @forelse($data['career_interests'] as $category => $count)
                @php $pct = $data['total_students'] > 0 ? round(($count / $data['total_students']) * 100) : 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-black/70">{{ $category }}</span>
                        <span class="text-black/50">{{ $count }} students ({{ $pct }}%)</span>
                    </div>
                    <div class="w-full h-2.5 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-black/40 text-sm text-center py-4">No data available</p>
                @endforelse
            </div>
        </div>

        {{-- Form & Stream Breakdown --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-black/10 p-6">
                <h2 class="font-bold text-black/80 mb-4">Breakdown by Form</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($data['form_breakdown'] as $form => $count)
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <p class="text-xl font-bold text-blue-700">{{ $count }}</p>
                        <p class="text-xs text-black/60">{{ $form }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-black/10 p-6">
                <h2 class="font-bold text-black/80 mb-4">Breakdown by Stream</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($data['stream_breakdown'] as $stream => $count)
                    <div class="bg-blue-50 rounded-xl p-3 text-center">
                        <p class="text-xl font-bold text-blue-700">{{ $count }}</p>
                        <p class="text-xs text-black/60">{{ $stream ?? 'N/A' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
