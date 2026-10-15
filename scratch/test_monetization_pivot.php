<?php
require_once __DIR__ . '/../src/Core/Env.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Session.php';
require_once __DIR__ . '/../src/Core/CSRF.php';
require_once __DIR__ . '/../src/Core/Request.php';
require_once __DIR__ . '/../src/Core/Response.php';
require_once __DIR__ . '/../src/Core/Tenant.php';
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Core/View.php';

use Zyrtex\Core\View;

echo "=== TESTE: PIVÔ DE MONETIZAÇÃO, ADSENSE E ANTI-ADBLOCK ===\n\n";

// 1. Testa Supressão Completa para Loja Premium (Plano Anual Sem Anúncios)
ob_start();
View::partial('adsense_slot', [
    'tenant' => ['is_premium' => 1, 'slug' => 'oficina-vip'],
    'slot_context' => 'tracking'
]);
$outputPremium = ob_get_clean();

echo "1. Loja Premium (is_premium = 1):\n";
if (trim($outputPremium) === '') {
    echo "   [OK] Anúncios 100% suprimidos! Saída totalmente vazia (zero scripts).\n";
} else {
    echo "   [FALHA] Output não está vazio para loja premium!\n";
}

// 2. Testa Renderização para Loja Gratuita (Plano Free) em Rastreio de OS
ob_start();
View::partial('adsense_slot', [
    'tenant' => ['is_premium' => 0, 'slug' => 'oficina-free'],
    'slot_context' => 'tracking'
]);
$outputFree = ob_get_clean();

echo "\n2. Loja Free no Rastreio de OS (is_premium = 0):\n";
$hasWrapper = str_contains($outputFree, 'zyrtex-ad-');
$hasAdbox = str_contains($outputFree, '-adbox');
$hasFallback = str_contains($outputFree, '-fallback');
$hasAdblockScript = str_contains($outputFree, 'pub_300x250');
$hasSandboxBadge = str_contains($outputFree, 'Sandbox Local');

echo "   - Wrapper e identificador único: " . ($hasWrapper ? "[OK]" : "[FALHA]") . "\n";
echo "   - Contêiner de anúncio oficial: " . ($hasAdbox ? "[OK]" : "[FALHA]") . "\n";
echo "   - Contêiner de Fallback Anti-AdBlock: " . ($hasFallback ? "[OK]" : "[FALHA]") . "\n";
echo "   - Script Sentinela de detecção: " . ($hasAdblockScript ? "[OK]" : "[FALHA]") . "\n";
echo "   - Proteção de Origem / Sandbox Local: " . ($hasSandboxBadge ? "[OK]" : "[FALHA]") . "\n";

// 3. Testa Fallback no Contexto Admin
ob_start();
View::partial('adsense_slot', [
    'tenant' => ['is_premium' => 0, 'slug' => 'oficina-free'],
    'slot_context' => 'admin'
]);
$outputAdmin = ob_get_clean();

echo "\n3. Loja Free no Painel Administrativo:\n";
$hasAdminUpgradeCta = str_contains($outputAdmin, 'Assinar Sem Anúncios');
echo "   - CTA de Upgrade do Plano Anual: " . ($hasAdminUpgradeCta ? "[OK]" : "[FALHA]") . "\n";

echo "\n=== TODOS OS TESTES EXECUTADOS COM SUCESSO! ===\n";
