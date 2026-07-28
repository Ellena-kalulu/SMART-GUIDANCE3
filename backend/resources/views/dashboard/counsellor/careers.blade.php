@extends('layouts.dashboard')

@section('title', 'Career Library')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Counsellor</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Career Library</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-black/80">Career Library</h1>
        <p class="text-black/50 text-sm mt-1">Browse all available career pathways and subject requirements.</p>
    </div>

    <!-- Search & Category Filter -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" id="careerSearch" placeholder="Search careers…"
                   class="w-full pl-10 pr-4 py-2.5 border border-black/15 rounded-xl text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500"
                   oninput="filterCareers()">
        </div>
        <select id="categoryFilter" onchange="filterCareers()"
                class="px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All Categories</option>
            @foreach($careers->pluck('category')->unique()->sort() as $cat)
            <option>{{ $cat }}</option>
            @endforeach
        </select>
    </div>

    <!-- Career Cards Grid -->
    @php
        $categoryColors = [
            'Technology'  => ['bg-blue-100',   'text-blue-700',   'bg-blue-50'],
            'Health'      => ['text-blue-100',  'text-blue-700',  'text-blue-50'],
            'Business'    => ['text-blue-100', 'text-blue-700', 'text-blue-50'],
            'Engineering' => ['text-blue-100', 'text-blue-700', 'text-blue-50'],
            'Education'   => ['text-blue-100',   'text-blue-700',   'text-blue-50'],
            'Agriculture' => ['text-blue-100',   'text-blue-700',   'text-blue-50'],
            'Arts'        => ['text-blue-100',   'text-blue-700',   'text-blue-50'],
            'Law'         => ['text-blue-100', 'text-blue-700', 'text-blue-50'],
        ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" id="careersGrid">
        @forelse($careers as $career)
        @php
            $colors = $categoryColors[$career->category] ?? ['bg-black/[0.03]','text-black/70','bg-white'];
        @endphp
        <div class="career-card bg-white rounded-2xl shadow-sm border border-black/10 p-5 hover:shadow-md transition-shadow"
             data-name="{{ strtolower($career->title) }}"
             data-category="{{ $career->category }}">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 {{ $colors[0] }} rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $colors[1] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="px-2 py-1 {{ $colors[0] }} {{ $colors[1] }} text-xs font-semibold rounded-full">
                    {{ $career->category }}
                </span>
            </div>
            <h3 class="font-bold text-black/80 mb-1">{{ $career->title }}</h3>
            @if($career->description)
            <p class="text-xs text-black/50 mb-3 line-clamp-2">{{ $career->description }}</p>
            @endif
            @if($career->subjectCombinations && $career->subjectCombinations->isNotEmpty())
            <div class="border-t border-black/10 pt-3 mt-3">
                <p class="text-xs font-semibold text-black/50 mb-2">Related Subjects</p>
                <div class="flex flex-wrap gap-1">
                    @foreach($career->subjectCombinations->take(4) as $combo)
                    <span class="px-2 py-0.5 bg-black/[0.03] text-black/60 text-xs rounded-full">{{ $combo->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-black/40 text-sm">
            No careers in the library yet. Contact the admin to add career data.
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
function filterCareers() {
    const search = document.getElementById('careerSearch').value.toLowerCase();
    const cat    = document.getElementById('categoryFilter').value;
    document.querySelectorAll('.career-card').forEach(card => {
        const ok = card.dataset.name.includes(search) && (!cat || card.dataset.category === cat);
        card.style.display = ok ? '' : 'none';
    });
}
</script>
@endpush
@endsection
