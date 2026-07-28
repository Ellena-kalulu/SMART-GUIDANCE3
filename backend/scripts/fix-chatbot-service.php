<?php
$path = dirname(__DIR__) . '/app/Services/ChatbotService.php';
$c = file_get_contents($path);
$bad = '        foreach ($this->geminiModelsToTry() as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiKey}";

        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                \'role\'  => $msg[\'role\'] === \'assistant\' ? \'model\' : \'user\',
                \'parts\' => [[\'text\' => $msg[\'content\']]],
            ];
        }

            try {';
$good = '        foreach ($this->geminiModelsToTry() as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiKey}";

            try {';
if (str_contains($c, $bad)) {
    $c = str_replace($bad, $good, $c);
    file_put_contents($path, $c);
    echo "duplicate contents removed\n";
} else { echo "nothing to fix\n"; }
