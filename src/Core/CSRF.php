<?php

namespace Zyrtex\Core;

class CSRF {
    public static function getToken(): string {
        Session::start();
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function token(): string {
        return self::getToken();
    }

    public static function validate(?string $token): bool {
        Session::start();
        $stored = Session::get('_csrf_token');
        if (empty($stored) || empty($token)) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function field(): string {
        $token = self::getToken();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
