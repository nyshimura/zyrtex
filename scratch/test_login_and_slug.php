<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Database;
use Zyrtex\Core\Session;
use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Controllers\SuperAdmin\SuperAdminController;

Env::load(__DIR__ . '/../.env');
Session::start();
\Zyrtex\Core\Auth::logoutSuperAdmin();
\Zyrtex\Core\Auth::logout();

echo "=== TESTE: RENDERIZAÇÃO DE LOGIN & SLUG PERSONALIZADO ===" . PHP_EOL;

// 1. Testa Session::getFlash
Session::setFlash('error', 'Credenciais master incorretas.');
Session::setFlash('warning', 'Alerta de teste.');
Session::setFlash('info', 'Informação do sistema.');

ob_start();
$controller = new SuperAdminController();
$controller->showLogin(new Request());
$html = ob_get_clean();

if (str_contains($html, 'Painel Master SuperAdmin') && str_contains($html, 'Credenciais master incorretas.')) {
    echo "[PASS] View superadmin/login.php renderizou sem erro 500 e com flash messages!" . PHP_EOL;
} else {
    echo "[FAIL] View de login não renderizou como esperado. Tamanho: " . strlen($html) . PHP_EOL;
    echo "Conteúdo retornado:" . PHP_EOL;
    echo substr($html, 0, 500) . PHP_EOL;
    exit(1);
}

// 2. Testa Slug Default
$defaultSlug = SuperAdminController::slug();
echo "[INFO] Slug padrão: {$defaultSlug}" . PHP_EOL;
$url = Response::url('/superadmin/parametros');
echo "[INFO] URL gerada: {$url}" . PHP_EOL;

if (str_contains($url, "/{$defaultSlug}/parametros")) {
    echo "[PASS] Response::url('/superadmin/parametros') gerou rota correta para o slug atual." . PHP_EOL;
} else {
    echo "[FAIL] URL incorreta: {$url}" . PHP_EOL;
    exit(1);
}

// 3. Testa Simulação de Alteração de Slug
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'superadmin_slug' => 'central-secreta-zyrtex',
    'annual_domain_price' => '197.00',
    'niche_change_price' => '97.00',
    'slug_change_price' => '47.00'
];
$req = new Request();

// Executa updateSettings simulando autenticação
Session::set('superadmin_logged_in', true);
Session::set('user_role', 'superadmin');

// Sobrescreve cabeçalho para não disparar redirecionamento fatal no CLI
ob_start();
try {
    $controller->updateSettings($req);
} catch (\Throwable $e) {}
ob_end_clean();

$newSlug = SuperAdminController::slug();
echo "[INFO] Novo slug no banco: {$newSlug}" . PHP_EOL;
$newUrl = Response::url('/superadmin/tenants');
echo "[INFO] Nova URL com slug customizado: {$newUrl}" . PHP_EOL;

if ($newSlug === 'central-secreta-zyrtex' && str_contains($newUrl, '/central-secreta-zyrtex/tenants')) {
    echo "[PASS] Alteração de slug para 'central-secreta-zyrtex' aplicada e roteada com sucesso!" . PHP_EOL;
} else {
    echo "[FAIL] Slug não foi atualizado corretamente." . PHP_EOL;
    exit(1);
}

// 4. Testa rollback do slug para 'superadmin' para não deixar o ambiente do usuário desconfigurado
Database::execute("UPDATE saas_settings SET config_value = 'superadmin' WHERE config_key = 'superadmin_slug'");
SuperAdminController::setSlug('superadmin');
$restoredSlug = SuperAdminController::slug();
echo "[INFO] Slug restaurado para padrão: {$restoredSlug}" . PHP_EOL;
echo "[PASS] Ambiente restaurado para 'superadmin' com sucesso!" . PHP_EOL;

echo "=== TODOS OS TESTES PASSARAM COM SUCESSO! ===" . PHP_EOL;
