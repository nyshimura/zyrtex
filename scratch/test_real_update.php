<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Database;
use Zyrtex\Services\AutoUpdaterService;

$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    Env::load($envFile);
}

echo "--- TESTANDO AUTOUPDATER SERVICE REAL ---\n";
try {
    $updater = new AutoUpdaterService();
    echo "Iniciando update()...\n";
    $result = $updater->update();
    echo "Resultado:\n";
    print_r($result);
} catch (\Throwable $e) {
    echo "EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
