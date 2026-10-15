<?php
require __DIR__ . '/../src/Core/Env.php';
require __DIR__ . '/../src/Core/Database.php';
require __DIR__ . '/../src/Core/Session.php';
require __DIR__ . '/../src/Core/Tenant.php';
require __DIR__ . '/../src/Core/Request.php';
require __DIR__ . '/../src/Core/Response.php';
require __DIR__ . '/../src/Services/QrCodeService.php';
require __DIR__ . '/../src/Services/ReferralService.php';
require __DIR__ . '/../src/Services/MercadoPagoService.php';
require __DIR__ . '/../src/Controllers/Public/WebhookController.php';
require __DIR__ . '/../src/Controllers/Public/PortalController.php';

\Zyrtex\Core\Env::load(__DIR__ . '/../.env');
\Zyrtex\Core\Session::start();

echo "=== TESTE: FLUXO DE PAGAMENTO PIX MULTI-OS & WEBHOOK ===\n\n";

// 1. Preparar OSs de teste (OS 1 e OS 3 de Mariana Oliveira)
\Zyrtex\Core\Database::execute("UPDATE service_orders SET payment_status = 'pending', payment_method = NULL WHERE id IN (1, 3)");

$osList = \Zyrtex\Core\Database::fetchAll("SELECT id, os_number, total_value, payment_status FROM service_orders WHERE id IN (1, 3)");
echo "1. Estado inicial das OSs:\n";
foreach ($osList as $os) {
    echo "  - OS #{$os['id']} ({$os['os_number']}): R$ {$os['total_value']} -> Status: {$os['payment_status']}\n";
}

// 2. Testar geração de pagamento multi-OS no MercadoPagoService
$tenant = \Zyrtex\Core\Database::fetchOne("SELECT * FROM tenants WHERE id = 1");
$amount = (float)array_sum(array_column($osList, 'total_value'));

$paymentResult1 = \Zyrtex\Services\MercadoPagoService::createTenantOSPayment(
    $tenant,
    $osList,
    $amount,
    "Pagamento agrupado OS 1 e 3",
    "mariana@email.com"
);

echo "\n2. Geração do Pagamento Multi-OS:\n";
echo "  - Payment ID: " . $paymentResult1['payment_id'] . "\n";
echo "  - Pix Code gerado: " . substr($paymentResult1['pix_code'], 0, 45) . "...\n";
echo "  - Sucesso: " . ($paymentResult1['success'] ? 'SIM' : 'NÃO') . "\n";

// 3. Testar Caching / Idempotência (não gerar códigos duplicados para a mesma seleção)
$paymentResult2 = \Zyrtex\Services\MercadoPagoService::createTenantOSPayment(
    $tenant,
    $osList,
    $amount,
    "Pagamento agrupado OS 1 e 3",
    "mariana@email.com"
);

$isSameCode = ($paymentResult1['pix_code'] === $paymentResult2['pix_code']);
$isSameId = ($paymentResult1['payment_id'] === $paymentResult2['payment_id']);
echo "\n3. Teste de Idempotência e Cache de Pagamento Ativo:\n";
echo "  - Reutilizou exatamente o mesmo Payment ID? " . ($isSameId ? 'SIM ✓' : 'NÃO ✗') . "\n";
echo "  - Reutilizou o mesmo Pix Code sem spammar novas ordens? " . ($isSameCode ? 'SIM ✓' : 'NÃO ✗') . "\n";

// 4. Testar o Webhook recebendo external_reference: "os_payment:1,3"
echo "\n4. Simulando Webhook de Pagamento Aprovado pelo Banco (external_reference: os_payment:1,3)...\n";

$webhook = new \Zyrtex\Controllers\Public\WebhookController();
$refReflection = new ReflectionClass($webhook);
$processMethod = $refReflection->getMethod('processApprovedOrder');
$processMethod->setAccessible(true);

$processMethod->invoke($webhook, 'os_payment:1,3', 'sim_mp_99887766', [
    'status' => 'approved',
    'transaction_amount' => $amount,
    'payment_method_id' => 'pix'
]);

// 5. Verificar se AMBAS as OSs deram baixa
$osAfter = \Zyrtex\Core\Database::fetchAll("SELECT id, os_number, total_value, payment_status, payment_method FROM service_orders WHERE id IN (1, 3)");
echo "\n5. Estado das OSs após o Webhook:\n";
$allPaid = true;
foreach ($osAfter as $os) {
    echo "  - OS #{$os['id']} ({$os['os_number']}): Status: {$os['payment_status']} | Método: {$os['payment_method']}\n";
    if ($os['payment_status'] !== 'paid') {
        $allPaid = false;
    }
}

if ($allPaid) {
    echo "\n>>> SUCESSO ABSOLUTO: As duas OSs diferentes deram baixa simultaneamente com 1 único QR Code! <<<\n";
} else {
    echo "\n>>> FALHA: Uma das OSs não foi atualizada! <<<\n";
}

// 6. Verificar logs de auditoria criados
$audits = \Zyrtex\Core\Database::fetchAll("SELECT * FROM audit_logs WHERE action = 'os_payment_approved_mercadopago' ORDER BY id DESC LIMIT 2");
echo "\n6. Logs de Auditoria:\n";
foreach ($audits as $audit) {
    echo "  - Log #{$audit['id']}: " . $audit['details_json'] . "\n";
}
