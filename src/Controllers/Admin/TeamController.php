<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;
use Zyrtex\Core\Audit;
use Zyrtex\Services\ImageCompressor;

class TeamController {
    /**
     * Lista de Funcionários & Controle de Permissões Granulares (RF03 / ACL)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $currentUser = Auth::user();

        // Apenas owner ou manager podem gerenciar equipe
        if (!in_array($currentUser['role'] ?? '', ['owner', 'manager'], true)) {
            Session::setFlash('error', 'Acesso restrito ao Administrador da oficina.');
            Response::redirect('/admin');
            return;
        }

        $users = Database::fetchAll(
            "SELECT id, name, email, role, permissions_json, avatar_url, active, created_at 
             FROM users 
             WHERE tenant_id = :tenant_id 
             ORDER BY role = 'owner' DESC, name ASC",
            ['tenant_id' => $tenantId]
        );

        View::render('admin/team/index', [
            'tenant' => $tenant,
            'user' => $currentUser,
            'teamMembers' => $users
        ], 'admin');
    }

    /**
     * Cadastro de Novo Funcionário / Técnico (POST /admin/equipe)
     */
    public function store(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $currentUser = Auth::user();

        if (!in_array($currentUser['role'] ?? '', ['owner', 'manager'], true)) {
            Session::setFlash('error', 'Permissão negada.');
            Response::redirect('/admin');
            return;
        }

        $name = trim((string)$request->post('name', ''));
        $email = trim((string)$request->post('email', ''));
        $password = (string)$request->post('password', '');
        $role = trim((string)$request->post('role', 'technician'));
        $avatarUrlInput = trim((string)$request->post('avatar_url', ''));

        // Permissões selecionadas no formulário
        $permissions = [
            'os' => (int)$request->post('perm_os', 0) === 1,
            'pos' => (int)$request->post('perm_pos', 0) === 1,
            'stock' => (int)$request->post('perm_stock', 0) === 1,
            'clients' => (int)$request->post('perm_clients', 0) === 1,
            'financial' => (int)$request->post('perm_financial', 0) === 1,
            'settings' => (int)$request->post('perm_settings', 0) === 1,
        ];

        if (empty($name) || empty($email) || empty($password)) {
            Session::setFlash('error', 'Nome, e-mail e senha inicial são obrigatórios.');
            Response::redirect('/admin/equipe');
            return;
        }

        // Verifica se e-mail já existe neste tenant
        $exists = Database::fetchOne(
            "SELECT id FROM users WHERE tenant_id = :tenant_id AND email = :email LIMIT 1",
            ['tenant_id' => $tenantId, 'email' => $email]
        );
        if ($exists) {
            Session::setFlash('error', 'Já existe um colaborador cadastrado com este e-mail.');
            Response::redirect('/admin/equipe');
            return;
        }

        // Upload de foto do colaborador (Upload de Arquivo OU Link Direto com GD)
        $avatarPath = null;
        if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = ImageCompressor::processUpload($_FILES['avatar_file'], 'avatars');
            if ($uploadResult['success']) {
                $avatarPath = $uploadResult['url'] ?? $uploadResult['path'];
            } else {
                Session::setFlash('error', 'Erro no upload da foto: ' . ($uploadResult['error'] ?? 'Formato inválido'));
                Response::redirect('/admin/equipe');
                return;
            }
        } elseif (!empty($avatarUrlInput)) {
            $urlResult = ImageCompressor::processUrl($avatarUrlInput, 'avatars');
            if ($urlResult['success']) {
                $avatarPath = $urlResult['url'] ?? $urlResult['path'];
            } else {
                $avatarPath = $avatarUrlInput;
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $permissionsJson = json_encode($permissions, JSON_UNESCAPED_UNICODE);

        try {
            Database::execute(
                "INSERT INTO users (tenant_id, name, email, password_hash, role, permissions_json, avatar_url, active)
                 VALUES (:tenant_id, :name, :email, :password_hash, :role, :permissions_json, :avatar_url, 1)",
                [
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'email' => $email,
                    'password_hash' => $passwordHash,
                    'role' => in_array($role, ['manager', 'technician', 'attendant'], true) ? $role : 'technician',
                    'permissions_json' => $permissionsJson,
                    'avatar_url' => $avatarPath
                ]
            );

            Audit::log($tenantId, (int)$currentUser['id'], 'team_member_created', [
                'name' => $name,
                'email' => $email,
                'role' => $role
            ]);

            Session::setFlash('success', "Colaborador {$name} adicionado à equipe com sucesso!");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao cadastrar colaborador: ' . $e->getMessage());
        }

        Response::redirect('/admin/equipe');
    }

    /**
     * Atualização de Funcionário / Permissões (POST /admin/equipe/editar)
     */
    public function update(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $currentUser = Auth::user();

        if (!in_array($currentUser['role'] ?? '', ['owner', 'manager'], true)) {
            Session::setFlash('error', 'Permissão negada.');
            Response::redirect('/admin');
            return;
        }

        $id = (int)$request->post('id', 0);
        $name = trim((string)$request->post('name', ''));
        $email = trim((string)$request->post('email', ''));
        $password = (string)$request->post('password', '');
        $role = trim((string)$request->post('role', 'technician'));
        $active = (int)$request->post('active', 1);
        $avatarUrlInput = trim((string)$request->post('avatar_url', ''));
        $removeAvatar = (int)$request->post('remove_avatar', 0) === 1;

        $targetUser = Database::fetchOne(
            "SELECT * FROM users WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $id, 'tenant_id' => $tenantId]
        );

        if (!$targetUser) {
            Session::setFlash('error', 'Colaborador não encontrado.');
            Response::redirect('/admin/equipe');
            return;
        }

        // Não permite que ninguém altere o papel do dono principal
        if ($targetUser['role'] === 'owner' && (int)$currentUser['id'] !== (int)$targetUser['id']) {
            Session::setFlash('error', 'Apenas o proprietário da conta pode alterar seus próprios dados.');
            Response::redirect('/admin/equipe');
            return;
        }

        if (empty($name) || empty($email)) {
            Session::setFlash('error', 'Nome e e-mail não podem ficar vazios.');
            Response::redirect('/admin/equipe');
            return;
        }

        // Permissões
        $permissions = [
            'os' => (int)$request->post('perm_os', 0) === 1,
            'pos' => (int)$request->post('perm_pos', 0) === 1,
            'stock' => (int)$request->post('perm_stock', 0) === 1,
            'clients' => (int)$request->post('perm_clients', 0) === 1,
            'financial' => (int)$request->post('perm_financial', 0) === 1,
            'settings' => (int)$request->post('perm_settings', 0) === 1,
        ];
        // Se for owner, tem todas as permissões
        if ($targetUser['role'] === 'owner') {
            $permissions = array_fill_keys(array_keys($permissions), true);
        }

        // Avatar
        $avatarPath = $targetUser['avatar_url'];
        if ($removeAvatar) {
            $avatarPath = null;
        } elseif (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = ImageCompressor::processUpload($_FILES['avatar_file'], 'avatars');
            if ($uploadResult['success']) {
                $avatarPath = $uploadResult['url'] ?? $uploadResult['path'];
            } else {
                Session::setFlash('error', 'Erro no upload da foto: ' . ($uploadResult['error'] ?? 'Formato inválido'));
                Response::redirect('/admin/equipe');
                return;
            }
        } elseif (!empty($avatarUrlInput)) {
            $urlResult = ImageCompressor::processUrl($avatarUrlInput, 'avatars');
            if ($urlResult['success']) {
                $avatarPath = $urlResult['url'] ?? $urlResult['path'];
            } else {
                $avatarPath = $avatarUrlInput;
            }
        }

        $params = [
            'id' => $id,
            'tenant_id' => $tenantId,
            'name' => $name,
            'email' => $email,
            'role' => $targetUser['role'] === 'owner' ? 'owner' : (in_array($role, ['manager', 'technician', 'attendant'], true) ? $role : 'technician'),
            'permissions_json' => json_encode($permissions, JSON_UNESCAPED_UNICODE),
            'avatar_url' => $avatarPath,
            'active' => $targetUser['role'] === 'owner' ? 1 : ($active ? 1 : 0)
        ];

        $sql = "UPDATE users SET name = :name, email = :email, role = :role, 
                permissions_json = :permissions_json, avatar_url = :avatar_url, active = :active";

        if (!empty($password)) {
            $sql .= ", password_hash = :password_hash";
            $params['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id = :id AND tenant_id = :tenant_id";

        try {
            Database::execute($sql, $params);

            Audit::log($tenantId, (int)$currentUser['id'], 'team_member_updated', [
                'user_id' => $id,
                'name' => $name,
                'role' => $params['role']
            ]);

            Session::setFlash('success', "Dados e permissões de {$name} atualizados com sucesso!");
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao atualizar colaborador: ' . $e->getMessage());
        }

        Response::redirect('/admin/equipe');
    }

    /**
     * Alternar Ativo / Inativo de Colaborador (POST /admin/equipe/toggle)
     */
    public function toggle(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $currentUser = Auth::user();

        if (!in_array($currentUser['role'] ?? '', ['owner', 'manager'], true)) {
            Session::setFlash('error', 'Permissão negada.');
            Response::redirect('/admin');
            return;
        }

        $id = (int)$request->post('id', 0);
        $targetUser = Database::fetchOne(
            "SELECT * FROM users WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $id, 'tenant_id' => $tenantId]
        );

        if (!$targetUser || $targetUser['role'] === 'owner') {
            Session::setFlash('error', 'Não é possível desativar este usuário.');
            Response::redirect('/admin/equipe');
            return;
        }

        $newStatus = (int)$targetUser['active'] === 1 ? 0 : 1;
        Database::execute(
            "UPDATE users SET active = :active WHERE id = :id AND tenant_id = :tenant_id",
            ['active' => $newStatus, 'id' => $id, 'tenant_id' => $tenantId]
        );

        Session::setFlash('success', "Status do colaborador atualizado.");
        Response::redirect('/admin/equipe');
    }
}
