<?php
$p = 'c:/laragon/www/SMART-GUIDANCE-TOOL-main/lang/ny/parent.php';
$c = file_get_contents($p);
$c = str_replace("woyang'anira", "woyang\\'anira", $c);
file_put_contents($p, $c);
echo "fixed\n";
