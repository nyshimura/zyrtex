<?php
require_once __DIR__ . '/../src/Core/Env.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Services/AIService.php';

use Zyrtex\Core\Database;
use Zyrtex\Services\AIService;

// Busca primeira OS existente
$os = Database::fetchOne("SELECT * FROM service_orders ORDER BY id DESC LIMIT 1");
if (!$os) {
    echo "Nenhuma OS encontrada para teste.\n";
    exit;
}

echo "Testando com OS #{$os['id']} (Equipamento: {$os['equipment_info']})...\n";

$techDiag = "Troca de conector dock tipo C oxidado por contato com líquido e substituição de tela touch trincada.";
$result = AIService::simplifyDiagnosis($techDiag, $os['equipment_info']);

echo "Resultado da IA:\n";
var_dump($result['success']);
echo "Texto gerado:\n" . $result['text'] . "\n";
echo "Provedor utilizado: " . ($result['provider'] ?? 'desconhecido') . "\n";
