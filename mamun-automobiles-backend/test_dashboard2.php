<?php

$baseUrl = 'http://localhost:8000/api/v1';

// Login
$ch = curl_init("$baseUrl/auth/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'manager@mamunerp.com',
    'password' => 'password123'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
$res = curl_exec($ch);
$loginData = json_decode($res, true);
curl_close($ch);

if (!isset($loginData['data']['token'])) {
    die("Login failed: $res\n");
}

$token = $loginData['data']['token'];

// Dashboard
$ch = curl_init("$baseUrl/invoices?limit=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    'Accept: application/json'
]);
$res = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "STATUS INVOICES: $status\n";
echo "RESPONSE INVOICES:\n" . substr($res, 0, 500) . "\n\n";

$ch = curl_init("$baseUrl/purchases?limit=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $token",
    'Accept: application/json'
]);
$res = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "STATUS PURCHASES: $status\n";
echo "RESPONSE PURCHASES:\n" . substr($res, 0, 500) . "\n\n";
