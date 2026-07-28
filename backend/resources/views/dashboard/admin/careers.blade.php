@extends('layouts.dashboard')
@section('title', 'Career Management')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Careers</span>
</nav>
@endsection

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editCareer: null, modalMode: 'create' }">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-black text-black">Career Management</h1>
            <p class="text-black/50 text-sm mt-0.5">{{ $careers->total() }} careers in the guidance system</p>
        </div>
        <button @click="showModal = true; modalMode = 'create'; editCareer = null"
                class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Career
        </button>
    </div>

    {{-- Category filter pills --}}
    @php
        $categories = $careers->pluck('category')->unique()->sort()->values();
    @endphp
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.careers') }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors border
                  {{ !request('category') ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-black/60 border-black/15 hover:border-blue-300' }}">
            All ({{ $careers->total() }})
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('admin.careers', ['category' => $cat]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors border
                  {{ request('category') === $cat ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-black/60 border-black/15 hover:border-blue-300' }}">
            {{ $cat }}
        </a>
        @endforeach
    </div>

    {{-- Careers grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($careers as $career)
        @php
            $catColors = [
                'Technology' => 'blue',
                'Health'      => 'green',
                'Engineering' => 'orange',
                'Business'    => 'purple',
                'Agriculture' => 'teal',
                'Education'   => 'yellow',
                'Arts'        => 'pink',
                'Law'         => 'red',
            ];
            $cc = $catColors[$career->category] ?? 'slate';
        @endphp
        <div class="bg-white rounded-xl border border-black/15 p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $cc }}-50 text-{{ $cc }}-700 border border-{{ $cc }}-100">
                    {{ $career->category }}
                </span>
                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button
                        @click="showModal = true; modalMode = 'edit'; editCareer = {
                            id: '{{ $career->uuid }}',
                            title: '{{ addslashes($career->title) }}',
                            category: '{{ $career->category }}',
                            description: `{{ addslashes($career->description) }}`,
                            required_skills: `{{ addslashes($career->required_skills) }}`
                        }"
                        class="p-1.5 rounded-lg hover:bg-blue-50 text-black/40 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form action="{{ route('admin.careers.delete', $career) }}" method="POST"
                          onsubmit="return confirm('Delete {{ $career->title }}?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="p-1.5 rounded-lg hover:bg-blue-50 text-black/40 hover:text-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <h3 class="font-bold text-black/80 mb-2">{{ $career->title }}</h3>
            <p class="text-sm text-black/50 line-clamp-2 mb-3">{{ $career->description }}</p>
            @if($career->required_skills)
            <div class="flex flex-wrap gap-1">
                @foreach(array_slice(explode(',', $career->required_skills), 0, 3) as $skill)
                <span class="px-2 py-0.5 bg-black/[0.03] text-black/60 rounded text-xs">{{ trim($skill) }}</span>
                @endforeach
            </div>
            @endif
            <div class="mt-3 pt-3 border-t border-black/10 text-xs text-black/40">
                {{ $career->subjects_count }} subject{{ $career->subjects_count !== 1 ? 's' : '' }} linked
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-2xl border border-black/15 p-16 text-center">
            <p class="text-black/40 font-medium">No careers found.</p>
            <button @click="showModal = true; modalMode = 'create'"
                    class="mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                Add First Career
            </button>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($careers->hasPages())
    <div>{{ $careers->withQueryString()->links() }}</div>
    @endif

    {{-- â”€â”€ Create / Edit Modal â”€â”€ --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         x-cloak>

        <div @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between px-6 py-5 border-b border-black/10">
                <h2 class="font-bold text-black/80 text-lg" x-text="modalMode === 'create' ? 'Add New Career' : 'Edit Career'"></h2>
                <button @click="showModal = false" class="text-black/40 hover:text-black/60">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Create form --}}
            <div x-show="modalMode === 'create'" class="p-6">
                <form action="{{ route('admin.careers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Career Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               placeholder="e.g. Software Engineer"
                               class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @error('title')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Category *</label>
                        <select name="category" required
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Select category</option>
                            @foreach(['Technology','Health','Engineering','Business','Agriculture','Education','Arts','Law'] as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Description *</label>
                        <textarea name="description" rows="3" required
                                  placeholder="Describe what this career involves..."
                                  class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none">{{ old('description') }}</textarea>
                        @error('description')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Required Skills</label>
                        <input type="text" name="required_skills" value="{{ old('required_skills') }}"
                               placeholder="e.g. Mathematics, Problem solving, Communication"
                               class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <p class="text-xs text-black/40 mt-1">Comma-separated skills</p>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors">
                            Create Career
                        </button>
                        <button type="button" @click="showModal = false"
                                class="px-5 py-3 border border-black/15 text-black/60 rounded-xl text-sm hover:bg-white transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Edit form --}}
            <div x-show="modalMode === 'edit' && editCareer" class="p-6">
                <template x-if="editCareer">
                    <form :action="`/admin/careers/${editCareer.id}`" method="POST" class="space-y-4">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Career Title *</label>
                            <input type="text" name="title" :value="editCareer.title" required
                                   class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Category *</label>
                            <select name="category" required
                                    class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @foreach(['Technology','Health','Engineering','Business','Agriculture','Education','Arts','Law'] as $cat)
                                <option value="{{ $cat }}" :selected="editCareer.category === '{{ $cat }}'">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Description *</label>
                            <textarea name="description" rows="3" required
                                      class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
                                      x-text="editCareer.description"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Required Skills</label>
                            <input type="text" name="required_skills" :value="editCareer.required_skills"
                                   class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit"
                                    class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors">
                                Save Changes
                            </button>
                            <button type="button" @click="showModal = false"
                                    class="px-5 py-3 border border-black/15 text-black/60 rounded-xl text-sm hover:bg-white transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
