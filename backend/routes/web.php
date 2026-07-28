<?php

use App\Http\Controllers\{ Admin\ReportController, AuthController, ResetPasswordController, ForgotPasswordController, StudentController, TeacherController, CounsellorController, ParentController, AdminController, WelcomeController, LocaleController, UssdController, };
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Counsellor\ReportCounsellorController;
use App\Http\Controllers\Teacher\ReportTeacherController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/ussd/simulator', [UssdController::class, 'simulator'])->name('ussd.simulator');
Route::post('/ussd/simulate', [UssdController::class, 'simulate'])->name('ussd.simulate');

Route::get('/downloads/student-grades', function () {
    $path = public_path('downloads/student_grades.csv');
    abort_unless(file_exists($path), 404);
    return response()->download($path, 'student_grades.csv', [
        'Content-Type'        => 'text/csv',
        'Content-Disposition' => 'attachment; filename="student_grades.csv"',
    ]);
})->name('downloads.student-grades');

Route::post('/locale', [LocaleController::class, 'setLocale'])->name('locale.set');

// ── Guest-only ────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/register/verify-student', [AuthController::class, 'verifyStudent'])->name('register.verify-student');
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);

    Route::get('/forgot-password',  [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password',        [ResetPasswordController::class, 'reset'])->name('password.update');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Email verification ─────────────────────────────────────────────────────
    Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Profile (all roles)
    Route::get('/profile',           [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile',           [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password',  [AuthController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',        [AuthController::class, 'deleteAccount'])->name('profile.delete');

    // ── Student ───────────────────────────────────────────────────────────────
    Route::middleware(['role:student', 'verified'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard',                        [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/assessment',                       [StudentController::class, 'assessment'])->name('assessment');
        Route::post('/assessment/submit',               [StudentController::class, 'submitAssessment'])->name('assessment.submit');
        Route::get('/recommendations',                  [StudentController::class, 'recommendations'])->name('recommendations');
        Route::post('/recommendations/{recommendation}/save',       [StudentController::class, 'saveRecommendation'])->name('recommendations.save');
        Route::post('/recommendations/{recommendation}/dismiss',    [StudentController::class, 'dismissRecommendation'])->name('recommendations.dismiss');
        Route::get('/progress',                         [StudentController::class, 'progress'])->name('progress');
        Route::get('/subject-combinations',             [StudentController::class, 'subjectCombinations'])->name('subject.combinations');
        Route::post('/subject-combinations/preferences',[StudentController::class, 'saveSubjectPreferences'])->name('subject.combinations.preferences');
        Route::post('/subject-combinations/simulate',   [StudentController::class, 'simulateGradeChange'])->name('subject.combinations.simulate');
        Route::get('/career-path',                      [StudentController::class, 'careerPath'])->name('career.path');
        Route::post('/career-path',                     [StudentController::class, 'setCareerGoal'])->name('career.path.store');

        // Self-service report downloads
        Route::get('/reports/career-match',           [StudentController::class, 'downloadCareerMatchReport'])->name('reports.career-match');
        Route::get('/reports/subject-combinations',   [StudentController::class, 'downloadSubjectCombinationsReport'])->name('reports.subject-combinations');
        Route::get('/reports/university-eligibility', [StudentController::class, 'downloadUniversityEligibilityReport'])->name('reports.university-eligibility');
        Route::get('/reports/academic-progress',      [StudentController::class, 'downloadAcademicProgressReport'])->name('reports.academic-progress');
        Route::get('/reports/development-plan',       [StudentController::class, 'downloadDevelopmentPlanReport'])->name('reports.development-plan');
        Route::get('/reports/assessment-summary',     [StudentController::class, 'downloadAssessmentSummaryReport'])->name('reports.assessment-summary');
        Route::get('/reports/saved-recommendations',  [StudentController::class, 'downloadSavedRecommendationsReport'])->name('reports.saved-recommendations');

        // University library
        Route::get('/universities',            [StudentController::class, 'universityLibrary'])->name('universities');

        // Counsellor appointments
        Route::get('/appointments',                          [StudentController::class, 'appointments'])->name('appointments');
        Route::post('/appointments',                         [StudentController::class, 'bookAppointment'])->name('appointments.store');
        Route::patch('/appointments/{session}/cancel',       [StudentController::class, 'cancelAppointment'])->name('appointments.cancel');
    });

    // ── Teacher ───────────────────────────────────────────────────────────────
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard',        [TeacherController::class, 'dashboard'])->name('dashboard');
        Route::get('/students',         [TeacherController::class, 'students'])->name('students');
        Route::get('/students/{user}',    [TeacherController::class, 'studentDetail'])->name('student.detail');
        Route::get('/reports',                          [TeacherController::class, 'reports'])->name('reports');
        Route::get('/reports/export',                   [TeacherController::class, 'exportReport'])->name('reports.export');
        Route::get('/students/{user}/report',             [TeacherController::class, 'downloadStudentReport'])->name('student.report');
        Route::post('/students/{user}/comment',           [TeacherController::class, 'storeComment'])->name('student.comment');
        Route::get('/analytics',                        [TeacherController::class, 'analytics'])->name('analytics');
        Route::get('/grades',                           [TeacherController::class, 'grades'])->name('grades');
        Route::get('/import-grades',                    [TeacherController::class, 'importGradesForm'])->name('import.grades');
        Route::post('/import-grades',                   [TeacherController::class, 'importGrades'])->name('import.grades.store');
        Route::get('/grades/template',                  [TeacherController::class, 'downloadGradesTemplate'])->name('grades.template');
        Route::get('/grades/blank-template',             [TeacherController::class, 'downloadBlankFormTemplate'])->name('grades.blank-template');
        Route::get('/grades/export',                   [TeacherController::class, 'exportAllGrades'])->name('grades.export');
        Route::get('/grades/export-form',               [TeacherController::class, 'exportFormGrades'])->name('grades.export-form');

        // Teacher Report Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportTeacherController::class, 'index'])->name('index');
            Route::get('/class-performance', [ReportTeacherController::class, 'classPerformance'])->name('class-performance');
            Route::get('/student-report-card/{id}', [ReportTeacherController::class, 'studentReportCard'])->name('student-report-card');
            Route::get('/bulk-progress', [ReportTeacherController::class, 'bulkProgress'])->name('bulk-progress');
            Route::get('/attention-list', [ReportTeacherController::class, 'studentsNeedingAttention'])->name('attention-list');
            Route::get('/career-distribution', [ReportTeacherController::class, 'careerInterestDistribution'])->name('career-distribution');
            Route::get('/assessment-trend', [ReportTeacherController::class, 'assessmentTrend'])->name('assessment-trend');
            Route::get('/subject-performance', [ReportTeacherController::class, 'subjectPerformance'])->name('subject-performance');
            Route::get('/form-comparison', [ReportTeacherController::class, 'formComparison'])->name('form-comparison');
            Route::get('/recommendation-acceptance', [ReportTeacherController::class, 'recommendationAcceptance'])->name('recommendation-acceptance');
            Route::get('/placement-prediction', [ReportTeacherController::class, 'universityPlacement'])->name('placement-prediction');
            Route::get('/parent-conference/{id}', [ReportTeacherController::class, 'parentConferenceReport'])->name('parent-conference');
            Route::get('/intervention-tracking', [ReportTeacherController::class, 'interventionTracking'])->name('intervention-tracking');
            Route::get('/weekly-summary', [ReportTeacherController::class, 'weeklyProgressSummary'])->name('weekly-summary');
        });
    });

    // ── Counsellor ────────────────────────────────────────────────────────────
    Route::middleware('role:counsellor')->prefix('counsellor')->name('counsellor.')->group(function () {
        Route::get('/dashboard',                [CounsellorController::class, 'dashboard'])->name('dashboard');
        Route::get('/students',                 [CounsellorController::class, 'students'])->name('students');
        Route::get('/students/{user}',          [CounsellorController::class, 'studentDetail'])->name('student.detail');
        Route::post('/counsel/session',         [CounsellorController::class, 'saveSession'])->name('session.save');
        Route::get('/analytics',                [CounsellorController::class, 'analytics'])->name('analytics');
        Route::get('/careers',                  [CounsellorController::class, 'careerLibrary'])->name('careers');
        Route::get('/sessions',                 [CounsellorController::class, 'sessions'])->name('sessions');
        Route::post('/sessions',                [CounsellorController::class, 'storeSession'])->name('sessions.store');
        Route::patch('/sessions/{session}/cancel',  [CounsellorController::class, 'cancelSession'])->name('sessions.cancel');
        Route::patch('/sessions/{session}/respond', [CounsellorController::class, 'respondToSession'])->name('sessions.respond');
        Route::patch('/sessions/{session}/confirm', [CounsellorController::class, 'confirmSession'])->name('sessions.confirm');
        Route::get('/career-profiles',          [CounsellorController::class, 'careerProfiles'])->name('career-profiles');
        Route::get('/at-risk',                  [CounsellorController::class, 'atRisk'])->name('at.risk');

        // Counsellor Report Routes (add inside counsellor middleware group)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportCounsellorController::class, 'index'])->name('index');
            Route::get('/career-analytics', [ReportCounsellorController::class, 'careerAnalytics'])->name('career-analytics');
            Route::get('/intervention-list', [ReportCounsellorController::class, 'interventionList'])->name('intervention-list');
            Route::get('/session-log', [ReportCounsellorController::class, 'sessionLog'])->name('session-log');
            Route::get('/career-library', [ReportCounsellorController::class, 'careerLibrary'])->name('career-library');
            Route::get('/university-mapping', [ReportCounsellorController::class, 'universityMapping'])->name('university-mapping');
            Route::get('/career-fair', [ReportCounsellorController::class, 'careerFairRecommendations'])->name('career-fair');
            Route::get('/success-stories', [ReportCounsellorController::class, 'successStories'])->name('success-stories');
            Route::get('/assessment-engagement', [ReportCounsellorController::class, 'assessmentEngagement'])->name('assessment-engagement');
            Route::get('/career-tracking', [ReportCounsellorController::class, 'careerPathTracking'])->name('career-tracking');
        });

    });


    // ── Parent ────────────────────────────────────────────────────────────────
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard',        [ParentController::class, 'dashboard'])->name('dashboard');
        Route::get('/children',         [ParentController::class, 'children'])->name('children');
        Route::get('/children/{user}',  [ParentController::class, 'childDetail'])->name('child.detail');
        Route::get('/progress',         [ParentController::class, 'progress'])->name('progress');
        Route::get('/reports',          [ParentController::class, 'reports'])->name('reports');
        Route::get('/reports/{user}/download', [ParentController::class, 'downloadChildReport'])->name('reports.download');
        Route::get('/messages', [ParentController::class, 'messages'])->name('messages');
        Route::post('/messages', [ParentController::class, 'sendMessage'])->name('messages.send');
        Route::post('/locale', [ParentController::class, 'setLocale'])->name('locale');
    });

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Users
        Route::get('/users',             [AdminController::class, 'users'])->name('users');
        Route::get('/users/create',      [AdminController::class, 'createUser'])->name('users.create');
        Route::post('/users',            [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{user}/edit',   [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/users/{user}',        [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/users/{user}',     [AdminController::class, 'deleteUser'])->name('users.delete');

        // Careers CRUD
        Route::get('/careers',           [AdminController::class, 'careers'])->name('careers');
        Route::post('/careers',          [AdminController::class, 'storeCareer'])->name('careers.store');
        Route::put('/careers/{career}',      [AdminController::class, 'updateCareer'])->name('careers.update');
        Route::delete('/careers/{career}',   [AdminController::class, 'deleteCareer'])->name('careers.delete');

        // Subjects CRUD
        Route::get('/subjects',          [AdminController::class, 'subjects'])->name('subjects');
        Route::post('/subjects',         [AdminController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{subject}',     [AdminController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{subject}',  [AdminController::class, 'deleteSubject'])->name('subjects.delete');

        // Reports & Settings
        Route::get('/reports',           [AdminController::class, 'reports'])->name('reports');
        Route::get('/settings',          [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings',         [AdminController::class, 'updateSettings'])->name('settings.update');


        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/', [AnalyticsController::class, 'index'])->name('index');
            Route::get('/users', [AnalyticsController::class, 'users'])->name('users');
            Route::get('/assessments', [AnalyticsController::class, 'assessments'])->name('assessments');
            Route::get('/careers', [AnalyticsController::class, 'careers'])->name('careers');
            Route::get('/system', [AnalyticsController::class, 'system'])->name('system');
            Route::post('/export', [AnalyticsController::class, 'export'])->name('export');
        });

        // Admin Report Routes (add inside admin middleware group)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
        });
    });
});
