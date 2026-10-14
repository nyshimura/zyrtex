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

            // Realiza login
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
     * Exibe o formulário de cadastro de nova oficina (/cadastro)
     */
    public function showRegister(Request $request): void {
        if (Auth::check()) {
            $slug = Tenant::currentSlug() ?? 'demo-assistencia';
            Response::redirect('/' . $slug . '/admin');
            return;
        }

        $niches = require __DIR__ . '/../../../config/niches.php';
        $old = Session::get('old_input', []);
        Session::remove('old_input');

        View::render('public/register', [
            'niches' => $niches,
            'old' => $old
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
}
