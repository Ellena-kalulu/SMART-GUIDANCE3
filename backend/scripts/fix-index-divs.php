<?php
$base = dirname(__DIR__);
$indexPath = $base . '/resources/views/dashboard/student/index.blade.php';
$index = file_get_contents($indexPath);
$index = preg_replace('/\n    <\/div>\n    <\/div>\n    <\/div>\n<\/div>\n@endsection\s*$/', "\n    </div>\n</div>\n@endsection\n", $index);
file_put_contents($indexPath, $index);
echo "index divs fixed\n";
