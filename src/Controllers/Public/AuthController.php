<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Public;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\Tenant;
use Zyrtex\Core\View;

class AuthController {
    /**
     * Exibe o formulário de login (/login)
     */
    public function showLogin(Request $request): void {
        if (Auth::check()) {
            $slug = Tenant::currentSlug() ?? 'demo-assistencia';
            Response::redirect('/' . $slug . '/admin');
            return;
        }

        $tenant = Tenant::current();

        View::render('public/login', [
            'tenant' => $tenant,
            'isSaaS' => Tenant::isSaaS(),
        ], Tenant::isSaaS() ? 'saas' : 'public');
    }

    /**
     * Processa a autenticação do usuário (POST /login)
     */
    public function login(Request $request): void {
        $email = trim((string)$request->post('email', ''));
        $password = (string)$request->post('password', '');

        if (empty($email) || empty($password)) {
            Session::setFlash('error', 'Por favor, informe seu e-mail e sua senha.');
            Response::redirect('/login');
            return;
        }

        try {
            $tenant = Tenant::current();

            // Se o login for feito diretamente no subdomínio/domínio de uma oficina específica:
            if ($tenant !== null) {
                $user = Database::fetchOne(
                    "SELECT u.*, t.slug as tenant_slug, t.business_name, t.niche, t.active as tenant_active 
                     FROM users u 
                     JOIN tenants t ON u.tenant_id = t.id 
                     WHERE u.email = :email AND u.tenant_id = :tenant_id AND u.active = 1 
                     LIMIT 1",
                    [
                        'email' => $email,
                        'tenant_id' => $tenant['id']
                    ]
                );
            } else {
                // Login via portal central do SaaS Zyrtex:
                $user = Database::fetchOne(
                    "SELECT u.*, t.slug as tenant_slug, t.business_name, t.niche, t.active as tenant_active 
                     FROM users u 
                     JOIN tenants t ON u.tenant_id = t.id 
                     WHERE u.email = :email AND u.active = 1 
                     LIMIT 1",
                    ['email' => $email]
                );
            }

            if (!$user || !password_verify($password, $user['password_hash'])) {
                Session::setFlash('error', 'E-mail ou senha incorretos. Verifique seus dados.');
                Response::redirect('/login');
                return;
            }

            if ((int)$user['tenant_active'] === 0) {
                Session::setFlash('error', 'Esta conta de oficina está inativa ou suspensa. Contate o suporte.');
                Response::redirect('/login');
                return;
            }

            // Intercepta se o usuário tiver Autenticação em Dois Fatores (2FA Steam Guard)
            if ((int)($user['two_factor_enabled'] ?? 0) === 1) {
                $code = \Zyrtex\Services\MailService::generateSteamCode();
                $codeHash = hash('sha256', $code);
                $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                Database::execute(
                    "UPDATE users SET two_factor_code_hash = :hash, two_factor_expires_at = :exp WHERE id = :id",
                    [
                        'hash' => $codeHash,
                        'exp' => $expiresAt,
                        'id' => $user['id']
                    ]
                );

                \Zyrtex\Services\MailService::sendTemplate('lojista_2fa_steam', $user['email'], $user['name'], [
                    'user_name' => $user['name'],
                    'user_email' => $user['email'],
                    'token_2fa' => $code,
                    'shop_name' => $user['business_name'] ?? 'Sua Oficina',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    'date_time' => date('d/m/Y \à\s H:i:s'),
                    'current_year' => date('Y')
                ], (int)$user['tenant_id']);

                Session::set('pending_2fa_user_id', (int)$user['id']);
                Session::set('pending_2fa_tenant_data', [
                    'id' => (int)$user['tenant_id'],
                    'slug' => $user['tenant_slug'],
                    'business_name' => $user['business_name']
                ]);
                Session::set('pending_2fa_email', $user['email']);
                Session::set('pending_2fa_name', $user['name']);

                Response::redirect('/login/2fa');
                return;
            }

            // Realiza login normal
            Auth::login($user, [
                'id' => (int)$user['tenant_id'],
                'slug' => $user['tenant_slug'],
                'business_name' => $user['business_name']
            ]);

            Session::setFlash('success', "Bem-vindo de volta, {$user['name']}!");

            // Redireciona para URL pretendida se houver
            $intended = Session::get('intended_url');
            if (!empty($intended)) {
                Session::remove('intended_url');
                Response::redirect($intended);
                return;
            }

            $tenantSlug = $user['tenant_slug'] ?? 'demo-assistencia';
            Response::redirect('/' . $tenantSlug . '/admin');

        } catch (\Throwable $e) {
            Session::setFlash('error', 'Ocorreu uma falha ao tentar autenticar: ' . $e->getMessage());
            Response::redirect('/login');
        }
    }

    /**
     * Rota de Convite de Parceiro (/convite/{slug})
     */
    public function showInvite(Request $request, string $slug): void {
        $slug = trim($slug);
        $referrer = Database::fetchOne(
            "SELECT id, business_name, slug, referral_code FROM tenants WHERE slug = :s OR referral_code = :s LIMIT 1",
            ['s' => $slug]
        );

        if ($referrer) {
            Session::set('referral_code', $referrer['referral_code'] ?: $referrer['slug']);
            Session::set('referrer_name', $referrer['business_name']);
        }

        Response::redirect('/cadastro?convite=' . urlencode($slug));
    }

    /**
     * Exibe o formulário de cadastro de nova oficina (/cadastro)
     */
    public function showRegister(Request $request): void {
        if (Auth::check()) {
            $slug = Tenant::currentSlug() ?? 'demo-assistencia';
            Response::redirect('/' . $slug . '/admin');
            return;
        }

        $conviteParam = trim((string)($request->get('convite') ?: $request->get('ref') ?: ''));
        if (!empty($conviteParam)) {
            $referrer = Database::fetchOne(
                "SELECT id, business_name, slug, referral_code FROM tenants WHERE slug = :s OR referral_code = :s LIMIT 1",
                ['s' => $conviteParam]
            );
            if ($referrer) {
                Session::set('referral_code', $referrer['referral_code'] ?: $referrer['slug']);
                Session::set('referrer_name', $referrer['business_name']);
            }
        }

        $niches = require __DIR__ . '/../../../config/niches.php';
        $old = Session::get('old_input', []);
        Session::remove('old_input');

        $referralCode = Session::get('referral_code', '');
        $referrerName = Session::get('referrer_name', '');

        View::render('public/register', [
            'niches' => $niches,
            'old' => $old,
            'referralCode' => $referralCode,
            'referrerName' => $referrerName
        ], 'saas');
    }

    /**
     * Processa a criação da nova oficina e do usuário Dono (POST /cadastro)
     */
    public function register(Request $request): void {
        $businessName = trim((string)$request->post('business_name', ''));
        $slugRaw = trim((string)$request->post('slug', ''));
        $niche = trim((string)$request->post('niche', ''));
        $ownerName = trim((string)$request->post('owner_name', ''));
        $email = strtolower(trim((string)$request->post('email', '')));
        $whatsapp = trim((string)$request->post('whatsapp', ''));
        $password = (string)$request->post('password', '');
        $passwordConfirm = (string)$request->post('password_confirm', '');
        $terms = $request->post('terms');

        // Preserva valores digitados em caso de erro
        $oldInput = [
            'business_name' => $businessName,
            'slug' => $slugRaw,
            'niche' => $niche,
            'owner_name' => $ownerName,
            'email' => $email,
            'whatsapp' => $whatsapp
        ];

        // 1. Validações Básicas
        if (empty($businessName) || empty($slugRaw) || empty($niche) || empty($ownerName) || empty($email) || empty($password)) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'Por favor, preencha todos os campos obrigatórios.');
            Response::redirect('/cadastro');
            return;
        }

        if (!$terms) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'Você precisa concordar com os Termos de Uso e Política de Privacidade.');
            Response::redirect('/cadastro');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'Informe um endereço de e-mail válido.');
            Response::redirect('/cadastro');
            return;
        }

        if (strlen($password) < 6) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'A senha precisa ter no mínimo 6 caracteres.');
            Response::redirect('/cadastro');
            return;
        }

        if ($password !== $passwordConfirm) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'As senhas digitadas não coincidem.');
            Response::redirect('/cadastro');
            return;
        }

        // 2. Validação do Nicho
        $nichesConfig = require __DIR__ . '/../../../config/niches.php';
        if (!array_key_exists($niche, $nichesConfig)) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'Selecione um segmento de atuação válido.');
            Response::redirect('/cadastro');
            return;
        }

        // 3. Sanitização e Validação do Subdomínio (Slug) estilo GitHub
        // Remove acentos (ex: São Paulo -> Sao Paulo)
        $slugClean = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slugRaw) ?: $slugRaw;
        // Converte para minúsculas, troca espaços e caracteres especiais por hífen e remove duplicados
        $slug = strtolower(trim($slugClean));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        if (strlen($slug) < 3 || strlen($slug) > 40) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'O subdomínio precisa ter entre 3 e 40 caracteres (letras, números e hífens).');
            Response::redirect('/cadastro');
            return;
        }

        // Lista de subdomínios reservados do SaaS
        $reservedSlugs = [
            'admin', 'superadmin', 'api', 'app', 'zyrtex', 'www', 'portal',
            'static', 'assets', 'support', 'suporte', 'mail', 'auth', 'login',
            'cadastro', 'rastreio', 'termos', 'privacidade', 'dashboard', 'ajax'
        ];

        if (in_array($slug, $reservedSlugs, true)) {
            Session::set('old_input', $oldInput);
            Session::setFlash('error', "O endereço '{$slug}' é reservado pelo sistema Zyrtex. Por favor, escolha outro.");
            Response::redirect('/cadastro');
            return;
        }

        try {
            // Verifica se o slug já existe
            $existingTenant = Database::fetchOne("SELECT id FROM tenants WHERE slug = :slug LIMIT 1", ['slug' => $slug]);
            if ($existingTenant) {
                Session::set('old_input', $oldInput);
                Session::setFlash('error', "O subdomínio '{$slug}.zyrtex.com.br' já está em uso por outra assistência. Por favor, tente outro nome.");
                Response::redirect('/cadastro');
                return;
            }

            // Verifica se o e-mail do usuário já existe
            $existingUser = Database::fetchOne("SELECT id FROM users WHERE email = :email LIMIT 1", ['email' => $email]);
            if ($existingUser) {
                Session::set('old_input', $oldInput);
                Session::setFlash('error', 'Já existe um cadastro com este e-mail. Faça login ou use outro e-mail.');
                Response::redirect('/cadastro');
                return;
            }

            // 4. Criação Atômica (Transação PDO)
            Database::beginTransaction();

            // Configuração padrão de impressora
            $printerSettings = json_encode([
                'default_format' => '80mm',
                'show_logo' => 1,
                'show_pix' => 1,
                'show_qr' => 1,
                'show_terms' => 1,
                'font_size' => 'normal'
            ]);

            // Cadastra a Oficina (Tenant)
            Database::execute(
                "INSERT INTO tenants (
                    business_name, slug, default_url, niche, is_premium, theme_color, active,
                    whatsapp_number, printer_settings_json, payment_mode, ai_provider
                ) VALUES (
                    :business_name, :slug, :default_url, :niche, 0, 'indigo', 1,
                    :whatsapp, :printer_settings, 'manual_pix', 'huggingface'
                )",
                [
                    'business_name' => $businessName,
                    'slug' => $slug,
                    'default_url' => "{$slug}.zyrtex.com.br",
                    'niche' => $niche,
                    'whatsapp' => $whatsapp,
                    'printer_settings' => $printerSettings
                ]
            );

            $tenantId = (int)Database::lastInsertId();

            // Cadastra o Usuário Dono (Owner)
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            Database::execute(
                "INSERT INTO users (
                    tenant_id, name, email, password_hash, role, active
                ) VALUES (
                    :tenant_id, :name, :email, :password_hash, 'owner', 1
                )",
                [
                    'tenant_id' => $tenantId,
                    'name' => $ownerName,
                    'email' => $email,
                    'password_hash' => $passwordHash
                ]
            );

            $userId = (int)Database::lastInsertId();

            Database::commit();

            // 4.1. Vincula indicação se o novo lojista veio de um convite de parceiro
            $refCode = (string)($request->post('referral_code') ?: Session::get('referral_code', ''));
            if (!empty($refCode)) {
                \Zyrtex\Services\ReferralService::linkReferralOnRegister($tenantId, $refCode);
                Session::remove('referral_code');
                Session::remove('referrer_name');
            }

            // 5. Autentica o novo usuário imediatamente
            $newUser = [
                'id' => $userId,
                'tenant_id' => $tenantId,
                'name' => $ownerName,
                'email' => $email,
                'role' => 'owner'
            ];

            Auth::login($newUser, [
                'id' => $tenantId,
                'slug' => $slug,
                'business_name' => $businessName
            ]);

            Session::setFlash('success', "Parabéns, {$ownerName}! Sua oficina '{$businessName}' foi criada com sucesso! Seu endereço de gestão exclusivo já está ativo.");
            Response::redirect('/' . $slug . '/admin');

        } catch (\Throwable $e) {
            Database::rollBack();
            Session::set('old_input', $oldInput);
            Session::setFlash('error', 'Falha ao concluir cadastro no banco de dados: ' . $e->getMessage());
            Response::redirect('/cadastro');
        }
    }

    /**
     * Encerra a sessão do usuário (/logout)
     */
    public function logout(Request $request): void {
        Auth::logout();
        Session::setFlash('info', 'Você encerrou sua sessão com segurança.');
        Response::redirect('/login');
    }

    /**
     * Tela de Autenticação em Dois Fatores (Zyrtex Guard / Steam Guard) (/login/2fa)
     */
    public function show2fa(Request $request): void {
        $userId = Session::get('pending_2fa_user_id');
        if (empty($userId)) {
            Response::redirect('/login');
            return;
        }

        $email = (string)Session::get('pending_2fa_email', '');
        // Oculta e-mail parcialmente (ex: c*****s@gmail.com)
        $atPos = strpos($email, '@');
        if ($atPos !== false && $atPos > 2) {
            $userPart = substr($email, 0, 1) . str_repeat('*', $atPos - 2) . substr($email, $atPos - 1, 1);
            $emailHint = $userPart . substr($email, $atPos);
        } else {
            $emailHint = $email;
        }

        $tenantData = Session::get('pending_2fa_tenant_data', []);

        View::render('public/auth/two_factor', [
            'emailHint' => $emailHint,
            'name' => Session::get('pending_2fa_name', 'Lojista'),
            'shopName' => $tenantData['business_name'] ?? 'Sua Oficina',
            'pageTitle' => 'Zyrtex Guard &bull; Código de Acesso Steam Style'
        ], 'public');
    }

    /**
     * Validação do Código 2FA Steam Guard (POST /login/2fa)
     */
    public function verify2fa(Request $request): void {
        $userId = Session::get('pending_2fa_user_id');
        if (empty($userId)) {
            Response::redirect('/login');
            return;
        }

        $code = strtoupper(trim((string)$request->post('code', '')));
        if (empty($code)) {
            Session::setFlash('error', 'Por favor, digite o código de 5 dígitos enviado ao seu e-mail.');
            Response::redirect('/login/2fa');
            return;
        }

        $codeHash = hash('sha256', $code);

        $user = Database::fetchOne(
            "SELECT u.*, t.slug as tenant_slug, t.business_name, t.active as tenant_active 
             FROM users u 
             JOIN tenants t ON u.tenant_id = t.id 
             WHERE u.id = :id AND u.two_factor_code_hash = :hash AND u.two_factor_expires_at > NOW() 
             LIMIT 1",
            [
                'id' => $userId,
                'hash' => $codeHash
            ]
        );

        if (!$user) {
            Session::setFlash('error', 'Código de autenticação inválido ou expirado. Verifique os 5 caracteres e tente novamente.');
            Response::redirect('/login/2fa');
            return;
        }

        // Limpa o código utilizado
        Database::execute(
            "UPDATE users SET two_factor_code_hash = NULL, two_factor_expires_at = NULL WHERE id = :id",
            ['id' => $userId]
        );

        $tenantData = Session::get('pending_2fa_tenant_data', [
            'id' => (int)$user['tenant_id'],
            'slug' => $user['tenant_slug'],
            'business_name' => $user['business_name']
        ]);

        // Limpa variáveis temporárias de 2FA
        Session::remove('pending_2fa_user_id');
        Session::remove('pending_2fa_tenant_data');
        Session::remove('pending_2fa_email');
        Session::remove('pending_2fa_name');

        // Autentica o usuário na sessão
        Auth::login($user, $tenantData);

        \Zyrtex\Core\Audit::log((int)$user['tenant_id'], (int)$user['id'], 'user_2fa_login_success', [
            'email' => $user['email'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'auth_method' => '2fa_steam_guard'
        ]);

        Session::setFlash('success', "✓ Código autenticado com sucesso! Bem-vindo, {$user['name']}.");

        $intended = Session::get('intended_url');
        if (!empty($intended)) {
            Session::remove('intended_url');
            Response::redirect($intended);
            return;
        }

        $tenantSlug = $tenantData['slug'] ?? ($user['tenant_slug'] ?? 'demo-assistencia');
        Response::redirect('/' . $tenantSlug . '/admin');
    }

    /**
     * Reenvio de Código 2FA Steam Guard (POST /login/2fa/reenviar)
     */
    public function resend2fa(Request $request): void {
        $userId = Session::get('pending_2fa_user_id');
        if (empty($userId)) {
            Response::redirect('/login');
            return;
        }

        $user = Database::fetchOne(
            "SELECT u.*, t.business_name FROM users u JOIN tenants t ON u.tenant_id = t.id WHERE u.id = :id LIMIT 1",
            ['id' => $userId]
        );

        if (!$user) {
            Response::redirect('/login');
            return;
        }

        $code = \Zyrtex\Services\MailService::generateSteamCode();
        $codeHash = hash('sha256', $code);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        Database::execute(
            "UPDATE users SET two_factor_code_hash = :hash, two_factor_expires_at = :exp WHERE id = :id",
            [
                'hash' => $codeHash,
                'exp' => $expiresAt,
                'id' => $userId
            ]
        );

        \Zyrtex\Services\MailService::sendTemplate('lojista_2fa_steam', $user['email'], $user['name'], [
            'user_name' => $user['name'],
            'user_email' => $user['email'],
            'token_2fa' => $code,
            'shop_name' => $user['business_name'] ?? 'Sua Oficina',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'date_time' => date('d/m/Y \à\s H:i:s'),
            'current_year' => date('Y')
        ], (int)$user['tenant_id']);

        Session::setFlash('info', '✓ Um novo código Zyrtex Guard foi enviado para seu e-mail.');
        Response::redirect('/login/2fa');
    }
}
