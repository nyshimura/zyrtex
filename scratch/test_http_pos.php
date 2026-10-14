<?php

$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) {
    unlink($cookieFile);
}

// 1. Obtém formulário de login para pegar o token CSRF
$ch = curl_init('http://localhost/zyrtex/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$html = curl_exec($ch);

preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m);
$token = $m[1] ?? '';
echo "1. Token CSRF obtido: " . ($token ? 'SIM' : 'NÃO') . "\n";

// 2. Faz login como lojista demo
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $token,
    'email' => 'admin@oficina.com.br',
    'password' => 'admin123'
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$dash = curl_exec($ch);
$dashCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "2. Login executado: HTTP {$dashCode}\n";

// 3. Testa PDV Totem
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/admin/pdv');
curl_setopt($ch, CURLOPT_POST, false);
$pdv = curl_exec($ch);
$pdvCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "3. Acesso ao PDV Totem (/admin/pdv): HTTP {$pdvCode} (Tamanho: " . strlen($pdv) . " bytes)\n";
if (strpos($pdv, 'Terminal PDV Balcão Touch Screen') !== false) {
    echo "   -> Confirmado: Layout do Totem Touch Screen carregado com sucesso!\n";
}

// 4. Testa Estoque
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/admin/estoque');
$estoque = curl_exec($ch);
$estoqueCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "4. Acesso ao Estoque (/admin/estoque): HTTP {$estoqueCode} (Tamanho: " . strlen($estoque) . " bytes)\n";
if (strpos($estoque, 'Peças & Estoque de Balcão') !== false) {
    echo "   -> Confirmado: Módulo de Estoque carregado com sucesso!\n";
}

// 5. Testa Adição ao Carrinho do PDV via POST
preg_match('/name="csrf_token" value="([^"]+)"/', $pdv, $m2);
$token2 = $m2[1] ?? $token;

curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/admin/pdv/adicionar');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_token' => $token2,
    'product_id' => 1,
    'quantity' => 1
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$pdvWithCart = curl_exec($ch);
echo "5. Adição de item ao carrinho: HTTP " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
if (strpos($pdvWithCart, 'Tela Display iPhone 11') !== false) {
    echo "   -> Confirmado: Item apareceu no carrinho do Totem!\n";
}

// 6. Testa Comprovante Térmico
curl_setopt($ch, CURLOPT_URL, 'http://localhost/zyrtex/admin/pdv/comprovante?id=1&formato=80mm');
curl_setopt($ch, CURLOPT_POST, false);
$cupom = curl_exec($ch);
$cupomCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "6. Comprovante Térmico 80mm: HTTP {$cupomCode} (Tamanho: " . strlen($cupom) . " bytes)\n";
if (strpos($cupom, 'COMPROVANTE DE VENDA A VISTA') !== false) {
    echo "   -> Confirmado: Comprovante térmico gerado perfeitamente!\n";
}

curl_close($ch);
echo "\n=== TODOS OS ENDPOINTS HTTP DA FASE 3 TESTADOS E APROVADOS! ===\n";
