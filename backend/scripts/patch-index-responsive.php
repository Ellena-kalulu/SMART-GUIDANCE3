<?php
$path = dirname(__DIR__) . '/resources/views/dashboard/student/index.blade.php';
$c = file_get_contents($path);
$c = str_replace('lg:grid-cols-[260px_minmax(0,1fr)]', 'md:grid-cols-[260px_minmax(0,1fr)]', $c);
$c = str_replace('lg:sticky lg:top-20', 'md:sticky md:top-20', $c);
$c = str_replace('hidden lg:block">Student Features', 'hidden md:block">Student Features', $c);
$c = str_replace('<div class="hidden lg:block">', '<div class="hidden md:block">', $c);
$c = str_replace('<div class="lg:hidden overflow-x-auto', '<div class="md:hidden overflow-x-auto', $c);
file_put_contents($path, $c);
echo "index responsive updated to md\n";
