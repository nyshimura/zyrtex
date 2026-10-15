<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Database;
use Zyrtex\Core\Session;
use Zyrtex\Core\CSRF;
use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Controllers\SuperAdmin\SuperAdminController;

Env::load(__DIR__ . '/../.env');
Session::start();

echo "=== TESTE: FLUXO DE LOGIN COM CSRF ===" . PHP_EOL;

// 1. Gera token CSRF como aconteceria na view
$token = CSRF::getToken();
echo "[INFO] Token CSRF gerado: {$token}" . PHP_EOL;

// 2. Simula o POST enviado pelo formulário do login
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    '_csrf' => $token,
    'email' => 'superadmin@zyrtex.com.br',
    'password' => 'admin123'
];

$req = new Request();
$submittedToken = $req->input('_csrf') ?? $req->input('csrf_token');

// Valida CSRF como faz o Router
if (!CSRF::validate($submittedToken)) {
    echo "[FAIL] Falha na validação do CSRF!" . PHP_EOL;
    exit(1);
}
echo "[PASS] Validação do CSRF aprovada com sucesso!" . PHP_EOL;

// Executa o login
$controller = new SuperAdminController();
$controller->login($req);

if (Session::get('superadmin_logged_in') === true && Session::get('user_role') === 'superadmin') {
    echo "[PASS] Autenticação do SuperAdmin realizada com sucesso! ID: " . Session::get('superadmin_id') . PHP_EOL;
} else {
    echo "[FAIL] Falha ao autenticar o SuperAdmin." . PHP_EOL;
    exit(1);
}

echo "=== SUCESSO COMPLETO NO FLUXO DE LOGIN! ===" . PHP_EOL;
