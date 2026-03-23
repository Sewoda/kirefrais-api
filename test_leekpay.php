<?php
$urls = [
    'https://api.leekpay.me/api/v1/checkout',
    'https://api.leekpay.me/checkout',
    'https://leekpay.me/api/v1/checkout'
];

foreach ($urls as $url) {
    echo $url . ":\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    
    echo "HTTP Status: " . $info['http_code'] . "\n";
    echo "Response: " . substr($response, 0, 100) . "\n\n";
    curl_close($ch);
}
