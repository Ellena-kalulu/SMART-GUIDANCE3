<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$users = App\Models\User::where('role', 'student')->get();
$controller = app(App\Http\Controllers\StudentController::class);

foreach ($users as $user) {
    auth()->login($user);
    try {
        $controller->subjectCombinations(request());
        echo 'OK student ' . $user->id . ' ' . $user->name . PHP_EOL;
    } catch (Throwable $e) {
        echo 'FAIL student ' . $user->id . ': ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    }
}
