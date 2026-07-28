@extends('layouts.dashboard')
@section('title', 'University Program Mapping')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">University Mapping</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Report #28</p>
                <h1 class="text-2xl font-bold">University Program Mapping</h1>
                <p class="text-blue-100 text-sm mt-1">All university programs with entry requirements and feeder careers</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.university-mapping'])
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $programs->count() }}</p>
            <p class="text-xs text-black/50 mt-1">Total Programs</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold border-blue-600">{{ $programs->groupBy('university_name')->count() }}</p>
            <p class="text-xs text-black/50 mt-1">Universities</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $programs->sum(fn($p) => $p->careers->count()) }}</p>
            <p class="text-xs text-black/50 mt-1">Career Linkages</p>
        </div>
    </div>

    {{-- Programs Table --}}
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">All University Programs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Program</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">University</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Min Points</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Required Subjects</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Feeder Careers</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse($programs as $program)
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-3">
                            <p class="font-medium text-black/80">{{ $program->name }}</p>
                            @if($program->duration)
                            <p class="text-xs text-black/40">{{ $program->duration }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-black/60">{{ $program->university_name ?? 'N/A' }}</td>
                        <td class="px-6 py-3 text-sm font-semibold text-blue-700">{{ $program->min_points ?? 'N/A' }}</td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($program->subjects->take(4) as $subject)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ $subject->code }}</span>
                                @endforeach
                                @if($program->subjects->count() > 4)
                                <span class="text-xs text-black/40">+{{ $program->subjects->count() - 4 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($program->careers->take(3) as $career)
                                <span class="px-2 py-0.5 bg-blue-50 bg-blue-700 text-white rounded text-xs">{{ $career->title }}</span>
                                @endforeach
                                @if($program->careers->count() > 3)
                                <span class="text-xs text-black/40">+{{ $program->careers->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-black/40 text-sm">No university programs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
