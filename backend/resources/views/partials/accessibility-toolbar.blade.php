{{-- Accessibility toolbar: dark mode, font size, high contrast, hints --}}
<div x-data="accessibilityTools()" x-init="init()" class="no-print">
    <div class="flex flex-wrap items-center gap-2 px-4 py-2 bg-brand-50 border-b border-brand-100 text-xs">
        <span class="font-semibold text-brand-800 mr-1">Accessibility:</span>

        <button type="button" @click="toggleDark()" class="px-2.5 py-1 rounded-lg border border-brand-200 bg-white text-brand-700 font-medium hover:bg-brand-100"
                title="Switch between light and dark mode">🌓 <span x-text="dark ? 'Light' : 'Dark'"></span> Mode</button>

        <div class="flex items-center gap-1 border border-brand-200 rounded-lg overflow-hidden bg-white">
            <button type="button" @click="setFontSize('sm')" class="px-2 py-1 hover:bg-brand-100 font-bold" title="Smaller text">A-</button>
            <button type="button" @click="setFontSize('base')" class="px-2 py-1 hover:bg-brand-100 border-x border-brand-200" title="Normal text">A</button>
            <button type="button" @click="setFontSize('lg')" class="px-2 py-1 hover:bg-brand-100 font-bold" title="Larger text">A+</button>
        </div>

        <button type="button" @click="toggleContrast()"
                class="px-2.5 py-1 rounded-lg border font-medium"
                :class="highContrast ? 'bg-black text-white border-black' : 'bg-white text-brand-700 border-brand-200 hover:bg-brand-100'"
                title="High contrast mode helps low-vision users">High Contrast</button>

        <span class="text-brand-600/70 hidden sm:inline ml-auto">Tip: use 🔊 on questions to listen, 🎤 for voice answers</span>
    </div>
</div>

<script>
function accessibilityTools() {
    return {
        dark: false,
        highContrast: false,
        init() {
            this.dark = localStorage.getItem('cg-dark') === '1';
            this.highContrast = localStorage.getItem('cg-contrast') === '1';
            const fs = localStorage.getItem('cg-font') || 'base';
            this.applyDark();
            this.applyContrast();
            this.setFontSize(fs, true);
        },
        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('cg-dark', this.dark ? '1' : '0');
            this.applyDark();
        },
        applyDark() {
            document.documentElement.classList.toggle('dark-mode', this.dark);
        },
        toggleContrast() {
            this.highContrast = !this.highContrast;
            localStorage.setItem('cg-contrast', this.highContrast ? '1' : '0');
            this.applyContrast();
        },
        applyContrast() {
            document.documentElement.classList.toggle('high-contrast', this.highContrast);
        },
        setFontSize(size, silent) {
            document.documentElement.classList.remove('font-sm', 'font-lg');
            if (size === 'sm') document.documentElement.classList.add('font-sm');
            if (size === 'lg') document.documentElement.classList.add('font-lg');
            if (!silent) localStorage.setItem('cg-font', size);
        }
    };
}
</script>
