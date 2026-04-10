<?php
$query = "Paris";
$url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($query) . '&format=json&limit=5';
$context = stream_context_create([
    'http' => [
        'header'  => "User-Agent: SymfonyApp/1.0\r\n",
        'timeout' => 3,
    ],
]);
$response = @file_get_contents($url, false, $context);
if ($response) {
    echo "SUCCESS\n";
    echo substr($response, 0, 100) . "\n";
} else {
    echo "FAILED\n";
    $error = error_get_last();
    print_r($error);
}
