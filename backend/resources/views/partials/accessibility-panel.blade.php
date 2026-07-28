{{-- Accessibility panel — top navigation dropdown --}}
<div x-data="accessibilityTools()" x-init="init()" class="relative no-print">
    <button type="button" @click.stop="panelOpen = !panelOpen"
            class="relative z-50 flex items-center gap-1.5 px-3 py-2 rounded-lg border border-blue-200 bg-white hover:bg-blue-50 text-sm font-medium text-blue-800 transition-colors cursor-pointer"
            title="Accessibility options"
            aria-label="Open accessibility options"
            aria-expanded="false"
            :aria-expanded="panelOpen">
        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span class="hidden sm:inline">Accessibility</span>
    </button>

    <div x-show="panelOpen" @click.away="panelOpen = false" x-transition
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-blue-100 py-3 z-[9999]"
         x-cloak role="dialog" aria-label="Accessibility controls">
        <p class="px-4 pb-2 text-xs font-bold text-blue-600 uppercase tracking-wider">Accessibility Controls</p>

        <div class="px-3 space-y-1">
<button type="button" @click="toggleContrast()"
                    class="w-full text-left px-3 py-2.5 rounded-lg text-sm flex items-center gap-2"
                    :class="highContrast ? 'bg-black text-yellow-300 font-semibold' : 'hover:bg-blue-50 text-black/75'">
                <span class="text-base">◐</span> High Contrast Mode
                <span x-show="highContrast" class="ml-auto text-xs">ON</span>
            </button>
            <div class="flex gap-1 px-1 pt-1">
                <button type="button" @click="setFontSize('sm')" class="flex-1 py-2.5 rounded-lg border text-sm font-bold transition-colors"
                        :class="fontSize === 'sm' ? 'bg-blue-600 text-white border-blue-600' : 'border-blue-200 hover:bg-blue-50'" title="Decrease font size">A−</button>
                <button type="button" @click="setFontSize('base')" class="flex-1 py-2.5 rounded-lg border text-sm transition-colors"
                        :class="fontSize === 'base' ? 'bg-blue-600 text-white border-blue-600' : 'border-blue-200 hover:bg-blue-50'" title="Normal font size">A</button>
                <button type="button" @click="setFontSize('lg')" class="flex-1 py-2.5 rounded-lg border text-sm font-bold transition-colors"
                        :class="fontSize === 'lg' ? 'bg-blue-600 text-white border-blue-600' : 'border-blue-200 hover:bg-blue-50'" title="Increase font size">A+</button>
            </div>
            <button type="button" @click="toggleKeyboardNav()" class="w-full text-left px-3 py-2.5 rounded-lg text-sm flex items-center gap-2"
                    :class="keyboardNav ? 'bg-blue-100 text-blue-800 font-semibold' : 'hover:bg-blue-50 text-black/75'">
                <span class="text-base">⌨</span> Keyboard Navigation
                <span x-show="keyboardNav" class="ml-auto text-xs text-blue-600">ON</span>
            </button>
            <button type="button" @click="toggleSimplified()" class="w-full text-left px-3 py-2.5 rounded-lg text-sm flex items-center gap-2"
                    :class="simplified ? 'bg-blue-100 text-blue-800 font-semibold' : 'hover:bg-blue-50 text-black/75'">
                <span class="text-base">Aa</span> Simplified Language Mode
                <span x-show="simplified" class="ml-auto text-xs text-blue-600">ON</span>
            </button>
            <button type="button" @click="toggleDark()" class="w-full text-left px-3 py-2.5 rounded-lg hover:bg-blue-50 text-sm text-black/75 flex items-center gap-2">
                <span class="text-base" x-text="dark ? '☀️' : '🌙'"></span>
                <span x-text="dark ? 'Light Mode' : 'Dark Mode'"></span>
            </button>
        </div>
    </div>
</div>
