<?php

namespace Zyrtex\Core;

class Response {
    public static function baseUrl(): string {
        $appConfig = require __DIR__ . '/../../config/app.php';
        return rtrim($appConfig['url'] ?? 'http://localhost/zyrtex', '/');
    }

    public static function url(string $path = ''): string {
        $baseUrl = self::baseUrl();
        if (empty($path)) {
            return $baseUrl;
        }

        $cleanPath = '/' . ltrim($path, '/');

        // Se for rota do painel admin (/admin ou /admin/...), anexa o slug do lojista se disponível
        if (str_starts_with($cleanPath, '/admin')) {
            $slug = Tenant::currentSlug();
            if (!empty($slug)) {
                // Evita duplicar caso o path já contenha /$slug/admin
                if (!str_starts_with($cleanPath, '/' . $slug . '/admin')) {
                    $cleanPath = '/' . $slug . $cleanPath;
                }
            }
        }

        // Se for rota do SuperAdmin e o slug foi personalizado por segurança
        if (str_starts_with($cleanPath, '/superadmin')) {
            $saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
            if ($saSlug !== 'superadmin') {
                $cleanPath = '/' . $saSlug . substr($cleanPath, strlen('/superadmin'));
            }
        }

        return $baseUrl . $cleanPath;
    }

    /**
     * Resolve a URL de um arquivo estático ou upload (ex: fotos de perfil, logotipo, produtos)
     */
    public static function asset(string $path = ''): string {
        if (empty($path)) {
            return '';
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $cleanPath = '/' . ltrim($path, '/');

        // Se a aplicação está rodando em subpasta (ex: /zyrtex), garante o prefixo correto
        $baseUrl = self::baseUrl();
        $basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '';
        $basePath = rtrim($basePath, '/');

        if (!empty($basePath) && !str_starts_with($cleanPath, $basePath . '/')) {
            $cleanPath = $basePath . $cleanPath;
        }

        return $cleanPath;
    }

    public static function redirect(string $url, int $statusCode = 302): void {
        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = self::url($url);
        }
        if (!headers_sent()) {
            header("Location: {$url}", true, $statusCode);
        }
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }

    public static function route(string $path, int $statusCode = 302): void {
        self::redirect($path, $statusCode);
    }

    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function notFound(string $message = 'Página não encontrada'): void {
        http_response_code(404);
        View::render('errors/404', ['message' => $message]);
        exit;
    }
}
