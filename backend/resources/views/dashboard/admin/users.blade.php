@extends('layouts.dashboard')
@section('title', 'User Management')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Users</span>
</nav>
@endsection

@section('content')
<div class="space-y-6" x-data="adminUsers()">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-black text-black">User Management</h1>
            <p class="text-black/50 text-sm mt-0.5">{{ $users->total() }} total users across all roles</p>
        </div>
        <button @click="openCreate()"
                class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-black/15 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-semibold text-black/50 mb-1.5 uppercase tracking-wide">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name or email..."
                           class="w-full pl-9 pr-4 py-2.5 border border-black/15 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-black/50 mb-1.5 uppercase tracking-wide">Role</label>
                <select name="role" class="px-3 py-2.5 border border-black/15 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="all" {{ request('role','all') === 'all' ? 'selected' : '' }}>All Roles</option>
                    @foreach($roles as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                        {{ ucfirst($role) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
                Filter
            </button>
            @if(request()->hasAny(['search','role']))
            <a href="{{ route('admin.users') }}" class="px-4 py-2.5 border border-black/15 rounded-lg text-sm text-black/60 hover:bg-white transition-colors">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Role summary chips --}}
    @php
        $roleCounts = \App\Models\User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total','role');
        $roleColors = ['student' => 'blue','teacher'=>'green','counsellor'=>'purple','parent'=>'orange','admin'=>'red'];
    @endphp
    <div class="flex flex-wrap gap-2">
        @foreach($roleColors as $r => $color)
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
            {{ ucfirst($r) }}: {{ $roleCounts[$r] ?? 0 }}
        </span>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-black/15 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white border-b border-black/10">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider hidden md:table-cell">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-black/50 uppercase tracking-wider hidden lg:table-cell">Joined</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-black/50 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @forelse($users as $user)
                    @php
                        $roleColorMap = ['student' => 'blue','teacher'=>'green','counsellor'=>'purple','parent'=>'orange','admin'=>'red'];
                        $rc = $roleColorMap[$user->role] ?? 'slate';
                    @endphp
                    <tr class="hover:bg-white transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-{{ $rc }}-100 text-{{ $rc }}-700 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-black/80 text-sm">{{ $user->name }}</p>
                                    <p class="text-xs text-black/40 md:hidden">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $rc }}-50 text-{{ $rc }}-700">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/60 hidden md:table-cell">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="flex items-center gap-1.5 text-xs font-semibold {{ $user->is_active ? 'text-emerald-700' : 'text-black/40' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-black/25' }}"></span>
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-black/50 hidden lg:table-cell">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2 justify-end">
                                <button @click="openEdit({{ json_encode(['id'=>$user->uuid,'name'=>$user->name,'email'=>$user->email,'role'=>$user->role,'is_active'=>(bool)$user->is_active]) }})"
                                        class="p-1.5 rounded-lg hover:bg-blue-50 text-black/40 hover:text-blue-600 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.delete', $user) }}" method="POST"
                                      onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg hover:bg-blue-50 text-black/40 hover:text-blue-600 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-black/40">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            <p class="font-medium">No users found</p>
                            <p class="text-sm mt-1">Try adjusting your search or filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($users->hasPages())
        <div class="px-6 py-4 border-t border-black/10">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- ═══ CREATE / EDIT USER MODAL ════════════════════════════════════════════ --}}
<div x-show="showCreate || showEdit" x-cloak
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click.self="showCreate=false;showEdit=false"
     class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-start justify-center pt-16 px-4 pb-8 overflow-y-auto">

    <div @click.stop class="w-full max-w-lg bg-white rounded-2xl shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80 text-lg" x-text="showCreate ? 'Create New User' : 'Edit User'"></h2>
            <button @click="showCreate=false;showEdit=false" class="w-8 h-8 rounded-full hover:bg-black/10 text-black/40 flex items-center justify-center transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Error list --}}
        <div x-show="errorMsg || errors.length" class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
            <p x-show="errorMsg" x-text="errorMsg" class="font-medium"></p>
            <ul x-show="errors.length" class="list-disc list-inside space-y-0.5 mt-1">
                <template x-for="e in errors"><li x-text="e"></li></template>
            </ul>
        </div>

        <form @submit.prevent="submitForm()" class="p-6 space-y-5">
            {{-- Role --}}
            <div>
                <label class="block text-sm font-semibold text-black/60 mb-2">Role</label>
                <div class="grid grid-cols-5 gap-2">
                    <template x-for="r in ['student','teacher','counsellor','parent','admin']" :key="r">
                        <label class="cursor-pointer text-center">
                            <input type="radio" :value="r" x-model="form.role" class="sr-only">
                            <div class="px-2 py-2.5 rounded-xl border-2 text-xs font-semibold transition-all"
                                 :class="form.role===r ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-black/15 text-black/60 hover:border-blue-300'"
                                 x-text="r.charAt(0).toUpperCase()+r.slice(1)"></div>
                        </label>
                    </template>
                </div>
            </div>

            {{-- Name & Email --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-black/60 mb-1.5">Full Name</label>
                    <input type="text" x-model="form.name" required placeholder="e.g. Chisomo Phiri"
                           class="w-full px-3 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/60 mb-1.5">Email</label>
                    <input type="email" x-model="form.email" required placeholder="user@example.com"
                           class="w-full px-3 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            {{-- Password (required for create, optional for edit) --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-black/60 mb-1.5">
                        Password <span x-show="showEdit" class="text-black/30 font-normal">(leave blank to keep)</span>
                    </label>
                    <input type="password" x-model="form.password" :required="showCreate"
                           placeholder="Min. 8 characters"
                           class="w-full px-3 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/60 mb-1.5">Confirm Password</label>
                    <input type="password" x-model="form.password_confirmation" :required="showCreate && form.password.length > 0"
                           placeholder="Repeat password"
                           class="w-full px-3 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            {{-- Active toggle (edit only) --}}
            <div x-show="showEdit" class="flex items-center gap-3">
                <input type="checkbox" id="modal_is_active" x-model="form.is_active"
                       class="w-4 h-4 rounded border-black/20 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <label for="modal_is_active" class="text-sm text-black/70 cursor-pointer">Account is active</label>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2 border-t border-black/10">
                <button type="submit" :disabled="submitting"
                        class="flex-1 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                    <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="submitting ? 'Saving...' : (showCreate ? 'Create User' : 'Save Changes')"></span>
                </button>
                <button type="button" @click="showCreate=false;showEdit=false"
                        class="px-5 py-2.5 border border-black/15 text-black/60 rounded-xl text-sm font-semibold hover:bg-black/5 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>{{-- end modal overlay --}}
</div>{{-- end space-y-6 x-data --}}

@push('scripts')
<script>
function adminUsers() {
    return {
        showCreate: false,
        showEdit: false,
        submitting: false,
        errorMsg: '',
        errors: [],
        editId: null,
        form: { role:'student', name:'', email:'', password:'', password_confirmation:'', is_active:true },

        openCreate() {
            this.form = { role:'student', name:'', email:'', password:'', password_confirmation:'', is_active:true };
            this.errors = []; this.errorMsg = '';
            this.showEdit = false; this.showCreate = true;
        },
        openEdit(user) {
            this.form = { role: user.role, name: user.name, email: user.email, password:'', password_confirmation:'', is_active: user.is_active };
            this.editId = user.id;
            this.errors = []; this.errorMsg = '';
            this.showCreate = false; this.showEdit = true;
        },

        async submitForm() {
            this.submitting = true; this.errors = []; this.errorMsg = '';
            const url = this.showCreate
                ? '{{ route("admin.users.store") }}'
                : `/admin/users/${this.editId}`;
            const method = this.showCreate ? 'POST' : 'PUT';
            const body = new FormData();
            body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            if (!this.showCreate) body.append('_method', 'PUT');
            Object.entries(this.form).forEach(([k,v]) => {
                if (k === 'is_active') body.append(k, v ? '1' : '0');
                else if (v !== '') body.append(k, v);
            });
            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    body
                });
                let data;
                try { data = await res.json(); } catch { data = {}; }
                if (res.ok && data.success !== false) {
                    const wasCreate = this.showCreate;
                    this.showCreate = false; this.showEdit = false;
                    this.showToast(data.message || (wasCreate ? 'User created successfully.' : 'User saved successfully.'), 'success');
                    setTimeout(() => location.reload(), 1200);
                } else if (res.status === 422 && data.errors) {
                    this.errors = Object.values(data.errors).flat();
                } else {
                    this.errorMsg = data.message || `Server error (${res.status}). Please try again.`;
                }
            } catch (err) {
                this.errorMsg = 'Could not reach the server. Check your connection and try again.';
            }
            this.submitting = false;
        },

        showToast(msg, type) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const el = document.createElement('div');
            el.style.cssText = 'display:flex;align-items:flex-start;gap:.75rem;padding:.875rem 1rem;background:#fff;border-radius:.75rem;box-shadow:0 4px 16px rgba(0,0,0,.12);border-left:4px solid ' + (type==='success'?'#16a34a':'#dc2626');
            el.innerHTML = '<div style="flex:1;font-size:.875rem;font-weight:500;color:' + (type==='success'?'#14532d':'#7f1d1d') + '">' + msg + '</div>';
            container.prepend(el);
            setTimeout(() => el.remove(), 5000);
        }
    };
}
</script>
@endpush

@endsection
