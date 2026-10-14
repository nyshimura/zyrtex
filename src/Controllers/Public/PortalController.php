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
 * Área exclusiva do cliente final para consulta de histórico de aparelhos,
 * aprovação digital de orçamentos em 1 clique e extrato financeiro com Pix/Mercado Pago.
 */
class PortalController {

    /**
     * Tela Principal do Portal do Cliente (/portal)
     */
    public function index(Request $request): void {
        $tenant = Tenant::current();
        $tenantId = Tenant::id();

        $searchQuery = trim((string)$request->query('q', $request->query('doc', $request->query('os', ''))));
        $cleanSearch = preg_replace('/\D/', '', $searchQuery);

        $client = null;
        $orders = [];
        $selectedOs = null;
        $totalPending = 0.0;
        $totalPaid = 0.0;
        $activeCount = 0;

        if (!empty($searchQuery)) {
            // 1. Tenta buscar por CPF/CNPJ ou Telefone do Cliente
            if (!empty($cleanSearch) && strlen($cleanSearch) >= 8) {
                if ($tenantId) {
                    $client = Database::fetchOne(
                        "SELECT * FROM clients 
                         WHERE tenant_id = :tenant_id 
                           AND (REPLACE(REPLACE(REPLACE(document, '.', ''), '-', ''), '/', '') = :doc 
                                OR REPLACE(REPLACE(REPLACE(REPLACE(phone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE :phone)
                         LIMIT 1",
                        [
                            'tenant_id' => $tenantId,
                            'doc' => $cleanSearch,
                            'phone' => '%' . substr($cleanSearch, -8)
                        ]
                    );
                } else {
                    $client = Database::fetchOne(
                        "SELECT c.*, t.slug as tenant_slug, t.business_name 
                         FROM clients c 
                         JOIN tenants t ON c.tenant_id = t.id
                         WHERE REPLACE(REPLACE(REPLACE(c.document, '.', ''), '-', ''), '/', '') = :doc 
                            OR REPLACE(REPLACE(REPLACE(REPLACE(c.phone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE :phone
                         ORDER BY c.id DESC LIMIT 1",
                        [
                            'doc' => $cleanSearch,
                            'phone' => '%' . substr($cleanSearch, -8)
                        ]
                    );
                }
            }

            // 2. Se não achou por documento ou buscou por código de OS (ex: OS-2026-001)
            if (!$client) {
                $osCond = $tenantId ? "os.tenant_id = :tenant_id AND " : "";
                $params = ['code1' => $searchQuery, 'code2' => $searchQuery];
                if ($tenantId) {
                    $params['tenant_id'] = $tenantId;
                }

                $foundOs = Database::fetchOne(
                    "SELECT os.*, c.id as cid, c.name as client_name, c.document as client_document, c.phone as client_phone
                     FROM service_orders os
                     JOIN clients c ON os.client_id = c.id
                     WHERE {$osCond} (os.os_number = :code1 OR os.identifier_code = :code2)
                     LIMIT 1",
                    $params
                );

                if ($foundOs) {
                    $selectedOs = $foundOs;
                    $client = [
                        'id' => $foundOs['cid'],
                        'tenant_id' => $foundOs['tenant_id'],
                        'name' => $foundOs['client_name'],
                        'document' => $foundOs['client_document'],
                        'phone' => $foundOs['client_phone']
                    ];
                    $tenantId = (int)$foundOs['tenant_id'];
                }
            }

            // 3. Se cliente foi identificado, busca todo o histórico de aparelhos e OSs
            if ($client) {
                $orders = Database::fetchAll(
                    "SELECT os.*, 
                            (SELECT COUNT(*) FROM os_parts WHERE os_id = os.id AND approval_status = 'pending_approval') as pending_parts_count,
                            (SELECT COUNT(*) FROM os_media WHERE os_id = os.id) as media_count
                     FROM service_orders os
                     WHERE os.client_id = :client_id AND os.tenant_id = :tenant_id
                     ORDER BY os.id DESC",
                    [
                        'client_id' => (int)$client['id'],
                        'tenant_id' => (int)$client['tenant_id']
                    ]
                );

                foreach ($orders as $o) {
                    $val = (float)$o['total_value'];
                    if ($o['payment_status'] === 'paid') {
                        $totalPaid += $val;
                    } else {
                        $totalPending += $val;
                    }

                    if (!in_array($o['status'], ['delivered', 'cancelled'], true)) {
                        $activeCount++;
                    }
                }

                // Se tenant não estava resolvido, obtém dados do tenant do cliente
                if (!$tenant || empty($tenant['id'])) {
                    $tenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => (int)$client['tenant_id']]);
                }
            }
        }

        // Gera QR Code Pix para pagamentos se loja tiver Pix configurado
        $pixQrCodeSvg = null;
        if ($tenant && !empty($tenant['pix_key'])) {
            $pixPayload = "Chave: " . ($tenant['pix_key']) . " | Beneficiario: " . ($tenant['pix_beneficiary'] ?? $tenant['business_name']);
            $pixQrCodeSvg = QrCodeService::generateSvg($pixPayload, 160);
        }

        $niches = require __DIR__ . '/../../../config/niches.php';
        $nicheConfig = $niches[$tenant['niche'] ?? 'cellphones'] ?? $niches['cellphones'];

        View::render('public/portal', [
            'tenant' => $tenant,
            'client' => $client,
            'orders' => $orders,
            'selectedOs' => $selectedOs,
            'searchQuery' => $searchQuery,
            'totalPending' => $totalPending,
            'totalPaid' => $totalPaid,
            'activeCount' => $activeCount,
            'pixQrCodeSvg' => $pixQrCodeSvg,
            'nicheConfig' => $nicheConfig,
            'pageTitle' => 'Portal do Cliente &bull; ' . ($tenant['business_name'] ?? 'Assistência Técnica')
        ], 'public');
    }

    /**
     * Aprovação Digital de Orçamento em 1 Clique (RF15)
     * POST /portal/aprovar
     */
    public function approve(Request $request): void {
        $osId = (int)$request->post('os_id', 0);
        $clientDoc = trim((string)$request->post('client_doc', ''));

        if ($osId <= 0) {
            Response::redirect('/portal');
            return;
        }

        $os = Database::fetchOne(
            "SELECT os.*, t.slug as tenant_slug 
             FROM service_orders os
             JOIN tenants t ON os.tenant_id = t.id
             WHERE os.id = :id LIMIT 1",
            ['id' => $osId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/portal');
            return;
        }

        $tenantId = (int)$os['tenant_id'];
        $tenantSlug = $os['tenant_slug'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = date('Y-m-d H:i:s');
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            Database::beginTransaction();

            // Atualiza status da OS com blindagem e registro de IP
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

            // Grava Auditoria Oficial Irrevogável (RF15)
            Audit::log($tenantId, null, 'client_digital_approval', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'total_value' => (float)$os['total_value'],
                'ip' => $ip,
                'user_agent' => $ua,
                'approved_at' => $now,
                'channel' => 'Portal do Cliente'
            ]);

            Database::commit();

            Session::setFlash('success', "✓ Orçamento da OS {$os['os_number']} aprovado com sucesso! Nossos técnicos já foram notificados e iniciarão o reparo.");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', 'Falha ao processar aprovação: ' . $e->getMessage());
        }

        $redirectUrl = Tenant::publicUrl($tenantSlug, '/portal' . (!empty($clientDoc) ? '?doc=' . urlencode($clientDoc) : '?os=' . urlencode($os['os_number'])));
        Response::redirect($redirectUrl);
    }
}
