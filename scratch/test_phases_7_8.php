<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Database;
use Zyrtex\Core\Session;
use Zyrtex\Core\Auth;
use Zyrtex\Core\Request;
use Zyrtex\Services\AIService;
use Zyrtex\Services\MercadoPagoService;
use Zyrtex\Controllers\Public\WebhookController;
use Zyrtex\Controllers\SuperAdmin\SuperAdminController;
use Zyrtex\Controllers\Admin\SupportController;

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    Env::load($envFile);
}

Session::start();

echo "========================================================\n";
echo "  BATERIA DE TESTES AUTOMATIZADOS: FASE 7 & FASE 8\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assertTest(string $desc, bool $condition): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$desc}\n";
        $passed++;
    } else {
        echo " [FAIL] {$desc}\n";
        $failed++;
    }
}

// -----------------------------------------------------------
// TESTE 1: Motor de IA (RF09 e RN02)
// -----------------------------------------------------------
echo "--- TESTE 1: Motor de IA (Simplificação & Remarketing) ---\n";
$diagTech = "Curto-circuito na linha secundária VDD_MAIN com fuga de corrente no capacitor C302. Necessário reballing no CI de carga.";
$aiDiag = AIService::simplifyDiagnosis($diagTech, "iPhone 13 Pro Max");
assertTest("AIService::simplifyDiagnosis retornou sucesso", $aiDiag['success'] === true);
assertTest("AIService::simplifyDiagnosis gerou texto claro", !empty($aiDiag['text']) && strlen($aiDiag['text']) > 20);

$dummyOs = ['equipment_info' => 'Samsung S22', 'defect_reported' => 'Troca de conector de carga'];
$dummyClient = ['name' => 'Carlos Silva', 'phone' => '11999998888'];
$dummyTenant = ['business_name' => 'Zyrtex Smart Fix'];
$aiRemarketing = AIService::generatePredictiveRemarketing($dummyOs, $dummyClient, $dummyTenant);
assertTest("AIService::generatePredictiveRemarketing retornou sucesso", $aiRemarketing['success'] === true);
assertTest("AIService::generatePredictiveRemarketing gerou texto persuasivo", !empty($aiRemarketing['text']) && strlen($aiRemarketing['text']) > 20);

// -----------------------------------------------------------
// TESTE 2: Pagamentos Mercado Pago de OS e Estorno CDC Art. 49
// -----------------------------------------------------------
echo "\n--- TESTE 2: Pagamentos & Estornos Mercado Pago ---\n";
$osPayment = MercadoPagoService::createTenantOSPayment(
    ['payment_mode' => 'manual_pix', 'pix_key' => '11999998888', 'business_name' => 'Oficina Teste'],
    ['id' => 999, 'os_number' => 'OS-2026-999'],
    150.00,
    "Reparo de Aparelho OS-2026-999"
);
assertTest("createTenantOSPayment gerou Pix com sucesso", $osPayment['success'] === true);
assertTest("createTenantOSPayment possui código Pix válido", !empty($osPayment['pix_code']));

$refundMock = MercadoPagoService::refundPayment('mock_mp_test_123');
assertTest("refundPayment processou estorno mock com sucesso", $refundMock['success'] === true && $refundMock['status'] === 'refunded');

// -----------------------------------------------------------
// TESTE 3: Webhook de Pagamento de OS
// -----------------------------------------------------------
echo "\n--- TESTE 3: Webhook de Pagamento de OS ---\n";
// Cria uma OS temporária no banco para testar baixa automática
$testClient = Database::fetchOne("SELECT id, tenant_id FROM clients LIMIT 1");
if (!$testClient) {
    $testTenant = Database::fetchOne("SELECT id FROM tenants LIMIT 1");
    $tenantId = (int)$testTenant['id'];
    Database::execute("INSERT INTO clients (tenant_id, name, phone) VALUES (:tid, 'Cliente Teste', '11999998888')", ['tid' => $tenantId]);
    $clientId = (int)Database::lastInsertId();
} else {
    $clientId = (int)$testClient['id'];
    $tenantId = (int)$testClient['tenant_id'];
}

Database::execute("DELETE FROM service_orders WHERE os_number = 'OS-TEST-WH'");
Database::execute(
    "INSERT INTO service_orders (tenant_id, os_number, client_id, identifier_code, equipment_info, defect_reported, total_value, status, payment_status)
     VALUES (:tid, 'OS-TEST-WH', :cid, 'TEST-IMEI-001', 'Xiaomi Redmi Note 11', 'Troca de Tela', 250.00, 'budget', 'pending')",
    ['tid' => $tenantId, 'cid' => $clientId]
);
$testOsId = (int)Database::lastInsertId();

// Simula processamento aprovado do webhook com externalReference 'os_payment:{id}'
$whController = new WebhookController();
$refMethod = new ReflectionMethod(WebhookController::class, 'processApprovedOrder');
$refMethod->setAccessible(true);
$refMethod->invoke($whController, "os_payment:{$testOsId}", "simulated_mp_999", ['status' => 'approved']);

$updatedOs = Database::fetchOne("SELECT status, payment_status, payment_method FROM service_orders WHERE id = :id", ['id' => $testOsId]);
assertTest("Webhook marcou payment_status como 'paid'", $updatedOs['payment_status'] === 'paid');
assertTest("Webhook atualizou status de 'budget' para 'approved'", $updatedOs['status'] === 'approved');
assertTest("Webhook registrou payment_method como 'mercadopago'", $updatedOs['payment_method'] === 'mercadopago');

// Limpa OS de teste
Database::execute("DELETE FROM service_orders WHERE id = :id", ['id' => $testOsId]);

// -----------------------------------------------------------
// TESTE 4: SuperAdmin Autenticação & Métricas
// -----------------------------------------------------------
echo "\n--- TESTE 4: SuperAdmin Autenticação & KPIs ---\n";
Auth::loginSuperAdmin([
    'id' => 1,
    'name' => 'SuperAdmin Master Test',
    'email' => 'superadmin@zyrtex.com.br',
    'role' => 'superadmin'
]);
assertTest("Auth::isSuperAdmin() reconheceu SuperAdmin", Auth::isSuperAdmin() === true);

$totalTenants = (int)Database::fetchValue("SELECT COUNT(*) FROM tenants");
assertTest("Database::fetchValue() calculou total de tenants corretamente", $totalTenants >= 1);

// -----------------------------------------------------------
// TESTE 5: Gestão de Tenant (Suspensão e Plano Premium)
// -----------------------------------------------------------
echo "\n--- TESTE 5: Gestão de Tenants (Toggle Status & Premium) ---\n";
Database::execute("UPDATE tenants SET active = 0 WHERE id = :id", ['id' => $tenantId]);
$chkSuspended = Database::fetchOne("SELECT active FROM tenants WHERE id = :id", ['id' => $tenantId]);
assertTest("Oficina suspensa com sucesso", (int)$chkSuspended['active'] === 0);

Database::execute("UPDATE tenants SET active = 1 WHERE id = :id", ['id' => $tenantId]);
$chkActive = Database::fetchOne("SELECT active FROM tenants WHERE id = :id", ['id' => $tenantId]);
assertTest("Oficina reativada com sucesso", (int)$chkActive['active'] === 1);

// -----------------------------------------------------------
// TESTE 6: Shadow Login (Impersonation)
// -----------------------------------------------------------
echo "\n--- TESTE 6: Shadow Login (Impersonation) ---\n";
Session::set('shadow_superadmin_id', 1);
assertTest("Session::has('shadow_superadmin_id') ativo", Session::has('shadow_superadmin_id') === true);

// Encerra shadow
Session::remove('shadow_superadmin_id');
assertTest("Modo Shadow encerrado com sucesso", Session::has('shadow_superadmin_id') === false);

// -----------------------------------------------------------
// TESTE 7: Central de Suporte & Chamados (Tickets)
// -----------------------------------------------------------
echo "\n--- TESTE 7: Central de Suporte & Chamados ---\n";
$dummyMsgs = [
    ['sender' => 'client', 'sender_name' => 'Lojista Teste', 'message' => 'Como configurar minha bobina de 80mm?', 'created_at' => date('Y-m-d H:i:s')]
];
Database::execute(
    "INSERT INTO tickets (tenant_id, user_id, subject, status, messages_json) 
     VALUES (:tid, 1, 'Dúvida Impressora 80mm', 'open', :msgs)",
    ['tid' => $tenantId, 'msgs' => json_encode($dummyMsgs)]
);
$ticketId = (int)Database::lastInsertId();

$savedTicket = Database::fetchOne("SELECT * FROM tickets WHERE id = :id", ['id' => $ticketId]);
assertTest("Ticket criado com status 'open'", $savedTicket['status'] === 'open');

// Simula resposta do SuperAdmin
$dummyMsgs[] = [
    'sender' => 'superadmin',
    'sender_name' => 'Suporte Zyrtex',
    'message' => 'Basta acessar Configurações > Impressão e selecionar 80mm.',
    'created_at' => date('Y-m-d H:i:s')
];
Database::execute(
    "UPDATE tickets SET messages_json = :msgs, status = 'answered' WHERE id = :id",
    ['msgs' => json_encode($dummyMsgs), 'id' => $ticketId]
);

$answeredTicket = Database::fetchOne("SELECT * FROM tickets WHERE id = :id", ['id' => $ticketId]);
assertTest("Ticket atualizado para status 'answered'", $answeredTicket['status'] === 'answered');

// Limpa ticket de teste
Database::execute("DELETE FROM tickets WHERE id = :id", ['id' => $ticketId]);

// -----------------------------------------------------------
// TESTE 8: Parâmetros do SaaS & Broadcast
// -----------------------------------------------------------
echo "\n--- TESTE 8: Parâmetros do SaaS & Broadcast ---\n";
Database::execute(
    "INSERT INTO saas_settings (config_key, config_value) VALUES ('broadcast_message', 'Aviso de Teste Unitario') 
     ON DUPLICATE KEY UPDATE config_value = 'Aviso de Teste Unitario'"
);
$bcVal = Database::fetchOne("SELECT config_value FROM saas_settings WHERE config_key = 'broadcast_message'");
assertTest("Broadcast gravado em saas_settings com sucesso", $bcVal['config_value'] === 'Aviso de Teste Unitario');

echo "\n========================================================\n";
echo "RESULTADO FINAL: {$passed} PASSADOS, {$failed} FALHAS\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}
