<?php
function deleteDir($dirPath) {
    if (!is_dir($dirPath)) return;
    $files = array_diff(scandir($dirPath), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dirPath/$file")) ? deleteDir("$dirPath/$file") : unlink("$dirPath/$file");
    }
    rmdir($dirPath);
}

$root = __DIR__;
$toDelete = ['avis', 'excursion', 'avis_data.sql', 'db_import.php', 'write_activite_ctrl.php', 'test_img.php', 'reverse-engineer.php'];

foreach ($toDelete as $item) {
    $path = $root . '/' . $item;
    if (is_dir($path)) {
        echo "Deleting directory: $item\n";
        deleteDir($path);
    } elseif (file_exists($path)) {
        echo "Deleting file: $item\n";
        unlink($path);
    }
}
echo "Cleanup completed.\n";
