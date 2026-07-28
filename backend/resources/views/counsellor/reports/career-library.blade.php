@extends('layouts.dashboard')
@section('title', 'Career Library Report')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Career Library</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-white text-xs font-semibold uppercase tracking-widest mb-1">Report #27</p>
                <h1 class="text-2xl font-bold">Career Library Report</h1>
                <p class="text-white text-sm mt-1">Complete catalog of all careers with subject requirements and university pathways</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.career-library'])
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        @php $categories = $careers->groupBy('category'); @endphp
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold border-blue-600">{{ $careers->count() }}</p>
            <p class="text-xs text-black/50 mt-1">Total Careers</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $categories->count() }}</p>
            <p class="text-xs text-black/50 mt-1">Categories</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5 text-center">
            <p class="text-3xl font-bold border-blue-600">{{ $careers->sum(fn($c) => $c->universityPrograms->count()) }}</p>
            <p class="text-xs text-black/50 mt-1">University Pathways</p>
        </div>
    </div>

    {{-- Careers by Category --}}
    @foreach($categories as $category => $group)
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b text-white border-black/10 bg-blue-600">
            <h2 class="font-bold text-white">{{ $category }}</h2>
            <p class="text-xs text-white">{{ $group->count() }} careers in this category</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Career</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Required Subjects</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">University Programs</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Growth Outlook</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @foreach($group as $career)
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-3">
                            <p class="font-medium text-black/80">{{ $career->title }}</p>
                            @if($career->description)
                            <p class="text-xs text-black/40 mt-0.5 line-clamp-1">{{ $career->description }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($career->subjects->take(3) as $subject)
                                <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs">{{ $subject->code }}</span>
                                @endforeach
                                @if($career->subjects->count() > 3)
                                <span class="text-xs text-black/40">+{{ $career->subjects->count() - 3 }} more</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-black/60">{{ $career->universityPrograms->count() }} programs</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded-lg text-xs font-medium">{{ $career->growth_outlook ?? 'Good' }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
