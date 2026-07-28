@props(['route', 'active' => false, 'label'])

@php
    $isActive = $active || request()->routeIs($route);
@endphp

<a href="{{ route($route) }}"
   class="sidebar-label flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
          {{ $isActive ? 'nav-active' : 'nav-inactive' }}">

    <svg class="w-4.5 h-4.5 shrink-0 {{ $isActive ? 'text-blue-200' : 'text-white/50' }}"
         fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:1.125rem;height:1.125rem">
        {{ $slot }}
    </svg>

    <span class="sidebar-label truncate">{{ $label }}</span>

    @if($isActive)
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-300 sidebar-label"></span>
    @endif
</a>
