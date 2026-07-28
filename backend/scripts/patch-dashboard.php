<?php
$p = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/backend/resources/views/layouts/dashboard.blade.php';
$t = file_get_contents($p);
if (strpos($t, 'VOICE_CONFIG') !== false) { echo "already done\n"; exit(0); }
$needle = "@php \$accessMode = session('access_mode', 'normal'); @endphp";
$insert = <<<'INS'


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
INS;
$t2 = str_replace($needle, $needle . $insert, $t);
if ($t2 === $t) { echo "pattern not found\n"; exit(1); }
file_put_contents($p, $t2);
echo "dashboard ok\n";
