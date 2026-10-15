<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;
use Zyrtex\Core\Tenant;
use Zyrtex\Services\PrintEngine;
use Zyrtex\Services\QrCodeService;
use Zyrtex\Services\ImageCompressor;

class ServiceOrderController {
    /**
     * Listagem de Ordens de Serviço (/admin/os)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $statusFilter = trim((string)$request->query('status', ''));
        $search = trim((string)$request->query('q', ''));

        $sql = "SELECT os.*, c.name as client_name, c.phone as client_phone 
                FROM service_orders os 
                JOIN clients c ON os.client_id = c.id 
                WHERE os.tenant_id = :tenant_id";
        $params = ['tenant_id' => $tenantId];

        if (!empty($statusFilter)) {
            $sql .= " AND os.status = :status";
            $params['status'] = $statusFilter;
        }

        if (!empty($search)) {
            $sql .= " AND (os.os_number LIKE :q1 OR os.identifier_code LIKE :q2 OR os.equipment_info LIKE :q3 OR c.name LIKE :q4 OR c.phone LIKE :q5)";
            $params['q1'] = "%{$search}%";
            $params['q2'] = "%{$search}%";
            $params['q3'] = "%{$search}%";
            $params['q4'] = "%{$search}%";
            $params['q5'] = "%{$search}%";
        }

        $sql .= " ORDER BY os.id DESC LIMIT 100";
        $orders = Database::fetchAll($sql, $params);

        // Contagens por status para os filtros rápidos
        $statusCounts = Database::fetchAll(
            "SELECT status, COUNT(*) as total FROM service_orders WHERE tenant_id = :tenant_id GROUP BY status",
            ['tenant_id' => $tenantId]
        );
        $countsMap = [];
        foreach ($statusCounts as $sc) {
            $countsMap[$sc['status']] = (int)$sc['total'];
        }

        View::render('admin/os/index', [
            'tenant' => $tenant,
            'user' => $user,
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'countsMap' => $countsMap
        ], 'admin');
    }

    /**
     * Formulário de Abertura de Nova OS Dinâmica (/admin/os/nova)
     */
    public function create(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $niches = require __DIR__ . '/../../../config/niches.php';
        $nicheKey = $tenant['niche'] ?? 'cellphones';
        $nicheConfig = $niches[$nicheKey] ?? $niches['cellphones'];

        // Lista de clientes da oficina para o select
        $clients = Database::fetchAll(
            "SELECT id, name, phone, document FROM clients WHERE tenant_id = :tenant_id ORDER BY name ASC",
            ['tenant_id' => $tenantId]
        );

        // Peças e insumos em estoque disponíveis para inclusão
        $availableProducts = Database::fetchAll(
            "SELECT id, name, price, stock FROM products_pos WHERE tenant_id = :tenant_id AND stock > 0 ORDER BY name ASC",
            ['tenant_id' => $tenantId]
        );

        $selectedClientId = (int)$request->query('client_id', 0);

        View::render('admin/os/create', [
            'tenant' => $tenant,
            'user' => $user,
            'nicheConfig' => $nicheConfig,
            'clients' => $clients,
            'availableProducts' => $availableProducts,
            'selectedClientId' => $selectedClientId,
            'old' => Session::get('old_os_input', [])
        ], 'admin');
        Session::remove('old_os_input');
    }

    /**
     * Gravação da Nova OS (POST /admin/os)
     */
    public function store(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();
        $userId = Auth::id();

        $clientId = (int)$request->post('client_id', 0);
        $identifierCode = trim((string)$request->post('identifier_code', ''));
        $equipmentInfo = trim((string)$request->post('equipment_info', ''));
        $defectReported = trim((string)$request->post('defect_reported', ''));
        $laborValue = (float)str_replace(',', '.', (string)$request->post('labor_value', '0'));
        $partsValue = (float)str_replace(',', '.', (string)$request->post('parts_value', '0'));
        $status = (string)$request->post('status', 'budget');
        $warrantyDays = (int)$request->post('warranty_days', 90);

        // Checklist dinâmico em array
        $checklistRaw = $request->post('checklist', []);
        $checklistJson = json_encode($checklistRaw, JSON_UNESCAPED_UNICODE);

        // Peças e insumos adicionados na abertura da OS
        $initialParts = $request->post('initial_parts', []);
        $validParts = [];
        $calculatedPartsSum = 0.0;

        if (is_array($initialParts)) {
            foreach ($initialParts as $p) {
                $pName = trim((string)($p['part_name'] ?? ''));
                $pId = (int)($p['product_id'] ?? 0);
                $qty = max(1, (int)($p['quantity'] ?? 1));
                $uPrice = (float)str_replace(',', '.', (string)($p['unit_price'] ?? '0'));

                if ($pId > 0 && empty($pName)) {
                    $prod = Database::fetchOne(
                        "SELECT name, price FROM products_pos WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
                        ['id' => $pId, 'tenant_id' => $tenantId]
                    );
                    if ($prod) {
                        $pName = $prod['name'];
                        if ($uPrice <= 0) $uPrice = (float)$prod['price'];
                    }
                }

                if (!empty($pName)) {
                    $validParts[] = [
                        'product_id' => $pId > 0 ? $pId : null,
                        'part_name' => $pName,
                        'quantity' => $qty,
                        'unit_price' => $uPrice
                    ];
                    $calculatedPartsSum += ($qty * $uPrice);
                }
            }
        }

        if ($calculatedPartsSum > 0) {
            $partsValue = $calculatedPartsSum;
        }

        if ($clientId <= 0 || empty($identifierCode) || empty($equipmentInfo) || empty($defectReported)) {
            Session::set('old_os_input', $_POST);
            Session::setFlash('error', 'Por favor, selecione o cliente e preencha o identificador, aparelho e o defeito relatado.');
            Response::redirect('/admin/os/nova');
            return;
        }

        try {
            Database::beginTransaction();

            // Gera o próximo número de OS sequencial para este tenant (ex: OS-2026-001)
            $year = date('Y');
            $lastOs = Database::fetchOne(
                "SELECT os_number FROM service_orders 
                 WHERE tenant_id = :tenant_id AND os_number LIKE :pattern 
                 ORDER BY id DESC LIMIT 1",
                [
                    'tenant_id' => $tenantId,
                    'pattern' => "OS-{$year}-%"
                ]
            );

            $seq = 1;
            if ($lastOs && preg_match('/OS-\d{4}-(\d+)/', $lastOs['os_number'], $matches)) {
                $seq = (int)$matches[1] + 1;
            }
            $osNumber = sprintf("OS-%s-%03d", $year, $seq);

            $totalValue = $laborValue + $partsValue;

            Database::execute(
                "INSERT INTO service_orders (
                    tenant_id, os_number, client_id, identifier_code, equipment_info, 
                    checklist_json, defect_reported, status, labor_value, parts_value, 
                    total_value, payment_status, warranty_days
                ) VALUES (
                    :tenant_id, :os_number, :client_id, :identifier_code, :equipment_info, 
                    :checklist_json, :defect_reported, :status, :labor_value, :parts_value, 
                    :total_value, 'pending', :warranty_days
                )",
                [
                    'tenant_id' => $tenantId,
                    'os_number' => $osNumber,
                    'client_id' => $clientId,
                    'identifier_code' => $identifierCode,
                    'equipment_info' => $equipmentInfo,
                    'checklist_json' => $checklistJson,
                    'defect_reported' => $defectReported,
                    'status' => $status,
                    'labor_value' => $laborValue,
                    'parts_value' => $partsValue,
                    'total_value' => $totalValue,
                    'warranty_days' => $warrantyDays
                ]
            );

            $newOsId = (int)Database::lastInsertId();

            // Insere peças iniciais vinculadas ao funcionário e aprovadas inicialmente
            foreach ($validParts as $vp) {
                Database::execute(
                    "INSERT INTO os_parts (
                        tenant_id, os_id, product_id, user_id, part_name, quantity, unit_price, approval_status
                    ) VALUES (
                        :tenant_id, :os_id, :product_id, :user_id, :part_name, :quantity, :unit_price, 'approved'
                    )",
                    [
                        'tenant_id' => $tenantId,
                        'os_id' => $newOsId,
                        'product_id' => $vp['product_id'],
                        'user_id' => $userId,
                        'part_name' => $vp['part_name'],
                        'quantity' => $vp['quantity'],
                        'unit_price' => $vp['unit_price']
                    ]
                );

                // Se aberta direto em aprovado/em andamento, dá baixa no estoque
                if (in_array($status, ['approved', 'in_progress'], true) && !empty($vp['product_id'])) {
                    Database::execute(
                        "UPDATE products_pos 
                         SET stock = stock - :qty 
                         WHERE id = :id AND tenant_id = :tenant_id AND is_service = 0",
                        ['qty' => $vp['quantity'], 'id' => $vp['product_id'], 'tenant_id' => $tenantId]
                    );
                }

                \Zyrtex\Core\Audit::log($tenantId, $userId, 'part_added', [
                    'os_id' => $newOsId,
                    'os_number' => $osNumber,
                    'part_name' => $vp['part_name'],
                    'quantity' => $vp['quantity'],
                    'unit_price' => $vp['unit_price'],
                    'approval_status' => 'approved',
                    'phase' => 'initial_creation',
                    'employee_name' => $user['name'] ?? 'Técnico'
                ]);
            }

            // Log de auditoria da criação da OS
            \Zyrtex\Core\Audit::log($tenantId, $userId, 'os_created', [
                'os_id' => $newOsId,
                'os_number' => $osNumber,
                'equipment' => $equipmentInfo,
                'status' => $status,
                'total_value' => $totalValue,
                'initial_parts_count' => count($validParts),
                'employee_name' => $user['name'] ?? 'Técnico'
            ]);

            Database::commit();

            Session::setFlash('success', "Ordem de Serviço {$osNumber} aberta com sucesso!");
            Response::redirect('/admin/os/ver?id=' . $newOsId);

        } catch (\Throwable $e) {
            Database::rollBack();
            Session::set('old_os_input', $_POST);
            Session::setFlash('error', 'Erro ao abrir OS: ' . $e->getMessage());
            Response::redirect('/admin/os/nova');
        }
    }

    /**
     * Visualização e Gestão Completa da OS (/admin/os/ver?id=X)
     */
    public function show(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $id = (int)$request->query('id', 0);
        $os = Database::fetchOne(
            "SELECT os.*, c.name as client_name, c.phone as client_phone, c.document as client_document, c.email as client_email, c.address as client_address
             FROM service_orders os 
             JOIN clients c ON os.client_id = c.id 
             WHERE os.id = :id AND os.tenant_id = :tenant_id LIMIT 1",
            ['id' => $id, 'tenant_id' => $tenantId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/admin/os');
            return;
        }

        // Checklist
        $checklist = json_decode($os['checklist_json'] ?? '[]', true) ?: [];

        // Prontuário Antifraude: verifica se este mesmo identificador (IMEI/Placa) já passou pela loja antes
        $fraudHistory = Database::fetchAll(
            "SELECT id, os_number, created_at, status, total_value, defect_reported 
             FROM service_orders 
             WHERE tenant_id = :tenant_id 
               AND identifier_code = :identifier_code 
               AND id != :current_id 
             ORDER BY id DESC",
            [
                'tenant_id' => $tenantId,
                'identifier_code' => $os['identifier_code'],
                'current_id' => $id
            ]
        );

        // Peças vinculadas à OS com dados do funcionário responsável
        $parts = Database::fetchAll(
            "SELECT p.*, u.name as employee_name, u.email as employee_email 
             FROM os_parts p 
             LEFT JOIN users u ON p.user_id = u.id 
             WHERE p.os_id = :os_id AND p.tenant_id = :tenant_id 
             ORDER BY p.id ASC",
            ['os_id' => $id, 'tenant_id' => $tenantId]
        );

        // Peças disponíveis no estoque para inclusão
        $availableProducts = Database::fetchAll(
            "SELECT id, name, price, stock FROM products_pos WHERE tenant_id = :tenant_id AND stock > 0 ORDER BY name ASC",
            ['tenant_id' => $tenantId]
        );

        // Timeline de auditoria interna da OS (exclusivo para o admin e equipe da oficina)
        $auditLogs = Database::fetchAll(
            "SELECT al.*, u.name as user_name 
             FROM audit_logs al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.tenant_id = :tenant_id 
               AND (
                   JSON_UNQUOTE(JSON_EXTRACT(al.details_json, '$.os_id')) = :os_id_str 
                   OR al.details_json LIKE :os_pattern
               ) 
             ORDER BY al.id DESC LIMIT 50",
            [
                'tenant_id' => $tenantId,
                'os_id_str' => (string)$id,
                'os_pattern' => '%"os_id":' . $id . '%'
            ]
        );

        // Mídias e Fotos da OS (Fase 5 - Prontuário Fotográfico)
        $mediaList = Database::fetchAll(
            "SELECT * FROM os_media WHERE os_id = :os_id AND tenant_id = :tenant_id ORDER BY id DESC",
            ['os_id' => $id, 'tenant_id' => $tenantId]
        );

        $niches = require __DIR__ . '/../../../config/niches.php';
        $nicheConfig = $niches[$tenant['niche'] ?? 'cellphones'] ?? $niches['cellphones'];

        View::render('admin/os/show', [
            'tenant' => $tenant,
            'user' => $user,
            'os' => $os,
            'checklist' => $checklist,
            'fraudHistory' => $fraudHistory,
            'parts' => $parts,
            'availableProducts' => $availableProducts,
            'auditLogs' => $auditLogs,
            'mediaList' => $mediaList,
            'nicheConfig' => $nicheConfig
        ], 'admin');
    }

    /**
     * Atualização de Status da OS (POST /admin/os/status)
     */
    public function updateStatus(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();
        $userId = Auth::id();

        $id = (int)$request->post('id', 0);
        $newStatus = (string)$request->post('status', '');
        $paymentStatus = (string)$request->post('payment_status', '');

        $validStatuses = ['budget', 'approved', 'in_progress', 'waiting_parts', 'ready', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses, true)) {
            Session::setFlash('error', 'Status inválido informado.');
            Response::redirect('/admin/os/ver?id=' . $id);
            return;
        }

        try {
            // Obtém status atual da OS para verificar transição de estoque (RN05)
            $currentOs = Database::fetchOne(
                "SELECT os_number, status FROM service_orders WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
                ['id' => $id, 'tenant_id' => $tenantId]
            );

            $oldStatus = $currentOs['status'] ?? 'budget';

            $params = [
                'status' => $newStatus,
                'id' => $id,
                'tenant_id' => $tenantId
            ];

            $sql = "UPDATE service_orders SET status = :status";
            if (!empty($paymentStatus)) {
                $sql .= ", payment_status = :payment_status";
                $params['payment_status'] = $paymentStatus;
            }
            $sql .= " WHERE id = :id AND tenant_id = :tenant_id";

            Database::execute($sql, $params);

            // Valida e Concede Bônus de Indicação Anti-Fraude se o pagamento da OS foi concluído
            if ($paymentStatus === 'paid') {
                \Zyrtex\Services\ReferralService::qualifyReferral($tenantId, 'first_os_paid', 'os:' . $id);
            }

            // RN05: Se a OS foi para "Aprovado" ou "Em Andamento" vindo de orçamento, baixa o estoque das peças vinculadas
            if ($oldStatus === 'budget' && in_array($newStatus, ['approved', 'in_progress'], true)) {
                $parts = Database::fetchAll(
                    "SELECT product_id, quantity FROM os_parts WHERE os_id = :os_id AND tenant_id = :tenant_id AND product_id IS NOT NULL AND approval_status = 'approved'",
                    ['os_id' => $id, 'tenant_id' => $tenantId]
                );

                foreach ($parts as $pt) {
                    Database::execute(
                        "UPDATE products_pos 
                         SET stock = stock - :qty 
                         WHERE id = :product_id AND tenant_id = :tenant_id AND is_service = 0",
                        [
                            'qty' => (int)$pt['quantity'],
                            'product_id' => (int)$pt['product_id'],
                            'tenant_id' => $tenantId
                        ]
                    );
                }
            }

            // Registra Auditoria
            \Zyrtex\Core\Audit::log($tenantId, $userId, 'status_changed', [
                'os_id' => $id,
                'os_number' => $currentOs['os_number'] ?? '',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'payment_status' => $paymentStatus,
                'employee_name' => $user['name'] ?? 'Funcionário'
            ]);

            Session::setFlash('success', "Status da OS atualizado para '{$newStatus}' com sucesso!");
            Response::redirect('/admin/os/ver?id=' . $id);
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao atualizar status: ' . $e->getMessage());
            Response::redirect('/admin/os/ver?id=' . $id);
        }
    }

    /**
     * Adiciona Peça à OS e Atualiza Valor Total (POST /admin/os/peca)
     */
    public function addPart(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();
        $userId = Auth::id();

        $osId = (int)$request->post('os_id', 0);
        $productId = (int)$request->post('product_id', 0);
        $partName = trim((string)$request->post('part_name', ''));
        $quantity = max(1, (int)$request->post('quantity', 1));
        $unitPrice = (float)str_replace(',', '.', (string)$request->post('unit_price', '0'));

        if ($productId > 0) {
            $product = Database::fetchOne(
                "SELECT name, price, stock, is_service FROM products_pos WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
                ['id' => $productId, 'tenant_id' => $tenantId]
            );
            if ($product) {
                if (empty($partName)) {
                    $partName = $product['name'];
                }
                if ($unitPrice <= 0) {
                    $unitPrice = (float)$product['price'];
                }
            }
        }

        if (empty($partName)) {
            Session::setFlash('error', 'Nome da peça ou produto é obrigatório.');
            Response::redirect('/admin/os/ver?id=' . $osId);
            return;
        }

        try {
            $os = Database::fetchOne(
                "SELECT id, os_number, status, labor_value FROM service_orders WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
                ['id' => $osId, 'tenant_id' => $tenantId]
            );

            if (!$os) {
                Session::setFlash('error', 'Ordem de serviço não encontrada.');
                Response::redirect('/admin/os');
                return;
            }

            // Regra de Negócio: Se a OS já foi aprovada/está em andamento, novos itens entram como 'pending_approval' (Aditivo)
            $isPostApproval = ($os['status'] !== 'budget');
            $approvalStatus = $isPostApproval ? 'pending_approval' : 'approved';

            // Insere peça na OS com funcionário e status de aprovação
            Database::execute(
                "INSERT INTO os_parts (tenant_id, os_id, product_id, user_id, part_name, quantity, unit_price, approval_status)
                 VALUES (:tenant_id, :os_id, :product_id, :user_id, :part_name, :quantity, :unit_price, :approval_status)",
                [
                    'tenant_id' => $tenantId,
                    'os_id' => $osId,
                    'product_id' => $productId > 0 ? $productId : null,
                    'user_id' => $userId,
                    'part_name' => $partName,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'approval_status' => $approvalStatus
                ]
            );
            $newPartId = (int)Database::lastInsertId();

            // Recalcula valor total de peças na OS (apenas não rejeitadas)
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

            // Se for aprovado direto e OS já aprovada/em andamento, baixa estoque
            if ($approvalStatus === 'approved' && in_array($os['status'], ['approved', 'in_progress'], true) && $productId > 0) {
                Database::execute(
                    "UPDATE products_pos 
                     SET stock = stock - :qty 
                     WHERE id = :id AND tenant_id = :tenant_id AND is_service = 0",
                    ['qty' => $quantity, 'id' => $productId, 'tenant_id' => $tenantId]
                );
            }

            // Registra Auditoria com data/hora e funcionário
            \Zyrtex\Core\Audit::log($tenantId, $userId, 'part_added', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'part_id' => $newPartId,
                'part_name' => $partName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'approval_status' => $approvalStatus,
                'is_post_approval' => $isPostApproval,
                'employee_name' => $user['name'] ?? 'Funcionário'
            ]);

            if ($isPostApproval) {
                Session::setFlash('info', "Item '{$partName}' adicionado como Aditivo pós-aprovação (Aguardando aprovação do cliente no rastreio da loja).");
            } else {
                Session::setFlash('success', "Peça '{$partName}' adicionada à OS!");
            }

            Response::redirect('/admin/os/ver?id=' . $osId);
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao adicionar peça: ' . $e->getMessage());
            Response::redirect('/admin/os/ver?id=' . $osId);
        }
    }

    /**
     * Remove Peça da OS e Atualiza Valor Total (POST /admin/os/peca/remover)
     */
    public function removePart(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();
        $userId = Auth::id();

        $partId = (int)$request->post('part_id', 0);
        $osId = (int)$request->post('os_id', 0);

        try {
            $part = Database::fetchOne(
                "SELECT * FROM os_parts WHERE id = :id AND tenant_id = :tenant_id AND os_id = :os_id LIMIT 1",
                ['id' => $partId, 'tenant_id' => $tenantId, 'os_id' => $osId]
            );

            if ($part) {
                $os = Database::fetchOne(
                    "SELECT os_number, status, labor_value FROM service_orders WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
                    ['id' => $osId, 'tenant_id' => $tenantId]
                );

                // Registra Auditoria da remoção antes de apagar
                \Zyrtex\Core\Audit::log($tenantId, $userId, 'part_removed', [
                    'os_id' => $osId,
                    'os_number' => $os['os_number'] ?? '',
                    'part_id' => $partId,
                    'part_name' => $part['part_name'],
                    'quantity' => (int)$part['quantity'],
                    'unit_price' => (float)$part['unit_price'],
                    'approval_status' => $part['approval_status'],
                    'employee_name' => $user['name'] ?? 'Funcionário'
                ]);

                // Se a peça já estava aprovada e o status da OS era de execução, devolve ao estoque
                if ($part['approval_status'] === 'approved' && $os && in_array($os['status'], ['approved', 'in_progress'], true) && !empty($part['product_id'])) {
                    Database::execute(
                        "UPDATE products_pos 
                         SET stock = stock + :qty 
                         WHERE id = :id AND tenant_id = :tenant_id AND is_service = 0",
                        ['qty' => (int)$part['quantity'], 'id' => (int)$part['product_id'], 'tenant_id' => $tenantId]
                    );
                }

                Database::execute(
                    "DELETE FROM os_parts WHERE id = :id AND tenant_id = :tenant_id",
                    ['id' => $partId, 'tenant_id' => $tenantId]
                );

                // Recalcula valor total de peças na OS
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

                Session::setFlash('info', "Item '{$part['part_name']}' removido. Ação registrada no histórico de auditoria interna.");
            }

            Response::redirect('/admin/os/ver?id=' . $osId);
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao remover peça: ' . $e->getMessage());
            Response::redirect('/admin/os/ver?id=' . $osId);
        }
    }

    /**
     * Atualização Técnica e Laudo (POST /admin/os/diagnostico)
     */
    public function updateDiagnosis(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $id = (int)$request->post('id', 0);
        $technicalDiagnosis = trim((string)$request->post('technical_diagnosis', ''));
        $laborValue = (float)str_replace(',', '.', (string)$request->post('labor_value', '0'));
        $partsValue = (float)str_replace(',', '.', (string)$request->post('parts_value', '0'));
        $warrantyDays = (int)$request->post('warranty_days', 90);

        $totalValue = $laborValue + $partsValue;

        try {
            Database::execute(
                "UPDATE service_orders 
                 SET technical_diagnosis = :technical_diagnosis, labor_value = :labor_value, 
                     parts_value = :parts_value, total_value = :total_value, warranty_days = :warranty_days
                 WHERE id = :id AND tenant_id = :tenant_id",
                [
                    'technical_diagnosis' => $technicalDiagnosis ?: null,
                    'labor_value' => $laborValue,
                    'parts_value' => $partsValue,
                    'total_value' => $totalValue,
                    'warranty_days' => $warrantyDays,
                    'id' => $id,
                    'tenant_id' => $tenantId
                ]
            );

            Session::setFlash('success', "Laudo técnico e valores atualizados!");
            Response::redirect('/admin/os/ver?id=' . $id);
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar laudo: ' . $e->getMessage());
            Response::redirect('/admin/os/ver?id=' . $id);
        }
    }

    /**
     * Motor de Impressão Multiformato (80mm, 58mm, A4 e Mini Adesivo QR Code) (RF16 & RF14)
     */
    public function print(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        $id = (int)$request->query('id', 0);
        $format = (string)$request->query('formato', '80mm');

        $os = Database::fetchOne(
            "SELECT os.*, c.name as client_name, c.phone as client_phone, c.document as client_document, c.address as client_address
             FROM service_orders os 
             JOIN clients c ON os.client_id = c.id 
             WHERE os.id = :id AND os.tenant_id = :tenant_id LIMIT 1",
            ['id' => $id, 'tenant_id' => $tenantId]
        );

        if (!$os) {
            Response::notFound('Ordem de Serviço não encontrada.');
            return;
        }

        $printerSettings = json_decode($tenant['printer_settings_json'] ?? '{}', true) ?: [
            'default_format' => '80mm',
            'show_logo' => 1,
            'show_pix' => 1,
            'show_qr' => 1,
            'show_terms' => 1
        ];

        $checklist = json_decode($os['checklist_json'] ?? '[]', true) ?: [];
        $niches = require __DIR__ . '/../../../config/niches.php';
        $nicheConfig = $niches[$tenant['niche'] ?? 'cellphones'] ?? $niches['cellphones'];

        // URL de rastreio direto para o QR Code (RF14)
        $trackingUrl = Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio?os=' . urlencode($os['os_number']));

        // Busca peças aplicadas na OS
        $parts = Database::fetchAll(
            "SELECT * FROM os_parts WHERE os_id = :os_id AND tenant_id = :tenant_id ORDER BY id ASC",
            ['os_id' => $id, 'tenant_id' => $tenantId]
        );

        $viewName = match ($format) {
            '58mm' => 'print/thermal_58mm',
            'label' => 'print/sticker_labels',
            'a4' => 'print/a4_document',
            default => 'print/thermal_80mm'
        };

        // Renderiza diretamente a view de impressão (layout nulo para impressão física 100% limpa, sem cabeçalhos ou rodapés web)
        View::render($viewName, [
            'tenant' => $tenant,
            'os' => $os,
            'parts' => $parts,
            'printerSettings' => $printerSettings,
            'checklist' => $checklist,
            'nicheConfig' => $nicheConfig,
            'trackingUrl' => $trackingUrl
        ], null);
    }

    /**
     * Teste de Alinhamento e Calibração de Impressora Física (/admin/os/imprimir-teste)
     */
    public function printTest(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        $format = (string)$request->query('formato', '80mm');
        $testData = \Zyrtex\Services\PrintEngine::getSampleTestData($tenant, $format);

        $trackingUrl = \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio?os=OS-TESTE-CALIBRA');
        $printerSettings = json_decode($tenant['printer_settings_json'] ?? '{}', true) ?: [
            'default_format' => $format,
            'show_logo' => 1,
            'show_pix' => 1,
            'show_qr' => 1,
            'show_terms' => 1
        ];

        $viewName = \Zyrtex\Services\PrintEngine::getViewForFormat($format);

        View::render($viewName, [
            'tenant' => $tenant,
            'os' => $testData['sampleOs'],
            'parts' => [
                ['part_name' => 'Peça de Teste de Alinhamento A', 'quantity' => 1, 'unit_price' => 50.00],
                ['part_name' => 'Insumo de Teste de Alinhamento B', 'quantity' => 2, 'unit_price' => 15.00]
            ],
            'printerSettings' => $printerSettings,
            'checklist' => $testData['sampleChecklist'],
            'nicheConfig' => $testData['nicheConfig'],
            'trackingUrl' => $trackingUrl
        ], null);
    }

    /**
     * Upload ou Vínculo de Mídia / Foto da OS (POST /admin/os/midia)
     */
    public function uploadMedia(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();

        $osId = (int)$request->post('os_id', 0);
        $mediaType = trim((string)$request->post('media_type', 'checkin'));
        $title = trim((string)$request->post('title', ''));
        $mediaUrlInput = trim((string)$request->post('media_url', ''));

        $os = Database::fetchOne(
            "SELECT id, os_number FROM service_orders WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $osId, 'tenant_id' => $tenantId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/admin/os');
            return;
        }

        $allowedTypes = ['checkin', 'bancada', 'part', 'report'];
        if (!in_array($mediaType, $allowedTypes, true)) {
            $mediaType = 'checkin';
        }

        $typeLabels = [
            'checkin' => 'Foto de Entrada / Check-in',
            'bancada' => 'Foto de Bancada / Reparo',
            'part'    => 'Foto da Peça Trocada',
            'report'  => 'Laudo Técnico / Teste Final'
        ];
        $fileName = !empty($title) ? $title : ($typeLabels[$mediaType] ?? 'Foto da OS');

        $filePath = null;
        $thumbBase64 = null;

        // Processa Upload de Arquivo OU Link Direto
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
            $result = ImageCompressor::processUpload($_FILES['media_file'], 'os');
            if (!$result['success']) {
                Session::setFlash('error', 'Falha ao processar imagem: ' . ($result['error'] ?? 'Formato inválido'));
                Response::redirect('/admin/os/ver?id=' . $osId);
                return;
            }
            $filePath = $result['url'] ?? $result['path'];
            $thumbBase64 = $result['thumbnail'] ?? null;
        } elseif (!empty($mediaUrlInput)) {
            $result = ImageCompressor::processUrl($mediaUrlInput, 'os');
            if ($result['success']) {
                $filePath = $result['url'] ?? $result['path'];
                $thumbBase64 = $result['thumbnail'] ?? null;
            } else {
                $filePath = $mediaUrlInput;
            }
        } else {
            Session::setFlash('error', 'Selecione um arquivo de imagem ou informe um link válido.');
            Response::redirect('/admin/os/ver?id=' . $osId);
            return;
        }

        $gdriveFileId = null;
        if (!empty($result['path']) && file_exists($result['path'])) {
            try {
                $gdriveRes = \Zyrtex\Services\GoogleDriveService::uploadFile(
                    $tenantId, 
                    $result['path'], 
                    "OS_{$os['os_number']}_{$mediaType}_" . basename($result['path'])
                );
                if ($gdriveRes['success']) {
                    $gdriveFileId = $gdriveRes['file_id'];
                }
            } catch (\Throwable $e) {
                // Silencioso se o Google Drive não estiver ativo para não travar a OS
            }
        }

        try {
            Database::execute(
                "INSERT INTO os_media (tenant_id, os_id, media_type, file_name, file_path, gdrive_file_id, thumbnail_base64)
                 VALUES (:tenant_id, :os_id, :media_type, :file_name, :file_path, :gdrive_file_id, :thumbnail_base64)",
                [
                    'tenant_id' => $tenantId,
                    'os_id' => $osId,
                    'media_type' => $mediaType,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'gdrive_file_id' => $gdriveFileId,
                    'thumbnail_base64' => $thumbBase64
                ]
            );

            \Zyrtex\Core\Audit::log($tenantId, (int)$user['id'], 'os_media_uploaded', [
                'os_id' => $osId,
                'os_number' => $os['os_number'],
                'media_type' => $mediaType,
                'file_name' => $fileName
            ]);

            Session::setFlash('success', 'Foto anexada ao prontuário da OS com sucesso!');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar mídia: ' . $e->getMessage());
        }

        Response::redirect('/admin/os/ver?id=' . $osId);
    }

    /**
     * Exclusão de Mídia da OS (POST /admin/os/midia/remover)
     */
    public function deleteMedia(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();

        $osId = (int)$request->post('os_id', 0);
        $mediaId = (int)$request->post('media_id', 0);

        $media = Database::fetchOne(
            "SELECT * FROM os_media WHERE id = :id AND os_id = :os_id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $mediaId, 'os_id' => $osId, 'tenant_id' => $tenantId]
        );

        if (!$media) {
            Session::setFlash('error', 'Mídia não encontrada.');
            Response::redirect('/admin/os/ver?id=' . $osId);
            return;
        }

        // Se for arquivo local em uploads, remove fisicamente
        if (!empty($media['file_path']) && str_starts_with($media['file_path'], '/uploads/')) {
            $fullPath = dirname(__DIR__, 3) . '/public' . $media['file_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        Database::execute(
            "DELETE FROM os_media WHERE id = :id AND tenant_id = :tenant_id",
            ['id' => $mediaId, 'tenant_id' => $tenantId]
        );

        \Zyrtex\Core\Audit::log($tenantId, (int)$user['id'], 'os_media_deleted', [
            'os_id' => $osId,
            'media_id' => $mediaId,
            'file_name' => $media['file_name']
        ]);

        Session::setFlash('success', 'Mídia removida do prontuário.');
        Response::redirect('/admin/os/ver?id=' . $osId);
    }

    /**
     * Traduz e simplifica o laudo técnico com IA descentralizada (POST /admin/os/ia-simplificar)
     */
    public function simplifyDiagnosis(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $osId = (int)$request->post('os_id', 0);
        $os = Database::fetchOne(
            "SELECT * FROM service_orders WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $osId, 'tenant_id' => $tenantId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/admin/os');
            return;
        }

        $postedDiag = trim((string)$request->post('technical_diagnosis', ''));
        $diagnosis = !empty($postedDiag) ? $postedDiag : trim((string)($os['technical_diagnosis'] ?? ''));

        if (empty($diagnosis)) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'error' => 'Preencha o Diagnóstico Técnico da Bancada para que a IA possa traduzi-lo.']);
                return;
            }
            Session::setFlash('warning', 'Preencha primeiro o Diagnóstico Técnico da Bancada para que a IA possa traduzi-lo para o cliente.');
            Response::redirect('/admin/os/ver?id=' . $osId);
            return;
        }

        // Se o técnico digitou um diagnóstico novo no campo, salva no banco imediatamente
        if (!empty($postedDiag) && $postedDiag !== ($os['technical_diagnosis'] ?? '')) {
            Database::execute(
                "UPDATE service_orders SET technical_diagnosis = :diag WHERE id = :id AND tenant_id = :tenant_id",
                ['diag' => $postedDiag, 'id' => $osId, 'tenant_id' => $tenantId]
            );
            $os['technical_diagnosis'] = $postedDiag;
        }

        $result = \Zyrtex\Services\AIService::simplifyDiagnosis($diagnosis, $os['equipment_info'], $tenant);

        if ($result['success'] && !empty($result['text'])) {
            Database::execute(
                "UPDATE service_orders SET simplified_diagnosis_ai = :ai_text WHERE id = :id AND tenant_id = :tenant_id",
                [
                    'ai_text' => $result['text'],
                    'id' => $osId,
                    'tenant_id' => $tenantId
                ]
            );

            \Zyrtex\Core\Audit::log($tenantId, (int)$user['id'], 'os_ai_diagnosis_simplified', [
                'os_id' => $osId,
                'provider' => $result['provider'] ?? 'huggingface',
                'is_fallback' => $result['is_fallback'] ?? false
            ]);

            if ($request->isAjax()) {
                Response::json([
                    'success' => true,
                    'text' => $result['text'],
                    'provider' => $result['provider'] ?? 'huggingface',
                    'is_fallback' => $result['is_fallback'] ?? false
                ]);
                return;
            }

            Session::setFlash('success', '✨ Laudo traduzido com sucesso pela IA! O cliente já pode visualizá-lo em linguagem clara no portal.');
        } else {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'error' => $result['error'] ?? 'Erro desconhecido']);
                return;
            }
            Session::setFlash('error', 'Não foi possível gerar a explicação simplificada: ' . ($result['error'] ?? 'Erro desconhecido'));
        }

        Response::redirect('/admin/os/ver?id=' . $osId);
    }

    /**
     * Gera mensagem persuasiva de remarketing para WhatsApp via IA (GET/POST /admin/os/ia-remarketing)
     */
    public function predictiveRemarketing(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        $osId = (int)($request->post('os_id') ?? $request->get('os_id') ?? 0);
        $os = Database::fetchOne(
            "SELECT * FROM service_orders WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $osId, 'tenant_id' => $tenantId]
        );

        if (!$os) {
            Session::setFlash('error', 'Ordem de Serviço não encontrada.');
            Response::redirect('/admin/os');
            return;
        }

        $client = Database::fetchOne(
            "SELECT * FROM clients WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => (int)$os['client_id'], 'tenant_id' => $tenantId]
        );

        $result = \Zyrtex\Services\AIService::generatePredictiveRemarketing($os, $client ?: [], $tenant);

        $cleanPhone = preg_replace('/\D/', '', $client['phone'] ?? '');
        $fullPhone = $cleanPhone;
        if (!empty($fullPhone) && !str_starts_with($fullPhone, '55')) {
            $fullPhone = '55' . $fullPhone;
        }

        if ($request->isAjax() || $request->query('json') === '1') {
            Response::json([
                'success' => $result['success'],
                'text' => $result['text'],
                'phone' => $fullPhone,
                'whatsapp_url' => !empty($fullPhone) ? ('https://wa.me/' . $fullPhone . '?text=' . urlencode($result['text'])) : null
            ]);
            return;
        }

        if (!empty($fullPhone) && !empty($result['text'])) {
            $waUrl = 'https://wa.me/' . $fullPhone . '?text=' . urlencode($result['text']);
            Response::redirect($waUrl);
            return;
        }

        Session::setFlash('info', 'Mensagem de Remarketing gerada: ' . $result['text']);
        Response::redirect('/admin/os/ver?id=' . $osId);
    }
}
