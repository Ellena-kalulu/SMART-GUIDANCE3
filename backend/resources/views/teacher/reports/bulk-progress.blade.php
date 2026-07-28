@extends('layouts.dashboard')

@section('title', 'Bulk Progress Report')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Bulk Progress</span>
</nav>
@endsection
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-black/80">Bulk Student Progress Report</h1>
            <p class="text-black/50 text-sm mt-1">{{ $formLevel ?? 'All Forms' }} @if(!empty($stream)) — {{ $stream }} @endif</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}"
               class="px-4 py-2 border border-black/15 text-black/60 rounded-xl text-sm font-semibold hover:bg-black/5 transition-colors">
                Export CSV
            </a>
            <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                Export PDF
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-black/[0.02]">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Student</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Form</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Stream</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Assessment</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Top Career</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Match %</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-black/50">Academic Avg</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @forelse($progressData ?? [] as $i => $row)
                    <tr class="hover:bg-black/[0.01]">
                        <td class="px-5 py-3 text-xs text-black/40">{{ $i + 1 }}</td>
                        <td class="px-5 py-3 font-medium text-black/80 text-sm">{{ $row['name'] }}</td>
                        <td class="px-5 py-3 text-sm text-black/60">{{ $row['form'] }}</td>
                        <td class="px-5 py-3 text-sm text-black/60">{{ $row['stream'] }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $row['assessment_status'] === 'Completed' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                {{ $row['assessment_status'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-sm text-black/70">{{ $row['top_career'] }}</td>
                        <td class="px-5 py-3 text-sm font-semibold {{ $row['match_score'] >= 70 ? 'text-blue-600' : ($row['match_score'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $row['match_score'] }}%
                        </td>
                        <td class="px-5 py-3 text-sm font-semibold text-black/70">{{ $row['academic_average'] }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-10 text-center text-black/40 text-sm">No student data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
