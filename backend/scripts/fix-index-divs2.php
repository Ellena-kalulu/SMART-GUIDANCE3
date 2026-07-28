<?php
$path = dirname(__DIR__) . '/resources/views/dashboard/student/index.blade.php';
$c = file_get_contents($path);
if (!preg_match('/\n    <\/div>\n<\/div>\n@endsection\s*$/', $c)) {
    $c = preg_replace('/\n    <\/div>\n@endsection\s*$/', "\n    </div>\n</div>\n@endsection\n", $c);
    file_put_contents($path, $c);
    echo "added missing grid closing div\n";
} else { echo "structure ok\n"; }
