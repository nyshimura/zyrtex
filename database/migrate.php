<?php

declare(strict_types=1);

/**
 * Runner Oficial de Migrações do Zyrtex SaaS
 * Executa com segurança migrações incrementais no banco de dados
 * sem nunca alterar o arquivo .env ou remover dados existentes.
 */

require_once __DIR__ . '/../src/Core/Env.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

require_once __DIR__ . '/../src/Core/Database.php';

use Zyrtex\Core\Database;

echo "=== ZYRTEX DATABASE MIGRATION RUNNER ===\n";
echo "Conectando ao banco de dados via .env...\n";

try {
    // Garante tabela de controle de migrações
    Database::execute("
        CREATE TABLE IF NOT EXISTS `migrations_history` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration_file` VARCHAR(255) NOT NULL UNIQUE,
            `executed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $executed = Database::fetchAll("SELECT migration_file FROM `migrations_history`");
    $executedMap = array_column($executed, 'migration_file');

    $migrationsDir = __DIR__ . '/migrations';
    if (!is_dir($migrationsDir)) {
        echo "Nenhum diretório de migrações pendente.\n";
        exit(0);
    }

    $files = scandir($migrationsDir);
    $pending = 0;

    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || !str_ends_with($file, '.php')) {
            continue;
        }

        if (in_array($file, $executedMap, true)) {
            continue;
        }

        echo "Executando migração: {$file}...\n";
        $filePath = $migrationsDir . '/' . $file;
        
        try {
            require_once $filePath;
            Database::execute("INSERT INTO `migrations_history` (migration_file) VALUES (:f)", ['f' => $file]);
            echo "✓ {$file} concluída com sucesso.\n";
            $pending++;
        } catch (\Throwable $e) {
            echo "✗ Erro na migração {$file}: " . $e->getMessage() . "\n";
        }
    }

    if ($pending === 0) {
        echo "✓ Banco de dados já está 100% atualizado. Nenhuma nova migração pendente.\n";
    } else {
        echo "✓ Total de {$pending} migrações aplicadas com sucesso.\n";
    }

} catch (\Throwable $e) {
    echo "ERRO CRÍTICO NA CONEXÃO: " . $e->getMessage() . "\n";
    exit(1);
}
