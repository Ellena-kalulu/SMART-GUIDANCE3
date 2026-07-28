<?php
$files = [
    'resources/views/dashboard/student/subject-combinations.blade.php',
    'resources/views/dashboard/student/chatbot.blade.php',
    'resources/views/dashboard/student/partials/academic-intelligence.blade.php',
    'resources/views/components/sidebar-nav.blade.php',
    'resources/views/layouts/auth.blade.php',
    'resources/views/layouts/dashboard.blade.php',
    'resources/views/dashboard/student/partials/sidebar.blade.php',
];
foreach ($files as $f) {
    $path = __DIR__ . '/../' . $f;
    if (!file_exists($path)) {
        echo "$f: MISSING\n";
        continue;
    }
    $bytes = file_get_contents($path, false, null, 0, 4);
    $hex = bin2hex($bytes);
    $enc = 'unknown';
    if (str_starts_with($hex, 'efbbbf')) $enc = 'UTF-8 BOM';
    elseif (str_starts_with($hex, 'fffe')) $enc = 'UTF-16 LE BOM';
    elseif (str_starts_with($hex, 'feff')) $enc = 'UTF-16 BE BOM';
    elseif (str_starts_with($hex, '3c403f')) $enc = 'UTF-8 (starts with @ or <)';
    elseif (str_starts_with($hex, '40003c00')) $enc = 'UTF-16 LE (starts with @)';
    echo "$f: $hex => $enc\n";
}
