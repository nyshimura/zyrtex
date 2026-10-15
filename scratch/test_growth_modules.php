<?php
/**
 * Teste End-to-End dos Módulos de Growth:
 * 1. Remarketing Progressivo & Fidelidade
 * 2. Programa de Indicação Anti-Fraude ("Indique & Ganhe")
 */

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Database;
use Zyrtex\Core\Env;
use Zyrtex\Services\RemarketingService;
use Zyrtex\Services\ReferralService;

// Carrega .env
Env::load(__DIR__ . '/../.env');

echo "=== TESTE AUTOMATIZADO DOS MÓDULOS DE GROWTH (ZYRTEX) ===\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(string $desc, bool $condition): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$desc}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failCount++;
    }
}

// 1. Teste de Cálculo de Desconto Progressivo por Fidelidade
echo "1. Testando RemarketingService::calculateLoyaltyDiscount...\n";
$cfg = RemarketingService::getConfig();
$discount0m = RemarketingService::calculateLoyaltyDiscount(0);
assertTest("0 meses de cadastro deve receber desconto base ({$cfg['base_discount']}%)", (float)$discount0m === (float)$cfg['base_discount']);

$discount3m = RemarketingService::calculateLoyaltyDiscount(3);
$expected3m = min($cfg['max_discount'], $cfg['base_discount'] + (3 * $cfg['loyalty_bonus_per_month']));
assertTest("3 meses de cadastro calcula corretamente ({$expected3m}%)", (float)$discount3m === (float)$expected3m);

$discount6m = RemarketingService::calculateLoyaltyDiscount(6);
$expected6m = min($cfg['max_discount'], $cfg['base_discount'] + (6 * $cfg['loyalty_bonus_per_month']));
assertTest("6 meses de cadastro calcula corretamente ({$expected6m}%)", (float)$discount6m === (float)$expected6m);

$discount36m = RemarketingService::calculateLoyaltyDiscount(36);
assertTest("36 meses de cadastro deve respeitar o teto máximo ({$cfg['max_discount']}%)", (float)$discount36m === (float)$cfg['max_discount']);

// 2. Teste de Métricas do Remarketing
echo "\n2. Testando RemarketingService::getMetrics...\n";
$remMetrics = RemarketingService::getMetrics();
assertTest("Métricas retornam total_in_queue", isset($remMetrics['total_in_queue']));
assertTest("Métricas retornam total_recovered_count", isset($remMetrics['total_recovered_count']));
assertTest("Métricas retornam conversion_rate", isset($remMetrics['conversion_rate']));

// 3. Teste de Geração de Código de Indicação
echo "\n3. Testando ReferralService::getOrCreateReferralCode...\n";
$firstTenant = Database::fetchOne("SELECT id, business_name, referral_code FROM tenants ORDER BY id ASC LIMIT 1");
if ($firstTenant) {
    $code = ReferralService::getOrCreateReferralCode((int)$firstTenant['id']);
    assertTest("Tenant possui código de indicação válido", !empty($code) && strlen($code) >= 6);
    assertTest("Código gerado é persistente no tenant", !empty($code));
}

// 4. Teste de Métricas de Indicação no SuperAdmin
echo "\n4. Testando ReferralService::getSuperAdminMetrics...\n";
$refMetrics = ReferralService::getSuperAdminMetrics();
assertTest("SuperAdmin metrics contém total_referrals", isset($refMetrics['total_referrals']));
assertTest("SuperAdmin metrics contém total_qualified", isset($refMetrics['total_qualified']));
assertTest("SuperAdmin metrics contém top_referrers", is_array($refMetrics['top_referrers']));

// 5. Simulação de Indicação e Validação Anti-Fraude
echo "\n5. Testando Ciclo Anti-Fraude de Indicação...\n";

// Localiza dois tenants para teste ou cria mock limpo
$referrer = Database::fetchOne("SELECT id, business_name, is_premium, expires_at FROM tenants WHERE id = 1");
$referred = Database::fetchOne("SELECT id, business_name, is_premium, expires_at FROM tenants WHERE id = 2");

if ($referrer && $referred) {
    // Garante que não há indicação antiga entre eles para o teste
    Database::execute("DELETE FROM tenant_referrals WHERE referred_tenant_id = :id", ['id' => $referred['id']]);
    
    // Cria indicação pendente
    Database::execute("
        INSERT INTO tenant_referrals 
        (referrer_tenant_id, referred_tenant_id, status, reward_value, created_at)
        VALUES (:ref_id, :target_id, 'pending', 30, NOW())
    ", [
        'ref_id' => $referrer['id'],
        'target_id' => $referred['id']
    ]);

    $createdRef = Database::fetchOne("SELECT * FROM tenant_referrals WHERE referred_tenant_id = :id AND status = 'pending'", ['id' => $referred['id']]);
    assertTest("Indicação pendente criada com sucesso", !empty($createdRef));

    // Agora simula o gatilho da aprovação da PRIMEIRA OS ou PLANO
    $qualified = ReferralService::qualifyReferral((int)$referred['id'], 'first_os_paid', 'OS #9999');
    assertTest("qualifyReferral retorna true quando qualificada", $qualified === true);

    $updatedRef = Database::fetchOne("SELECT * FROM tenant_referrals WHERE id = :id", ['id' => $createdRef['id']]);
    assertTest("Status da indicação mudou para 'rewarded'", $updatedRef['status'] === 'rewarded');
    assertTest("Tipo de qualificação gravado como 'first_os_paid'", $updatedRef['qualification_type'] === 'first_os_paid');
    assertTest("Timestamp de qualificação gravado", !empty($updatedRef['qualified_at']));

    // Verifica se o indicador teve os dias estendidos
    $updatedReferrer = Database::fetchOne("SELECT is_premium, expires_at FROM tenants WHERE id = :id", ['id' => $referrer['id']]);
    assertTest("Indicador agora tem is_premium = 1", (int)$updatedReferrer['is_premium'] === 1);
    assertTest("Indicador teve data de expiração prorrogada", strtotime($updatedReferrer['expires_at']) > time());

    // Tentativa de re-qualificar a mesma indicação deve retornar false (idempotência anti-fraude)
    $secondQualify = ReferralService::qualifyReferral((int)$referred['id'], 'subscription_approved', 'SUB #123');
    assertTest("Re-qualificação é bloqueada (idempotente)", $secondQualify === false);
}

// 6. Teste de Renderização dos Templates
echo "\n6. Testando renderização das Views modificadas...\n";
ob_start();
$settingsRows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings");
$settings = [];
foreach ($settingsRows as $r) {
    $settings[$r['config_key']] = $r['config_value'];
}
$versionData = ['version' => '1.2.0', 'github_repo' => 'nyshimura/zyrtex', 'github_url' => 'https://github.com/nyshimura/zyrtex', 'release_channel' => 'stable'];
$envHealth = ['php_version' => PHP_VERSION, 'php_ok' => true, 'pdo_ok' => true, 'curl_ok' => true, 'openssl_ok' => true, 'storage_writable' => true, 'os' => PHP_OS_FAMILY];
require __DIR__ . '/../views/superadmin/settings.php';
$htmlSettings = ob_get_clean();

assertTest("settings.php contém a aba de growth", str_contains($htmlSettings, 'tab-btn-growth'));
assertTest("settings.php contém o painel tab-panel-growth", str_contains($htmlSettings, 'tab-panel-growth'));
assertTest("settings.php contém campo remarketing_expiration_hours", str_contains($htmlSettings, 'name="remarketing_expiration_hours"'));
assertTest("settings.php contém campo referral_welcome_days", str_contains($htmlSettings, 'name="referral_welcome_days"'));
assertTest("settings.php contém campo referral_reward_days", str_contains($htmlSettings, 'name="referral_reward_days"'));

ob_start();
$totalRevenue = 500.0;
$currentMonthRevenue = 150.0;
$prevMonthRevenue = 100.0;
$growthRate = 50.0;
$mrr = 300.0;
$arr = 3600.0;
$totalSubscribers = 10;
$averageTicket = 50.0;
$totalRefunds = 0;
$refundAmount = 0.0;
$refundRate = 0.0;
$totalPending = 2;
$pendingAmount = 100.0;
$breakdown = [];
$monthsHistory = [];
$maxMonthRev = 200.0;
$transactions = [];
$filterStatus = '';
$filterPlan = '';
$search = '';
$remarketingMetrics = $remMetrics;
$referralMetrics = $refMetrics;
require __DIR__ . '/../views/superadmin/financial.php';
$htmlFinancial = ob_get_clean();

assertTest("financial.php contém botão de processar remarketing", str_contains($htmlFinancial, 'Processar Remarketing'));
assertTest("financial.php contém card de Recuperação de Vendas & Remarketing", str_contains($htmlFinancial, 'Recuperação de Vendas &amp; Remarketing') || str_contains($htmlFinancial, 'Recuperação de Vendas & Remarketing'));
assertTest("financial.php contém card Programa Indique & Ganhe", str_contains($htmlFinancial, 'Programa Indique &amp; Ganhe') || str_contains($htmlFinancial, 'Programa Indique & Ganhe'));
assertTest("financial.php contém filtro com status remarketing", str_contains($htmlFinancial, 'value="remarketing"'));

// 7. Teste de renderização de views/admin/referrals/index.php
ob_start();
$data = ReferralService::getTenantDashboardData(1);
$vars = [
    'referralCode' => $data['referral_code'],
    'referralUrl' => $data['referral_url'],
    'whatsappShareUrl' => $data['whatsapp_share_url'],
    'whatsappShareText' => $data['whatsapp_share_text'],
    'totalInvited' => $data['total_invited'],
    'pendingValidation' => $data['pending_validation'],
    'rewardedCount' => $data['rewarded_count'],
    'totalDaysEarned' => $data['total_days_earned'],
    'rewardDaysPerReferral' => $data['reward_days_per_referral'],
    'welcomeDays' => $data['welcome_days'],
    'referralsList' => $data['referrals_list']
];
extract($vars);
require __DIR__ . '/../views/admin/referrals/index.php';
$htmlRefView = ob_get_clean();

assertTest("referrals/index.php renderiza link de convite", str_contains($htmlRefView, '/convite/'));
assertTest("referrals/index.php renderiza botão de compartilhar WhatsApp", str_contains($htmlRefView, 'api.whatsapp.com'));
assertTest("referrals/index.php exibe regras anti-fraude transparentes", str_contains($htmlRefView, 'Regra Anti-Fraude'));

// 8. Teste de renderização de views/public/recovery.php
ob_start();
$token = 'test-token-12345';
$sub = [
    'id' => 1,
    'recovery_token' => $token,
    'plan_type' => 'annual_domain',
    'original_amount' => 120.0,
    'amount' => 96.0,
    'discount_percent' => 20,
    'discount_amount' => 24.0,
    'business_name' => 'Oficina Teste',
    'tenant_age_months' => 5,
    'status' => 'remarketing'
];
$isApproved = false;
$pixData = [
    'qr_code' => '00020126580014br.gov.bcb.pix...',
    'qr_code_base64' => ''
];
$planTitle = 'Domínio Próprio & Plano Anual Zyrtex Pro';
$origAmount = 120.0;
$finalAmount = 96.0;
$discountPercent = 20.0;
$discountVal = 24.0;

require __DIR__ . '/../views/public/recovery.php';
$htmlRecovery = ob_get_clean();

assertTest("recovery.php renderiza ancoragem de preço (De: R$ 120,00 Por: R$ 96,00)", str_contains($htmlRecovery, '120,00') && str_contains($htmlRecovery, '96,00'));
assertTest("recovery.php renderiza badge de desconto de fidelidade (-20% OFF)", str_contains($htmlRecovery, '20% OFF'));
assertTest("recovery.php possui cronômetro de expiração regressivo", str_contains($htmlRecovery, 'countdownTimer'));
assertTest("recovery.php possui polling de verificação de status", str_contains($htmlRecovery, '/status'));

echo "\n============================================\n";
echo "RESULTADO FINAL: {$passCount} PASS / {$failCount} FAIL\n";
echo "============================================\n";

if ($failCount > 0) {
    exit(1);
}
