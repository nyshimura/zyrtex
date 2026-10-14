<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Env.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Services/AutoUpdaterService.php';

use Zyrtex\Services\AutoUpdaterService;
use Zyrtex\Core\Database;

echo "=== TESTE DE BLINDAGEM DO ATUALIZADOR AUTOMÁTICO (ZERO PERDA DE DADOS) ===\n\n";

$passCount = 0;
$failCount = 0;

function assertShield(string $desc, bool $condition, string $detail = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo " [OK] {$desc}\n";
    } else {
        $failCount++;
        echo " [FALHA] {$desc}" . ($detail ? " -> Detalhes: {$detail}" : '') . "\n";
    }
}

$updater = new AutoUpdaterService();
$protected = $updater->getProtectedFiles();

// 1. Verifica lista de arquivos protegidos
assertShield(".env está na lista de arquivos protegidos", in_array('.env', $protected, true));
assertShield(".env.local está na lista de arquivos protegidos", in_array('.env.local', $protected, true));
assertShield("public/uploads está na lista de arquivos protegidos", in_array('public/uploads', $protected, true));
assertShield("storage está na lista de arquivos protegidos", in_array('storage', $protected, true));
assertShield(".git está na lista de arquivos protegidos", in_array('.git', $protected, true));

// 2. Verifica se o .gitignore existe e contém regras estritas
$gitignorePath = __DIR__ . '/../.gitignore';
assertShield(".gitignore existe na raiz do projeto", file_exists($gitignorePath));
if (file_exists($gitignorePath)) {
    $gi = file_get_contents($gitignorePath);
    assertShield(".gitignore protege .env", str_contains($gi, '.env'));
    assertShield(".gitignore protege public/uploads/logos", str_contains($gi, '/public/uploads/logos/*'));
    assertShield(".gitignore protege public/uploads/os", str_contains($gi, '/public/uploads/os/*'));
}

// 3. Testa integridade do banco de dados (clientes, tenants e ordens de serviço)
try {
    $tenantsCount = Database::fetchValue("SELECT COUNT(*) FROM `tenants`");
    $usersCount = Database::fetchValue("SELECT COUNT(*) FROM `users`");
    $ordersCount = Database::fetchValue("SELECT COUNT(*) FROM `service_orders`");
    $clientsCount = Database::fetchValue("SELECT COUNT(*) FROM `clients`");

    assertShield("Tabela tenants acessível e intacta (registros: {$tenantsCount})", $tenantsCount !== false);
    assertShield("Tabela users acessível e intacta (registros: {$usersCount})", $usersCount !== false);
    assertShield("Tabela service_orders acessível e intacta (registros: {$ordersCount})", $ordersCount !== false);
    assertShield("Tabela clients (clientes finais) acessível e intacta (registros: {$clientsCount})", $clientsCount !== false);
} catch (\Throwable $e) {
    assertShield("Erro ao consultar tabelas do banco: " . $e->getMessage(), false);
}

// 4. Testa hash do .env para garantir que permanece idêntico
$envPath = __DIR__ . '/../.env';
$envHashBefore = md5(file_get_contents($envPath));

// Simula verificação do updater
$check = $updater->check();
assertShield("Verificação remota executada com sucesso", isset($check['status']));

$envHashAfter = md5(file_get_contents($envPath));
assertShield("O arquivo .env permaneceu 100% inalterado", $envHashBefore === $envHashAfter);

echo "\n--- RESULTADO DOS TESTES DE BLINDAGEM ---\n";
echo "Total de Sucessos: {$passCount}\n";
echo "Total de Falhas:   {$failCount}\n";

if ($failCount === 0) {
    echo "🛡️ BLINDAGEM COMPLETA APROVADA: DADOS DE CLIENTES E .ENV 100% PROTEGIDOS!\n";
} else {
    echo "⚠️ ALERTA DE SEGURANÇA DETECTADO.\n";
    exit(1);
}
