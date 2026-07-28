<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\User::where('role', 'student')->first();
if (!$user) {
    echo "No student user\n";
    exit(1);
}

Auth::login($user);
$session = $user->chatbotSessions()->create();

$request = Illuminate\Http\Request::create('/student/chatbot/send', 'POST', [
    'session_id' => $session->uuid,
    'message'    => 'What career suits me?',
]);
$request->headers->set('Accept', 'application/json');

try {
    $controller = app(App\Http\Controllers\StudentController::class);
    $response = $controller->chatbotSend($request);
    echo $response->getContent() . "\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
