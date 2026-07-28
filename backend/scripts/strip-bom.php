<?php

$root = dirname(__DIR__);
$bom = "\xEF\xBB\xBF";
$fixed = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $content = file_get_contents($path);
    if (str_starts_with($content, $bom)) {
        file_put_contents($path, substr($content, 3));
        echo "Fixed: {$path}\n";
        $fixed++;
    }
}

echo "Done. Fixed {$fixed} file(s).\n";
