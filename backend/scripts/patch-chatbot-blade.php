<?php
$f = __DIR__ . '/../resources/views/dashboard/student/chatbot.blade.php';
$c = file_get_contents($f);
$old = "const sessionId = '{{ \$activeSession->uuid }}';
const csrfToken = '{{ csrf_token() }}';";
$new = "const sessionId = @json(\$activeSession->uuid);
const csrfToken = document.querySelector('meta[name=\"csrf-token\"]')?.content || @json(csrf_token());
const chatSendUrl = @json(route('student.chatbot.send'));";
if (strpos($c, 'chatSendUrl') === false) {
    $c = str_replace($old, $new, $c);
}
$c = str_replace("fetch('{{ route(\"student.chatbot.send\") }}', {", "fetch(chatSendUrl, {", $c);
$c = str_replace(
    "appendMessage('bot', 'Connection error. Please check your internet and try again.');",
    "appendMessage('bot', err?.message ? ('Error: ' + err.message) : 'Could not reach the assistant. Please refresh and try again.');",
    $c
);
// safer JSON parse
$needle = '        const data = await res.json();';
$replacement = '        let data;
        try { data = await res.json(); } catch (parseErr) {
            appendMessage(\'bot\', \'Unexpected server response. Please refresh and try again.\');
            return;
        }';
if (strpos($c, 'Unexpected server response') === false) {
    $c = str_replace($needle, $replacement, $c);
}
file_put_contents($f, $c);
echo "chatbot blade ok\n";
