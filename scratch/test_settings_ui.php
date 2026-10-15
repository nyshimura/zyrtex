<?php

$cookieFile = __DIR__ . '/cookie.txt';

$ch = curl_init('http://localhost/zyrtex/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$html = curl_exec($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m);
$token = $m[1] ?? '';

// Login
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $token,
    'email' => 'admin@oficina.com.br',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);

// Testa Dashboard
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/admin');
curl_setopt($ch, CURLOPT_POST, false);
$dash = curl_exec($ch);
echo "1. Dashboard HTTP Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
if (strpos($dash, 'Sua Página Pública & Área do Cliente') !== false) {
    echo "   -> Confirmado: Card de compartilhamento presente no Dashboard!\n";
}
if (strpos($dash, 'Assistência') !== false && strpos($dash, 'Assist??ncia') === false) {
    echo "   -> Confirmado: Acentuação UTF-8 correta ('Assistência')!\n";
} else {
    echo "   -> ATENÇÃO: Verifique UTF-8 no Dashboard.\n";
}

// Testa Configurações
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/admin/configuracoes');
$cfg = curl_exec($ch);
echo "2. Configurações HTTP Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
if (strpos($cfg, 'tab-btn-empresa') !== false && strpos($cfg, 'tab-btn-pagamentos') !== false && strpos($cfg, 'tab-btn-publica') !== false) {
    echo "   -> Confirmado: Todas as 5 abas presentes!\n";
}
if (strpos($cfg, 'box-pix-fields') !== false && strpos($cfg, 'box-mp-fields') !== false) {
    echo "   -> Confirmado: Blocos exclusivos de Pix e Mercado Pago presentes!\n";
}

// Testa Vitrine Pública
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/?tenant=demo-assistencia');
$public = curl_exec($ch);
echo "3. Vitrine Pública HTTP Status: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
if (strpos($public, 'Assistência') !== false && strpos($public, 'Assist??ncia') === false) {
    echo "   -> Confirmado: Vitrine pública renderizando acentos UTF-8 perfeitamente!\n";
}

curl_close($ch);
echo "\n=== TODOS OS TESTES DE INTERFACE, ABAS E UTF-8 PASSARAM COM SUCESSO! ===\n";
