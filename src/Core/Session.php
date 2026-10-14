<?php

namespace Zyrtex\Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurações seguras de cookie de sessão
            if (!headers_sent()) {
                @ini_set('session.cookie_httponly', '1');
                @ini_set('session.use_only_cookies', '1');
                @session_start();
            } else {
                @session_start();
            }
        }
    }

    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Define uma mensagem flash (sucesso, erro, alerta, info)
     */
    public static function setFlash(string $type, string $message): void {
        self::start();
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Recupera uma mensagem flash de um tipo específico e a remove da sessão
     */
    public static function getFlash(string $type): ?string {
        self::start();
        if (!empty($_SESSION['_flash'][$type])) {
            $msg = array_shift($_SESSION['_flash'][$type]);
            if (empty($_SESSION['_flash'][$type])) {
                unset($_SESSION['_flash'][$type]);
            }
            return $msg;
        }
        return null;
    }

    /**
     * Recupera todas as mensagens flash e as limpa da sessão
     */
    public static function getFlashes(): array {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    /**
     * Destrói a sessão por completo
     */
    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
