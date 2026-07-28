<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\{ActivityLog, StudentProfile, User};
use Illuminate\Auth\Events\{Registered, Verified};
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, DB, Hash, Mail};
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    // ── Registration ─────────────────────────────────────────────

    /** Show the registration form */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /** Handle registration form submission */
    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:150', 'unique:users,email'],
            'role'              => ['required', 'in:student,parent'],
            'password'          => ['required', 'confirmed', 'min:3'],

            // Student-only fields
            'form_level'        => ['nullable', 'required_if:role,student', 'in:Form 1,Form 2,Form 3,Form 4'],

            // Parent-only fields
            'phone'             => ['nullable', 'required_if:role,parent', 'string', 'max:20'],
            'alt_phone'         => ['nullable', 'string', 'max:20'],
            'relationship'      => ['nullable', 'required_if:role,parent', 'string', 'max:50'],
            'linked_student_id' => ['nullable', 'required_if:role,parent', 'exists:users,id'],

            // Terms acceptance
            'terms'             => ['accepted'],
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'alt_phone' => $data['alt_phone'] ?? null,
                'password'  => Hash::make($data['password']),
                'role'      => $data['role'],
                'is_active' => true,
            ]);

            if ($user->isStudent()) {
                StudentProfile::create([
                    'user_id'        => $user->id,
                    'form_level'     => $data['form_level'],
                    'student_number' => $this->generateStudentNumber(),
                ]);
            }

            if ($user->isParent() && !empty($data['linked_student_id'])) {
                $user->children()->attach($data['linked_student_id'], [
                    'relationship' => $data['relationship'] ?? 'parent',
                ]);
            }

            event(new Registered($user));
            Auth::login($user);

            ActivityLog::record('register', $user, ['role' => $user->role]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Registration failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }

        // Send welcome email outside the transaction so a mail failure never blocks registration
        try { Mail::send(new WelcomeMail($user)); } catch (\Exception) {}

        // Trigger email verification after the transaction commits
        try { $user->sendEmailVerificationNotification(); } catch (\Exception) {}

        return redirect($this->redirectAfterLogin($user))
            ->with('success', "Welcome, {$user->name}! Your account has been created.");
    }

    /** AJAX: verify a student number during parent registration */
    public function verifyStudent(Request $request)
    {
        $request->validate(['student_number' => ['required', 'string']]);

        $profile = StudentProfile::where('student_number', $request->student_number)->first();
        if (!$profile) {
            return response()->json(['found' => false, 'message' => 'Student number not found.'], 404);
        }

        return response()->json([
            'found'   => true,
            'name'    => $profile->user->name,
            'form'    => $profile->form_level,
            'school'  => 'Luwinga Secondary School',
            'user_id' => $profile->user_id,
        ]);
    }

    // ── Login ─────────────────────────────────────────────────────

    /** Show the login form */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /** Handle login form submission */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $login    = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $user = User::where('email', $login)->first();

        if (!$user && !str_contains($login, '@')) {
            $profile = StudentProfile::where('student_number', $login)->first();
            $user    = $profile?->user;
        }

        if (!$user || !Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors(['login' => 'These credentials do not match our records.']);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'login' => 'Your account has been deactivated. Please contact the administrator.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put('access_mode', $request->input('access_mode', 'normal'));
        ActivityLog::record('login');

        return redirect($this->redirectAfterLogin($user))
            ->with('success', "Welcome back, {$user->name}!");
    }

    // ── Logout ────────────────────────────────────────────────────

    /** Log the user out */
    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record('logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'You have been signed out successfully.');
    }

    // ── Profile ───────────────────────────────────────────────────

    /** Show user profile */
    public function profile(): View
    {
        $user = Auth::user();
        $profile = $user->studentProfile;

        return view('profile.index', compact('user', 'profile'));
    }

    /** Update user profile */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'form_level'      => ['nullable', 'in:Form 1,Form 2,Form 3,Form 4'],
            'stream'          => ['nullable', 'string', 'max:50'],
            'gender'          => ['nullable', 'in:male,female,other'],
            'disability_type' => ['nullable', 'in:none,visual_impairment,hearing_impairment,physical_disability,learning_disability'],
            'interests'       => ['nullable', 'string', 'max:500'],
            'skills'          => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        if ($user->isStudent() && $user->studentProfile) {
            $user->studentProfile->update([
                'form_level'      => $data['form_level'] ?? $user->studentProfile->form_level,
                'stream'          => $data['stream'] ?? $user->studentProfile->stream,
                'gender'          => $data['gender'] ?? $user->studentProfile->gender,
                'disability_type' => $data['disability_type'] ?? $user->studentProfile->disability_type ?? 'none',
                'interests'       => $data['interests'] ?? $user->studentProfile->interests,
                'skills'          => $data['skills'] ?? $user->studentProfile->skills,
            ]);
        }
        ActivityLog::record('profile_update');

        return back()->with('success', 'Profile updated successfully.');
    }

    /** Update password */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::record('password_change');

        return back()->with('success', 'Password changed successfully.');
    }

    // ── Email Verification ────────────────────────────────────────

    /** Handle the signed verification link clicked from email */
    public function verifyEmail(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();
        event(new Verified($request->user()));
        ActivityLog::record('email_verified');

        return redirect($this->redirectAfterLogin($request->user()))
            ->with('success', 'Your email has been verified. Welcome!');
    }

    /** Resend the verification email */
    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect($this->redirectAfterLogin($request->user()));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    }

    /** Permanently delete the authenticated user's account */
    public function deleteAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        ActivityLog::record('account_deleted', $user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('welcome')
            ->with('status', 'Your account has been permanently deleted.');
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function redirectAfterLogin(User $user): string
    {
        return match ($user->role) {
            'student'    => route('student.dashboard'),
            'teacher'    => route('teacher.dashboard'),
            'counsellor' => route('counsellor.dashboard'),
            'parent'     => route('parent.dashboard'),
            'admin'      => route('admin.dashboard'),
            default      => route('welcome'),
        };
    }

    private function generateStudentNumber(): string
    {
        $year = date('Y');
        $lastStudent = StudentProfile::latest()->first();
        $number = $lastStudent ? intval(substr($lastStudent->student_number, -4)) + 1 : 1;
        return "LSS/{$year}/" . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
