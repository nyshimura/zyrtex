<?php

namespace Zyrtex\Core;

class Tenant {
    private static ?array $currentTenant = null;
    private static bool $isSaaSContext = false;

    /**
     * Resolve se a requisição está no contexto da Plataforma SaaS Zyrtex ou em uma Oficina específica
     */
    public static function resolve(Request $request): ?array {
        if (self::$currentTenant !== null || self::$isSaaSContext) {
            return self::$currentTenant;
        }

        Session::start();
        $host = $request->getHost();

        // 1. Forçar visualização da Plataforma SaaS (ex: ?saas=1 ou ?tenant=none)
        if ($request->query('saas') === '1' || $request->query('tenant') === 'none') {
            Session::remove('current_tenant_slug');
            self::$currentTenant = null;
            self::$isSaaSContext = true;
            return null;
        }

        // 2. Parâmetro amigável no Path da URL (ex: http://localhost/zyrtex/demo-assistencia ou /demo-assistencia/admin ou /demo-assistencia/rastreio)
        $path = $request->getPath();
        $segments = explode('/', trim($path, '/'));
        $firstSegment = $segments[0] ?? '';

        $reservedSlugs = [
            'admin', 'superadmin', 'login', 'cadastro', 'logout', 
            'rastreio', 'portal', 'webhook', 'ajax', 'assets', 'css', 
            'js', 'images', 'favicon.ico', 'api', 'saas'
        ];

        if (!empty($firstSegment) && !in_array($firstSegment, $reservedSlugs, true)) {
            $tenant = self::findBySlug($firstSegment);
            if ($tenant) {
                $remainingSegments = array_slice($segments, 1);

                // Isolamento de segurança: se usuário está logado e tentando acessar a área de admin de outra oficina
                if (in_array('admin', $remainingSegments, true) && Auth::check() && Auth::role() !== 'superadmin') {
                    $userTenantId = Auth::tenantId();
                    if ($userTenantId !== null && (int)$userTenantId !== (int)$tenant['id']) {
                        $userTenant = Auth::currentTenant();
                        if ($userTenant && !empty($userTenant['slug'])) {
                            Session::setFlash('warning', 'Você foi direcionado ao painel da sua própria oficina.');
                            Response::redirect('/' . $userTenant['slug'] . '/admin');
                            exit;
                        }
                    }
                }

                Session::set('current_tenant_slug', $tenant['slug']);
                self::$currentTenant = $tenant;
                self::$isSaaSContext = false;

                // Remove o slug do tenant do caminho para o roteador tratar o restante normalmente
                // Ex: /demo-assistencia -> /
                // Ex: /demo-assistencia/admin -> /admin
                // Ex: /demo-assistencia/admin/os -> /admin/os
                // Ex: /demo-assistencia/rastreio -> /rastreio
                $remainingPath = '/' . implode('/', $remainingSegments);
                $request->setPath($remainingPath === '//' ? '/' : $remainingPath);

                return self::$currentTenant;
            }
        }

        // 3. Se acessou diretamente o painel admin (/admin ou /admin/...) sem slug na URL
        $cleanPath = '/' . ltrim($path, '/');
        if (str_starts_with($cleanPath, '/admin')) {
            if (Auth::check()) {
                $userTenant = Auth::currentTenant();
                if ($userTenant && !empty($userTenant['slug'])) {
                    self::$currentTenant = $userTenant;
                    self::$isSaaSContext = false;
                    Session::set('current_tenant_slug', $userTenant['slug']);

                    // Se for requisição GET direta (ex: digitou no navegador ou link /admin),
                    // redireciona para a URL canônica com o slug do lojista: /demo-assistencia/admin
                    if ($request->isGet()) {
                        $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
                        Response::redirect('/' . $userTenant['slug'] . $cleanPath . $queryString);
                        exit;
                    }

                    return self::$currentTenant;
                }
            }
        }

        // 4. Parâmetro explícito de teste local: ?tenant=slug (compatibilidade legada)
        $tenantQuery = $request->query('tenant');
        if (!empty($tenantQuery)) {
            $tenant = self::findBySlug($tenantQuery);
            if ($tenant) {
                Session::set('current_tenant_slug', $tenant['slug']);
                self::$currentTenant = $tenant;
                self::$isSaaSContext = false;
                return self::$currentTenant;
            }
        }

        // 5. Domínio Customizado (Produção - ex: www.oficinajoao.com.br)
        $appConfig = require __DIR__ . '/../../config/app.php';
        $appHost = parse_url($appConfig['url'] ?? 'http://localhost/zyrtex', PHP_URL_HOST) ?? 'localhost';

        if ($host !== $appHost && $host !== 'localhost' && $host !== '127.0.0.1') {
            $tenant = self::findByDomain($host);
            if ($tenant) {
                // Verificação de expiração de domínio (RN-Shield03)
                if (!empty($tenant['expires_at']) && strtotime($tenant['expires_at']) < time() && $tenant['is_premium']) {
                    $tenant['is_premium'] = 0;
                    $tenant['domain_expired_warning'] = true;
                }
                self::$currentTenant = $tenant;
                self::$isSaaSContext = false;
                return self::$currentTenant;
            }

            // 6. Subdomínio da plataforma (ex: oficinajoao.zyrtex.com.br)
            $parts = explode('.', $host);
            if (count($parts) >= 3 && $parts[0] !== 'www') {
                $subdomain = $parts[0];
                $tenant = self::findBySlug($subdomain);
                if ($tenant) {
                    self::$currentTenant = $tenant;
                    self::$isSaaSContext = false;
                    return self::$currentTenant;
                }
            }
        }

        // 7. Por padrão, no domínio raiz ou localhost sem slug/parâmetro, o contexto é a PLATAFORMA SAAS ZYRTEX
        self::$currentTenant = null;
        self::$isSaaSContext = true;
        return null;
    }

    /**
     * Retorna a URL pública amigável de uma oficina
     * - Em desenvolvimento local: http://localhost/zyrtex/demo-assistencia
     * - Em produção na raiz do host: https://meusite.com/demo-assistencia
     * - Em produção com domínio próprio: https://www.oficina.com.br
     * - Em produção com subdomínio (se configurado): https://demo-assistencia.zyrtex.com.br
     */
    public static function publicUrl(?string $slug = null, string $path = ''): string {
        $appConfig = require __DIR__ . '/../../config/app.php';
        $baseUrl = rtrim($appConfig['url'] ?? 'http://localhost/zyrtex', '/');
        $cleanPath = ltrim($path, '/');

        // Se slug não foi fornecido, tenta pegar do tenant atual
        if (empty($slug)) {
            $slug = self::currentSlug() ?? '';
        }

        // Se o tenant possuir domínio próprio customizado ativo
        if (!empty(self::$currentTenant['custom_domain']) && !empty(self::$currentTenant['is_premium'])) {
            $customDomain = rtrim(self::$currentTenant['custom_domain'], '/');
            if (!str_starts_with($customDomain, 'http://') && !str_starts_with($customDomain, 'https://')) {
                $customDomain = 'https://' . $customDomain;
            }
            return empty($cleanPath) ? $customDomain : $customDomain . '/' . $cleanPath;
        }

        // Se habilitado subdomínios via .env (ex: ENABLE_SUBDOMAINS=true)
        if (!empty($_ENV['ENABLE_SUBDOMAINS']) && filter_var($_ENV['ENABLE_SUBDOMAINS'], FILTER_VALIDATE_BOOLEAN) && !empty($slug)) {
            $parsed = parse_url($baseUrl);
            $host = $parsed['host'] ?? 'localhost';
            $scheme = $parsed['scheme'] ?? 'http';
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            if ($host !== 'localhost' && $host !== '127.0.0.1') {
                $subdomainUrl = "{$scheme}://{$slug}.{$host}{$port}";
                return empty($cleanPath) ? $subdomainUrl : $subdomainUrl . '/' . $cleanPath;
            }
        }

        // Padrão estruturado e seguro: URL base + slug (funciona perfeitamente local e na raiz do host oficial)
        $url = $baseUrl;
        if (!empty($slug)) {
            $url .= '/' . $slug;
        }
        if (!empty($cleanPath)) {
            $url .= '/' . $cleanPath;
        }
        return $url;
    }

    public static function isSaaS(): bool {
        return self::$isSaaSContext || self::$currentTenant === null;
    }

    public static function current(): ?array {
        return self::$currentTenant;
    }

    public static function id(): ?int {
        return self::$currentTenant['id'] ?? null;
    }

    public static function currentSlug(): ?string {
        if (!empty(self::$currentTenant['slug'])) {
            return self::$currentTenant['slug'];
        }

        Session::start();
        $sessionSlug = Session::get('current_tenant_slug');
        if (!empty($sessionSlug)) {
            return (string)$sessionSlug;
        }

        if (Auth::check()) {
            $user = Auth::user();
            if (!empty($user['tenant_slug'])) {
                Session::set('current_tenant_slug', $user['tenant_slug']);
                return (string)$user['tenant_slug'];
            }

            $userTenant = Auth::currentTenant();
            if ($userTenant && !empty($userTenant['slug'])) {
                Session::set('current_tenant_slug', $userTenant['slug']);
                return (string)$userTenant['slug'];
            }
        }

        return null;
    }

    public static function setCurrent(?array $tenant): void {
        self::$currentTenant = $tenant;
        self::$isSaaSContext = ($tenant === null);
    }

    public static function slug(): ?string {
        return self::currentSlug();
    }

    public static function niche(): string {
        return self::$currentTenant['niche'] ?? 'cellphones';
    }

    public static function isPremium(): bool {
        return (bool)(self::$currentTenant['is_premium'] ?? false);
    }

    private static function findBySlug(string $slug): ?array {
        try {
            return Database::fetchOne("SELECT * FROM tenants WHERE slug = :slug AND active = 1 LIMIT 1", ['slug' => $slug]);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function findByDomain(string $domain): ?array {
        try {
            return Database::fetchOne("SELECT * FROM tenants WHERE custom_domain = :domain AND active = 1 LIMIT 1", ['domain' => $domain]);
        } catch (\Throwable) {
            return null;
        }
    }
}
