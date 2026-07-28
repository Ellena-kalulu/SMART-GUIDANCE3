<?php
$path = dirname(__DIR__) . '/resources/views/dashboard/student/index.blade.php';
$c = file_get_contents($path);
$fixed = preg_replace('/(@endforelse\s*\n    <\/div>\s*\n)<\/div>\s*\n@endsection/s', "$1    </div>\n</div>\n@endsection", $c, 1, $count);
if ($count) {
    file_put_contents($path, $fixed);
    echo "fixed missing div\n";
} else { echo "no change needed\n"; }
