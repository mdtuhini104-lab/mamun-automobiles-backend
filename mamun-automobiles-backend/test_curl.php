<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/v1/dashboard");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
));
$result = curl_exec($ch);
$info = curl_getinfo($ch);
echo "HTTP CODE: " . $info['http_code'] . "\n";
echo "BODY: " . $result . "\n";
curl_close($ch);
