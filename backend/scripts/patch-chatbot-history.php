<?php
$f = __DIR__ . '/../app/Http/Controllers/StudentController.php';
$c = file_get_contents($f);
$old = '            $userMessage = $request->input(\'message\');

            $session->messages()->create([\'sender\' => \'user\', \'message\' => $userMessage]);

            $history = $session->messages()
                ->orderBy(\'created_at\')
                ->get([\'sender\', \'message\'])
                ->toArray();';
$new = '            $userMessage = $request->input(\'message\');

            $history = $session->messages()
                ->orderBy(\'created_at\')
                ->get([\'sender\', \'message\'])
                ->toArray();

            $session->messages()->create([\'sender\' => \'user\', \'message\' => $userMessage]);';
if (strpos($c, '->toArray();' . "\n\n            \$session->messages()->create") === false && strpos($c, '->toArray();' . "\r\n\r\n            \$session->messages()->create") === false) {
    $c = str_replace($old, $new, $c);
    file_put_contents($f, $c);
    echo "history order fixed\n";
} else {
    echo "already fixed or pattern mismatch\n";
}
