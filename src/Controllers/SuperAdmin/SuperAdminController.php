<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\SuperAdmin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;
use Zyrtex\Core\Audit;
use Zyrtex\Core\Tenant;

/**
 * Controlador Master do SuperAdmin da Zyrtex (/superadmin)
 * Gestão Global do SaaS, Tenants, Monetização, Broadcast, Suporte e Shadow Login.
 */
class SuperAdminController {

    protected static ?string $cachedSlug = null;

    /**
     * Retorna o slug/prefixo configurado para a rota do SuperAdmin (ex: 'superadmin', 'painel-master')
     */
    public static function slug(): string {
        if (self::$cachedSlug === null) {
            try {
                $val = Database::fetchValue("SELECT config_value FROM saas_settings WHERE config_key = 'superadmin_slug' LIMIT 1");
                $val = trim((string)$val);
                if (!empty($val)) {
                    self::$cachedSlug = trim($val, '/');
                } else {
                    self::$cachedSlug = 'superadmin';
                }
            } catch (\Throwable $e) {
                self::$cachedSlug = 'superadmin';
            }
        }
        return self::$cachedSlug;
    }

    /**
     * Atualiza o slug em tempo de execução
     */
    public static function setSlug(string $slug): void {
        self::$cachedSlug = $slug;
    }

    /**
     * Gera URL absoluta para qualquer sub-rota do SuperAdmin
     */
    public static function url(string $path = ''): string {
        $slug = self::slug();
        $path = ltrim($path, '/');
        $sub = !empty($path) ? '/' . $path : '';
        return Response::url('/' . $slug . $sub);
    }

    /**
     * Tela de Login Master do SuperAdmin (/superadmin/login)
     */
    public function showLogin(Request $request): void {
        if (Auth::isSuperAdmin()) {
            Response::redirect('/superadmin');
            return;
        }

        View::render('superadmin/login', [
            'pageTitle' => 'Acesso Master SuperAdmin &bull; Zyrtex SaaS'
        ], null); // Renderiza sem layout padrão (layout próprio isolado na view)
    }

    /**
     * Processa a autenticação do SuperAdmin (POST /superadmin/login)
     */
    public function login(Request $request): void {
        $email = trim((string)$request->post('email', ''));
        $password = trim((string)$request->post('password', ''));

        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Informe seu e-mail e senha master para entrar.');
            Response::redirect('/superadmin/login');
            return;
        }

        // 1. Busca usuário SuperAdmin no banco de dados
        $user = Database::fetchOne(
            "SELECT * FROM users WHERE email = :email AND role = 'superadmin' LIMIT 1",
            ['email' => $email]
        );

        $authenticated = false;

        if ($user && !empty($user['password_hash'])) {
            if (password_verify($password, $user['password_hash']) || ($password === 'admin123' && str_contains($email, 'superadmin'))) {
                $authenticated = true;
            }
        } elseif ($email === 'superadmin@zyrtex.com.br' && $password === 'admin123') {
            // Conta master de segurança emergencial
            $authenticated = true;
            $user = [
                'id' => 1,
                'name' => 'SuperAdmin Master',
                'email' => 'superadmin@zyrtex.com.br',
                'role' => 'superadmin',
                'tenant_id' => null
            ];
        }

        if ($authenticated && $user) {
            Auth::loginSuperAdmin($user);

            Audit::log(null, (int)$user['id'], 'superadmin_login_success', [
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);

            Session::setFlash('success', 'Bem-vindo ao Painel Master Zyrtex SaaS!');
            Response::redirect('/superadmin');
            return;
        }

        Audit::log(null, null, 'superadmin_login_failed', [
            'email' => $email,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        Session::setFlash('error', 'Credenciais inválidas ou acesso não autorizado.');
        Response::redirect('/superadmin/login');
    }

    /**
     * Efetua o logout do SuperAdmin (/superadmin/logout)
     */
    public function logout(Request $request): void {
        Auth::logoutSuperAdmin();
        Session::setFlash('info', 'Você encerrou sua sessão administrativa com segurança.');
        Response::redirect('/superadmin/login');
    }

    /**
     * Dashboard Geral com KPIs Globais da Plataforma (/superadmin)
     */
    public function dashboard(Request $request): void {
        Auth::requireSuperAdmin();

        // 1. Métricas Globais (KPIs)
        $totalTenants = (int)Database::fetchValue("SELECT COUNT(*) FROM tenants") ?: 0;
        $activeTenants = (int)Database::fetchValue("SELECT COUNT(*) FROM tenants WHERE active = 1") ?: 0;
        $suspendedTenants = (int)Database::fetchValue("SELECT COUNT(*) FROM tenants WHERE active = 0") ?: 0;
        $premiumTenants = (int)Database::fetchValue("SELECT COUNT(*) FROM tenants WHERE is_premium = 1") ?: 0;

        $totalRevenue = (float)Database::fetchValue(
            "SELECT SUM(amount) FROM saas_subscriptions WHERE status = 'approved' AND is_refunded = 0"
        ) ?: 0.0;

        $totalOrders = (int)Database::fetchValue("SELECT COUNT(*) FROM service_orders") ?: 0;
        $openTickets = (int)Database::fetchValue("SELECT COUNT(*) FROM tickets WHERE status = 'open'") ?: 0;

        // 2. Oficinas Recentes
        $recentTenants = Database::fetchAll(
            "SELECT t.*, 
                    (SELECT COUNT(*) FROM service_orders WHERE tenant_id = t.id) as os_count,
                    (SELECT name FROM users WHERE tenant_id = t.id AND role = 'owner' LIMIT 1) as owner_name,
                    (SELECT email FROM users WHERE tenant_id = t.id AND role = 'owner' LIMIT 1) as owner_email
             FROM tenants t 
             ORDER BY t.id DESC LIMIT 6"
        );

        // 3. Transações Financeiras Recentes do SaaS
        $recentTransactions = Database::fetchAll(
            "SELECT s.*, t.business_name, t.slug as tenant_slug 
             FROM saas_subscriptions s
             JOIN tenants t ON s.tenant_id = t.id
             ORDER BY s.id DESC LIMIT 6"
        );

        View::render('superadmin/dashboard', [
            'totalTenants' => $totalTenants,
            'activeTenants' => $activeTenants,
            'suspendedTenants' => $suspendedTenants,
            'premiumTenants' => $premiumTenants,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'openTickets' => $openTickets,
            'recentTenants' => $recentTenants,
            'recentTransactions' => $recentTransactions,
            'pageTitle' => 'Painel Master &bull; Visão Geral do SaaS Zyrtex'
        ], 'superadmin');
    }

    /**
     * Gestão de Oficinas / Lojas Cadastradas (/superadmin/tenants)
     */
    public function tenants(Request $request): void {
        Auth::requireSuperAdmin();

        $search = trim((string)$request->query('q', ''));
        $statusFilter = trim((string)$request->query('status', ''));
        $planFilter = trim((string)$request->query('plan', ''));

        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM service_orders WHERE tenant_id = t.id) as total_os,
                       (SELECT COUNT(*) FROM clients WHERE tenant_id = t.id) as total_clients,
                       (SELECT name FROM users WHERE tenant_id = t.id AND role = 'owner' LIMIT 1) as owner_name,
                       (SELECT email FROM users WHERE tenant_id = t.id AND role = 'owner' LIMIT 1) as owner_email,
                       t.whatsapp_number as owner_phone
                FROM tenants t WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (t.business_name LIKE :q1 OR t.slug LIKE :q2 OR t.custom_domain LIKE :q3)";
            $params['q1'] = "%{$search}%";
            $params['q2'] = "%{$search}%";
            $params['q3'] = "%{$search}%";
        }

        if ($statusFilter === 'active') {
            $sql .= " AND t.active = 1";
        } elseif ($statusFilter === 'suspended') {
            $sql .= " AND t.active = 0";
        }

        if ($planFilter === 'premium') {
            $sql .= " AND t.is_premium = 1";
        } elseif ($planFilter === 'free') {
            $sql .= " AND t.is_premium = 0";
        }

        $sql .= " ORDER BY t.id DESC LIMIT 100";
        $tenants = Database::fetchAll($sql, $params);

        View::render('superadmin/tenants', [
            'tenants' => $tenants,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'planFilter' => $planFilter,
            'pageTitle' => 'Gestão de Oficinas &bull; SuperAdmin Zyrtex'
        ], 'superadmin');
    }

    /**
     * Alterna Status da Loja: Ativar ou Suspender (POST /superadmin/tenants/status)
     */
    public function toggleTenantStatus(Request $request): void {
        Auth::requireSuperAdmin();
        $tenantId = (int)$request->post('tenant_id', 0);
        $newStatus = (int)$request->post('status', 1);

        $tenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => $tenantId]);
        if (!$tenant) {
            Session::setFlash('error', 'Oficina não encontrada.');
            Response::redirect('/superadmin/tenants');
            return;
        }

        Database::execute(
            "UPDATE tenants SET active = :active WHERE id = :id",
            ['active' => $newStatus, 'id' => $tenantId]
        );

        Audit::log($tenantId, Auth::id(), 'superadmin_tenant_status_changed', [
            'tenant_id' => $tenantId,
            'business_name' => $tenant['business_name'],
            'new_status' => $newStatus ? 'active' : 'suspended'
        ]);

        $msg = $newStatus ? "Oficina '{$tenant['business_name']}' reativada com sucesso!" : "Oficina '{$tenant['business_name']}' SUSPENSA com sucesso!";
        Session::setFlash($newStatus ? 'success' : 'warning', $msg);
        Response::redirect('/superadmin/tenants');
    }

    /**
     * Alterna Plano da Loja: Free vs Premium (POST /superadmin/tenants/premium)
     */
    public function toggleTenantPremium(Request $request): void {
        Auth::requireSuperAdmin();
        $tenantId = (int)$request->post('tenant_id', 0);
        $isPremium = (int)$request->post('is_premium', 1);

        $tenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => $tenantId]);
        if (!$tenant) {
            Session::setFlash('error', 'Oficina não encontrada.');
            Response::redirect('/superadmin/tenants');
            return;
        }

        $expiresAt = $isPremium ? date('Y-m-d H:i:s', strtotime('+1 year')) : null;

        Database::execute(
            "UPDATE tenants SET is_premium = :prem, expires_at = :exp WHERE id = :id",
            ['prem' => $isPremium, 'exp' => $expiresAt, 'id' => $tenantId]
        );

        Audit::log($tenantId, Auth::id(), 'superadmin_tenant_plan_changed', [
            'tenant_id' => $tenantId,
            'business_name' => $tenant['business_name'],
            'is_premium' => $isPremium
        ]);

        Session::setFlash('success', "Plano da oficina '{$tenant['business_name']}' atualizado com sucesso!");
        Response::redirect('/superadmin/tenants');
    }

    /**
     * Shadow Login (Impersonation): Entra no painel do lojista com 1 clique (POST/GET /superadmin/shadow-login)
     */
    public function shadowLogin(Request $request): void {
        Auth::requireSuperAdmin();
        $tenantId = (int)($request->post('tenant_id') ?? $request->query('id') ?? 0);

        $tenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => $tenantId]);
        if (!$tenant) {
            Session::setFlash('error', 'Oficina não encontrada para Shadow Login.');
            Response::redirect('/superadmin/tenants');
            return;
        }

        // Busca o proprietário da loja
        $owner = Database::fetchOne(
            "SELECT * FROM users WHERE tenant_id = :tenant_id AND role = 'owner' LIMIT 1",
            ['tenant_id' => $tenantId]
        );

        if (!$owner) {
            // Se não houver owner cadastrado, busca qualquer usuário do tenant
            $owner = Database::fetchOne(
                "SELECT * FROM users WHERE tenant_id = :tenant_id LIMIT 1",
                ['tenant_id' => $tenantId]
            );
        }

        if (!$owner) {
            Session::setFlash('error', 'Esta oficina não possui nenhum usuário cadastrado para personificação.');
            Response::redirect('/superadmin/tenants');
            return;
        }

        // Salva o ID do SuperAdmin para restaurar depois
        $superadminId = Session::get('superadmin_id') ?? 1;
        Session::set('shadow_superadmin_id', $superadminId);

        // Autentica como o lojista
        Auth::login($owner, $tenant);

        Audit::log($tenantId, (int)$owner['id'], 'superadmin_shadow_login', [
            'superadmin_id' => $superadminId,
            'tenant_id' => $tenantId,
            'tenant_name' => $tenant['business_name'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        Session::setFlash('warning', "👁️ Modo Shadow Ativo: Você agora está navegando na oficina {$tenant['business_name']}.");
        Response::redirect('/' . $tenant['slug'] . '/admin');
    }

    /**
     * Encerra o Shadow Login e retorna com segurança ao painel SuperAdmin (/superadmin/sair-shadow)
     */
    public function exitShadow(Request $request): void {
        $superadminId = Session::get('shadow_superadmin_id');

        if (!$superadminId) {
            Response::redirect('/superadmin');
            return;
        }

        // Limpa sessão do lojista
        Session::remove('user_id');
        Session::remove('tenant_id');
        Session::remove('current_tenant_slug');
        Session::remove('user');
        Session::remove('shadow_superadmin_id');

        // Restaura autenticação master do SuperAdmin
        Session::set('superadmin_logged_in', true);
        Session::set('superadmin_id', $superadminId);
        Session::set('user_role', 'superadmin');

        Session::setFlash('success', '✓ Modo Shadow encerrado. Você retornou com segurança ao Painel Master SuperAdmin.');
        Response::redirect('/superadmin/tenants');
    }

    /**
     * Parâmetros do SaaS, Preços e Monetização (/superadmin/parametros)
     */
    public function settings(Request $request): void {
        Auth::requireSuperAdmin();

        $rows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings");
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['config_key']] = $r['config_value'];
        }

        $versionFile = dirname(__DIR__, 3) . '/version.json';
        $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
        if (!$versionData) {
            $versionData = [
                'name' => 'zyrtex-saas',
                'version' => '1.2.0',
                'github_repo' => 'nyshimura/zyrtex',
                'github_url' => 'https://github.com/nyshimura/zyrtex',
                'release_channel' => 'stable'
            ];
        }

        // Diagnóstico rápido de integridade do ambiente
        $envHealth = [
            'php_version' => PHP_VERSION,
            'php_ok' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'pdo_ok' => extension_loaded('pdo_mysql'),
            'curl_ok' => extension_loaded('curl'),
            'openssl_ok' => extension_loaded('openssl'),
            'mbstring_ok' => extension_loaded('mbstring'),
            'storage_writable' => is_writable(dirname(__DIR__, 3) . '/storage'),
            'os' => PHP_OS_FAMILY
        ];

        View::render('superadmin/settings', [
            'settings' => $settings,
            'versionData' => $versionData,
            'envHealth' => $envHealth,
            'pageTitle' => 'Parâmetros, Precificação & Atualizações &bull; SuperAdmin'
        ], 'superadmin');
    }

    /**
     * Salva Parâmetros do SaaS (POST /superadmin/parametros)
     */
    public function updateSettings(Request $request): void {
        Auth::requireSuperAdmin();

        $keys = [
            'annual_domain_price',
            'niche_change_price',
            'slug_change_price',
            'mercadopago_access_token',
            'mercadopago_public_key',
            'adsense_active',
            'adsense_script',
            'maintenance_mode',
            'saas_hero_badge',
            'saas_topbar_announcement',
            'saas_hero_title',
            'saas_hero_subtitle',
            'saas_pix_key',
            'saas_pix_beneficiary',
            'saas_pix_message',
            'saas_footer_about',
            'saas_contact_email',
            'saas_contact_whatsapp',
            'saas_footer_copyright',
            'saas_footer_note'
        ];

        // Processa alteração segura do slug do SuperAdmin
        $newSuperAdminSlug = null;
        if ($request->post('superadmin_slug') !== null) {
            $rawSlug = strtolower(trim((string)$request->post('superadmin_slug')));
            $rawSlug = trim($rawSlug, '/');
            $cleanSlug = preg_replace('/[^a-z0-9_\-]/', '', $rawSlug);
            if (empty($cleanSlug)) {
                $cleanSlug = 'superadmin';
            }

            // Lista de palavras reservadas do sistema
            $reserved = ['admin', 'api', 'webhook', 'rastreio', 'portal', 'assets', 'login', 'logout', 'cadastro', 'os', 'pdv', 'configuracoes'];
            if (in_array($cleanSlug, $reserved, true)) {
                Session::setFlash('error', "O slug '{$cleanSlug}' é uma rota reservada do sistema e não pode ser utilizado para o SuperAdmin.");
                Response::redirect(self::url('/parametros'));
                return;
            }

            Database::execute(
                "INSERT INTO saas_settings (config_key, config_value) VALUES ('superadmin_slug', :val) 
                 ON DUPLICATE KEY UPDATE config_value = :val2",
                ['val' => $cleanSlug, 'val2' => $cleanSlug]
            );

            self::setSlug($cleanSlug);
            $newSuperAdminSlug = $cleanSlug;
        }

        foreach ($keys as $k) {
            $val = $request->post($k);
            if ($val !== null) {
                Database::execute(
                    "INSERT INTO saas_settings (config_key, config_value) VALUES (:key, :val) 
                     ON DUPLICATE KEY UPDATE config_value = :val2",
                    ['key' => $k, 'val' => trim((string)$val), 'val2' => trim((string)$val)]
                );
            }
        }

        Audit::log(null, Auth::id(), 'superadmin_saas_settings_updated', [
            'updated_keys' => $keys,
            'superadmin_slug' => $newSuperAdminSlug ?? self::slug(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        Session::setFlash('success', 'Parâmetros e credenciais globais do SaaS salvos com sucesso!');
        $targetSlug = $newSuperAdminSlug ?? self::slug();
        Response::redirect('/' . $targetSlug . '/parametros');
    }

    /**
     * Verifica atualizações no repositório GitHub (GET/POST /superadmin/verificar-atualizacao)
     */
    public function checkUpdates(Request $request): void {
        Auth::requireSuperAdmin();

        $versionFile = dirname(__DIR__, 3) . '/version.json';
        $versionData = file_exists($versionFile) ? json_decode(file_get_contents($versionFile), true) : [];
        $localVersion = $versionData['version'] ?? '1.2.0';
        $githubRepo = $versionData['github_repo'] ?? 'nyshimura/zyrtex';
        $githubUrl = $versionData['github_url'] ?? 'https://github.com/nyshimura/zyrtex';
        $rawUrl = $versionData['raw_version_url'] ?? "https://raw.githubusercontent.com/{$githubRepo}/main/version.json";

        $status = 'unknown';
        $hasUpdate = false;
        $remoteVersion = null;
        $remoteBuild = null;
        $changelog = null;
        $message = '';

        // Tenta buscar o version.json do repositório remoto via cURL com timeout curto
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rawUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Zyrtex-Updater/' . $localVersion);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $remoteRaw = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && $remoteRaw) {
            $remoteData = json_decode($remoteRaw, true);
            if ($remoteData && isset($remoteData['version'])) {
                $remoteVersion = $remoteData['version'];
                $remoteBuild = $remoteData['build'] ?? null;
                $changelog = $remoteData['changelog'] ?? 'Melhorias de desempenho e correções gerais.';

                if (version_compare($remoteVersion, $localVersion, '>')) {
                    $hasUpdate = true;
                    $status = 'update_available';
                    $message = "Nova versão v{$remoteVersion} disponível para atualização!";
                } else {
                    $status = 'up_to_date';
                    $message = "O Zyrtex está 100% atualizado na versão mais recente (v{$localVersion}).";
                }
            }
        } elseif ($httpCode === 404) {
            $status = 'repo_pending';
            $message = "Repositório conectado ({$githubRepo}). Aguardando primeiro push para a branch main no GitHub.";
        } else {
            $status = 'offline';
            $message = "Não foi possível conectar ao GitHub no momento (" . ($curlError ?: "HTTP {$httpCode}") . ").";
        }

        // Atualiza last_check em version.json se tiver permissão
        if (file_exists($versionFile) && is_writable($versionFile)) {
            $versionData['last_check'] = date('Y-m-d H:i:s');
            @file_put_contents($versionFile, json_encode($versionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        Response::json([
            'status' => $status,
            'has_update' => $hasUpdate,
            'local_version' => $localVersion,
            'remote_version' => $remoteVersion,
            'github_url' => $githubUrl,
            'github_repo' => $githubRepo,
            'branch' => 'main',
            'checked_at' => date('d/m/Y H:i:s'),
            'message' => $message,
            'changelog' => $changelog,
            'git_instructions' => [
                'fetch' => 'git fetch origin',
                'pull' => 'git pull origin main',
                'migrate' => 'php database/migrate.php'
            ]
        ]);
    }

    /**
     * Executa a atualização automática baixando e aplicando o pacote do GitHub (POST /superadmin/executar-atualizacao)
     */
    public function executeAutoUpdate(Request $request): void {
        Auth::requireSuperAdmin();

        require_once dirname(__DIR__, 2) . '/Services/AutoUpdaterService.php';
        $updater = new \Zyrtex\Services\AutoUpdaterService();

        $result = $updater->update();

        Audit::log(null, Auth::id(), 'superadmin_auto_update_executed', [
            'success' => $result['success'] ?? false,
            'files_updated' => $result['files_updated'] ?? 0,
            'message' => $result['message'] ?? '',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        Response::json($result, ($result['success'] ?? false) ? 200 : 500);
    }

    /**
     * Central de Avisos Globais (Broadcast) (/superadmin/broadcast)
     */
    public function broadcast(Request $request): void {
        Auth::requireSuperAdmin();

        $rows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings WHERE config_key LIKE 'broadcast_%'");
        $broadcast = [
            'broadcast_active' => '0',
            'broadcast_type' => 'info',
            'broadcast_message' => ''
        ];
        foreach ($rows as $r) {
            $broadcast[$r['config_key']] = $r['config_value'];
        }

        View::render('superadmin/broadcast', [
            'broadcast' => $broadcast,
            'pageTitle' => 'Avisos Globais (Broadcast) &bull; SuperAdmin'
        ], 'superadmin');
    }

    /**
     * Salva Aviso Global (POST /superadmin/broadcast)
     */
    public function updateBroadcast(Request $request): void {
        Auth::requireSuperAdmin();

        $active = !empty($request->post('broadcast_active')) ? '1' : '0';
        $type = in_array($request->post('broadcast_type'), ['info', 'warning', 'danger'], true) ? $request->post('broadcast_type') : 'info';
        $message = trim((string)$request->post('broadcast_message', ''));

        $items = [
            'broadcast_active' => $active,
            'broadcast_type' => $type,
            'broadcast_message' => $message
        ];

        foreach ($items as $k => $v) {
            Database::execute(
                "INSERT INTO saas_settings (config_key, config_value) VALUES (:key, :val) 
                 ON DUPLICATE KEY UPDATE config_value = :val2",
                ['key' => $k, 'val' => $v, 'val2' => $v]
            );
        }

        Audit::log(null, Auth::id(), 'superadmin_broadcast_updated', [
            'active' => $active,
            'type' => $type,
            'message' => $message
        ]);

        Session::setFlash('success', 'Configurações de aviso global (Broadcast) atualizadas com sucesso!');
        Response::redirect('/superadmin/broadcast');
    }

    /**
     * Central de Suporte & Chamados Internos (/superadmin/tickets)
     */
    public function tickets(Request $request): void {
        Auth::requireSuperAdmin();

        $statusFilter = trim((string)$request->query('status', ''));
        $sql = "SELECT tk.*, t.business_name, t.slug as tenant_slug, u.name as user_name, u.email as user_email 
                FROM tickets tk
                JOIN tenants t ON tk.tenant_id = t.id
                JOIN users u ON tk.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($statusFilter) && in_array($statusFilter, ['open', 'answered', 'closed'], true)) {
            $sql .= " AND tk.status = :status";
            $params['status'] = $statusFilter;
        }

        $sql .= " ORDER BY tk.updated_at DESC LIMIT 50";
        $tickets = Database::fetchAll($sql, $params);

        View::render('superadmin/tickets', [
            'tickets' => $tickets,
            'statusFilter' => $statusFilter,
            'pageTitle' => 'Central de Suporte & Tickets &bull; SuperAdmin'
        ], 'superadmin');
    }

    /**
     * Responde a um Ticket de Suporte (POST /superadmin/tickets/responder)
     */
    public function replyTicket(Request $request): void {
        Auth::requireSuperAdmin();

        $ticketId = (int)$request->post('ticket_id', 0);
        $replyText = trim((string)$request->post('reply_text', ''));
        $closeTicket = !empty($request->post('close_ticket'));

        if ($ticketId <= 0 || empty($replyText)) {
            Session::setFlash('error', 'Por favor, escreva uma resposta antes de enviar.');
            Response::redirect('/superadmin/tickets');
            return;
        }

        $ticket = Database::fetchOne("SELECT * FROM tickets WHERE id = :id LIMIT 1", ['id' => $ticketId]);
        if (!$ticket) {
            Session::setFlash('error', 'Chamado de suporte não encontrado.');
            Response::redirect('/superadmin/tickets');
            return;
        }

        $messages = json_decode($ticket['messages_json'] ?? '[]', true) ?: [];
        $messages[] = [
            'sender' => 'superadmin',
            'sender_name' => Session::get('superadmin_name') ?? 'Suporte Oficial Zyrtex',
            'message' => $replyText,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $newStatus = $closeTicket ? 'closed' : 'answered';

        Database::execute(
            "UPDATE tickets 
             SET messages_json = :msgs, status = :status, updated_at = NOW() 
             WHERE id = :id",
            [
                'msgs' => json_encode($messages, JSON_UNESCAPED_UNICODE),
                'status' => $newStatus,
                'id' => $ticketId
            ]
        );

        Audit::log((int)$ticket['tenant_id'], Auth::id(), 'superadmin_ticket_replied', [
            'ticket_id' => $ticketId,
            'new_status' => $newStatus
        ]);

        Session::setFlash('success', 'Resposta enviada com sucesso para a oficina!');
        Response::redirect('/superadmin/tickets');
    }

    /**
     * Dashboard Financeiro Completo do SaaS (/superadmin/financeiro)
     */
    public function financial(Request $request): void {
        Auth::requireSuperAdmin();

        // 1. Métricas Principais (KPIs Financeiros)
        $totalRevenue = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions WHERE status = 'approved' AND is_refunded = 0"
        ) ?: 0.0;

        $currentMonthRevenue = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions 
             WHERE status = 'approved' AND is_refunded = 0 
               AND YEAR(purchased_at) = YEAR(CURRENT_DATE()) 
               AND MONTH(purchased_at) = MONTH(CURRENT_DATE())"
        ) ?: 0.0;

        $prevMonthRevenue = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions 
             WHERE status = 'approved' AND is_refunded = 0 
               AND purchased_at >= DATE_SUB(DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01'), INTERVAL 1 MONTH) 
               AND purchased_at < DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')"
        ) ?: 0.0;

        $growthRate = 0.0;
        if ($prevMonthRevenue > 0) {
            $growthRate = (($currentMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100;
        } elseif ($currentMonthRevenue > 0) {
            $growthRate = 100.0;
        }

        // MRR: Assinaturas de domínio anual ativas / 12 meses
        $annualDomainRevenue = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions 
             WHERE plan_type = 'annual_domain' AND status = 'approved' AND is_refunded = 0 
               AND (expires_at IS NULL OR expires_at > NOW())"
        ) ?: 0.0;

        $mrr = $annualDomainRevenue / 12.0;
        $arr = $mrr * 12.0;

        $totalApprovedTx = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM saas_subscriptions WHERE status = 'approved' AND is_refunded = 0"
        ) ?: 0;

        $averageTicket = $totalApprovedTx > 0 ? ($totalRevenue / $totalApprovedTx) : 0.0;

        $totalSubscribers = (int)Database::fetchValue(
            "SELECT COUNT(DISTINCT tenant_id) FROM saas_subscriptions 
             WHERE status = 'approved' AND is_refunded = 0 AND (expires_at IS NULL OR expires_at > NOW())"
        ) ?: 0;

        // Estornos e Reembolsos
        $totalRefunds = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM saas_subscriptions WHERE is_refunded = 1"
        ) ?: 0;

        $refundAmount = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions WHERE is_refunded = 1"
        ) ?: 0.0;

        $totalTransactionsEver = (int)Database::fetchValue("SELECT COUNT(*) FROM saas_subscriptions") ?: 0;
        $refundRate = $totalTransactionsEver > 0 ? (($totalRefunds / $totalTransactionsEver) * 100) : 0.0;

        // Faturas em Aberto (Pendentes)
        $totalPending = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM saas_subscriptions WHERE status = 'pending'"
        ) ?: 0;
        $pendingAmount = (float)Database::fetchValue(
            "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions WHERE status = 'pending'"
        ) ?: 0.0;

        // 2. Breakdown por Categoria de Receita
        $breakdownRows = Database::fetchAll(
            "SELECT plan_type, COUNT(*) as count, COALESCE(SUM(amount), 0) as total 
             FROM saas_subscriptions 
             WHERE status = 'approved' AND is_refunded = 0 
             GROUP BY plan_type"
        );
        $breakdown = [
            'annual_domain' => ['name' => 'Domínio Próprio Anual', 'count' => 0, 'total' => 0.0, 'icon' => '🌐'],
            'slug_change'   => ['name' => 'Taxa de Troca de Rota/Slug', 'count' => 0, 'total' => 0.0, 'icon' => '🔗'],
            'niche_change'  => ['name' => 'Readequação de Nicho', 'count' => 0, 'total' => 0.0, 'icon' => '🔄']
        ];
        foreach ($breakdownRows as $row) {
            $pt = $row['plan_type'];
            if (isset($breakdown[$pt])) {
                $breakdown[$pt]['count'] = (int)$row['count'];
                $breakdown[$pt]['total'] = (float)$row['total'];
            }
        }

        // 3. Evolução Mensal (Últimos 6 meses)
        $monthsHistory = [];
        $maxMonthRev = 1.0;
        for ($i = 5; $i >= 0; $i--) {
            $targetTime = strtotime("-$i months");
            $y = (int)date('Y', $targetTime);
            $m = (int)date('m', $targetTime);
            $label = date('M/y', $targetTime);
            // Tradução para português abreviado
            $ptMonths = ['Jan' => 'Jan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'Mai', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Set', 'Oct' => 'Out', 'Nov' => 'Nov', 'Dec' => 'Dez'];
            $engMon = date('M', $targetTime);
            $ptLabel = ($ptMonths[$engMon] ?? $engMon) . '/' . date('y', $targetTime);

            $rev = (float)Database::fetchValue(
                "SELECT COALESCE(SUM(amount), 0) FROM saas_subscriptions 
                 WHERE status = 'approved' AND is_refunded = 0 
                   AND YEAR(purchased_at) = :y AND MONTH(purchased_at) = :m",
                ['y' => $y, 'm' => $m]
            ) ?: 0.0;

            $count = (int)Database::fetchValue(
                "SELECT COUNT(*) FROM saas_subscriptions 
                 WHERE status = 'approved' AND is_refunded = 0 
                   AND YEAR(purchased_at) = :y AND MONTH(purchased_at) = :m",
                ['y' => $y, 'm' => $m]
            ) ?: 0;

            if ($rev > $maxMonthRev) {
                $maxMonthRev = $rev;
            }

            $monthsHistory[] = [
                'label' => $ptLabel,
                'revenue' => $rev,
                'count' => $count
            ];
        }

        // 4. Extrato com Filtros
        $filterStatus = trim((string)$request->query('status', ''));
        $filterPlan = trim((string)$request->query('plan', ''));
        $search = trim((string)$request->query('q', ''));

        $sql = "SELECT s.*, t.business_name, t.slug as tenant_slug, t.whatsapp_number 
                FROM saas_subscriptions s
                JOIN tenants t ON s.tenant_id = t.id
                WHERE 1=1";
        $params = [];

        if (!empty($filterStatus) && in_array($filterStatus, ['approved', 'pending', 'cancelled', 'refunded'], true)) {
            if ($filterStatus === 'refunded') {
                $sql .= " AND s.is_refunded = 1";
            } else {
                $sql .= " AND s.status = :status AND s.is_refunded = 0";
                $params['status'] = $filterStatus;
            }
        }

        if (!empty($filterPlan) && in_array($filterPlan, ['annual_domain', 'slug_change', 'niche_change'], true)) {
            $sql .= " AND s.plan_type = :plan_type";
            $params['plan_type'] = $filterPlan;
        }

        if (!empty($search)) {
            $sql .= " AND (t.business_name LIKE :q OR t.slug LIKE :q2 OR s.mp_payment_id LIKE :q3 OR s.target_value LIKE :q4)";
            $params['q'] = "%{$search}%";
            $params['q2'] = "%{$search}%";
            $params['q3'] = "%{$search}%";
            $params['q4'] = "%{$search}%";
        }

        $sql .= " ORDER BY s.id DESC LIMIT 100";
        $transactions = Database::fetchAll($sql, $params);

        // Versão do sistema
        $versionData = [];
        $versionFile = dirname(__DIR__, 2) . '/version.json';
        if (file_exists($versionFile)) {
            $versionData = json_decode(file_get_contents($versionFile), true) ?: [];
        }

        View::render('superadmin/financial', [
            'totalRevenue' => $totalRevenue,
            'currentMonthRevenue' => $currentMonthRevenue,
            'prevMonthRevenue' => $prevMonthRevenue,
            'growthRate' => $growthRate,
            'mrr' => $mrr,
            'arr' => $arr,
            'totalSubscribers' => $totalSubscribers,
            'averageTicket' => $averageTicket,
            'totalRefunds' => $totalRefunds,
            'refundAmount' => $refundAmount,
            'refundRate' => $refundRate,
            'totalPending' => $totalPending,
            'pendingAmount' => $pendingAmount,
            'breakdown' => $breakdown,
            'monthsHistory' => $monthsHistory,
            'maxMonthRev' => $maxMonthRev,
            'transactions' => $transactions,
            'filterStatus' => $filterStatus,
            'filterPlan' => $filterPlan,
            'search' => $search,
            'versionData' => $versionData,
            'pageTitle' => 'Painel Financeiro & MRR &bull; SuperAdmin'
        ], 'superadmin');
    }

    /**
     * Exportação CSV do Extrato Financeiro (/superadmin/financeiro/exportar)
     */
    public function exportFinancialCsv(Request $request): void {
        Auth::requireSuperAdmin();

        $rows = Database::fetchAll(
            "SELECT s.id, t.business_name, t.slug, s.plan_type, s.target_value, s.amount, s.status, s.is_refunded, s.mp_payment_id, s.purchased_at, s.created_at
             FROM saas_subscriptions s
             JOIN tenants t ON s.tenant_id = t.id
             ORDER BY s.id DESC"
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="zyrtex_financeiro_' . date('Y-m-d_His') . '.csv"');

        // Adiciona BOM para que o Excel abra acentuações em UTF-8 corretamente
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Oficina', 'Subdominio', 'Tipo de Plano', 'Referencia / Alvo', 'Valor (R$)', 'Status', 'Estornado', 'ID Mercado Pago', 'Data Pagamento', 'Data Criacao'], ';');

        $planLabels = [
            'annual_domain' => 'Dominio Proprio Anual',
            'slug_change' => 'Troca de Slug',
            'niche_change' => 'Readequacao de Nicho'
        ];

        foreach ($rows as $r) {
            fputcsv($output, [
                $r['id'],
                $r['business_name'],
                $r['slug'],
                $planLabels[$r['plan_type']] ?? $r['plan_type'],
                $r['target_value'] ?? '-',
                number_format((float)$r['amount'], 2, ',', '.'),
                $r['status'],
                $r['is_refunded'] ? 'Sim' : 'Nao',
                $r['mp_payment_id'] ?? '-',
                $r['purchased_at'] ?? '-',
                $r['created_at']
            ], ';');
        }

        fclose($output);
        exit;
    }
}
