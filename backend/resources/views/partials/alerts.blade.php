{{--
    Toast notifications — fixed top-right, always visible regardless of scroll position.
    Auto-dismiss after 5s (errors after 7s). Stacks multiple messages.
--}}

<div id="toast-container" class="fixed top-20 right-4 z-[9999] flex flex-col gap-2 pointer-events-none" style="max-width:26rem;width:calc(100vw - 2rem)">

@if (session('success'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
     x-transition:leave-end="opacity-0 translate-x-4 scale-95"
     class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 bg-white border border-green-200 rounded-xl shadow-lg shadow-green-900/10"
     style="border-left:4px solid #16a34a">
    <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 text-sm text-green-900 font-medium">{{ session('success') }}</div>
    <button @click="show = false" class="text-green-400 hover:text-green-600 transition-colors shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

@if (session('error'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 7000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
     x-transition:leave-end="opacity-0 translate-x-4 scale-95"
     class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 bg-white border border-red-200 rounded-xl shadow-lg shadow-red-900/10"
     style="border-left:4px solid #dc2626">
    <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 text-sm text-red-900 font-medium">{{ session('error') }}</div>
    <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

@if (session('info'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-x-0 scale-100"
     x-transition:leave-end="opacity-0 translate-x-4 scale-95"
     class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 bg-white border border-blue-200 rounded-xl shadow-lg shadow-blue-900/10"
     style="border-left:4px solid #2563eb">
    <svg class="w-5 h-5 text-blue-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 text-sm text-blue-900 font-medium">{{ session('info') }}</div>
    <button @click="show = false" class="text-blue-400 hover:text-blue-600 transition-colors shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

@if (session('status'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 5000)"
     class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 bg-white border border-blue-200 rounded-xl shadow-lg"
     style="border-left:4px solid #2563eb">
    <div class="flex-1 text-sm text-blue-900 font-medium">{{ session('status') }}</div>
</div>
@endif

@if (!empty($errors) && $errors->any())
<div x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-x-4 scale-95"
     x-transition:enter-end="opacity-100 translate-x-0 scale-100"
     class="pointer-events-auto flex items-start gap-3 px-4 py-3.5 bg-white border border-red-200 rounded-xl shadow-lg shadow-red-900/10"
     style="border-left:4px solid #dc2626">
    <svg class="w-5 h-5 text-red-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div class="flex-1 text-sm text-red-900">
        <p class="font-semibold mb-1">Please fix the following:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif

</div>
