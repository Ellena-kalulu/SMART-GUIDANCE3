<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$key = config('services.gemini.key');
if (empty($key)) {
    echo "GEMINI_API_KEY is empty\n";
    exit(1);
}

foreach (['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-2.0-flash-lite', 'gemini-1.5-flash-8b'] as $model) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";
    $r = Illuminate\Support\Facades\Http::timeout(15)->post($url, [
        'contents' => [['role' => 'user', 'parts' => [['text' => 'Say hi in one word']]]],
        'generationConfig' => ['maxOutputTokens' => 10],
    ]);
    echo $model . ': ' . $r->status() . ' - ' . substr($r->body(), 0, 150) . PHP_EOL;
}
