<?php
$base = dirname(__DIR__);

// Parent messages translations
$path = $base . '/resources/views/dashboard/parent/messages.blade.php';
$content = file_get_contents($path);
$content = str_replace(
    'Start the conversation by sending a message below.',
    "{{ __('parent.start_conversation') }}",
    $content
);
$content = str_replace(
    'Select a recipient and send your first message using the form on the left.',
    "{{ __('parent.select_recipient_hint') }}",
    $content
);
file_put_contents($path, $content);

echo "Parent messages updated\n";
