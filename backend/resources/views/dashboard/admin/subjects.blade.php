@extends('layouts.dashboard')
@section('title', 'Subject Management')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Subject Management</span>
</nav>
@endsection

@section('content')
<div class="space-y-6" x-data="{ showModal: false, editSubject: null, modalMode: 'create' }">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-black text-black">Subject Management</h1>
            <p class="text-black/50 text-sm mt-0.5">{{ $subjects->total() }} MSCE subjects in the system</p>
        </div>
        <button @click="showModal = true; modalMode = 'create'; editSubject = null"
                class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Subject
        </button>
    </div>

    {{-- Category breakdown --}}
    @php
        $catCounts = $subjects->getCollection()->groupBy('category')->map->count();
        $catColors = ['science' => 'blue','arts'=>'purple','commerce'=>'orange','technical'=>'green','general'=>'slate'];
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach($catColors as $cat => $color)
        <div class="bg-white rounded-xl border border-black/15 p-4 text-center">
            <p class="text-2xl font-black text-{{ $color }}-600">{{ $catCounts[$cat] ?? 0 }}</p>
            <p class="text-xs font-semibold text-black/50 uppercase tracking-wide mt-0.5">{{ ucfirst($cat) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-black/15 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white border-b border-black/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider hidden md:table-cell">Linked Careers</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-black/50 uppercase tracking-wider">Compulsory</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-black/50 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($subjects as $subject)
                    @php $cc = $catColors[$subject->category] ?? 'slate'; @endphp
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-black/80 text-sm">{{ $subject->name }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <code class="px-2 py-0.5 bg-black/[0.03] text-black/70 rounded text-xs font-mono">{{ $subject->code }}</code>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $cc }}-50 text-{{ $cc }}-700">
                                {{ ucfirst($subject->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/50 hidden md:table-cell">
                            {{ $subject->careers_count }} career{{ $subject->careers_count !== 1 ? 's' : '' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($subject->is_compulsory)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Yes
                            </span>
                            @else
                            <span class=”text-xs text-black/40”>—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                <button
                                    @click="showModal = true; modalMode = 'edit'; editSubject = {
                                        id: '{{ $subject->uuid }}',
                                        name: '{{ addslashes($subject->name) }}',
                                        code: '{{ $subject->code }}',
                                        category: '{{ $subject->category }}',
                                        is_compulsory: {{ $subject->is_compulsory ? 'true' : 'false' }},
                                        description: `{{ addslashes($subject->description ?? '') }}`
                                    }"
                                    class="p-1.5 rounded-lg hover:bg-blue-50 text-black/40 hover:text-blue-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('admin.subjects.delete', $subject) }}" method="POST"
                                      onsubmit="return confirm('Delete {{ $subject->name }}? This may affect career and university mappings.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg hover:bg-blue-50 text-black/40 hover:text-blue-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-black/40">No subjects found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subjects->hasPages())
        <div class="px-6 py-4 border-t border-black/10">{{ $subjects->links() }}</div>
        @endif
    </div>

    {{-- Modal --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         @keydown.escape.window="showModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak>

        <div @click.stop
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

            <div class="flex items-center justify-between px-6 py-5 border-b border-black/10">
                <h2 class="font-bold text-black/80 text-lg" x-text="modalMode === 'create' ? 'Add New Subject' : 'Edit Subject'"></h2>
                <button @click="showModal = false" class="text-black/40 hover:text-black/60">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Create --}}
            <div x-show="modalMode === 'create'" class="p-6">
                <form action="{{ route('admin.subjects.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Subject Name *</label>
                            <input type="text" name="name" required placeholder="e.g. Biology"
                                   class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @error('name')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Code *</label>
                            <input type="text" name="code" required placeholder="e.g. BIO01"
                                   class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 uppercase">
                            @error('code')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Category *</label>
                        <select name="category" required
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Select category</option>
                            @foreach(['science','arts','commerce','technical','general'] as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
                                  placeholder="Optional description..."></textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_compulsory" id="comp_create" value="1"
                               class="w-4 h-4 rounded border-black/20 text-blue-600 focus:ring-blue-500">
                        <label for="comp_create" class="text-sm text-black/70 cursor-pointer">Compulsory for all students</label>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors">
                            Create Subject
                        </button>
                        <button type="button" @click="showModal = false"
                                class="px-5 py-3 border border-black/15 text-black/60 rounded-xl text-sm hover:bg-white transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- Edit --}}
            <div x-show="modalMode === 'edit' && editSubject" class="p-6">
                <template x-if="editSubject">
                    <form :action="`/admin/subjects/${editSubject.id}`" method="POST" class="space-y-4">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-black/70 mb-1.5">Subject Name *</label>
                                <input type="text" name="name" :value="editSubject.name" required
                                       class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-black/70 mb-1.5">Code *</label>
                                <input type="text" name="code" :value="editSubject.code" required
                                       class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 uppercase">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Category *</label>
                            <select name="category" required
                                    class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                                @foreach(['science','arts','commerce','technical','general'] as $cat)
                                <option value="{{ $cat }}" :selected="editSubject.category === '{{ $cat }}'">{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-black/70 mb-1.5">Description</label>
                            <textarea name="description" rows="2"
                                      class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
                                      x-text="editSubject.description"></textarea>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_compulsory" id="comp_edit" value="1"
                                   :checked="editSubject.is_compulsory"
                                   class="w-4 h-4 rounded border-black/20 text-blue-600 focus:ring-blue-500">
                            <label for="comp_edit" class="text-sm text-black/70 cursor-pointer">Compulsory for all students</label>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors">
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
