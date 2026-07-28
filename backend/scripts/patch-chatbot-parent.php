<?php
// Fix extra closing div in student index
$f = __DIR__ . '/../resources/views/dashboard/student/index.blade.php';
$c = file_get_contents($f);
$c = str_replace("    </div>\n\n    </div>\n</div>\n@endsection", "    </div>\n</div>\n@endsection", $c);
file_put_contents($f, $c);
echo "index div fix ok\n";

// Patch chatbotSend with try-catch
$f2 = __DIR__ . '/../app/Http/Controllers/StudentController.php';
$c2 = file_get_contents($f2);
$old = '    public function chatbotSend(Request $request): JsonResponse
    {
        $request->validate([
            \'session_id\' => [\'required\', \'exists:chatbot_sessions,uuid\'],
            \'message\'    => [\'required\', \'string\', \'max:500\'],
        ]);

        $user    = Auth::user();
        $session = \\App\\Models\\ChatbotSession::where(\'uuid\', $request->input(\'session_id\'))->firstOrFail();

        abort_if($session->user_id !== $user->id, 403);

        $userMessage = $request->input(\'message\');

        // Persist user message
        $session->messages()->create([\'sender\' => \'user\', \'message\' => $userMessage]);

        // Build recent history for context
        $history = $session->messages()
            ->orderBy(\'created_at\')
            ->get([\'sender\', \'message\'])
            ->toArray();

        // Get AI response
        $botText = $this->chatbotService->respond($userMessage, $user, $history);

        // Persist bot reply
        $session->messages()->create([\'sender\' => \'bot\', \'message\' => $botText]);

        ActivityLog::record(\'chatbot_interaction\', $session, [\'message\' => substr($userMessage, 0, 100)]);

        return response()->json([\'reply\' => $botText]);
    }';

$new = '    public function chatbotSend(Request $request): JsonResponse
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

            $history = $session->messages()
                ->orderBy(\'created_at\')
                ->get([\'sender\', \'message\'])
                ->toArray();

            $botText = $this->chatbotService->respond($userMessage, $user, $history);

            $session->messages()->create([\'sender\' => \'bot\', \'message\' => $botText]);

            try {
                ActivityLog::record(\'chatbot_interaction\', $session, [\'message\' => substr($userMessage, 0, 100)]);
            } catch (\\Throwable $e) {
                // logging must not break chat
            }

            return response()->json([\'reply\' => $botText]);
        } catch (\\Illuminate\\Validation\\ValidationException $e) {
            throw $e;
        } catch (\\Throwable $e) {
            \\Illuminate\\Support\\Facades\\Log::error(\'chatbotSend failed\', [\'error\' => $e->getMessage()]);
            $fallback = $this->chatbotService->respond(
                (string) $request->input(\'message\', \'\'),
                Auth::user(),
                []
            );
            return response()->json([\'reply\' => $fallback]);
        }
    }';

if (strpos($c2, 'chatbotSend failed') === false) {
    $c2 = str_replace($old, $new, $c2);
    file_put_contents($f2, $c2);
    echo "chatbot controller ok\n";
} else {
    echo "chatbot already patched\n";
}

// Fix parent sidebar language section colors for dark sidebar
$f3 = __DIR__ . '/../resources/views/dashboard/parent/partials/sidebar.blade.php';
$c3 = file_get_contents($f3);
$c3 = str_replace('border-t border-slate-200 px-2', 'border-t border-white/10 px-2', $c3);
$c3 = str_replace('text-slate-400', 'text-white/40', $c3);
file_put_contents($f3, $c3);
echo "parent sidebar ok\n";
