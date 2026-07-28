<?php
$path = __DIR__ . '/../app/Http/Controllers/StudentController.php';
$content = file_get_contents($path);
$oldDash = "    public function dashboard(): View
    {
        \$user    = Auth::user();
        \$profile = \$user->studentProfile;

        \$assessmentsCompleted = AssessmentAttempt::where('student_id', \$user->id)";
$newDash = "    public function dashboard(): View
    {
        \$user    = Auth::user();
        \$profile = \$user->studentProfile;

        \$latestAttempt = AssessmentAttempt::where('student_id', \$user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();
        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttempt);

        \$assessmentsCompleted = AssessmentAttempt::where('student_id', \$user->id)";
if (str_contains($content, $oldDash)) { $content = str_replace($oldDash, $newDash, $content); echo "dash ok\n"; }
$oldDup = "        \$latestAttempt = AssessmentAttempt::where('student_id', \$user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();

        \$analysis = \$latestAttempt?->analysis;";
$newDup = "        \$latestAttempt = \$latestAttempt?->fresh(['analysis']);
        \$analysis = \$latestAttempt?->analysis;";
$pos = strpos($content, 'recentActivities');
if ($pos !== false) {
    $sub = substr($content, $pos);
    if (str_contains($sub, $oldDup)) {
        $sub = str_replace($oldDup, $newDup, $sub);
        $content = substr($content, 0, $pos) . $sub;
        echo "dup ok\n";
    }
}
$blocks = [
["    public function recommendations(Request \$request): View\n    {\n        \$user = Auth::user();\n        \$type = \$request->input('type', 'all');",
 "    public function recommendations(Request \$request): View\n    {\n        \$user = Auth::user();\n        \$latestAttempt = AssessmentAttempt::where('student_id', \$user->id)\n            ->where('status', 'completed')\n            ->with('analysis')\n            ->latest('completed_at')\n            ->first();\n        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttempt);\n        \$type = \$request->input('type', 'all');"],
["    public function progress(Request \$request): View\n    {\n        \$user = Auth::user();\n        \$profile = \$user->studentProfile;",
 "    public function progress(Request \$request): View\n    {\n        \$user = Auth::user();\n        \$profile = \$user->studentProfile;\n\n        \$latestAttemptForRefresh = AssessmentAttempt::where('student_id', \$user->id)\n            ->where('status', 'completed')\n            ->with('analysis')\n            ->latest('completed_at')\n            ->first();\n        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttemptForRefresh);"],
["    public function subjectCombinations(Request \$request): View\n    {\n        \$user = Auth::user();\n        \$profile = \$user->studentProfile;",
 "    public function subjectCombinations(Request \$request): View\n    {\n        \$user = Auth::user();\n        \$profile = \$user->studentProfile;\n\n        \$latestAttemptForRefresh = AssessmentAttempt::where('student_id', \$user->id)\n            ->where('status', 'completed')\n            ->with('analysis')\n            ->latest('completed_at')\n            ->first();\n        \$this->refreshAnalysisIfNeeded(\$user, \$latestAttemptForRefresh);"],
];
foreach ($blocks as [$o,$n]) { if (str_contains($content,$o)) { $content=str_replace($o,$n,$content); echo "block ok\n"; } else echo "block miss\n"; }
file_put_contents($path,$content);
echo "done\n";
