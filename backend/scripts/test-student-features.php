<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user = App\Models\User::where('role', 'student')->first();
Auth::login($user);
$c = app(App\Http\Controllers\StudentController::class);
foreach (['subjectCombinations'=>fn()=>$c->subjectCombinations(request()),'progress'=>fn()=>$c->progress(request()),'careerPath'=>fn()=>$c->careerPath(),'chatbot'=>fn()=>$c->chatbot()] as $n=>$fn) {
  try { $v=$fn(); echo "OK $n -> {$v->name()}\n"; } catch (Throwable $e) { echo "FAIL $n: {$e->getMessage()}\n"; }
}
