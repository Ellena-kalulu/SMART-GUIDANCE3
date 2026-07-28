@props(['headers', 'rows', 'searchable' => true, 'actions' => true])

<div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
    @if($searchable)
    <div class="p-4 border-b border-black/10">
        <div class="relative max-w-sm">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" placeholder="Search..."
                   class="w-full pl-10 pr-4 py-2 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-sm">
        </div>
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-white border-b border-black/10">
                <tr>
                    @foreach($headers as $header)
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">{{ $header }}</th>
                    @endforeach
                    @if($actions)
                        <th class="px-6 py-4 text-right text-xs font-semibold text-black/50 uppercase tracking-wider">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-black/10">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
