<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;

class ClientController {
    /**
     * Listagem de Clientes (/admin/clientes)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $search = trim((string)$request->query('q', ''));

        if (!empty($search)) {
            $clients = Database::fetchAll(
                "SELECT c.*, 
                        (SELECT COUNT(*) FROM service_orders WHERE client_id = c.id AND tenant_id = :t1) as total_os
                 FROM clients c 
                 WHERE c.tenant_id = :tenant_id 
                   AND (c.name LIKE :q1 OR c.phone LIKE :q2 OR c.document LIKE :q3 OR c.email LIKE :q4)
                 ORDER BY c.name ASC",
                [
                    'tenant_id' => $tenantId,
                    't1' => $tenantId,
                    'q1' => "%{$search}%",
                    'q2' => "%{$search}%",
                    'q3' => "%{$search}%",
                    'q4' => "%{$search}%"
                ]
            );
        } else {
            $clients = Database::fetchAll(
                "SELECT c.*, 
                        (SELECT COUNT(*) FROM service_orders WHERE client_id = c.id AND tenant_id = :t1) as total_os
                 FROM clients c 
                 WHERE c.tenant_id = :tenant_id 
                 ORDER BY c.id DESC 
                 LIMIT 50",
                [
                    'tenant_id' => $tenantId,
                    't1' => $tenantId
                ]
            );
        }

        View::render('admin/clients/index', [
            'tenant' => $tenant,
            'user' => $user,
            'clients' => $clients,
            'search' => $search
        ], 'admin');
    }

    /**
     * Formulário de Novo Cliente (/admin/clientes/novo)
     */
    public function create(Request $request): void {
        Auth::requireAuth();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        View::render('admin/clients/create', [
            'tenant' => $tenant,
            'user' => $user,
            'old' => Session::get('old_client_input', [])
        ], 'admin');
        Session::remove('old_client_input');
    }

    /**
     * Gravação de Novo Cliente (POST /admin/clientes)
     */
    public function store(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $name = trim((string)$request->post('name', ''));
        $phone = trim((string)$request->post('phone', ''));
        $document = trim((string)$request->post('document', ''));
        $email = strtolower(trim((string)$request->post('email', '')));
        $address = trim((string)$request->post('address', ''));
        $notes = trim((string)$request->post('notes', ''));

        if (empty($name) || empty($phone)) {
            Session::set('old_client_input', $_POST);
            Session::setFlash('error', 'Nome e WhatsApp/Telefone são obrigatórios.');
            Response::redirect('/admin/clientes/novo');
            return;
        }

        try {
            Database::execute(
                "INSERT INTO clients (tenant_id, name, document, phone, email, address, notes) 
                 VALUES (:tenant_id, :name, :document, :phone, :email, :address, :notes)",
                [
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'document' => $document ?: null,
                    'phone' => $phone,
                    'email' => $email ?: null,
                    'address' => $address ?: null,
                    'notes' => $notes ?: null
                ]
            );

            $newId = Database::lastInsertId();
            $flashMsg = "Cliente '{$name}' cadastrado com sucesso!";

            // Disparo automático do E-mail de Ativação de Conta se houver e-mail informado
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    $rawToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawToken);
                    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

                    Database::execute(
                        "UPDATE clients 
                         SET status = 'pending_activation', 
                             activation_token_hash = :hash, 
                             activation_expires_at = :exp 
                         WHERE id = :id",
                        [
                            'hash' => $tokenHash,
                            'exp' => $expiresAt,
                            'id' => $newId
                        ]
                    );

                    $tenant = Auth::currentTenant();
                    $shopName = $tenant['business_name'] ?? 'Nossa Assistência Técnica';
                    $shopPhone = $tenant['phone'] ?? '';
                    $activationUrl = Response::url('/portal/ativar?token=' . $rawToken);

                    \Zyrtex\Services\MailService::sendTemplate('client_activation', $email, $name, [
                        'client_name' => $name,
                        'client_email' => $email,
                        'activation_url' => $activationUrl,
                        'shop_name' => $shopName,
                        'shop_phone' => $shopPhone,
                        'current_year' => date('Y'),
                        'app_url' => Response::url('/')
                    ], $tenantId);

                    $flashMsg .= " Um e-mail com o link seguro para ativar a conta foi enviado para {$email}.";
                } catch (\Throwable $mailEx) {
                    // Não quebra o cadastro do cliente caso o servidor de e-mail oscile
                }
            }

            Session::setFlash('success', $flashMsg);

            // Se veio parâmetro de retorno para OS
            $returnTo = $request->post('return_to');
            if ($returnTo === 'os_nova') {
                Response::redirect('/admin/os/nova?client_id=' . $newId);
                return;
            }

            Response::redirect('/admin/clientes');
        } catch (\Throwable $e) {
            Session::set('old_client_input', $_POST);
            Session::setFlash('error', 'Erro ao salvar cliente: ' . $e->getMessage());
            Response::redirect('/admin/clientes/novo');
        }
    }

    /**
     * Formulário de Edição de Cliente (/admin/clientes/editar?id=X)
     */
    public function edit(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $id = (int)$request->query('id', 0);
        $client = Database::fetchOne(
            "SELECT * FROM clients WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $id, 'tenant_id' => $tenantId]
        );

        if (!$client) {
            Session::setFlash('error', 'Cliente não encontrado.');
            Response::redirect('/admin/clientes');
            return;
        }

        View::render('admin/clients/edit', [
            'tenant' => $tenant,
            'user' => $user,
            'client' => $client
        ], 'admin');
    }

    /**
     * Atualização de Cliente (POST /admin/clientes/editar)
     */
    public function update(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $id = (int)$request->post('id', 0);
        $name = trim((string)$request->post('name', ''));
        $phone = trim((string)$request->post('phone', ''));
        $document = trim((string)$request->post('document', ''));
        $email = strtolower(trim((string)$request->post('email', '')));
        $address = trim((string)$request->post('address', ''));
        $notes = trim((string)$request->post('notes', ''));

        if (empty($name) || empty($phone)) {
            Session::setFlash('error', 'Nome e WhatsApp/Telefone são obrigatórios.');
            Response::redirect('/admin/clientes/editar?id=' . $id);
            return;
        }

        try {
            $updated = Database::execute(
                "UPDATE clients 
                 SET name = :name, document = :document, phone = :phone, email = :email, address = :address, notes = :notes 
                 WHERE id = :id AND tenant_id = :tenant_id",
                [
                    'name' => $name,
                    'document' => $document ?: null,
                    'phone' => $phone,
                    'email' => $email ?: null,
                    'address' => $address ?: null,
                    'notes' => $notes ?: null,
                    'id' => $id,
                    'tenant_id' => $tenantId
                ]
            );

            Session::setFlash('success', "Dados do cliente atualizados com sucesso!");
            Response::redirect('/admin/clientes');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao atualizar cliente: ' . $e->getMessage());
            Response::redirect('/admin/clientes/editar?id=' . $id);
        }
    }

    /**
     * Ficha do Cliente & Histórico de OS (/admin/clientes/ver?id=X)
     */
    public function show(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $id = (int)$request->query('id', 0);
        $client = Database::fetchOne(
            "SELECT * FROM clients WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $id, 'tenant_id' => $tenantId]
        );

        if (!$client) {
            Session::setFlash('error', 'Cliente não encontrado.');
            Response::redirect('/admin/clientes');
            return;
        }

        // Histórico de ordens de serviço do cliente
        $orders = Database::fetchAll(
            "SELECT * FROM service_orders 
             WHERE client_id = :client_id AND tenant_id = :tenant_id 
             ORDER BY id DESC",
            ['client_id' => $id, 'tenant_id' => $tenantId]
        );

        View::render('admin/clients/show', [
            'tenant' => $tenant,
            'user' => $user,
            'client' => $client,
            'orders' => $orders
        ], 'admin');
    }
}
