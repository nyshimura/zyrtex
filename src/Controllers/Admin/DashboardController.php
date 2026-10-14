<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;

class DashboardController {
    /**
     * Exibe o painel principal do lojista (/admin)
     */
    public function index(Request $request): void {
        Auth::requireAuth();

        $tenantId = Auth::tenantId();
        $user = Auth::user();
        $tenant = Auth::currentTenant();

        // 1. Métricas principais (KPIs) com isolamento Multi-Tenant estrito
        $openOsCount = (int)Database::fetchOne(
            "SELECT COUNT(*) as total FROM service_orders 
             WHERE tenant_id = :tenant_id AND status NOT IN ('delivered', 'cancelled')",
            ['tenant_id' => $tenantId]
        )['total'] ?? 0;

        $readyOsCount = (int)Database::fetchOne(
            "SELECT COUNT(*) as total FROM service_orders 
             WHERE tenant_id = :tenant_id AND status = 'ready'",
            ['tenant_id' => $tenantId]
        )['total'] ?? 0;

        $clientsCount = (int)Database::fetchOne(
            "SELECT COUNT(*) as total FROM clients 
             WHERE tenant_id = :tenant_id",
            ['tenant_id' => $tenantId]
        )['total'] ?? 0;

        $revenueMonth = (float)Database::fetchOne(
            "SELECT COALESCE(SUM(total_value), 0) as total FROM service_orders 
             WHERE tenant_id = :tenant_id AND payment_status = 'paid'",
            ['tenant_id' => $tenantId]
        )['total'] ?? 0.0;

        // 2. Últimas Ordens de Serviço
        $recentOrders = Database::fetchAll(
            "SELECT os.*, c.name as client_name, c.phone as client_phone 
             FROM service_orders os 
             JOIN clients c ON os.client_id = c.id 
             WHERE os.tenant_id = :tenant_id 
             ORDER BY os.id DESC 
             LIMIT 6",
            ['tenant_id' => $tenantId]
        );

        View::render('admin/dashboard', [
            'user' => $user,
            'tenant' => $tenant,
            'kpis' => [
                'open_os' => $openOsCount,
                'ready_os' => $readyOsCount,
                'clients' => $clientsCount,
                'revenue' => $revenueMonth
            ],
            'recentOrders' => $recentOrders
        ], 'admin');
    }
}
