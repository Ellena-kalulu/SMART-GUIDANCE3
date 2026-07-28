@props(['routeName'])

<div class="flex items-center gap-2 flex-wrap">
    <a href="{{ route($routeName, array_merge(request()->query(), ['format' => 'pdf'])) }}"
       class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Download PDF
    </a>
    <a href="{{ route($routeName, array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Download CSV
    </a>
    <button type="button" onclick="window.print()"
            class="flex items-center gap-2 px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Print
    </button>
</div>
