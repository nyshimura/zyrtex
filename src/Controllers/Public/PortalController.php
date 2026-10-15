<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Public;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Tenant;
use Zyrtex\Core\View;
use Zyrtex\Core\Audit;
use Zyrtex\Services\QrCodeService;
use Zyrtex\Services\MercadoPagoService;

/**
 * Portal do Cliente (RF15)
 * Área exclusiva e autenticada do cliente final para consulta de histórico de aparelhos,
 * aprovação digital de orçamentos em 1 clique e pagamento Pix por OS individual ou agrupada.
 */
class PortalController {

    /**
     * Tela Principal do Portal do Cliente (/portal)
     * - Se o cliente está logado: exibe aparelhos, ordens de serviço e extrato financeiro com seleção de OSs para Pix.
     * - Se não está logado e foi passado ?os=...: redireciona para o rastreio público (/rastreio?os=...).
     * - Se não está logado: exibe a tela de login segura (CPF ou E-mail + Senha).
     */
    public function index(Request $request): void {
        $tenant = Tenant::current();
        $tenantId = Tenant::id();

        // Se o usuário não está autenticado e passou código de OS, direciona ao rastreio público (QR Code)
        $osParam = trim((string)$request->query('os', ''));
        if (!empty($osParam) && !Session::has('client_id')) {
            $trackingUrl = Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio?os=' . urlencode($osParam));
            Response::redirect($trackingUrl);
            return;
        }

        $clientId = Session::get('client_id');
        $client = null;
        $orders = [];
        $pendingOrders = [];
        $totalPending = 0.0;
        $totalPaid = 0.0;
        $activeCount = 0;

        $initialSelectedIds = [];
        $initialPixPayload = '';
        $initialQrCodeUrl = '';
        $initialPixTotal = 0.0;
        $initialOsNumbersStr = '';
        $initialWhatsAppUrl = '';

        if ($clientId) {
            $client = Database::fetchOne(
                "SELECT c.*, t.slug as tenant_slug, t.business_name 
                 FROM clients c 
                 JOIN tenants t ON c.tenant_id = t.id 
                 WHERE c.id = :id AND c.status = 'active' 
                 LIMIT 1",
                ['id' => (int)$clientId]
            );

            if (!$client) {
                // Cliente inválido ou inativo, encerra sessão
                Session::remove('client_id');
                Session::remove('client_name');
                Session::remove('client_email');
                Session::remove('client_tenant_id');
                Response::redirect(Tenant::publicUrl($tenant['slug'] ?? null, '/portal'));
                return;
            }

            $clientTenantId = (int)$client['tenant_id'];

            // Se o tenant atual não foi resolvido pela URL, assume o tenant do cliente
            if (!$tenant || empty($tenant['id'])) {
                $tenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => $clientTenantId]);
            }

            // Busca histórico completo de ordens de serviço do cliente
            $orders = Database::fetchAll(
                "SELECT os.*, 
                        (SELECT COUNT(*) FROM os_parts WHERE os_id = os.id AND approval_status = 'pending_approval') as pending_parts_count,
                        (SELECT COUNT(*) FROM os_media WHERE os_id = os.id) as media_count
                 FROM service_orders os
                 WHERE os.client_id = :client_id AND os.tenant_id = :tenant_id
                 ORDER BY os.id DESC",
                [
                    'client_id' => (int)$client['id'],
                    'tenant_id' => $clientTenantId
                ]
            );

            foreach ($orders as $o) {
                $val = (float)$o['total_value'];
                if ($o['payment_status'] === 'paid') {
                    $totalPaid += $val;
                } else {
                    $totalPending += $val;
                    $pendingOrders[] = $o;
                }

                if (!in_array($o['status'], ['delivered', 'cancelled'], true)) {
                    $activeCount++;
                }
            }

            // Seleção inicial de OS para pagamento via Pix
            // Se veio ?pagar_os=ID, foca apenas essa OS; caso contrário, seleciona todas as pendentes
            $targetOsId = (int)$request->query('pagar_os', 0);
            $initialSelected = [];

            if ($targetOsId > 0) {
                foreach ($pendingOrders as $po) {
                    if ((int)$po['id'] === $targetOsId) {
                        $initialSelected[] = $po;
                        break;
                    }
                }
            }

            if (empty($initialSelected) && !empty($pendingOrders)) {
                $initialSelected = $pendingOrders;
            }

            if (!empty($initialSelected)) {
                $initialSelectedIds = array_map(fn($o) => (int)$o['id'], $initialSelected);
                $initialPixTotal = (float)array_sum(array_column($initialSelected, 'total_value'));
                $initialOsNumbers = array_column($initialSelected, 'os_number');
                $initialOsNumbersStr = implode(', ', $initialOsNumbers);

                $pixKey = trim((string)($tenant['pix_key'] ?? ''));
                $beneficiary = trim((string)($tenant['pix_beneficiary'] ?? ($tenant['business_name'] ?? 'Assistência Técnica')));
                $city = 'BRASIL';
                $ref = count($initialSelected) === 1 ? (string)$initialSelected[0]['os_number'] : 'ZYRTEX';

                if (!empty($pixKey)) {
                    $initialPixPayload = self::buildPixPayload($pixKey, $beneficiary, $city, $initialPixTotal, $ref);
                    $initialQrCodeUrl = QrCodeService::url($initialPixPayload, 200);

                    $cleanPhone = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? ($tenant['phone'] ?? ''));
                    $waMsg = urlencode("Olá! Acabei de realizar o pagamento Pix no valor de R$ " . number_format($initialPixTotal, 2, ',', '.') . " referente às OS ({$initialOsNumbersStr}) na {$tenant['business_name']}. Segue o comprovante.");
                    $initialWhatsAppUrl = !empty($cleanPhone) ? "https://wa.me/55{$cleanPhone}?text={$waMsg}" : '';
                }
            }
        }

        $niches = require __DIR__ . '/../../../config/niches.php';
        $nicheConfig = $niches[$tenant['niche'] ?? 'cellphones'] ?? $niches['cellphones'];

        View::render('public/portal', [
            'tenant' => $tenant,
            'client' => $client,
            'orders' => $orders,
            'pendingOrders' => $pendingOrders,
            'initialSelectedIds' => $initialSelectedIds,
            'initialPixTotal' => $initialPixTotal,
            'initialOsNumbersStr' => $initialOsNumbersStr,
            'initialPixPayload' => $initialPixPayload,
            'initialQrCodeUrl' => $initialQrCodeUrl,
            'initialWhatsAppUrl' => $initialWhatsAppUrl,
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
            'activeCount' => $activeCount,
            'nicheConfig' => $nicheConfig,
            'pageTitle' => $client 
                ? 'Portal do Cliente &bull; ' . ($tenant['business_name'] ?? 'Assistência Técnica')
                : 'Login &bull; Portal do Cliente'
        ], 'public');
    }

    /**
     * Gera Cobrança Pix Dinâmica para as Ordens de Serviço Selecionadas (POST /portal/gerar-pix)
     */
    public function generatePix(Request $request): void {
        $clientId = Session::get('client_id');
        if (!$clientId) {
            Response::json(['success' => false, 'message' => 'Sessão expirada. Faça login novamente.'], 401);
            return;
        }

        $tenant = Tenant::current();

        // Recebe os IDs das OSs selecionadas (array ou string com IDs separados por vírgula)
        $rawIds = $request->post('os_ids');
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }
        $ids = array_filter(array_map('intval', (array)$rawIds), fn($id) => $id > 0);

        if (empty($ids)) {
            Response::json(['success' => false, 'message' => 'Nenhuma ordem de serviço foi selecionada para pagamento.'], 400);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([(int)$clientId], $ids);

        $selectedOrders = Database::fetchAll(
            "SELECT id, os_number, equipment_info, total_value, payment_status, tenant_id 
             FROM service_orders 
             WHERE client_id = ? AND id IN ({$placeholders}) AND payment_status != 'paid' 
             ORDER BY id ASC",
            $params
        );

        if (empty($selectedOrders)) {
            Response::json(['success' => false, 'message' => 'As ordens de serviço selecionadas já foram pagas ou não foram encontradas.'], 404);
            return;
        }

        $firstTenantId = (int)$selectedOrders[0]['tenant_id'];
        if (!$tenant || empty($tenant['id'])) {
            $tenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => $firstTenantId]);
        }

        $totalAmount = (float)array_sum(array_column($selectedOrders, 'total_value'));
        $osNumbers = array_column($selectedOrders, 'os_number');
        $osNumbersStr = implode(', ', $osNumbers);

        $paymentMode = $tenant['payment_mode'] ?? 'manual_pix';
        $mpAccessToken = trim((string)($tenant['mp_access_token'] ?? ''));

        // 1. Se oficina usa Mercado Pago Oficial (Geração automática com idempotência)
        if ($paymentMode === 'mercadopago' && !empty($mpAccessToken)) {
            $client = Database::fetchOne("SELECT * FROM clients WHERE id = :id LIMIT 1", ['id' => (int)$clientId]);
            $description = "Pagamento OS (" . substr($osNumbersStr, 0, 70) . ") - " . ($tenant['business_name'] ?? 'Oficina');
            $webhookUrl = Tenant::publicUrl(null, '/webhook/mercadopago');

            $mpResult = MercadoPagoService::createTenantOSPayment(
                $tenant,
                $selectedOrders,
                $totalAmount,
                $description,
                $client['email'] ?? null,
                $webhookUrl
            );

            if (!empty($mpResult['success']) && !empty($mpResult['pix_code'])) {
                $cleanPhone = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? ($tenant['phone'] ?? ''));
                $waMsg = urlencode("Olá! Acabei de realizar o pagamento Pix no valor de R$ " . number_format($totalAmount, 2, ',', '.') . " referente às OS ({$osNumbersStr}) na {$tenant['business_name']}. Segue o comprovante.");
                $waUrl = !empty($cleanPhone) ? "https://wa.me/55{$cleanPhone}?text={$waMsg}" : '';

                Response::json([
                    'success' => true,
                    'gateway' => 'mercadopago',
                    'payment_id' => $mpResult['payment_id'] ?? null,
                    'total' => $totalAmount,
                    'total_formatted' => 'R$ ' . number_format($totalAmount, 2, ',', '.'),
                    'os_ids' => array_column($selectedOrders, 'id'),
                    'os_numbers' => $osNumbers,
                    'os_numbers_str' => $osNumbersStr,
                    'count' => count($selectedOrders),
                    'pix_code' => $mpResult['pix_code'],
                    'qrcode_url' => $mpResult['qrcode_url'],
                    'whatsapp_url' => $waUrl
                ]);
                return;
            }
        }

        // 2. Modo Chave Pix Manual do Lojista
        $pixKey = trim((string)($tenant['pix_key'] ?? ''));
        $beneficiary = trim((string)($tenant['pix_beneficiary'] ?? ($tenant['business_name'] ?? 'Assistência Técnica')));
        $city = 'BRASIL';
        $ref = count($selectedOrders) === 1 ? (string)$selectedOrders[0]['os_number'] : 'ZYRTEX';

        if (empty($pixKey)) {
            Response::json(['success' => false, 'message' => 'A oficina ainda não cadastrou uma Chave Pix para recebimento.'], 422);
            return;
        }

        $pixPayload = self::buildPixPayload($pixKey, $beneficiary, $city, $totalAmount, $ref);
        $qrcodeUrl = QrCodeService::url($pixPayload, 200);

        $cleanPhone = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? ($tenant['phone'] ?? ''));
        $waMsg = urlencode("Olá! Acabei de realizar o pagamento Pix no valor de R$ " . number_format($totalAmount, 2, ',', '.') . " referente às OS ({$osNumbersStr}) na {$tenant['business_name']}. Segue o comprovante.");
        $waUrl = !empty($cleanPhone) ? "https://wa.me/55{$cleanPhone}?text={$waMsg}" : '';

        Response::json([
            'success' => true,
            'gateway' => 'manual_pix',
            'total' => $totalAmount,
            'total_formatted' => 'R$ ' . number_format($totalAmount, 2, ',', '.'),
            'os_ids' => array_column($selectedOrders, 'id'),
            'os_numbers' => $osNumbers,
            'os_numbers_str' => $osNumbersStr,
            'count' => count($selectedOrders),
            'pix_key' => $pixKey,
            'pix_key_type' => $tenant['pix_key_type'] ?? 'Chave',
            'beneficiary' => $beneficiary,
            'pix_code' => $pixPayload,
            'qrcode_url' => $qrcodeUrl,
            'whatsapp_url' => $waUrl
        ]);
    }

    /**
     * Verifica em tempo real o status de pagamento das OSs selecionadas (GET /portal/status-pagamento?os_ids=1,3)
     */
    public function checkPaymentStatus(Request $request): void {
        $clientId = Session::get('client_id');
        if (!$clientId) {
            Response::json(['success' => false, 'message' => 'Não autenticado'], 401);
            return;
        }

        $rawIds = $request->get('os_ids');
        if (is_string($rawIds)) {
            $rawIds = explode(',', $rawIds);
        }
        $ids = array_filter(array_map('intval', (array)$rawIds), fn($id) => $id > 0);

        if (empty($ids)) {
            Response::json(['success' => false, 'message' => 'Nenhuma OS informada'], 400);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([(int)$clientId], $ids);

        $orders = Database::fetchAll(
            "SELECT id, os_number, payment_status, status FROM service_orders WHERE client_id = ? AND id IN ({$placeholders})",
            $params
        );

        $total = count($ids);
        $paidCount = 0;
        foreach ($orders as $o) {
            if ($o['payment_status'] === 'paid') {
                $paidCount++;
            }
        }

        Response::json([
            'success' => true,
            'all_paid' => ($total > 0 && $paidCount === $total),
            'paid_count' => $paidCount,
            'total_count' => $total,
            'orders' => $orders
        ]);
    }

    /**
     * Gera string no formato padrão EMV BR Code / Pix Copia e Cola com cálculo de CRC16
     */
    public static function buildPixPayload(string $key, string $beneficiary, string $city, float $amount, string $txid = '***'): string {
        $formatTLV = function(string $id, string $val): string {
            $len = str_pad((string)strlen($val), 2, '0', STR_PAD_LEFT);
            return $id . $len . $val;
        };

        // 26 Merchant Account Info
        $gui = $formatTLV('00', 'br.gov.bcb.pix');
        $chave = $formatTLV('01', $key);
        $merchantInfo = $formatTLV('26', $gui . $chave);

        // Amount
        $amountStr = number_format($amount, 2, '.', '');
        $amountTLV = $formatTLV('54', $amountStr);

        // Reference / TxId
        $cleanTxid = substr(preg_replace('/[^a-zA-Z0-9]/', '', $txid), 0, 25) ?: '***';
        $refTLV = $formatTLV('62', $formatTLV('05', $cleanTxid));

        $cleanBeneficiary = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $beneficiary), 0, 25) ?: 'OFICINA';
        $cleanCity = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $city), 0, 15) ?: 'BRASIL';

        $payload = $formatTLV('00', '01')
                 . $merchantInfo
                 . $formatTLV('52', '0000')
                 . $formatTLV('53', '986')
                 . $amountTLV
                 . $formatTLV('58', 'BR')
                 . $formatTLV('59', $cleanBeneficiary)
                 . $formatTLV('60', $cleanCity)
                 . $refTLV
                 . '6304';

        // CRC16-CCITT
        $polynomial = 0x1021;
        $result = 0xFFFF;
        for ($i = 0; $i < strlen($payload); $i++) {
            $result ^= (ord($payload[$i]) << 8);
            for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                if (($result <<= 1) & 0x10000) {
                    $result ^= $polynomial;
                }
                $result &= 0xFFFF;
            }
        }
        $crc = strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));

        return $payload . $crc;
    }

    /**
     * Autenticação do Cliente no Portal (POST /portal/login)
     * Suporta Login com CPF (com ou sem formatação) ou E-mail + Senha
     */
    public function login(Request $request): void {
        $loginInput = trim((string)$request->post('login', ''));
        $password = (string)$request->post('password', '');
        $tenant = Tenant::current();
        $tenantId = Tenant::id();
        $tenantSlug = $tenant['slug'] ?? null;

        if (empty($loginInput) || empty($password)) {
            Session::setFlash('error', 'Por favor, informe seu CPF ou E-mail e sua senha de acesso.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        $cleanDigits = preg_replace('/\D/', '', $loginInput);
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL) !== false;

        $client = null;
        if ($tenantId) {
            $client = Database::fetchOne(
                "SELECT c.*, t.slug as tenant_slug, t.business_name 
                 FROM clients c
                 JOIN tenants t ON c.tenant_id = t.id
                 WHERE c.tenant_id = :tenant_id 
                   AND (
                       (:is_email = 1 AND LOWER(c.email) = :login_lower)
                       OR (:has_digits = 1 AND REPLACE(REPLACE(REPLACE(REPLACE(c.document, '.', ''), '-', ''), '/', ''), ' ', '') = :clean_doc)
                   )
                 LIMIT 1",
                [
                    'tenant_id' => $tenantId,
                    'is_email' => $isEmail ? 1 : 0,
                    'login_lower' => strtolower($loginInput),
                    'has_digits' => !empty($cleanDigits) ? 1 : 0,
                    'clean_doc' => $cleanDigits
                ]
            );
        } else {
            $client = Database::fetchOne(
                "SELECT c.*, t.slug as tenant_slug, t.business_name 
                 FROM clients c
                 JOIN tenants t ON c.tenant_id = t.id
                 WHERE (
                       (:is_email = 1 AND LOWER(c.email) = :login_lower)
                       OR (:has_digits = 1 AND REPLACE(REPLACE(REPLACE(REPLACE(c.document, '.', ''), '-', ''), '/', ''), ' ', '') = :clean_doc)
                   )
                 ORDER BY c.id DESC LIMIT 1",
                [
                    'is_email' => $isEmail ? 1 : 0,
                    'login_lower' => strtolower($loginInput),
                    'has_digits' => !empty($cleanDigits) ? 1 : 0,
                    'clean_doc' => $cleanDigits
                ]
            );
        }

        if (!$client) {
            Session::setFlash('error', 'Cadastro não localizado. Verifique se o CPF ou E-mail informado está correto.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        if ($client['status'] === 'pending_activation') {
            Session::setFlash('warning', 'Sua conta ainda não foi ativada. Verifique o link enviado ao seu e-mail para cadastrar sua senha.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        if (empty($client['password_hash']) || !password_verify($password, $client['password_hash'])) {
            Session::setFlash('error', 'Senha incorreta. Por favor, tente novamente ou solicite redefinição.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        // Login validado com sucesso!
        Session::set('client_id', (int)$client['id']);
        Session::set('client_name', $client['name']);
        Session::set('client_email', $client['email']);
        Session::set('client_tenant_id', (int)$client['tenant_id']);
        Session::set('current_tenant_slug', $client['tenant_slug']);

        Audit::log((int)$client['tenant_id'], null, 'client_portal_login', [
            'client_id' => $client['id'],
            'client_name' => $client['name'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        Session::setFlash('success', "Olá, {$client['name']}! Bem-vinda ao seu Portal.");
        Response::redirect(Tenant::publicUrl($client['tenant_slug'] ?? $tenantSlug, '/portal'));
    }

    /**
     * Encerra a Sessão do Cliente (GET/POST /portal/sair)
     */
    public function logout(Request $request): void {
        $tenantSlug = Tenant::currentSlug();
        Session::remove('client_id');
        Session::remove('client_name');
        Session::remove('client_email');
        Session::remove('client_tenant_id');

        Session::setFlash('info', 'Você saiu da sua conta do Portal do Cliente.');
        Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
    }

    /**
     * Aprovação Digital de Orçamento em 1 Clique (RF15)
     * POST /portal/aprovar
     */
    public function approve(Request $request): void {
        $clientId = Session::get('client_id');
        $tenantSlug = Tenant::currentSlug();

        if (!$clientId) {
            Session::setFlash('error', 'Faça login no portal para aprovar o orçamento.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        $osId = (int)$request->post('os_id', 0);
        if ($osId <= 0) {
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        $os = Database::fetchOne(
            "SELECT os.*, t.slug as tenant_slug 
             FROM service_orders os
             JOIN tenants t ON os.tenant_id = t.id
             WHERE os.id = :id AND os.client_id = :client_id LIMIT 1",
            ['id' => $osId, 'client_id' => $clientId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada ou não pertence a este cadastro.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
            return;
        }

        $tenantId = (int)$os['tenant_id'];
        $tenantSlug = $os['tenant_slug'] ?? $tenantSlug;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = date('Y-m-d H:i:s');
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            Database::beginTransaction();

            Database::execute(
                "UPDATE service_orders 
                 SET status = 'approved',
                     approval_status = 'approved',
                     approval_ip = :ip,
                     approval_date = :app_date,
                     approval_user_agent = :ua
                 WHERE id = :id AND tenant_id = :tenant_id",
                [
                    'ip' => $ip,
                    'app_date' => $now,
                    'ua' => $ua,
                    'id' => $osId,
                    'tenant_id' => $tenantId
                ]
            );

            // Aprova também eventuais peças adicionais pendentes
            Database::execute(
                "UPDATE os_parts 
                 SET approval_status = 'approved' 
                 WHERE os_id = :os_id AND tenant_id = :tenant_id AND approval_status = 'pending_approval'",
                ['os_id' => $osId, 'tenant_id' => $tenantId]
            );

            // Baixa automática no estoque das peças aprovadas (RN05)
            $parts = Database::fetchAll(
                "SELECT product_id, quantity FROM os_parts 
                 WHERE os_id = :os_id AND tenant_id = :tenant_id AND product_id IS NOT NULL AND approval_status = 'approved'",
                ['os_id' => $osId, 'tenant_id' => $tenantId]
            );
            foreach ($parts as $pt) {
                Database::execute(
                    "UPDATE products_pos 
                     SET stock = stock - :qty 
                     WHERE id = :id AND tenant_id = :tenant_id AND is_service = 0",
                    ['qty' => (int)$pt['quantity'], 'id' => (int)$pt['product_id'], 'tenant_id' => $tenantId]
                );
            }

            Audit::log($tenantId, null, 'client_digital_approval', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'total_value' => (float)$os['total_value'],
                'client_id' => $clientId,
                'ip' => $ip,
                'user_agent' => $ua,
                'approved_at' => $now,
                'channel' => 'Portal do Cliente Autenticado'
            ]);

            Database::commit();

            Session::setFlash('success', "✓ Orçamento da OS {$os['os_number']} aprovado com sucesso! Nossos técnicos já foram notificados e iniciarão o reparo.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', 'Falha ao processar aprovação: ' . $e->getMessage());
        }

        Response::redirect(Tenant::publicUrl($tenantSlug, '/portal'));
    }

    /**
     * Tela de Ativação de Conta do Cliente (/portal/ativar?token=...)
     */
    public function showActivate(Request $request): void {
        $token = trim((string)$request->query('token', ''));

        if (empty($token)) {
            View::render('public/portal/activate', [
                'status' => 'missing_token',
                'message' => 'O link de ativação está incompleto ou o token não foi fornecido. Por favor, verifique a mensagem recebida no seu e-mail.',
                'pageTitle' => 'Ativação de Conta &bull; Zyrtex'
            ], 'public');
            return;
        }

        $tokenHash = hash('sha256', $token);
        $client = Database::fetchOne(
            "SELECT c.*, t.business_name, t.slug as tenant_slug, t.phone as shop_phone 
             FROM clients c 
             JOIN tenants t ON c.tenant_id = t.id 
             WHERE c.activation_token_hash = :hash 
             LIMIT 1",
            ['hash' => $tokenHash]
        );

        if (!$client) {
            View::render('public/portal/activate', [
                'status' => 'invalid_token',
                'message' => 'Link de ativação inválido ou a conta já foi previamente ativada. Você já pode acessar o Portal do Cliente com seu CPF ou E-mail.',
                'pageTitle' => 'Link Inválido &bull; Zyrtex'
            ], 'public');
            return;
        }

        // Verifica expiração (7 dias)
        if (!empty($client['activation_expires_at']) && strtotime($client['activation_expires_at']) < time()) {
            View::render('public/portal/activate', [
                'status' => 'expired_token',
                'client' => $client,
                'message' => 'Este link de ativação expirou por motivos de segurança. Entre em contato com a loja para solicitar um novo link.',
                'pageTitle' => 'Link Expirado &bull; Zyrtex'
            ], 'public');
            return;
        }

        View::render('public/portal/activate', [
            'status' => 'ready',
            'client' => $client,
            'token' => $token,
            'pageTitle' => 'Ativar Minha Conta &bull; ' . $client['business_name']
        ], 'public');
    }

    /**
     * Processa a Confirmação de Ativação do Cliente (POST /portal/ativar)
     */
    public function processActivate(Request $request): void {
        $token = trim((string)$request->post('token', ''));
        $password = (string)$request->post('password', '');
        $passwordConfirm = (string)$request->post('password_confirmation', '');

        if (empty($token)) {
            Session::setFlash('error', 'Token de ativação ausente.');
            Response::redirect('/portal');
            return;
        }

        $tokenHash = hash('sha256', $token);
        $client = Database::fetchOne(
            "SELECT c.*, t.business_name, t.slug as tenant_slug 
             FROM clients c 
             JOIN tenants t ON c.tenant_id = t.id 
             WHERE c.activation_token_hash = :hash 
             LIMIT 1",
            ['hash' => $tokenHash]
        );

        if (!$client) {
            Session::setFlash('error', 'Este link de ativação já foi utilizado ou é inválido.');
            Response::redirect('/portal');
            return;
        }

        $passHash = null;
        if (!empty($password)) {
            if (strlen($password) < 6) {
                Session::setFlash('error', 'A senha escolhida deve conter pelo menos 6 caracteres.');
                Response::redirect('/portal/ativar?token=' . urlencode($token));
                return;
            }
            if ($password !== $passwordConfirm) {
                Session::setFlash('error', 'A confirmação de senha não confere com a senha digitada.');
                Response::redirect('/portal/ativar?token=' . urlencode($token));
                return;
            }
            $passHash = password_hash($password, PASSWORD_BCRYPT);
        }

        Database::execute(
            "UPDATE clients 
             SET status = 'active', 
                 activated_at = NOW(), 
                 activation_token_hash = NULL, 
                 activation_expires_at = NULL, 
                 password_hash = COALESCE(:pass, password_hash) 
             WHERE id = :id",
            [
                'pass' => $passHash,
                'id' => $client['id']
            ]
        );

        Audit::log((int)$client['tenant_id'], null, 'client_portal_account_activated', [
            'client_id' => $client['id'],
            'client_name' => $client['name'],
            'client_email' => $client['email'],
            'has_custom_password' => !empty($passHash),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        // Autentica automaticamente o cliente após ativar
        Session::set('client_id', (int)$client['id']);
        Session::set('client_name', $client['name']);
        Session::set('client_email', $client['email']);
        Session::set('client_tenant_id', (int)$client['tenant_id']);
        Session::set('current_tenant_slug', $client['tenant_slug']);

        Session::setFlash('success', "✓ Parabéns, {$client['name']}! Sua conta na {$client['business_name']} foi ativada com sucesso. Você já está logada no Portal do Cliente.");
        Response::redirect(Tenant::publicUrl($client['tenant_slug'] ?? null, '/portal'));
    }
}
