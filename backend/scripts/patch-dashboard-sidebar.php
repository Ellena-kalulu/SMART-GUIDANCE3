<?php
$path = dirname(__DIR__) . '/resources/views/layouts/dashboard.blade.php';
$layout = file_get_contents($path);
$layout = str_replace(
    'style="transition: width 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1)">',
    'style="transition: width 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1); background:linear-gradient(180deg,#0f2550 0%,#122d5e 60%,#0e2347 100%)">',
    $layout
);
$layout = str_replace(
    'class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg transition-colors hover:bg-slate-100"
                        style="color:#6b7280" aria-label="Toggle menu">',
    'class="@if(auth()->user()->isStudent()) md:hidden @else lg:hidden @endif flex items-center justify-center w-9 h-9 rounded-lg transition-colors hover:bg-slate-100"
                        style="color:#6b7280" aria-label="Toggle menu">',
    $layout
);
file_put_contents($path, $layout);
echo "layout patched\n";
