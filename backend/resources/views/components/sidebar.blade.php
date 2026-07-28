@props(['icon', 'route', 'active' => false, 'label'])

<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group
          {{ $active
             ? 'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 shadow-sm'
             : 'text-black/60 hover:bg-white hover:text-blue-600' }}">
    <svg class="w-5 h-5 {{ $active ? 'text-blue-600' : 'text-black/40 group-hover:text-blue-500' }}"
         fill="none" viewBox="0 0 24 24" stroke="currentColor">
        {{ $icon }}
    </svg>
    <span class="font-medium text-sm">{{ $label }}</span>
    @if($active)
        <svg class="w-4 h-4 ml-auto text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    @endif
</a>
