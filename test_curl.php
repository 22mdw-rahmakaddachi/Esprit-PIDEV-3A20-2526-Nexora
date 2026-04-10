<?php
$ch = curl_init('http://127.0.0.1:8000/admin/destinations/new');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
file_put_contents('test_output.html', $res);
