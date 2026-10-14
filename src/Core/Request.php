<?php

namespace Zyrtex\Core;

class Request {
    private string $method;
    private string $uri;
    private array $get;
    private array $post;
    private array $files;
    private array $server;

    public function __construct() {
        $this->server = $_SERVER;
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->get = $_GET;
        if (empty($this->get) && !empty($this->server['QUERY_STRING'])) {
            parse_str($this->server['QUERY_STRING'], $this->get);
        }
        $this->post = $_POST;
        $this->files = $_FILES;

        // Suporte automático a payloads JSON (ex: Webhooks e AJAX)
        $raw = file_get_contents('php://input');
        if (!empty($raw) && (str_contains($this->server['CONTENT_TYPE'] ?? '', 'application/json') || str_starts_with(trim($raw), '{'))) {
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $this->post = array_merge($this->post, $json);
            }
        }

        $this->uri = $this->parseUri();
    }

    private function parseUri(): string {
        $uri = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptName = str_replace('\\', '/', $this->server['SCRIPT_NAME'] ?? '');
        $dir = str_replace('\\', '/', dirname($scriptName));

        // Percorre os diretórios base (ex: /zyrtex/public -> remove /zyrtex/public ou /zyrtex)
        while ($dir !== '/' && $dir !== '.' && $dir !== '') {
            if (str_starts_with($uri, $dir)) {
                $uri = substr($uri, strlen($dir));
                break;
            }
            $nextDir = str_replace('\\', '/', dirname($dir));
            if ($nextDir === $dir) {
                break;
            }
            $dir = $nextDir;
        }

        // Se ainda contiver '/public', limpa
        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '//' ? '/' : $uri;
    }

    public function getMethod(): string {
        return $this->method;
    }

    public function isPost(): bool {
        return $this->method === 'POST';
    }

    public function isGet(): bool {
        return $this->method === 'GET';
    }

    public function getPath(): string {
        return $this->uri;
    }

    public function setPath(string $path): void {
        $clean = '/' . trim($path, '/');
        $this->uri = ($clean === '//') ? '/' : $clean;
    }

    public function input(string $key, mixed $default = null): mixed {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed {
        return $this->post[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed {
        return $this->get[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed {
        return $this->query($key, $default);
    }

    public function all(): array {
        return array_merge($this->get, $this->post);
    }

    public function file(string $key): ?array {
        return $this->files[$key] ?? null;
    }

    public function ip(): string {
        return $this->server['HTTP_CLIENT_IP']
            ?? $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    public function userAgent(): string {
        return $this->server['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public function getHost(): string {
        return $this->server['HTTP_HOST'] ?? 'localhost';
    }
}
