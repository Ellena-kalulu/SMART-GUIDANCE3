<?php
$base = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/backend';

// dashboard
$p = $base . '/resources/views/layouts/dashboard.blade.php';
$t = file_get_contents($p);
$old = "@php `$accessMode = session('access_mode', 'normal'); @endphp\r\n@if(`$accessMode === 'voice')\r\n<script src=\"{{ asset('js/voice-mode.js') }}\" defer></script>\r\n@elseif(`$accessMode === 'visual')";
if (strpos($t, 'VOICE_CONFIG') !== false) {
    echo "dashboard already patched\n";
} else {
    $new = <<<'BLADE'
@php $accessMode = session('access_mode', 'normal'); @endphp
@if($accessMode === 'voice' && auth()->check() && auth()->user()->isStudent())
<script>
window.VOICE_CONFIG = {
    csrf: @json(csrf_token()),
    askUrl: @json(route('student.voice.ask')),
    routes: {
        dashboard: @json(route('student.dashboard')),
        assessment: @json(route('student.assessment')),
        progress: @json(route('student.progress')),
        career: @json(route('student.career.path')),
        recommendation: @json(route('student.recommendations')),
        subject: @json(route('student.subject.combinations')),
        university: @json(route('student.universities')),
        chatbot: @json(route('student.chatbot')),
        appointment: @json(route('student.appointments')),
    }
};
</script>
@endif
@if($accessMode === 'voice')
<script src="{{ asset('js/voice-mode.js') }}" defer></script>
@elseif($accessMode === 'visual')
BLADE;
    $t2 = str_replace(
        "@php `$accessMode = session('access_mode', 'normal'); @endphp\n@if(`$accessMode === 'voice')\n<script src=\"{{ asset('js/voice-mode.js') }}\" defer></script>\n@elseif(`$accessMode === 'visual')",
        $new,
        $t
    );
    if ($t2 === $t) {
        $t2 = str_replace(
            "@php `$accessMode = session('access_mode', 'normal'); @endphp\r\n@if(`$accessMode === 'voice')\r\n<script src=\"{{ asset('js/voice-mode.js') }}\" defer></script>\r\n@elseif(`$accessMode === 'visual')",
            $new,
            $t
        );
    }
    if ($t2 === $t) { echo "dashboard pattern not found\n"; } else { file_put_contents($p, $t2); echo "dashboard ok\n"; }
}

// web.php
$p = $base . '/routes/web.php';
$t = file_get_contents($p);
if (strpos($t, 'voice.ask') === false) {
    $t = str_replace(
        "Route::post('/chatbot/send',                    [StudentController::class, 'chatbotSend'])->name('chatbot.send');",
        "Route::post('/chatbot/send',                    [StudentController::class, 'chatbotSend'])->name('chatbot.send');\n        Route::post('/voice/ask',                       [StudentController::class, 'voiceAsk'])->name('voice.ask');",
        $t
    );
}
$t = preg_replace("/\r?\n\s*\/\/ Career Roadmap Planner\r?\n\s*Route::get\('\/career-roadmap'[^\n]*\r?\n/", "\n", $t);
file_put_contents($p, $t);
echo "web.php ok\n";

// sidebar
$p = $base . '/resources/views/dashboard/student/partials/sidebar.blade.php';
$t = file_get_contents($p);
$t2 = preg_replace("/\r?\n\s*<x-sidebar-nav :route=\"'student\.career\.roadmap'\".*?<\/x-sidebar-nav>/s", '', $t);
file_put_contents($p, $t2);
echo "sidebar ok\n";
