<?php
$ch = curl_init('http://127.0.0.1:8000/api/v1/dashboard');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Origin: http://localhost:5173',
    'Access-Control-Request-Method: GET'
]);
$res = curl_exec($ch);
echo $res;
