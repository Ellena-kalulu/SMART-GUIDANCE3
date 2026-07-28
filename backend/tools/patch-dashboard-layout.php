<?php
$path = __DIR__ . '/../resources/views/layouts/dashboard.blade.php';
$content = file_get_contents($path);
$content = str_replace(
    "body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow-x: hidden; color: #0f172a; }",
    "body { font-family: 'Inter', sans-serif; background: #f8fafc; overflow-x: clip; color: #0f172a; }",
    $content
);
$insert = "\n        #sidebar-nav { max-height: calc(100vh - 14rem); }\n        @media (min-width: 1024px) {\n            #main-content { margin-left: 16rem; width: calc(100% - 16rem); }\n        }";
if (!str_contains($content, '#sidebar-nav { max-height')) {
    $content = str_replace(
        '        header { overflow: visible !important; }',
        '        header { overflow: visible !important; }' . $insert,
        $content
    );
}
file_put_contents($path, $content);
echo "layout ok\n";
