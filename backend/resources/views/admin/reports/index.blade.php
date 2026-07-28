@extends('layouts.dashboard')

@section('title', 'System Reports')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600 transition-colors">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">System Reports</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- ── Header Banner ── --}}
    <div class="bg-gradient-to-r from-black/80 via-blue-900 to-blue-800 rounded-2xl p-6 text-white relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="absolute right-20 bottom-0 w-32 h-32 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="relative z-10 flex items-start justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/15 rounded-xl flex items-center justify-center shrink-0 backdrop-blur-sm border border-white/20">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight">System Reports</h1>
                    <p class="text-blue-300 text-xs mt-0.5">Luwinga Secondary School · Smart Guidance Tool</p>
                    <p class="text-blue-100 text-sm mt-1">Generate, filter, and export analytics across all modules.</p>
                </div>
            </div>
            <div class="text-right shrink-0">
                <p class="text-blue-300 text-xs uppercase tracking-widest">Generated</p>
                <p class="text-white font-semibold text-sm">{{ now()->format('D, d M Y') }}</p>
                <p class="text-blue-300 text-xs">{{ now()->format('g:i A') }}</p>
            </div>
        </div>
    </div>

    {{-- ── Report Configuration Card ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-black/15 overflow-hidden">

        {{-- Card Header --}}
        <div class="flex items-center justify-between px-6 py-3.5 border-b border-black/10 bg-white">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <h2 class="font-semibold text-black/70 text-sm">Report Configuration</h2>
            </div>
            <span class="text-xs text-black/40">Complete all steps below</span>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}">

            {{-- ── Step 1: Report Type (collapsible) ── --}}
            @php $reportTypeOpen = !$reportType; @endphp
            <div class="border-b border-black/10" id="report-type-section">

                {{-- Toggle Header --}}
                <button type="button" id="report-type-toggle"
                    onclick="toggleReportType()"
                    class="w-full flex items-center gap-2.5 px-6 py-4 text-left hover:bg-white transition-colors duration-150">

                    <div class="w-6 h-6 bg-blue-600 text-white rounded-full text-xs font-bold flex items-center justify-center shrink-0">1</div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-black/70 flex items-center gap-2 flex-wrap">
                            Select Report Type
                            <span class="bg-blue-600">*</span>
                            @if($reportType)
                            @php $selCard = collect([
                                ['value'=>'user_analytics','label'=>'User Analytics','icon_color'=>'text-blue-600','icon_bg'=>'bg-blue-100'],
                                ['value'=>'student_analytics','label'=>'Student Analytics','icon_color'=>'text-blue-600','icon_bg'=>'text-blue-100'],
                                ['value'=>'assessment_analytics','label'=>'Assessment Analytics','icon_color'=>'text-blue-600','icon_bg'=>'text-blue-100'],
                                ['value'=>'career_analytics','label'=>'Career Analytics','icon_color'=>'text-blue-600','icon_bg'=>'text-blue-100'],
                                ['value'=>'recommendation_analytics','label'=>'Recommendations','icon_color'=>'text-blue-600','icon_bg'=>'text-blue-100'],
                                ['value'=>'university_analytics','label'=>'University Analytics','icon_color'=>'text-blue-600','icon_bg'=>'text-blue-100'],
                                ['value'=>'academic_performance','label'=>'Academic Performance','icon_color'=>'text-blue-600','icon_bg'=>'bg-blue-100'],
                                ['value'=>'activity_logs','label'=>'Activity Logs','icon_color'=>'text-black/60','icon_bg'=>'bg-black/[0.03]'],
                                ['value'=>'engagement_summary','label'=>'Engagement Summary','icon_color'=>'text-blue-600','icon_bg'=>'text-blue-100'],
                            ])->firstWhere('value', $reportType); @endphp
                            <span id="report-type-pill"
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ $selCard['label'] ?? $reportType }}
                            </span>
                            @endif
                        </p>
                        <p class="text-xs text-black/40">Choose what data to include in your report</p>
                    </div>

                    {{-- Chevron --}}
                    <svg id="report-type-chevron"
                        class="w-5 h-5 text-black/40 shrink-0 transition-transform duration-200 {{ $reportTypeOpen ? 'rotate-180' : '' }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Collapsible Body --}}
                <div id="report-type-body"
                    class="overflow-hidden transition-all duration-300 ease-in-out"
                    style="{{ $reportTypeOpen ? '' : 'max-height:0;' }}">
                    <div class="px-6 pb-6 pt-1">

                @php
                $reportCards = [
                    [
                        'value'      => 'user_analytics',
                        'label'      => 'User Analytics',
                        'desc'       => 'Users by role & status',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'bg-blue-100',
                        'icon'       => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'value'      => 'student_analytics',
                        'label'      => 'Student Analytics',
                        'desc'       => 'Demographics & assessment status',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
                    ],
                    [
                        'value'      => 'assessment_analytics',
                        'label'      => 'Assessment Analytics',
                        'desc'       => 'Completion rates & trends',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    ],
                    [
                        'value'      => 'career_analytics',
                        'label'      => 'Career Analytics',
                        'desc'       => 'Career interest & match scores',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    ],
                    [
                        'value'      => 'recommendation_analytics',
                        'label'      => 'Recommendations',
                        'desc'       => 'Generation & acceptance rates',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                    ],
                    [
                        'value'      => 'university_analytics',
                        'label'      => 'University Analytics',
                        'desc'       => 'Program popularity & matches',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                    ],
                    [
                        'value'      => 'academic_performance',
                        'label'      => 'Academic Performance',
                        'desc'       => 'Student scores & grades',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    ],
                    [
                        'value'      => 'activity_logs',
                        'label'      => 'Activity Logs',
                        'desc'       => 'System events & user actions',
                        'icon_color' => 'text-black/60',
                        'icon_bg'    => 'bg-black/[0.03]',
                        'icon'       => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    ],
                    [
                        'value'      => 'engagement_summary',
                        'label'      => 'Engagement Summary',
                        'desc'       => 'Key metrics overview',
                        'icon_color' => 'text-blue-600',
                        'icon_bg'    => 'text-blue-100',
                        'icon'       => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    ],
                ];
                @endphp

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
                    @foreach($reportCards as $card)
                    @php $isSelected = ($reportType ?? '') === $card['value']; @endphp
                    <label class="relative block cursor-pointer select-none">
                        <input type="radio" name="report_type" value="{{ $card['value'] }}"
                            class="sr-only peer" {{ $isSelected ? 'checked' : '' }} required>

                        {{-- Card body — direct sibling of input so peer-checked: works --}}
                        <div class="rounded-xl border-2 transition-all duration-150 p-3
                            {{ $isSelected
                                ? 'border-blue-500 bg-blue-50 shadow-sm'
                                : 'border-black/15 bg-white hover:border-blue-300 hover:bg-blue-50/40' }}
                            peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-sm">
                            <div class="w-9 h-9 {{ $card['icon_bg'] }} rounded-lg flex items-center justify-center mb-2.5">
                                <svg class="w-4.5 h-4.5 {{ $card['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card['icon'] }}"/>
                                </svg>
                            </div>
                            <p class="text-xs font-semibold text-black/70 leading-tight">{{ $card['label'] }}</p>
                            <p class="text-xs text-black/40 mt-0.5 leading-tight">{{ $card['desc'] }}</p>
                        </div>

                        {{-- Checkmark badge — also direct sibling of input --}}
                        <div class="absolute top-1.5 right-1.5 w-5 h-5 bg-blue-600 rounded-full
                            flex items-center justify-center pointer-events-none transition-all duration-150
                            {{ $isSelected ? 'opacity-100 scale-100' : 'opacity-0 scale-0' }}
                            peer-checked:opacity-100 peer-checked:scale-100">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Description hint for selected type --}}
                @if($reportType)
                @php $sel = collect($reportCards)->firstWhere('value', $reportType); @endphp
                <div class="mt-3 flex items-center gap-2 text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                    <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><strong>{{ $sel['label'] ?? $reportType }}</strong> — {{ $sel['desc'] ?? '' }}</span>
                </div>
                @endif

                    </div>{{-- /padding wrapper --}}
                </div>{{-- /report-type-body --}}
            </div>{{-- /report-type-section --}}

            {{-- ── Step 2: Filters (collapsible) ── --}}
            @php
                $activeFilters = collect([
                    'Date From'       => $dateFrom ?? '',
                    'Date To'         => $dateTo ?? '',
                    'Role'            => $role ?? '',
                    'Form Level'      => $formLevel ?? '',
                    'Stream'          => $stream ?? '',
                    'Career Category' => $category ?? '',
                    'Status'          => $status ?? '',
                ])->filter(fn($v) => $v !== '');
                $filtersOpen = $activeFilters->isNotEmpty();
            @endphp

            <div class="border-b border-black/10 bg-white/60" id="filters-section">

                {{-- Toggle Header --}}
                <button type="button" id="filters-toggle"
                    onclick="toggleFilters()"
                    class="w-full flex items-center gap-2.5 px-6 py-4 text-left hover:bg-black/[0.03]/60 transition-colors duration-150 group">

                    <div class="w-6 h-6 bg-black/40 text-white rounded-full text-xs font-bold flex items-center justify-center shrink-0">2</div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-black/70 flex items-center gap-2 flex-wrap">
                            Apply Filters
                            <span class="text-xs font-normal text-black/40">(optional)</span>
                            @if($activeFilters->isNotEmpty())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V15a1 1 0 01-.553.894l-4 2A1 1 0 017 17v-6.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                                </svg>
                                {{ $activeFilters->count() }} active
                            </span>
                            @endif
                        </p>
                        <p class="text-xs text-black/40">Narrow results by date range, role, stream, or category</p>
                    </div>

                    {{-- Active filter pills (collapsed summary) --}}
                    @if($activeFilters->isNotEmpty())
                    <div id="filters-pills" class="hidden items-center gap-1.5 flex-wrap">
                        @foreach($activeFilters->take(3) as $label => $val)
                        <span class="px-2 py-0.5 bg-blue-50 border border-blue-200 text-blue-700 rounded text-xs">{{ $label }}: {{ $val }}</span>
                        @endforeach
                        @if($activeFilters->count() > 3)
                        <span class="px-2 py-0.5 bg-black/[0.03] text-black/50 rounded text-xs">+{{ $activeFilters->count() - 3 }} more</span>
                        @endif
                    </div>
                    @endif

                    {{-- Chevron --}}
                    <svg id="filters-chevron"
                        class="w-5 h-5 text-black/40 shrink-0 transition-transform duration-200 {{ $filtersOpen ? 'rotate-180' : '' }}"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Collapsible Body --}}
                <div id="filters-body"
                    class="overflow-hidden transition-all duration-300 ease-in-out"
                    style="{{ $filtersOpen ? '' : 'max-height:0;' }}">
                    <div class="px-6 pb-6 pt-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Date Range block --}}
                            <div class="bg-white border border-black/15 rounded-xl p-4">
                                <p class="text-xs font-semibold text-black/50 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    Date Range
                                </p>
                                <div class="flex gap-3 items-center">
                                    <div class="flex-1">
                                        <label class="block text-xs text-black/50 mb-1">From</label>
                                        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                                    </div>
                                    <div class="text-black/30 mt-4">→</div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-black/50 mb-1">To</label>
                                        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                                    </div>
                                </div>
                            </div>

                            {{-- User & Academic Filters block --}}
                            <div class="bg-white border border-black/15 rounded-xl p-4">
                                <p class="text-xs font-semibold text-black/50 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                    </svg>
                                    User &amp; Academic Filters
                                </p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-black/50 mb-1">Role</label>
                                        <select name="role"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="">All Roles</option>
                                            @foreach($filterOptions['roles'] as $r)
                                                <option value="{{ $r }}" {{ ($role ?? '') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-black/50 mb-1">Status</label>
                                        <select name="status"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="">All</option>
                                            @foreach($filterOptions['statuses'] as $s)
                                                <option value="{{ $s }}" {{ ($status ?? '') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-black/50 mb-1">Form Level</label>
                                        <select name="form_level"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="">All Forms</option>
                                            @foreach($filterOptions['form_levels'] as $f)
                                                <option value="{{ $f }}" {{ ($formLevel ?? '') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-black/50 mb-1">Stream</label>
                                        <select name="stream"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="">All Streams</option>
                                            @foreach($filterOptions['streams'] as $s)
                                                <option value="{{ $s }}" {{ ($stream ?? '') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs text-black/50 mb-1">Career Category</label>
                                        <select name="category"
                                            class="w-full px-3 py-2 text-sm border border-black/15 rounded-lg
                                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                            <option value="">All Categories</option>
                                            @foreach($filterOptions['career_categories'] as $c)
                                                <option value="{{ $c }}" {{ ($category ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                            @endforeach
                                        </select>
                                    </div>{{-- /.col-span-2 --}}
                                </div>{{-- /.grid-cols-2 --}}
                            </div>{{-- /User & Academic block --}}
                        </div>{{-- /.grid-cols-2-outer --}}
                    </div>{{-- /padding wrapper --}}
                </div>{{-- /filters-body --}}
            </div>{{-- /filters-section --}}

            {{-- ── Step 3: Output & Actions ── --}}
            <div class="p-6">

                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-6 h-6 bg-black/40 text-white rounded-full text-xs font-bold flex items-center justify-center shrink-0">3</div>
                    <div>
                        <p class="text-sm font-semibold text-black/70">Choose Output &amp; Generate</p>
                        <p class="text-xs text-black/40">View in browser, download as PDF, or export as CSV</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4">

                    {{-- Output format segmented control --}}
                    <div class="inline-flex rounded-xl border border-black/15 shadow-sm overflow-hidden bg-white">
                        <label class="cursor-pointer">
                            <input type="radio" name="format" value="html" class="sr-only peer"
                                {{ (!request('format') || request('format') === 'html') ? 'checked' : '' }}>
                            <div class="px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-all duration-150
                                        text-black/60 hover:bg-white
                                        peer-checked:bg-white peer-checked:text-black/80 peer-checked:shadow-sm peer-checked:font-semibold
                                        border-r border-black/15">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                View
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="format" value="pdf" class="sr-only peer"
                                {{ request('format') === 'pdf' ? 'checked' : '' }}>
                            <div class="px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-all duration-150
                                        text-black/60 hover:bg-white
                                        peer-checked:bg-white peer-checked:text-blue-700 peer-checked:shadow-sm peer-checked:font-semibold
                                        border-r border-black/15">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="format" value="csv" class="sr-only peer"
                                {{ request('format') === 'csv' ? 'checked' : '' }}>
                            <div class="px-4 py-2.5 text-sm font-medium flex items-center gap-2 transition-all duration-150
                                        text-black/60 hover:bg-white
                                        peer-checked:bg-white peer-checked:bg-blue-700 peer-checked:shadow-sm peer-checked:font-semibold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                CSV
                            </div>
                        </label>
                    </div>

                    {{-- Primary action --}}
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700
                               text-white font-semibold text-sm rounded-xl transition-all shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate Report
                    </button>

                    {{-- Reset --}}
                    <a href="{{ route('admin.reports.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 border border-black/20
                               text-black/60 font-medium text-sm rounded-xl hover:bg-white transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Results Section ── --}}
    @if(isset($reportType) && $reportType)
    <div class="bg-white rounded-2xl shadow-sm border border-black/15 overflow-hidden animate-fadeIn">

        {{-- Report Header --}}
        <div class="px-6 py-4 border-b border-black/10 bg-gradient-to-r from-white to-white">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-1 h-5 bg-blue-600 rounded-full"></div>
                        <h2 class="text-base font-bold text-black/80">{{ $data['title'] ?? 'Report' }}</h2>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs text-black/40 ml-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ now()->format('F j, Y · g:i A') }}
                        </span>
                        @if(($dateFrom ?? '') || ($dateTo ?? ''))
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $dateFrom ?: 'All time' }} — {{ $dateTo ?: 'Today' }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1 text-blue-600 font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            {{ number_format(count($data['rows'])) }} records
                        </span>
                    </div>
                </div>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-black/20
                           text-black/60 rounded-lg hover:bg-white transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        @if(isset($data['summary']) && count($data['summary']) > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 p-5 bg-white/50 border-b border-black/10">
            @foreach(array_slice($data['summary'], 0, 8) as $key => $value)
            <div class="bg-white rounded-xl p-3 text-center shadow-sm border border-black/10">
                <p class="text-xs text-black/40 uppercase tracking-wide mb-1 leading-tight">{{ str_replace('_', ' ', ucfirst($key)) }}</p>
                <p class="text-xl font-bold text-black/80">
                    @if(is_array($value))
                        {{ number_format(count($value)) }}
                    @elseif(is_numeric($value))
                        {{ number_format($value) }}
                    @else
                        {{ $value }}
                    @endif
                </p>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Data Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-black/80 text-white">
                        @foreach($data['headers'] as $header)
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider whitespace-nowrap">
                            {{ $header }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($data['rows'] as $row)
                    <tr class="hover:bg-blue-50/40 transition-colors duration-100">
                        @foreach($row as $cell)
                        <td class="px-4 py-3 text-sm text-black/70 whitespace-nowrap">
                            @if(is_numeric($cell) && strlen((string)$cell) > 4 && !str_contains((string)$cell, '%'))
                                {{ number_format($cell) }}
                            @else
                                {{ $cell }}
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($data['headers']) }}" class="px-4 py-20 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-black/[0.03] rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-black/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-black/50 font-semibold">No records found</p>
                                <p class="text-xs text-black/40 mt-1">Try adjusting your date range or filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer --}}
        <div class="px-6 py-3 border-t border-black/10 bg-white/50 flex justify-between items-center flex-wrap gap-2">
            <div class="text-xs text-black/40">
                Report ID: <span class="font-mono font-medium text-black/60">{{ strtoupper(substr(md5(now()->toDateTimeString()), 0, 8)) }}</span>
            </div>
            <div class="text-xs text-black/40">
                Showing <span class="font-semibold text-black/60">{{ count($data['rows']) }}</span> records
            </div>
        </div>
    </div>

    @else

    {{-- ── Empty State ── --}}
    <div class="bg-white rounded-2xl border border-black/15 shadow-sm overflow-hidden">
        <div class="py-16 px-8 text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-black/70 mb-2">No Report Generated Yet</h3>
            <p class="text-sm text-black/50 max-w-sm mx-auto leading-relaxed">
                Select a report type above, apply optional filters, choose your output format, and click <strong>Generate Report</strong>.
            </p>
            <div class="mt-6 flex flex-wrap gap-2 justify-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    User Analytics
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 bg-blue-600 rounded-lg text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                    Student Analytics
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 bg-blue-600 rounded-lg text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Assessment Reports
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 bg-blue-600 rounded-lg text-xs font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Career Analytics
                </span>
            </div>
        </div>
    </div>
    @endif

</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn { animation: fadeIn 0.25s ease-out; }
</style>

<script>
(function () {

    // ── Shared collapse utility ──────────────────────────────────────────────
    function collapseSection(bodyId, chevronId, pillsId, openFlag) {
        var body    = document.getElementById(bodyId);
        var chevron = document.getElementById(chevronId);
        var pills   = pillsId ? document.getElementById(pillsId) : null;

        if (!body) return;

        if (openFlag) {
            body.style.maxHeight = body.scrollHeight + 'px';
            if (chevron) chevron.classList.add('rotate-180');
            if (pills)   pills.classList.add('hidden');
        } else {
            body.style.maxHeight = body.scrollHeight + 'px';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    body.style.maxHeight = '0';
                });
            });
            if (chevron) chevron.classList.remove('rotate-180');
            if (pills)   pills.classList.remove('hidden');
        }
    }

    // ── Step 1: Report Type ──────────────────────────────────────────────────
    var reportTypeOpen = {{ $reportTypeOpen ? 'true' : 'false' }};

    function toggleReportType() {
        reportTypeOpen = !reportTypeOpen;
        collapseSection('report-type-body', 'report-type-chevron', null, reportTypeOpen);

        // Hide/show the selected-type pill in the header based on collapsed state
        var pill = document.getElementById('report-type-pill');
        if (pill) pill.style.display = reportTypeOpen ? 'none' : '';
    }

    window.toggleReportType = toggleReportType;

    // ── Step 2: Filters ──────────────────────────────────────────────────────
    var filtersOpen = {{ $filtersOpen ? 'true' : 'false' }};

    function toggleFilters() {
        filtersOpen = !filtersOpen;
        collapseSection('filters-body', 'filters-chevron', 'filters-pills', filtersOpen);
    }

    window.toggleFilters = toggleFilters;

    // ── Init on DOM ready ────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        // Set initial max-heights so transitions work from a defined value
        var rtBody = document.getElementById('report-type-body');
        if (rtBody) rtBody.style.maxHeight = reportTypeOpen ? rtBody.scrollHeight + 'px' : '0';

        var fBody = document.getElementById('filters-body');
        if (fBody) fBody.style.maxHeight = filtersOpen ? fBody.scrollHeight + 'px' : '0';

        // Hide pill when report type panel is open on load (redundant to show both)
        var pill = document.getElementById('report-type-pill');
        if (pill) pill.style.display = reportTypeOpen ? 'none' : '';
    });

})();
</script>
@endsection
