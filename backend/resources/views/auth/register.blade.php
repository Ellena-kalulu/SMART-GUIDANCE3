@extends('layouts.auth')

@section('title', 'Create Account')

@section('auth-content')

<div class="mb-8">
    <h1 class="text-black font-black text-3xl tracking-tight mb-2">Create Account</h1>
    <p class="text-black/50">Join the future of career guidance</p>
</div>

@if($errors->any())
<div class="mb-6 p-4 bg-red-500 rounded-xl">
    @foreach($errors->all() as $error)
        <p class="text-sm flex text-white items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('register') }}" class="space-y-4" id="registerForm">
    @csrf

    {{-- Role Selection --}}
    <div>
        <label class="block text-sm font-semibold text-black/70 mb-2">I am a...</label>
        <div class="grid grid-cols-2 gap-3" id="roleSelector">
            @foreach([['student', 'Student'], ['parent', 'Parent']] as [$val, $label])
            <label class="role-option cursor-pointer">
                <input type="radio" name="role" value="{{ $val }}" class="hidden role-input"
                       {{ old('role', 'student') === $val ? 'checked' : '' }}>
                <div class="role-card p-3 rounded-xl border-2 transition-all text-center
                            {{ old('role', 'student') === $val
                                ? 'border-blue-600 bg-blue-50 text-blue-700'
                                : 'border-black/15 bg-white text-black/60 hover:border-blue-300' }}">
                    <div class="text-sm font-semibold">{{ $label }}</div>
                </div>
            </label>
            @endforeach
        </div>
        <p class="text-xs text-black/40 mt-2">Teacher &amp; Counsellor accounts are created by the school administrator.</p>
    </div>

    {{-- ── SECTION 1: Personal Information ── --}}
    <div>
        <label for="name" class="block text-sm font-semibold text-black/70 mb-2">Full Name *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                   class="w-full pl-10 pr-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="e.g., Chisomo Phiri">
        </div>
    </div>

    {{-- ── SECTION 2: Contact Information ── --}}
    <div>
        <label for="email" class="block text-sm font-semibold text-black/70 mb-2">Email Address *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                   class="w-full pl-10 pr-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="yourname@email.com">
        </div>
    </div>

    {{-- Parent-only contact fields --}}
    <div id="parentContactFields" class="{{ old('role', 'student') === 'parent' ? '' : 'hidden' }} space-y-4">

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="phone" class="block text-sm font-semibold text-black/70 mb-2">Phone Number *</label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                       class="w-full px-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                       placeholder="+265 888 000 000">
            </div>
            <div>
                <label for="alt_phone" class="block text-sm font-semibold text-black/70 mb-2">Alt. Phone <span class="text-black/30 font-normal">(optional)</span></label>
                <input type="tel" name="alt_phone" id="alt_phone" value="{{ old('alt_phone') }}"
                       class="w-full px-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                       placeholder="+265 999 000 000">
            </div>
        </div>

        {{-- ── SECTION 3: Relationship to Student ── --}}
        <div>
            <label for="relationship" class="block text-sm font-semibold text-black/70 mb-2">Relationship to Student *</label>
            <select name="relationship" id="relationship"
                    class="w-full px-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all bg-white">
                <option value="">Select relationship...</option>
                @foreach(['Mother','Father','Guardian','Aunt','Uncle','Grandparent','Other'] as $rel)
                    <option value="{{ strtolower($rel) }}" {{ old('relationship') === strtolower($rel) ? 'selected' : '' }}>{{ $rel }}</option>
                @endforeach
            </select>
        </div>

        {{-- ── SECTION 4: Student Linking ── --}}
        <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
            <p class="text-sm font-semibold text-blue-800 mb-3">Link to Your Child's Account</p>

            <label for="student_number" class="block text-sm font-semibold text-black/70 mb-2">Student Number *</label>
            <div class="flex gap-2">
                <input type="text" name="student_number" id="student_number" value="{{ old('student_number') }}"
                       class="flex-1 px-4 py-3 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all bg-white"
                       placeholder="e.g. LSS/2024/0001">
                <button type="button" id="verifyStudentBtn"
                        class="px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all text-sm whitespace-nowrap">
                    Verify Student
                </button>
            </div>

            {{-- Student found card --}}
            <div id="studentFound" class="hidden mt-3 p-3 bg-white rounded-xl border border-green-200">
                <div class="flex items-center gap-2 mb-1">
                    <svg class="w-4 h-4 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-green-700">Student Found</span>
                </div>
                <div class="text-sm text-black/70 space-y-0.5 ml-6">
                    <p>Name: <span id="sv-name" class="font-semibold text-black"></span></p>
                    <p>Form: <span id="sv-form" class="font-semibold text-black"></span></p>
                    <p>School: <span id="sv-school" class="font-semibold text-black"></span></p>
                </div>
                <input type="hidden" name="linked_student_id" id="linked_student_id">
            </div>

            {{-- Student not found --}}
            <div id="studentNotFound" class="hidden mt-3 p-3 bg-red-50 rounded-xl border border-red-200">
                <p class="text-sm text-red-700 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span id="sv-error-msg">Student number not found. Please check and try again.</span>
                </p>
            </div>
        </div>
    </div>

    {{-- ── SECTION 1b: Student form level ── --}}
    <div id="studentFields" class="{{ old('role', 'student') === 'student' ? '' : 'hidden' }} space-y-4">
        <div>
            <label for="form_level" class="block text-sm font-semibold text-black/70 mb-2">Form Level *</label>
            <select name="form_level" id="form_level"
                    class="w-full px-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all bg-white">
                <option value="">Select Form</option>
                @foreach(['Form 1', 'Form 2', 'Form 3', 'Form 4'] as $form)
                    <option value="{{ $form }}" {{ old('form_level') === $form ? 'selected' : '' }}>{{ $form }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── SECTION 5: Password ── --}}
    <div>
        <label for="password" class="block text-sm font-semibold text-black/70 mb-2">Password *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <input type="password" name="password" id="password" required
                   class="w-full pl-10 pr-12 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="Create a strong password">
            <button type="button" onclick="togglePassword('password')"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-black/40 hover:text-black/60">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-black/70 mb-2">Confirm Password *</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full pl-10 pr-12 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="Confirm your password">
            <button type="button" onclick="togglePassword('password_confirmation')"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-black/40 hover:text-black/60">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── SECTION 6: Terms ── --}}
    <div class="space-y-2">
        <div id="parentTerms" class="{{ old('role', 'student') === 'parent' ? '' : 'hidden' }}">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="terms_guardian" id="terms_guardian"
                       class="w-5 h-5 mt-0.5 text-blue-600 border-black/20 rounded focus:ring-blue-500">
                <span class="text-sm text-black/60 leading-relaxed">I confirm that I am the parent or legal guardian of this student.</span>
            </label>
        </div>
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="terms" id="terms" required
                   class="w-5 h-5 mt-0.5 text-blue-600 border-black/20 rounded focus:ring-blue-500">
            <span class="text-sm text-black/60 leading-relaxed">
                I agree to the
                <a href="#" class="text-blue-600 hover:underline font-medium">Terms of Service</a>
                and
                <a href="#" class="text-blue-600 hover:underline font-medium">Privacy Policy</a>.
            </span>
        </label>
    </div>

    <button type="submit" id="submitBtn"
            class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all">
        Create Account
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-black/60">
        Already have an account?
        <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:text-blue-700 ml-1">Sign In</a>
    </p>
</div>

<script>
// Role selection toggle
document.querySelectorAll('.role-input').forEach(radio => {
    radio.addEventListener('change', function() {
        const isParent = this.value === 'parent';
        document.getElementById('studentFields').classList.toggle('hidden', isParent);
        document.getElementById('parentContactFields').classList.toggle('hidden', !isParent);
        document.getElementById('parentTerms').classList.toggle('hidden', !isParent);

        document.querySelectorAll('.role-card').forEach(card => {
            card.classList.remove('border-blue-600', 'bg-blue-50', 'text-blue-700');
            card.classList.add('border-black/15', 'bg-white', 'text-black/60');
        });
        this.closest('.role-option').querySelector('.role-card').classList.add('border-blue-600', 'bg-blue-50', 'text-blue-700');
    });
});

// Verify student number
document.getElementById('verifyStudentBtn').addEventListener('click', function() {
    const num = document.getElementById('student_number').value.trim();
    const found = document.getElementById('studentFound');
    const notFound = document.getElementById('studentNotFound');
    found.classList.add('hidden');
    notFound.classList.add('hidden');

    if (!num) { return; }

    this.textContent = 'Checking...';
    this.disabled = true;
    const btn = this;

    fetch('{{ route('register.verify-student') }}?student_number=' + encodeURIComponent(num), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        btn.textContent = 'Verify Student';
        btn.disabled = false;
        if (data.found) {
            document.getElementById('sv-name').textContent   = data.name;
            document.getElementById('sv-form').textContent   = data.form;
            document.getElementById('sv-school').textContent = data.school;
            document.getElementById('linked_student_id').value = data.user_id;
            found.classList.remove('hidden');
        } else {
            document.getElementById('sv-error-msg').textContent = data.message || 'Student not found.';
            notFound.classList.remove('hidden');
        }
    })
    .catch(() => {
        btn.textContent = 'Verify Student';
        btn.disabled = false;
        document.getElementById('sv-error-msg').textContent = 'Error checking student. Please try again.';
        notFound.classList.remove('hidden');
    });
});

// Client-side: require verified student before parent can submit
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const roleInput = document.querySelector('.role-input:checked');
    if (roleInput && roleInput.value === 'parent') {
        const linkedId = document.getElementById('linked_student_id').value;
        if (!linkedId) {
            e.preventDefault();
            document.getElementById('studentNotFound').classList.remove('hidden');
            document.getElementById('sv-error-msg').textContent = 'Please verify a student number before submitting.';
            document.getElementById('student_number').focus();
        }
    }
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}
</script>

@endsection
