<?php

namespace App\Services;

use App\Models\{
    AssessmentAnalysis,
    CounsellingSession,
    Recommendation,
    StudentProfile,
    User,
};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Intelligent USSD handler for Smart MSCE Subject Combination Guidance.
 * Compatible with Africa's Talking / TNM / Airtel USSD gateways.
 * Implements all 20 intelligent features described in the project spec.
 */
class UssdService
{
    private const SESSION_TTL = 300;    // 5 min USSD session
    private const JOURNEY_TTL = 86400;  // 24 h Smart Journey phone→student map

    public function __construct(
        protected CareerPathTrackingService $pathTracking,
        protected AcademicProgressService   $academicProgress,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // ENTRY POINT
    // ─────────────────────────────────────────────────────────────────────────

    public function handle(string $sessionId, string $phoneNumber, string $text): string
    {
        $parts  = $text === '' ? [] : explode('*', $text);
        $level  = count($parts);
        $choice = $parts[0] ?? '';

        // Feature 20: Smart USSD Journey — remember returning students by phone
        $journeyKey      = "ussd:journey:{$phoneNumber}";
        $rememberedId    = Cache::get($journeyKey);
        $rememberedUser  = $rememberedId ? User::with('studentProfile')->find($rememberedId) : null;

        // ── Level 0: Main menu ────────────────────────────────────────────
        if ($level === 0) {
            return $this->con($this->mainMenu());
        }

        // ── Level 1: User chose a menu option ────────────────────────────
        if ($level === 1) {
            if ($choice === '0') {
                return $this->end($this->goodbye($rememberedUser));
            }

            // Feature 20: Skip student-number prompt for known phones
            if ($rememberedUser && in_array($choice, ['1','2','3','4','5','9'])) {
                return $this->dispatchStudent($rememberedUser, $this->padSkippedStudentNumber($parts), $level, $choice, $phoneNumber, true);
            }

            return match ($choice) {
                '1','2','3','4','5','9' => $this->con("SMART CAREER GUIDANCE\n\nEnter student number:\n(e.g. LSS/2024/0001)"),
                '6' => $this->con("Parent Access\n\nEnter student number:"),
                '7' => $this->con("Teacher Lookup\n\nEnter student number:"),
                default => $this->end("Invalid option.\nDial *384*12345# to restart."),
            };
        }

        // ── Level 2+: Resolve student from parts[1] ────────────────────
        if ($choice === '6') {
            return $this->handleParentAccess($parts, $level);
        }

        if ($choice === '7') {
            return $this->handleTeacherLookup($parts, $level);
        }

        // Feature 20: Known phones never went through the "enter student number" step,
        // so a follow-up tap (e.g. picking a submenu option) must not be re-read as a
        // student number lookup — route it straight through with the same padding.
        if ($rememberedUser && in_array($choice, ['1','2','3','4','5','9'])) {
            return $this->dispatchStudent($rememberedUser, $this->padSkippedStudentNumber($parts), $level, $choice, $phoneNumber);
        }

        $studentNumber = strtoupper(trim($parts[1] ?? ''));
        $student       = $this->findStudent($studentNumber);

        if (!$student) {
            return $this->end(
                "Student not found:\n{$studentNumber}\n\nCheck number and\ntry again.\nExample: LSS/2024/0001"
            );
        }

        // Feature 20: Remember this phone→student association
        Cache::put($journeyKey, $student->id, self::JOURNEY_TTL);

        return $this->dispatchStudent($student, $parts, $level, $choice, $phoneNumber, $level === 2);
    }

    private function dispatchStudent(User $student, array $parts, int $level, string $choice, string $phone, bool $isFirstScreen = false): string
    {
        $response = match ($choice) {
            '1'     => $this->handleRecommendation($student, $parts),
            '2'     => $this->handleAcademicProgress($student),
            '3'     => $this->handleSubjectComparison($student),
            '4'     => $this->handleStudyAdvice($student),
            '5'     => $this->handleBookCounsellor($student, $parts, $phone),
            '9'     => $this->handleGoalTracker($student),
            default => $this->end('Session expired. Dial again.'),
        };

        // The welcome message only appears once the student is identified — on the
        // first screen after they enter their student number (or via the
        // remembered-phone shortcut), never on the plain services menu.
        return $isFirstScreen ? $this->prependGreeting($response, $student) : $response;
    }

    private function prependGreeting(string $response, User $student): string
    {
        $prefix = '';
        if (str_starts_with($response, 'CON ')) {
            $prefix = 'CON ';
        } elseif (str_starts_with($response, 'END ')) {
            $prefix = 'END ';
        }
        $body = $prefix ? substr($response, 4) : $response;

        $avg = $student->academicResults()->latest('updated_at')->take(10)->get()->avg('score');
        $greeting = $this->personalizedGreeting($student, $avg);

        return $prefix . $greeting . "\n\n" . $body;
    }

    /**
     * Feature 20: remembered-phone sessions skip the "enter student number" step, so
     * their parts array is missing the slot at index 1 that handlers like
     * handleRecommendation()/handleBookCounsellor() expect their sub-choice to sit
     * after (index 2). Insert a placeholder so that indexing lines up.
     */
    private function padSkippedStudentNumber(array $parts): array
    {
        if (count($parts) < 2) {
            return $parts;
        }

        return [$parts[0], null, ...array_slice($parts, 1)];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN MENU — Feature 18 (Personalised Greeting) + Feature 20 (Journey)
    // ─────────────────────────────────────────────────────────────────────────

    // The initial dial-in screen always shows the plain service list — no welcome
    // message. The personalised greeting only appears once the student is identified
    // (after they enter their student number, or via the remembered-phone shortcut),
    // inside the screen for the feature they picked.
    private function mainMenu(): string
    {
        $menuItems = implode("\n", [
            "1 My Recommendation",
            "2 Academic Progress",
            "3 Subject Comparison",
            "4 AI Study Advice",
            "5 Book Counsellor",
            "6 Parent Access",
            "7 Teacher Lookup",
            "9 Goal Tracker",
            "0 Exit",
        ]);

        return implode("\n", [
            "SMART CAREER GUIDANCE",
            "Luwinga Secondary",
            "",
            $menuItems,
        ]);
    }

    // Feature 18: Personalised greeting comparing current vs last-seen average
    private function personalizedGreeting(User $student, ?float $avg): string
    {
        $firstName   = $this->firstName($student);
        $lastAvgKey  = "ussd:avg:{$student->id}";
        $lastAvg     = Cache::get($lastAvgKey);

        if ($lastAvg !== null && $avg !== null) {
            $diff = $avg - $lastAvg;
            if ($diff >= 3) {
                Cache::put($lastAvgKey, $avg, self::JOURNEY_TTL);
                return "Welcome back, {$firstName}!\nYou have improved\nsince last time.";
            }
            if ($diff <= -3) {
                return "Welcome back, {$firstName}.\nSome grades need\nattention.";
            }
        }

        if ($avg !== null) {
            Cache::put($lastAvgKey, $avg, self::JOURNEY_TTL);
        }

        return "Welcome back, {$firstName}!";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 1: INTELLIGENT SUBJECT COMBINATION RECOMMENDATION
    // ─────────────────────────────────────────────────────────────────────────

    private function handleRecommendation(User $student, array $parts): string
    {
        $firstName = $this->firstName($student);
        $results   = $student->academicResults()->with('subject')->get();
        $report    = $this->getReport($student);
        $paths     = $report['msce_paths'] ?? [];
        $topPath   = $paths[0] ?? null;

        if (!$topPath || $results->isEmpty()) {
            return $this->end(implode("\n", [
                "SMART CAREER GUIDANCE",
                "",
                "Hello {$firstName}",
                "",
                "No recommendation yet.",
                "Ask teacher to",
                "upload your grades.",
                "",
                "Use web portal for",
                "full details.",
            ]));
        }

        $pathName   = str_replace(' Path', '', $topPath['path'] ?? 'Unknown');
        $confidence = round($topPath['score'] ?? 0);
        $subChoice  = $parts[2] ?? null;

        // Initial screen (no sub-choice yet)
        if ($subChoice === null) {
            $reasons = $this->buildReasons($student, $topPath, $results);

            $lines = [
                "SMART CAREER GUIDANCE",
                "",
                "Hello {$firstName}",
                "",
                "Your recommended",
                "MSCE Combination is",
                "",
                strtoupper($pathName),
                "",
                "Confidence: {$confidence}%",
                "",
                "Reason:",
            ];
            foreach ($reasons as $r) {
                $lines[] = $r;
            }
            $lines[] = "";
            $lines[] = "1. View Details";
            $lines[] = "2. Next Steps";

            return $this->con(implode("\n", $lines));
        }

        // Sub-option: View Details
        if ($subChoice === '1') {
            $subjects  = $topPath['subjects'] ?? [];
            $nrCount   = count($topPath['not_ready_reasons'] ?? []);
            $risk      = $nrCount === 0 ? 'Low' : ($nrCount === 1 ? 'Medium' : 'High');
            $ratingTxt = match ($topPath['status'] ?? '') {
                'ready'   => 'Highly Recommended',
                'partial' => 'Good - Improve Some',
                default   => 'Possible - Needs Work',
            };

            $lines = [
                strtoupper($pathName) . " DETAILS",
                "",
                "Required Subjects:",
            ];
            foreach ($subjects as $s) {
                $gs     = $topPath['grade_scores'][$s] ?? null;
                $icon   = $gs === null ? '?' : ($gs >= 55 ? '✓' : '!');
                $score  = $gs !== null ? " ({$gs}%)" : '';
                $lines[] = "{$icon} {$s}{$score}";
            }
            $lines[] = "";
            $lines[] = "Risk: {$risk}";
            $lines[] = $ratingTxt;

            return $this->end(implode("\n", $lines));
        }

        // Sub-option: Next Steps
        if ($subChoice === '2') {
            return $this->end(implode("\n", [
                "NEXT STEPS",
                "",
                "1. Talk to your Career",
                "   Guidance Teacher",
                "",
                "2. Share report with",
                "   parent/guardian",
                "",
                "3. Select Form 3",
                "   combination based",
                "   on this advice",
                "",
                "4. Track progress",
                "   each term",
                "",
                "Dial again for more.",
            ]));
        }

        return $this->end("Invalid option.");
    }

    private function buildReasons(User $student, array $topPath, Collection $results): array
    {
        $reasons = $topPath['why_reasons'] ?? [];
        if (!empty($reasons)) {
            return array_map(fn($r) => $this->truncate($r, 28), array_slice($reasons, 0, 2));
        }

        // Auto-generate from grades
        $sorted  = $results->with('subject')->sortByDesc('score');
        $top1    = $sorted->first();
        $top2    = $sorted->skip(1)->first();
        $lines   = [];

        if ($top1?->subject) {
            $label = $this->scoreLabel($top1->score);
            $lines[] = "{$label} " . $this->truncate($top1->subject->name, 20);
        }
        if ($top2?->subject) {
            $label = $this->scoreLabel($top2->score);
            $lines[] = "{$label} " . $this->truncate($top2->subject->name, 20);
        }

        return $lines ?: ['Based on grades and interests'];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 2: ACADEMIC PROGRESS SUMMARY + Feature 4 (Risk Alert)
    // ─────────────────────────────────────────────────────────────────────────

    private function handleAcademicProgress(User $student): string
    {
        $results = $student->academicResults()->with('subject')->get();

        if ($results->isEmpty()) {
            return $this->end("No grades uploaded yet.\nAsk teacher to\nupload results first.");
        }

        $avg     = round($results->avg('score'));
        $sw      = $this->academicProgress->getStrengthsWeaknesses($results);
        $trend   = $this->computeTrend($results);

        $strongest = isset($sw['strengths'][0]) ? $this->truncate($sw['strengths'][0], 22) : 'N/A';
        $weakest   = isset($sw['weaknesses'][0]) ? $this->truncate($sw['weaknesses'][0], 22) : 'None';

        $lines = [
            "Academic Progress",
            "",
            "Average: {$avg}%",
            "",
            "Strongest Subject:",
            $strongest,
            "",
            "Needs Improvement:",
            $weakest,
            "",
            "Trend: {$trend}",
        ];

        // Feature 4: Risk Alert
        $critical = $results->filter(fn($r) => ($r->score ?? 0) < 60)->sortBy('score')->first();
        if ($critical) {
            $subName = $critical->subject?->name ?? 'a key subject';
            $score   = round($critical->score ?? 0);
            $lines[] = "";
            $lines[] = "⚠ Warning:";
            $lines[] = "Your {$subName}";
            $lines[] = "has dropped below 60%.";

            $report  = $this->getReport($student);
            $top     = $report['msce_paths'][0] ?? null;
            if ($top) {
                $pathName = str_replace(' Path', '', $top['path'] ?? '');
                $lines[] = "";
                $lines[] = "{$pathName} Combination";
                $lines[] = "may be affected.";
            }
        }

        $lines[] = "";
        $lines[] = "0. Back";

        return $this->end(implode("\n", $lines));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 3 + 5: AI STUDY ADVICE + WHAT SHOULD I IMPROVE
    // ─────────────────────────────────────────────────────────────────────────

    private function handleStudyAdvice(User $student): string
    {
        $firstName = $this->firstName($student);
        $results   = $student->academicResults()->with('subject')->get();

        if ($results->isEmpty()) {
            return $this->end("No grades to analyse.\nAsk teacher to\nupload your results.");
        }

        $report  = $this->getReport($student);
        $paths   = $report['msce_paths'] ?? [];
        $topPath = $paths[0] ?? null;

        // Feature 5: What Should I Improve
        $mustImprove = [];
        foreach (($topPath['subjects'] ?? []) as $sub) {
            $gs = $topPath['grade_scores'][$sub] ?? null;
            if ($gs !== null && $gs < 65) {
                $mustImprove[] = $sub;
            }
        }

        // Feature 4: Risk alert for critical subjects
        $criticals = $results->filter(fn($r) => ($r->score ?? 0) < 55)->sortBy('score')->take(2);

        $lines = ["AI Study Advice", "", "Hello {$firstName},", ""];

        if ($criticals->isNotEmpty()) {
            $lines[] = "⚠ URGENT:";
            foreach ($criticals as $r) {
                $name  = $r->subject?->name ?? 'Subject';
                $score = round($r->score ?? 0);
                $lines[] = "- {$name} at {$score}%";
            }
            $lines[] = "Needs immediate work!";
            $lines[] = "";
        }

        if (!empty($mustImprove)) {
            $pathName = str_replace(' Path', '', $topPath['path'] ?? '');
            $lines[] = "To keep {$pathName},";
            $lines[] = "improve:";
            foreach (array_slice($mustImprove, 0, 3) as $s) {
                $lines[] = "- {$s}";
            }
            $lines[] = "";
        }

        // Today's general advice from weak subjects
        $weak3 = $results->filter(fn($r) => ($r->score ?? 0) < 70)->sortBy('score')->take(3);
        if ($weak3->isNotEmpty()) {
            $lines[] = "Today's Study:";
            foreach ($weak3 as $r) {
                $lines[] = "- Revise " . $this->truncate($r->subject?->name ?? 'Subject', 18);
            }
            $lines[] = "";
        }

        $avg = $results->avg('score');
        $lines[] = $avg >= 75 ? "Excellent progress!" : ($avg >= 60 ? "Good effort. Keep going!" : "Extra study needed.");
        $lines[] = "Good luck!";

        return $this->end(implode("\n", $lines));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 6: SUBJECT COMBINATION COMPARISON
    // ─────────────────────────────────────────────────────────────────────────

    private function handleSubjectComparison(User $student): string
    {
        $report = $this->getReport($student);
        $paths  = $report['msce_paths'] ?? [];

        if (empty($paths)) {
            return $this->end("No data for comparison.\nUpload grades first.");
        }

        $lines = ["Subject Comparison", ""];

        foreach (array_slice($paths, 0, 5) as $i => $path) {
            $name   = str_replace(' Path', '', $path['path'] ?? 'Path');
            $score  = round($path['score'] ?? 0);
            $status = $score >= 75 ? 'Suitable' : ($score >= 55 ? 'Possible' : 'Less Suitable');
            $medal  = $i === 0 ? '★ ' : ($i === 1 ? '◎ ' : '  ');
            $lines[] = "{$medal}" . ($i + 1) . ". {$name}";
            $lines[] = "   {$status}";
        }

        $lines[] = "";
        $lines[] = "★ = Best match";

        return $this->end(implode("\n", $lines));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 7: PARENT ACCESS (PIN-protected)
    // ─────────────────────────────────────────────────────────────────────────

    private function handleParentAccess(array $parts, int $level): string
    {
        $studentNumber = strtoupper(trim($parts[1] ?? ''));
        $student       = $this->findStudent($studentNumber);

        if (!$student) {
            return $this->end("Student not found.\n{$studentNumber}\n\nCheck and try again.");
        }

        $profile   = $student->studentProfile;
        $firstName = $this->firstName($student);

        // Level 2: student found, ask for PIN
        if ($level === 2) {
            return $this->con(implode("\n", [
                "Parent Access",
                "",
                "Student: {$firstName}",
                "Form: " . ($profile?->form_level ?? 'N/A'),
                "",
                "Enter 4-digit PIN:",
                "(Default: last 4 of",
                "student number)",
            ]));
        }

        // Level 3: validate PIN
        $pin         = trim($parts[2] ?? '');
        $rawNumber   = preg_replace('/\D/', '', $profile?->student_number ?? '');
        $expectedPin = strlen($rawNumber) >= 4 ? substr($rawNumber, -4) : '1234';

        if ($pin !== $expectedPin) {
            return $this->end("Incorrect PIN.\nContact school\nfor assistance.");
        }

        // PIN correct — show parent-friendly summary
        $results = $student->academicResults()->with('subject')->get();
        $avg     = $results->isNotEmpty() ? round($results->avg('score')) : null;

        $report  = $this->getReport($student);
        $topPath = $report['msce_paths'][0] ?? null;
        $rec     = $topPath ? str_replace(' Path', '', $topPath['path'] ?? '') : 'Not yet';
        $nrCount = count($topPath['not_ready_reasons'] ?? []);
        $risk    = $nrCount === 0 ? 'Low' : ($nrCount === 1 ? 'Medium' : 'High');

        return $this->end(implode("\n", [
            "Parent Access",
            "",
            "Student",
            $student->name,
            "",
            "Recommended",
            $rec,
            "",
            "Average",
            $avg !== null ? "{$avg}%" : 'No grades yet',
            "",
            "Risk",
            $risk,
            "",
            "Dial again for updates.",
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 8: TEACHER QUICK LOOKUP
    // ─────────────────────────────────────────────────────────────────────────

    private function handleTeacherLookup(array $parts, int $level): string
    {
        $studentNumber = strtoupper(trim($parts[1] ?? ''));
        $student       = $this->findStudent($studentNumber);

        if (!$student) {
            return $this->end("Student not found:\n{$studentNumber}");
        }

        $profile = $student->studentProfile;
        $results = $student->academicResults()->with('subject')->get();
        $avg     = $results->isNotEmpty() ? round($results->avg('score')) : null;

        $report  = $this->getReport($student);
        $topPath = $report['msce_paths'][0] ?? null;
        $rec     = $topPath ? str_replace(' Path', '', $topPath['path'] ?? 'N/A') : 'N/A';
        $conf    = $topPath ? round($topPath['score'] ?? 0) : 0;

        $top3 = $results->sortByDesc('score')->take(3);

        $lines = [
            "Teacher Lookup",
            "",
            "Name: {$student->name}",
            "No: " . ($profile?->student_number ?? 'N/A'),
            "Form: " . ($profile?->form_level ?? 'N/A'),
            "",
            "Avg: " . ($avg !== null ? "{$avg}%" : 'No grades'),
            "Recommendation: {$rec}",
            "Confidence: {$conf}%",
        ];

        if ($top3->isNotEmpty()) {
            $lines[] = "";
            $lines[] = "Best Subjects:";
            foreach ($top3->values() as $i => $r) {
                $lines[] = ($i + 1) . ". " . $this->truncate($r->subject?->name ?? 'Subject', 16) . ": " . round($r->score) . "%";
            }
        }

        return $this->end(implode("\n", $lines));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 9: COUNSELLING BOOKING
    // ─────────────────────────────────────────────────────────────────────────

    private function handleBookCounsellor(User $student, array $parts, string $phone): string
    {
        // No sub-choice yet: show counsellor list
        if (!isset($parts[2])) {
            $counsellors = User::where('role', 'counsellor')->where('is_active', true)->get();

            if ($counsellors->isEmpty()) {
                return $this->end("No counsellors available.\nContact school office.");
            }

            $lines = ["Counselling", ""];
            foreach ($counsellors->values() as $i => $c) {
                $lines[] = ($i + 1) . ". " . $this->truncate($c->name, 18);
            }
            $lines[] = "";
            $lines[] = "2. View Appointment";
            $lines[] = "0. Back";

            return $this->con(implode("\n", $lines));
        }

        $sub = $parts[2] ?? '';

        if ($sub === '0') {
            return $this->con($this->mainMenu());
        }

        if ($sub === '2') {
            $existing = CounsellingSession::where('student_id', $student->id)
                ->whereIn('status', ['pending_confirmation', 'confirmed'])
                ->orderByDesc('created_at')
                ->first();

            if (!$existing) {
                return $this->end("No appointments found.\nBook one from menu.");
            }

            return $this->end(implode("\n", [
                "Your Appointment",
                "",
                "Status: " . ucfirst(str_replace('_', ' ', $existing->status)),
                "Date: " . $existing->scheduled_at?->format('d M Y'),
                "Counsellor ID: " . $existing->counsellor_id,
            ]));
        }

        $counsellors = User::where('role', 'counsellor')->where('is_active', true)->get();
        $counsellor  = $counsellors->get((int) $sub - 1);

        if (!$counsellor) {
            return $this->end("Invalid selection.\nDial again to retry.");
        }

        CounsellingSession::create([
            'counsellor_id'    => $counsellor->id,
            'student_id'       => $student->id,
            'scheduled_at'     => now()->addDay(),
            'venue'            => null,
            'status'           => 'pending_confirmation',
            'initiated_by'     => 'student',
            'appointment_type' => 'phone',
            'student_reason'   => "USSD booking from {$phone}",
        ]);

        return $this->end(implode("\n", [
            "Appointment",
            "Requested",
            "",
            "Counsellor:",
            $counsellor->name,
            "",
            "Waiting for",
            "Counsellor Approval",
            "",
            "You will be notified",
            "when confirmed.",
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FEATURE 13: GOAL TRACKER
    // ─────────────────────────────────────────────────────────────────────────

    private function handleGoalTracker(User $student): string
    {
        $profile  = $student->studentProfile;
        $career   = $profile?->preferred_career;
        $report   = $this->getReport($student);
        $paths    = $report['msce_paths'] ?? [];
        $topPath  = $paths[0] ?? null;
        $pathName = $topPath ? str_replace(' Path', '', $topPath['path'] ?? '') : null;

        $totalSubs = count($topPath['subjects'] ?? []);
        $readySubs = 0;

        foreach (($topPath['subjects'] ?? []) as $sub) {
            $gs = $topPath['grade_scores'][$sub] ?? null;
            if ($gs !== null && $gs >= 55) {
                $readySubs++;
            }
        }

        $pct    = $totalSubs > 0 ? round(($readySubs / $totalSubs) * 100) : 0;
        $filled = $totalSubs > 0 ? round(($readySubs / $totalSubs) * 10) : 0;
        $bar    = str_repeat('█', $filled) . str_repeat('░', 10 - $filled);

        $notReady = [];
        foreach (($topPath['subjects'] ?? []) as $sub) {
            $gs = $topPath['grade_scores'][$sub] ?? null;
            if ($gs === null || $gs < 55) {
                $notReady[] = $sub;
            }
        }

        $lines = ["Goal Tracker", ""];

        if ($pathName) {
            $lines[] = "Goal: {$pathName}";
        }
        if ($career) {
            $lines[] = "Career: " . $this->truncate($career, 22);
        }

        $lines[] = "";
        $lines[] = "Progress: {$readySubs}/{$totalSubs}";
        $lines[] = "[{$bar}]";
        $lines[] = "{$pct}% ready";

        if (!empty($notReady)) {
            $lines[] = "";
            $lines[] = "Keep improving:";
            foreach (array_slice($notReady, 0, 2) as $s) {
                $lines[] = "- {$s}";
            }
        } else {
            $lines[] = "";
            $lines[] = "All subjects ready!";
        }

        return $this->end(implode("\n", $lines));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function getReport(User $student): array
    {
        try {
            return $this->academicProgress->getFullReport($student);
        } catch (\Throwable) {
            return [];
        }
    }

    private function computeTrend(Collection $results): string
    {
        if ($results->count() < 2) {
            return 'N/A';
        }

        $sorted  = $results->sortBy(fn($r) => $r->updated_at ?? now());
        $half    = (int) ceil($sorted->count() / 2);
        $oldAvg  = $sorted->take($half)->avg('score') ?? 0;
        $newAvg  = $sorted->reverse()->take($half)->avg('score') ?? 0;
        $diff    = $newAvg - $oldAvg;

        if ($diff >= 4) return 'Improving ↑';
        if ($diff <= -4) return 'Declining ↓';
        return 'Stable →';
    }

    private function scoreLabel(?float $score): string
    {
        if ($score === null) return '';
        return $score >= 80 ? 'Excellent' : ($score >= 65 ? 'Strong' : ($score >= 50 ? 'Average' : 'Below Avg'));
    }

    private function firstName(User $user): string
    {
        return explode(' ', $user->name)[0];
    }

    private function findStudent(string $studentNumber): ?User
    {
        if ($studentNumber === '') return null;
        $profile = StudentProfile::where('student_number', $studentNumber)->first();
        return $profile?->user?->load('studentProfile');
    }

    private function truncate(string $text, int $max): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text));
        return strlen($text) <= $max ? $text : substr($text, 0, $max - 3) . '...';
    }

    private function con(string $message): string
    {
        return str_starts_with($message, 'CON ') ? $message : "CON {$message}";
    }

    private function end(string $message): string
    {
        return str_starts_with($message, 'END ') ? $message : "END {$message}";
    }

    private function goodbye(?User $student = null): string
    {
        $name = $student ? "\n" . $this->firstName($student) . "," : '';
        return implode("\n", [
            "SMART CAREER GUIDANCE",
            "",
            "Thank you{$name}",
            "",
            "Study hard and",
            "reach your goals.",
            "",
            "Luwinga Secondary",
            "School",
        ]);
    }
}
