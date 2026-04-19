<?php
require 'vendor/autoload.php';
$m = new Intervention\Image\ImageManager('gd');
echo get_class($m) . " OK\n";

// Test read vs make
$methods = get_class_methods($m);
echo "Methods: " . implode(', ', array_filter($methods, fn($x) => in_array($x, ['make','read','create']))) . "\n";
