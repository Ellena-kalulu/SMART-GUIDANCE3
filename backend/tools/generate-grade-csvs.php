<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$dir = storage_path('app/grade-imports');
$written = app(\App\Services\GradesCsvService::class)->writeSampleFilesToDisk($dir);
echo count($written) . " CSV files written to $dir\n";
