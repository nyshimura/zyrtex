<?php

declare(strict_types=1);

$ch = curl_init('http://localhost/zyrtex/demo-assistencia');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code for /demo-assistencia: {$code}\n";
echo "Response snippet: " . substr(strip_tags((string)$res), 0, 150) . "\n";
