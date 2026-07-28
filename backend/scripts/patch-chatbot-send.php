<?php
$base = dirname(__DIR__);
$ctrlPath = $base . '/app/Http/Controllers/StudentController.php';
$ctrl = file_get_contents($ctrlPath);
if (str_contains($ctrl, 'chatbotSend failed')) { echo "already patched\n"; exit(0);} 
$pattern = '/public function chatbotSend\(Request \$request\): JsonResponse\s*\{[\s\S]*?return response\(\)->json\(\[\'reply\' => \$botText\]\);\s*\}/';
$replacement = 'public function chatbotSend(Request $request): JsonResponse
    {
        try {
            $request->validate([
                \'session_id\' => [\'required\', \'exists:chatbot_sessions,uuid\'],
                \'message\'    => [\'required\', \'string\', \'max:500\'],
            ]);

            $user    = Auth::user();
            $session = \\App\\Models\\ChatbotSession::where(\'uuid\', $request->input(\'session_id\'))->firstOrFail();
            abort_if($session->user_id !== $user->id, 403);

            $userMessage = $request->input(\'message\');
            $session->messages()->create([\'sender\' => \'user\', \'message\' => $userMessage]);

            $history = $session->messages()->orderBy(\'created_at\')->get([\'sender\', \'message\'])->toArray();
            $botText = $this->chatbotService->respond($userMessage, $user, $history);
            $session->messages()->create([\'sender\' => \'bot\', \'message\' => $botText]);

            try { ActivityLog::record(\'chatbot_interaction\', $session, [\'message\' => substr($userMessage, 0, 100)]); } catch (\\Throwable $e) {}

            return response()->json([\'reply\' => $botText]);
        } catch (\\Illuminate\\Validation\\ValidationException $e) {
            throw $e;
        } catch (\\Throwable $e) {
            Log::error(\'chatbotSend failed\', [\'error\' => $e->getMessage()]);
            return response()->json([
                \'reply\' => $this->chatbotService->respond((string) $request->input(\'message\', \'\'), Auth::user(), []),
            ]);
        }
    }';
$ctrl = preg_replace($pattern, $replacement, $ctrl, 1, $count);
if (!$count) { fwrite(STDERR, "chatbotSend not patched\n"); exit(1);} 
file_put_contents($ctrlPath, $ctrl);
echo "chatbotSend patched\n";
