<?php

namespace App\Http\Controllers;

use App\Models\{Career, StudentProfile, Subject, SubjectCombination, UniversityProgram, User};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\View\View;

class AdminController extends Controller
{
    /** Dashboard */
    public function dashboard(): View
    {
        $totalUsers = User::count();
        $students = User::where('role', 'student')->count();
        $teachers = User::where('role', 'teacher')->count();
        $counsellors = User::where('role', 'counsellor')->count();
        $parents = User::where('role', 'parent')->count();

        $careers = Career::count();
        $subjects = Subject::count();
        $universityPrograms = UniversityProgram::count();

        // Recent users
        $recentUsers = User::latest()->take(10)->get();

        return view('dashboard.admin.index', compact(
            'totalUsers', 'students', 'teachers', 'counsellors', 'parents',
            'careers', 'subjects', 'universityPrograms', 'recentUsers'
        ));
    }

    /** User Management */
    public function users(Request $request): View
    {
        $query = User::query();

        if ($request->has('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->latest()->paginate(20);
        $roles = ['student', 'teacher', 'counsellor', 'parent', 'admin'];

        return view('dashboard.admin.users', compact('users', 'roles'));
    }

    /** Create user form */
    public function createUser(): View
    {
        return view('dashboard.admin.users-create');
    }

    /** Store user */
    public function storeUser(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $data = $request->validate([
                'name'     => ['required', 'string', 'max:100'],
                'email'    => ['required', 'email', 'unique:users'],
                'role'     => ['required', 'in:student,teacher,counsellor,parent,admin'],
                'password' => ['required', 'min:6', 'confirmed'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json(['success' => false, 'errors' => $ve->errors()], 422);
            }
            return back()->withErrors($ve->errors())->withInput();
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'role'      => $data['role'],
                'password'  => $data['password'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($data['role'] === 'student') {
                $year = date('Y');
                $last = StudentProfile::latest()->value('student_number');
                $num  = $last ? intval(substr($last, -4)) + 1 : 1;
                StudentProfile::create([
                    'user_id'        => $user->id,
                    'student_number' => "LSS/{$year}/" . str_pad($num, 4, '0', STR_PAD_LEFT),
                    'form_level'     => $request->input('form_level', 'Form 1'),
                    'stream'         => $request->input('stream'),
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Could not create user: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Could not create user. Please try again.'])->withInput();
        }

        if ($this->wantsJsonResponse($request)) {
            return response()->json([
                'success' => true,
                'message' => "User {$user->name} created successfully.",
                'user'    => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', "User {$user->name} created successfully.");
    }

    /** Edit user form */
    public function editUser(User $user): View
    {
        return view('dashboard.admin.users-edit', compact('user'));
    }

    /** Update user */
    public function updateUser(Request $request, User $user): RedirectResponse|JsonResponse
    {
        try {
            $data = $request->validate([
                'name'      => ['required', 'string', 'max:100'],
                'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
                'role'      => ['required', 'in:student,teacher,counsellor,parent,admin'],
                'is_active' => ['boolean'],
            ]);

            if ($request->filled('password')) {
                $request->validate(['password' => ['min:6', 'confirmed']]);
                $data['password'] = $request->password;
            }
        } catch (\Illuminate\Validation\ValidationException $ve) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json(['success' => false, 'errors' => $ve->errors()], 422);
            }
            return back()->withErrors($ve->errors())->withInput();
        }

        try {
            $user->update($data);
        } catch (\Throwable $e) {
            if ($this->wantsJsonResponse($request)) {
                return response()->json(['success' => false, 'message' => 'Could not update user: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Could not update user. Please try again.'])->withInput();
        }

        if ($this->wantsJsonResponse($request)) {
            return response()->json([
                'success' => true,
                'message' => "User {$user->name} updated successfully.",
                'user'    => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'is_active' => $user->is_active],
            ]);
        }

        return redirect()->route('admin.users')
            ->with('success', "User {$user->name} updated successfully.");
    }

    /** Delete user */
    public function deleteUser(User $user): RedirectResponse
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', "User deleted successfully.");
    }

    /** Career Management */
    public function careers(Request $request): View
    {
        $careers = Career::withCount('subjects')->paginate(15);
        return view('dashboard.admin.careers', compact('careers'));
    }

    /** Store career */
    public function storeCareer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'required_skills' => ['nullable', 'string'],
        ]);

        Career::create($data);

        return back()->with('success', 'Career created successfully.');
    }

    /** Update career */
    public function updateCareer(Request $request, Career $career): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'required_skills' => ['nullable', 'string'],
        ]);

        $career->update($data);

        return back()->with('success', 'Career updated successfully.');
    }

    /** Delete career */
    public function deleteCareer(Career $career): RedirectResponse
    {
        $career->delete();

        return back()->with('success', 'Career deleted successfully.');
    }

    /** Subject Management */
    public function subjects(): View
    {
        $subjects = Subject::withCount('careers')->paginate(15);
        return view('dashboard.admin.subjects', compact('subjects'));
    }

    /** Store subject */
    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects'],
            'category' => ['required', 'string', 'max:50'],
            'is_compulsory' => ['boolean'],
        ]);

        Subject::create($data);

        return back()->with('success', 'Subject created successfully.');
    }

    /** Update subject */
    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:subjects,code,' . $subject->id],
            'category' => ['required', 'string', 'max:50'],
            'is_compulsory' => ['boolean'],
        ]);

        $subject->update($data);

        return back()->with('success', 'Subject updated successfully.');
    }

    /** Delete subject */
    public function deleteSubject(Subject $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('success', 'Subject deleted successfully.');
    }

    /** System Reports */
    public function reports(): View
    {
        // User growth by month
        $userGrowth = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->take(12)
            ->get();

        // Active users by role
        $activeUsers = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        // System statistics
        $stats = [
            'total_users' => User::count(),
            'total_careers' => Career::count(),
            'total_subjects' => Subject::count(),
            'total_universities' => UniversityProgram::count(),
            'subject_combinations' => SubjectCombination::count(),
        ];

        return view('dashboard.admin.reports', compact('userGrowth', 'activeUsers', 'stats'));
    }

    /** System Settings */
    public function settings(): View
    {
        return view('dashboard.admin.settings');
    }

    /** Update settings */
    public function updateSettings(Request $request): RedirectResponse
    {
        // In production, store settings in database or config
        $request->validate([
            'site_name' => ['required', 'string'],
            'contact_email' => ['required', 'email'],
        ]);

        // Save settings logic here

        return back()->with('success', 'Settings updated successfully.');
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->ajax()
            || $request->wantsJson()
            || $request->header('X-Requested-With') === 'XMLHttpRequest'
            || str_contains((string) $request->header('Accept'), 'application/json');
    }

}