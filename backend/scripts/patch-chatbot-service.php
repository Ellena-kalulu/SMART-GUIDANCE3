<?php
$base = dirname(__DIR__);
$chatPath = $base . '/app/Services/ChatbotService.php';
$chat = file_get_contents($chatPath);
if (str_contains($chat, 'geminiModelsToTry')) { echo "already patched\n"; exit(0);} 
$old = '    private function callGemini(string $systemPrompt, array $messages): ?string
    {
        $model = config(\'services.gemini.model\', \'gemini-2.0-flash\');
        $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiKey}";';
if (!str_contains($chat, $old)) { fwrite(STDERR, "pattern not found\n"); exit(1);} 
$chat = str_replace($old, '    private function geminiModelsToTry(): array
    {
        return array_values(array_unique(array_filter([
            config(\'services.gemini.model\', \'gemini-1.5-flash\'),
            \'gemini-1.5-flash\',
            \'gemini-2.0-flash-lite\',
            \'gemini-2.0-flash\',
        ])));
    }

    private function callGemini(string $systemPrompt, array $messages): ?string
    {
        $contents = [];
        foreach ($messages as $msg) {
            $contents[] = [
                \'role\'  => $msg[\'role\'] === \'assistant\' ? \'model\' : \'user\',
                \'parts\' => [[\'text\' => $msg[\'content\']]],
            ];
        }

        foreach ($this->geminiModelsToTry() as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->geminiKey}";', $chat);
$chat = str_replace("        try {\n            \$response = Http::timeout(30)->post(\$url, [", "            try {\n                \$response = Http::timeout(12)->post(\$url, [", $chat);
$chat = str_replace("            if (\$response->successful()) {\n                return \$response->json('candidates.0.content.parts.0.text');\n            }\n\n            Log::warning('Gemini API error', ['status' => \$response->status(), 'body' => \$response->body()]);\n        } catch (\\Exception \$e) {\n            Log::error('ChatbotService Gemini exception', ['error' => \$e->getMessage()]);\n        }\n\n        return null;\n    }", "                if (\$response->successful()) {\n                    \$text = \$response->json('candidates.0.content.parts.0.text');\n                    if (is_string(\$text) && trim(\$text) !== '') {\n                        return \$text;\n                    }\n                }\n\n                Log::warning('Gemini API error', ['model' => \$model, 'status' => \$response->status()]);\n            } catch (\\Throwable \$e) {\n                Log::error('ChatbotService Gemini exception', ['model' => \$model, 'error' => \$e->getMessage()]);\n            }\n        }\n\n        return null;\n    }", $chat);
$chat = preg_replace('/\n        \$contents = \[\];\n        foreach \(\$messages as \$msg\) \{[\s\S]*?\}\n\n        foreach \(\$this->geminiModelsToTry\(\) as \$model\) \{\n            \$url = "https:\/\/generativelanguage\.googleapis\.com\/v1beta\/models\/\{\$model\}:generateContent\?key=\{\$this->geminiKey\}";\n\n        \$contents = \[\];\n        foreach \(\$messages as \$msg\) \{[\s\S]*?\}\n\n        foreach/', "\n        foreach", $chat, 1);
file_put_contents($chatPath, $chat);
echo "ChatbotService patched\n";
