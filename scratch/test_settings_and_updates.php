<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Env.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Session.php';
require_once __DIR__ . '/../src/Core/CSRF.php';
require_once __DIR__ . '/../src/Core/Request.php';
require_once __DIR__ . '/../src/Core/Response.php';
require_once __DIR__ . '/../src/Core/Tenant.php';
require_once __DIR__ . '/../src/Core/Auth.php';
require_once __DIR__ . '/../src/Core/View.php';
require_once __DIR__ . '/../src/Core/Audit.php';
require_once __DIR__ . '/../src/Controllers/SuperAdmin/SuperAdminController.php';

echo "--- INICIANDO TESTES DO NOVO LAYOUT DE PARÂMETROS E ATUALIZADOR GITHUB ---\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck(string $desc, bool $condition, string $detail = ''): void {
    global $passCount, $failCount;
    if ($condition) {
        $passCount++;
        echo " [OK] {$desc}\n";
    } else {
        $failCount++;
        echo " [FALHA] {$desc}" . ($detail ? " -> Detalhes: {$detail}" : '') . "\n";
    }
}

// Inicia sessão mock
$_SESSION = [
    'user_id' => 1,
    'user_role' => 'superadmin',
    'superadmin_logged' => true,
    'csrf_token' => 'mock_token_123'
];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/superadmin/parametros';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

$controller = new \Zyrtex\Controllers\SuperAdmin\SuperAdminController();
$request = new \Zyrtex\Core\Request();

// 1. Testa Controller settings() e renderização de view
try {
    ob_start();
    $controller->settings($request);
    $htmlSettings = ob_get_clean();

    assertCheck("Tela de Parâmetros renderizada com sucesso (HTML gerado)", !empty($htmlSettings));
    assertCheck("Contém aba de Preços & Taxas", str_contains($htmlSettings, 'tab-btn-precos') && str_contains($htmlSettings, 'tab-panel-precos'));
    assertCheck("Contém aba do Mercado Pago Master", str_contains($htmlSettings, 'tab-btn-mercadopago') && str_contains($htmlSettings, 'tab-panel-mercadopago'));
    assertCheck("Contém aba do Google AdSense", str_contains($htmlSettings, 'tab-btn-adsense') && str_contains($htmlSettings, 'tab-panel-adsense'));
    assertCheck("Contém aba da Vitrine & Rodapé", str_contains($htmlSettings, 'tab-btn-vitrine') && str_contains($htmlSettings, 'tab-panel-vitrine'));
    assertCheck("Contém aba de Segurança Master", str_contains($htmlSettings, 'tab-btn-seguranca') && str_contains($htmlSettings, 'tab-panel-seguranca'));
    assertCheck("Contém aba de Atualizações (GitHub)", str_contains($htmlSettings, 'tab-btn-updates') && str_contains($htmlSettings, 'tab-panel-updates'));
    assertCheck("Exibe repositório oficial nyshimura/zyrtex", str_contains($htmlSettings, 'nyshimura/zyrtex'));
    assertCheck("Possui botão para verificar atualizações no GitHub", str_contains($htmlSettings, 'btnCheckUpdates'));
    assertCheck("Contém script vanilla de troca de abas", str_contains($htmlSettings, 'switchSettingsTab'));
} catch (\Throwable $e) {
    assertCheck("Erro ao renderizar tela de Parâmetros: " . $e->getMessage(), false);
}

// 2. Testa renderização do gráfico financeiro (financial)
try {
    ob_start();
    $controller->financial($request);
    $htmlFinancial = ob_get_clean();

    assertCheck("Tela Financeira renderizada com sucesso", !empty($htmlFinancial));
    assertCheck("Gráfico utiliza grid de 6 colunas sem overflow", str_contains($htmlFinancial, 'grid grid-cols-6'));
    assertCheck("Contém overflow-hidden no card e no grid", str_contains($htmlFinancial, 'overflow-hidden'));
    assertCheck("Contém trilha de fundo para as barras", str_contains($htmlFinancial, 'bg-slate-950/90 rounded-xl'));
} catch (\Throwable $e) {
    assertCheck("Erro ao renderizar tela Financeira: " . $e->getMessage(), false);
}

// 3. Testa salvamento de parâmetros
try {
    $_POST = [
        'csrf_token' => 'mock_token_123',
        'annual_domain_price' => '199.90',
        'niche_change_price' => '89.00',
        'slug_change_price' => '19.90',
        'saas_hero_title' => 'Gestão Inteligente Zyrtex Teste',
        'adsense_active' => '1'
    ];
    $postReq = new \Zyrtex\Core\Request();
    
    $keys = [
        'annual_domain_price',
        'niche_change_price',
        'slug_change_price',
        'saas_hero_title',
        'adsense_active'
    ];
    foreach ($keys as $k) {
        $val = $postReq->post($k);
        \Zyrtex\Core\Database::execute(
            "INSERT INTO saas_settings (config_key, config_value) VALUES (:key, :val) 
             ON DUPLICATE KEY UPDATE config_value = :val2",
            ['key' => $k, 'val' => trim((string)$val), 'val2' => trim((string)$val)]
        );
    }

    $savedPrice = \Zyrtex\Core\Database::fetchValue("SELECT config_value FROM saas_settings WHERE config_key = 'annual_domain_price'");
    $savedTitle = \Zyrtex\Core\Database::fetchValue("SELECT config_value FROM saas_settings WHERE config_key = 'saas_hero_title'");

    assertCheck("Preço salvo no banco corretamente: R$ 199.90", $savedPrice === '199.90');
    assertCheck("Título da vitrine salvo no banco corretamente", $savedTitle === 'Gestão Inteligente Zyrtex Teste');

    // Restaura padrão
    \Zyrtex\Core\Database::execute("UPDATE saas_settings SET config_value = '197.00' WHERE config_key = 'annual_domain_price'");
    \Zyrtex\Core\Database::execute("UPDATE saas_settings SET config_value = 'A Gestão Mais Rápida e Sem Travamentos para sua Assistência.' WHERE config_key = 'saas_hero_title'");
} catch (\Throwable $e) {
    assertCheck("Erro ao testar salvamento de parâmetros: " . $e->getMessage(), false);
}

echo "\n--- RESUMO PARCIAL DOS TESTES ---\n";
echo "Total de Sucessos: {$passCount}\n";
echo "Total de Falhas:   {$failCount}\n\n";

echo "--- EXECUTANDO TESTE DO ENDPOINT checkUpdates() ---\n";
// Executa o checkUpdates que encerra o processo via exit
$controller->checkUpdates($request);
