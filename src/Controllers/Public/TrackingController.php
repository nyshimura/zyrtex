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

class TrackingController {
    /**
     * Tela de Rastreio Público da OS e Prontuário Digital (/rastreio ou /portal)
     */
    public function index(Request $request): void {
        $tenant = Tenant::current();
        $tenantId = Tenant::id();
        $osCode = trim((string)$request->query('os', $request->query('q', '')));

        $os = null;
        $approvedParts = [];
        $pendingParts = [];
        $rejectedParts = [];

        if (!empty($osCode)) {
            if ($tenantId) {
                $os = Database::fetchOne(
                    "SELECT os.*, c.name as client_name, c.phone as client_phone, c.document as client_document, 
                            t.slug as tenant_slug, t.business_name, t.whatsapp_number, t.address_json, t.theme_color, t.niche
                     FROM service_orders os
                     JOIN clients c ON os.client_id = c.id
                     JOIN tenants t ON os.tenant_id = t.id
                     WHERE os.tenant_id = :tenant_id AND (os.os_number = :code1 OR os.identifier_code = :code2)
                     LIMIT 1",
                    ['tenant_id' => $tenantId, 'code1' => $osCode, 'code2' => $osCode]
                );
            } else {
                $os = Database::fetchOne(
                    "SELECT os.*, c.name as client_name, c.phone as client_phone, c.document as client_document, 
                            t.slug as tenant_slug, t.business_name, t.whatsapp_number, t.address_json, t.theme_color, t.niche
                     FROM service_orders os
                     JOIN clients c ON os.client_id = c.id
                     JOIN tenants t ON os.tenant_id = t.id
                     WHERE os.os_number = :code1 OR os.identifier_code = :code2
                     ORDER BY os.id DESC LIMIT 1",
                    ['code1' => $osCode, 'code2' => $osCode]
                );
            }

            if ($os) {
                // Se tenant não estava resolvido, define o tenant encontrado na OS
                if (!$tenant) {
                    $tenant = [
                        'id' => $os['tenant_id'],
                        'slug' => $os['tenant_slug'] ?? '',
                        'business_name' => $os['business_name'],
                        'whatsapp_number' => $os['whatsapp_number'],
                        'phone' => $os['whatsapp_number'],
                        'address_json' => $os['address_json'],
                        'niche' => $os['niche'] ?? 'cellphones'
                    ];
                }

                // Busca as peças ATIVAS da OS (itens removidos pelo técnico são excluídos do banco e não aparecem para o cliente!)
                $activeParts = Database::fetchAll(
                    "SELECT id, part_name, quantity, unit_price, approval_status, created_at 
                     FROM os_parts 
                     WHERE os_id = :os_id AND tenant_id = :tenant_id 
                     ORDER BY id ASC",
                    ['os_id' => (int)$os['id'], 'tenant_id' => (int)$os['tenant_id']]
                );

                foreach ($activeParts as $p) {
                    if ($p['approval_status'] === 'approved') {
                        $approvedParts[] = $p;
                    } elseif ($p['approval_status'] === 'pending_approval') {
                        $pendingParts[] = $p;
                    } elseif ($p['approval_status'] === 'rejected') {
                        $rejectedParts[] = $p;
                    }
                }
                // Busca fotos e mídias do aparelho registradas na OS (Fase 5)
                $mediaList = Database::fetchAll(
                    "SELECT id, media_type, file_name, file_path, thumbnail_base64, created_at 
                     FROM os_media 
                     WHERE os_id = :os_id AND tenant_id = :tenant_id 
                     ORDER BY id ASC",
                    ['os_id' => (int)$os['id'], 'tenant_id' => (int)$os['tenant_id']]
                );
            }
        }

        $niches = require __DIR__ . '/../../../config/niches.php';
        $nicheConfig = $niches[$tenant['niche'] ?? 'cellphones'] ?? $niches['cellphones'];

        View::render('public/tracking', [
            'tenant' => $tenant,
            'searchedOs' => $osCode,
            'os' => $os,
            'approvedParts' => $approvedParts,
            'pendingParts' => $pendingParts,
            'rejectedParts' => $rejectedParts,
            'mediaList' => $mediaList ?? [],
            'nicheConfig' => $nicheConfig
        ], 'public');
    }

    /**
     * Cliente aprova novos itens adicionais incluídos pela bancada pós-orçamento (POST /rastreio/aprovar-adicional)
     */
    public function approveAddition(Request $request): void {
        $osId = (int)$request->post('os_id', 0);
        $osNumber = trim((string)$request->post('os_number', ''));

        if ($osId <= 0) {
            Response::redirect('/rastreio');
            return;
        }

        $os = Database::fetchOne(
            "SELECT * FROM service_orders WHERE id = :id LIMIT 1",
            ['id' => $osId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de serviço não encontrada.');
            Response::redirect('/rastreio');
            return;
        }

        $tenantId = (int)$os['tenant_id'];

        try {
            Database::beginTransaction();

            // Atualiza todos os itens pendentes para aprovados
            Database::execute(
                "UPDATE os_parts 
                 SET approval_status = 'approved' 
                 WHERE os_id = :os_id AND tenant_id = :tenant_id AND approval_status = 'pending_approval'",
                ['os_id' => $osId, 'tenant_id' => $tenantId]
            );

            // Se a OS já está em andamento, baixa estoque das peças recém-aprovadas
            if (in_array($os['status'], ['approved', 'in_progress'], true)) {
                $pendingParts = Database::fetchAll(
                    "SELECT product_id, quantity FROM os_parts 
                     WHERE os_id = :os_id AND tenant_id = :tenant_id AND product_id IS NOT NULL AND approval_status = 'approved'",
                    ['os_id' => $osId, 'tenant_id' => $tenantId]
                );
                foreach ($pendingParts as $pt) {
                    Database::execute(
                        "UPDATE products_pos 
                         SET stock = stock - :qty 
                         WHERE id = :id AND tenant_id = :tenant_id AND is_service = 0",
                        ['qty' => (int)$pt['quantity'], 'id' => (int)$pt['product_id'], 'tenant_id' => $tenantId]
                    );
                }
            }

            // Recalcula valor total
            $totalParts = (float)Database::fetchColumn(
                "SELECT COALESCE(SUM(quantity * unit_price), 0) FROM os_parts WHERE os_id = :os_id AND tenant_id = :tenant_id AND approval_status != 'rejected'",
                ['os_id' => $osId, 'tenant_id' => $tenantId]
            );

            Database::execute(
                "UPDATE service_orders 
                 SET parts_value = :parts_val, total_value = labor_value + :parts_val 
                 WHERE id = :os_id AND tenant_id = :tenant_id",
                ['parts_val' => $totalParts, 'os_id' => $osId, 'tenant_id' => $tenantId]
            );

            // Grava Auditoria Oficial
            Audit::log($tenantId, null, 'client_approved_additions', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'by' => 'Cliente através do Portal de Rastreio Público',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            Database::commit();

            $tenantSlug = Database::fetchColumn("SELECT slug FROM tenants WHERE id = :id", ['id' => $tenantId]) ?: null;
            Session::setFlash('success', 'Itens adicionais aprovados com sucesso! Nossos técnicos darão andamento ao serviço.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/rastreio?os=' . urlencode($os['os_number'])));

        } catch (\Throwable $e) {
            Database::rollBack();
            $tenantSlug = Database::fetchColumn("SELECT slug FROM tenants WHERE id = :id", ['id' => $tenantId]) ?: null;
            Session::setFlash('error', 'Erro ao aprovar itens: ' . $e->getMessage());
            Response::redirect(Tenant::publicUrl($tenantSlug, '/rastreio?os=' . urlencode($os['os_number'])));
        }
    }

    /**
     * Cliente recusa novos itens adicionais sugeridos (POST /rastreio/rejeitar-adicional)
     */
    public function rejectAddition(Request $request): void {
        $osId = (int)$request->post('os_id', 0);

        if ($osId <= 0) {
            Response::redirect('/rastreio');
            return;
        }

        $os = Database::fetchOne(
            "SELECT * FROM service_orders WHERE id = :id LIMIT 1",
            ['id' => $osId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de serviço não encontrada.');
            Response::redirect('/rastreio');
            return;
        }

        $tenantId = (int)$os['tenant_id'];

        try {
            Database::beginTransaction();

            // Marca itens pendentes como rejeitados
            Database::execute(
                "UPDATE os_parts 
                 SET approval_status = 'rejected' 
                 WHERE os_id = :os_id AND tenant_id = :tenant_id AND approval_status = 'pending_approval'",
                ['os_id' => $osId, 'tenant_id' => $tenantId]
            );

            // Recalcula valor total excluindo itens recusados
            $totalParts = (float)Database::fetchColumn(
                "SELECT COALESCE(SUM(quantity * unit_price), 0) FROM os_parts WHERE os_id = :os_id AND tenant_id = :tenant_id AND approval_status = 'approved'",
                ['os_id' => $osId, 'tenant_id' => $tenantId]
            );

            Database::execute(
                "UPDATE service_orders 
                 SET parts_value = :parts_val, total_value = labor_value + :parts_val 
                 WHERE id = :os_id AND tenant_id = :tenant_id",
                ['parts_val' => $totalParts, 'os_id' => $osId, 'tenant_id' => $tenantId]
            );

            // Grava Auditoria Oficial
            Audit::log($tenantId, null, 'client_rejected_additions', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'by' => 'Cliente através do Portal de Rastreio Público',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            Database::commit();

            $tenantSlug = Database::fetchColumn("SELECT slug FROM tenants WHERE id = :id", ['id' => $tenantId]) ?: null;
            Session::setFlash('info', 'Os itens adicionais foram recusados. Nossos técnicos foram notificados.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/rastreio?os=' . urlencode($os['os_number'])));

        } catch (\Throwable $e) {
            Database::rollBack();
            $tenantSlug = Database::fetchColumn("SELECT slug FROM tenants WHERE id = :id", ['id' => $tenantId]) ?: null;
            Session::setFlash('error', 'Erro ao processar: ' . $e->getMessage());
            Response::redirect(Tenant::publicUrl($tenantSlug, '/rastreio?os=' . urlencode($os['os_number'])));
        }
    }

    /**
     * Cliente aprova o orçamento inicial completo (POST /rastreio/aprovar-orcamento)
     */
    public function approveBudget(Request $request): void {
        $osId = (int)$request->post('os_id', 0);

        if ($osId <= 0) {
            Response::redirect('/rastreio');
            return;
        }

        $os = Database::fetchOne(
            "SELECT * FROM service_orders WHERE id = :id LIMIT 1",
            ['id' => $osId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de serviço não encontrada.');
            Response::redirect('/rastreio');
            return;
        }

        $tenantId = (int)$os['tenant_id'];

        try {
            Database::beginTransaction();

            Database::execute(
                "UPDATE service_orders SET status = 'approved' WHERE id = :id AND tenant_id = :tenant_id",
                ['id' => $osId, 'tenant_id' => $tenantId]
            );

            // Baixa estoque de todas as peças aprovadas
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

            Audit::log($tenantId, null, 'client_approved_budget', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'total_value' => (float)$os['total_value'],
                'by' => 'Cliente via Portal de Rastreio Público'
            ]);

            Database::commit();

            $tenantSlug = Database::fetchColumn("SELECT slug FROM tenants WHERE id = :id", ['id' => $tenantId]) ?: null;
            Session::setFlash('success', 'Orçamento aprovado com sucesso! Nossos técnicos iniciarão o reparo.');
            Response::redirect(Tenant::publicUrl($tenantSlug, '/rastreio?os=' . urlencode($os['os_number'])));

        } catch (\Throwable $e) {
            Database::rollBack();
            $tenantSlug = Database::fetchColumn("SELECT slug FROM tenants WHERE id = :id", ['id' => $tenantId]) ?: null;
            Session::setFlash('error', 'Erro ao aprovar orçamento: ' . $e->getMessage());
            Response::redirect(Tenant::publicUrl($tenantSlug, '/rastreio?os=' . urlencode($os['os_number'])));
        }
    }
}
