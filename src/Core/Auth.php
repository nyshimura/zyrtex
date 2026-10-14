<?php

declare(strict_types=1);

namespace Zyrtex\Core;

class Auth {
    /**
     * Registra o usuário autenticado na sessão de forma segura
     */
    public static function login(array $user, ?array $tenant = null): void {
        Session::start();
        session_regenerate_id(true);

        Session::set('user_id', (int)$user['id']);
        Session::set('tenant_id', (int)$user['tenant_id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);

        $slug = $tenant['slug'] ?? $user['tenant_slug'] ?? null;
        if (!empty($slug)) {
            Session::set('current_tenant_slug', $slug);
        }

        // Guarda os dados essenciais limpos do usuário
        Session::set('user', [
            'id' => (int)$user['id'],
            'tenant_id' => (int)$user['tenant_id'],
            'tenant_slug' => $slug,
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'permissions_json' => $user['permissions_json'] ?? null,
        ]);
    }

    /**
     * Realiza o logout e limpa os dados de autenticação
     */
    public static function logout(): void {
        Session::start();
        Session::remove('user_id');
        Session::remove('tenant_id');
        Session::remove('user_role');
        Session::remove('user_name');
        Session::remove('user_email');
        Session::remove('user');
        
        if (!headers_sent()) {
            @session_regenerate_id(true);
        }
    }

    /**
     * Verifica se há um usuário autenticado
     */
    public static function check(): bool {
        Session::start();
        return Session::has('user_id') && (int)Session::get('user_id') > 0;
    }

    /**
     * Retorna os dados do usuário autenticado
     */
    public static function user(): ?array {
        Session::start();
        return Session::get('user');
    }

    /**
     * Retorna o ID do usuário autenticado
     */
    public static function id(): ?int {
        Session::start();
        $id = Session::get('user_id');
        return $id !== null ? (int)$id : null;
    }

    /**
     * Retorna o tenant_id do usuário autenticado (Chave de Isolamento Multi-Tenant)
     */
    public static function tenantId(): ?int {
        Session::start();
        $tenantId = Session::get('tenant_id');
        return $tenantId !== null ? (int)$tenantId : null;
    }

    /**
     * Retorna a função / papel do usuário ('owner', 'manager', 'technician', 'attendant')
     */
    public static function role(): string {
        Session::start();
        return (string)(Session::get('user_role') ?? 'guest');
    }

    /**
     * Verifica se o usuário tem uma das funções informadas
     */
    public static function hasRole(string|array $roles): bool {
        if (!self::check()) {
            return false;
        }

        $rolesArray = is_array($roles) ? $roles : [$roles];
        return in_array(self::role(), $rolesArray, true);
    }

    /**
     * Verifica se o usuário é o Dono da Oficina ('owner')
     */
    public static function isOwner(): bool {
        return self::hasRole('owner');
    }

    /**
     * Exige que o usuário esteja autenticado. Se não estiver, redireciona para login.
     */
    public static function requireAuth(string $redirectPath = '/login'): void {
        if (!self::check()) {
            Session::start();
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            if (!empty($requestUri) && !str_contains($requestUri, '/login') && !str_contains($requestUri, '/logout')) {
                Session::set('intended_url', $requestUri);
            }
            Session::setFlash('warning', 'Por favor, faça login para acessar esta área.');
            Response::redirect($redirectPath);
            exit;
        }
    }

    /**
     * Exige uma função específica (ACL). Se o usuário não tiver permissão, redireciona.
     */
    public static function requireRole(string|array $roles, string $forbiddenRedirect = '/admin'): void {
        self::requireAuth();

        if (!self::hasRole($roles)) {
            Session::setFlash('error', 'Acesso restrito. Seu perfil de usuário não tem permissão para esta funcionalidade.');
            Response::redirect($forbiddenRedirect);
            exit;
        }
    }

    /**
     * Verifica se o usuário autenticado é SuperAdmin do SaaS Zyrtex
     */
    public static function isSuperAdmin(): bool {
        Session::start();
        return (Session::get('superadmin_logged_in') === true) || (self::role() === 'superadmin');
    }

    /**
     * Exige autenticação SuperAdmin do SaaS
     */
    public static function requireSuperAdmin(?string $redirectPath = null): void {
        Session::start();
        if (!self::isSuperAdmin()) {
            Session::setFlash('warning', 'Acesso restrito à equipe SuperAdmin da Zyrtex.');
            if ($redirectPath === null) {
                $saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
                $redirectPath = '/' . $saSlug . '/login';
            }
            Response::redirect($redirectPath);
            exit;
        }
    }

    /**
     * Registra o SuperAdmin na sessão
     */
    public static function loginSuperAdmin(array $user): void {
        Session::start();
        if (!headers_sent()) {
            @session_regenerate_id(true);
        }
        Session::set('superadmin_logged_in', true);
        Session::set('superadmin_id', (int)$user['id']);
        Session::set('superadmin_email', $user['email']);
        Session::set('superadmin_name', $user['name']);
        Session::set('user_role', 'superadmin');
    }

    /**
     * Efetua o logout do SuperAdmin
     */
    public static function logoutSuperAdmin(): void {
        Session::start();
        Session::remove('superadmin_logged_in');
        Session::remove('superadmin_id');
        Session::remove('superadmin_email');
        Session::remove('superadmin_name');
        if (Session::get('user_role') === 'superadmin') {
            Session::remove('user_role');
        }
        session_regenerate_id(true);
    }

    /**
     * Busca os dados completos do Tenant da sessão do usuário logado
     */
    public static function currentTenant(): ?array {
        $tenantId = self::tenantId();
        if (!$tenantId) {
            return null;
        }

        try {
            return Database::fetchOne("SELECT * FROM tenants WHERE id = :id AND active = 1 LIMIT 1", ['id' => $tenantId]);
        } catch (\Throwable) {
            return null;
        }
    }
}
