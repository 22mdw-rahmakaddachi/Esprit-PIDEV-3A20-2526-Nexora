<?php
$url = 'https://nominatim.openstreetmap.org/search?q=Paris&format=json&limit=5';
$context = stream_context_create([
    'http' => [
        'header'  => "User-Agent: SymfonyApp/1.0\r\n",
        'timeout' => 3,
    ],
]);
$response = file_get_contents($url, false, $context);
if ($response === false) {
    echo "FAILED: " . print_r(error_get_last(), true) . "\n";
} else {
    echo "SUCCESS: " . substr($response, 0, 100) . "\n";
}
