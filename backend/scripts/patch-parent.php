<?php
$base = dirname(__DIR__);
$f = $base . '/app/Http/Controllers/ParentController.php';
$c = file_get_contents($f);
$c = str_replace(
    '$children = $parent->children()->pluck(\'id\');',
    '$childIds = $parent->children()->pluck(\'users.id\');',
    $c
);
$c = str_replace(
    'abort_if(!$this->canMessage($parent, $recipient, $children), 403);',
    'abort_if(!$this->canMessage($parent, $recipient, $childIds), 403);',
    $c
);
$c = str_replace(
    'if (!empty($data[\'student_id\']) && !$children->contains($data[\'student_id\'])) {',
    'if (!empty($data[\'student_id\']) && !$childIds->contains($data[\'student_id\'])) {',
    $c
);
file_put_contents($f, $c);
echo "patched\n";
