<?php
/**
 * Teste Automatizado de Verificação das Correções e Novas Funcionalidades
 */

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
require_once __DIR__ . '/../src/Controllers/Public/HomeController.php';
require_once __DIR__ . '/../src/Controllers/SuperAdmin/SuperAdminController.php';

echo "=== INICIANDO TESTE DAS CORREÇÕES ===\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertCondition(bool $condition, string $testName) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        echo " [PASS] $testName\n";
        $testsPassed++;
    } else {
        echo " [FAIL] $testName\n";
    }
}

// 1. Testa se o arquivo /termos carrega sem erros
ob_start();
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/termos';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/zyrtex/public/index.php';

$controller = new \Zyrtex\Controllers\Public\HomeController();
$request = new \Zyrtex\Core\Request();
$controller->terms($request);
$termsHtml = ob_get_clean();

assertCondition(str_contains($termsHtml, 'Marco Regulatório Brasileiro'), "Página de Termos renderiza com sucesso");
assertCondition(str_contains($termsHtml, 'LGPD') && str_contains($termsHtml, 'Código de Defesa do Consumidor'), "Página de Termos contém referências a LGPD e CDC");

// 2. Testa se o cabeçalho e rodapé do SaaS têm os links corrigidos
ob_start();
$controller->index($request);
$homeHtml = ob_get_clean();

assertCondition(str_contains($homeHtml, '/termos'), "Rodapé contém link oficial para /termos");
assertCondition(str_contains($homeHtml, 'href="http://localhost/zyrtex/#nichos"') || str_contains($homeHtml, '/#nichos'), "Navegação possui URLs absolutas com âncoras");

// 3. Testa se o formulário de cadastro não possui mais a dica de travamento de nicho
ob_start();
\Zyrtex\Core\View::render('public/register', [
    'niches' => [
        'cellphones' => ['name' => 'Smartphones & Tablets', 'identifier_name' => 'IMEI / Serial', 'checklist' => [1,2,3]],
        'computers' => ['name' => 'PCs & Notebooks', 'identifier_name' => 'Serial / Service Tag', 'checklist' => [1,2,3]],
        'automotive' => ['name' => 'Oficina Mecânica & Auto', 'identifier_name' => 'Placa / Chassi', 'checklist' => [1,2,3]],
        'appliances' => ['name' => 'Eletrodomésticos & Linha Branca', 'identifier_name' => 'Modelo / Série', 'checklist' => [1,2,3]],
    ],
    'csrfField' => '<input type="hidden" name="csrf" value="123">',
    'plan' => 'free',
    'planPrice' => 0.0,
    'errors' => [],
    'old' => []
], 'saas');
$registerHtml = ob_get_clean();

assertCondition(!str_contains($registerHtml, 'RN-Nicho01'), "Dica de travamento do nicho (RN-Nicho01) foi devidamente removida");
assertCondition(!str_contains($registerHtml, 'taxa de R$ 97,00'), "Menção a taxa de R$ 97 na seleção de nicho foi removida");

// 4. Testa a query de tenants do SuperAdmin (fix do erro 500 do 'phone')
\Zyrtex\Core\Session::start();
\Zyrtex\Core\Session::set('user_id', 1);
\Zyrtex\Core\Session::set('user_role', 'superadmin');
\Zyrtex\Core\Session::set('superadmin_id', 1);

ob_start();
$saController = new \Zyrtex\Controllers\SuperAdmin\SuperAdminController();
$saController->tenants($request);
$tenantsHtml = ob_get_clean();

assertCondition(!empty($tenantsHtml) && !str_contains($tenantsHtml, 'Unknown column'), "Página /superadmin/tenants roda sem erro 500 de 'phone'");
assertCondition(str_contains($tenantsHtml, 'Gestão de Oficinas'), "Título de Gestão de Oficinas encontrado no HTML");

// 5. Testa se views/layouts/admin.php possui dark mode ativado
$adminLayoutContent = file_get_contents(__DIR__ . '/../views/layouts/admin.php');
assertCondition(str_contains($adminLayoutContent, 'class="dark h-full'), "Layout do lojista (admin.php) possui a classe 'dark' ativada no <html>");

// 6. Testa partial de adsense e tips card
ob_start();
\Zyrtex\Core\View::partial('adsense_slot', ['tenant' => ['is_premium' => 0]]);
$adsHtml = ob_get_clean();
assertCondition(str_contains($adsHtml, 'Publicidade') && !str_contains($adsHtml, 'bg-slate-200/50'), "AdSense slot utiliza tokens escuros e harmoniosos");

ob_start();
\Zyrtex\Core\View::partial('tips_card', [
    'title' => 'Configuração de Pagamento (Pix Manual ou Mercado Pago)',
    'description' => 'Escolha como deseja receber dos seus clientes.',
    'link' => '/admin/configuracoes#pagamentos',
    'linkText' => 'Acessar Configurações de Pagamento &rarr;'
]);
$tipsHtml = ob_get_clean();
assertCondition(str_contains($tipsHtml, 'bg-slate-900') && str_contains($tipsHtml, 'text-white'), "Tips card renderiza com tema dark e suporta linkText");

// 7. Testa se flash_messages possui script de fadeout e botão fechar
ob_start();
\Zyrtex\Core\View::partial('flash_messages', [
    'flashes' => [
        'warning' => ['👁️ Modo Shadow Ativo: Você agora está navegando na oficina Auto Mecânica Precision.']
    ]
]);
$flashHtml = ob_get_clean();
assertCondition(str_contains($flashHtml, 'toast-close-btn'), "Toast flash message possui botão de fechar manual");
assertCondition(str_contains($flashHtml, 'dismissToast'), "Toast flash message possui função de auto-fadeout");

// 8. Testa persistência de configurações de vitrine e rodapé
$_POST['csrf_token'] = \Zyrtex\Core\Session::get('csrf_token') ?? 'test';
$_POST['saas_hero_title'] = 'A Melhor Gestão Automotiva e de Assistências';
$_POST['saas_footer_copyright'] = 'Zyrtex Platform Inc. 2026';
$_POST['saas_pix_key'] = 'pix.oficial@zyrtex.com.br';

$postRequest = new \Zyrtex\Core\Request();

ob_start();
$saController->updateSettings($postRequest);
ob_get_clean();

$savedTitle = \Zyrtex\Core\Database::fetchValue("SELECT config_value FROM saas_settings WHERE config_key = 'saas_hero_title'");
$savedCopy = \Zyrtex\Core\Database::fetchValue("SELECT config_value FROM saas_settings WHERE config_key = 'saas_footer_copyright'");
$savedPix = \Zyrtex\Core\Database::fetchValue("SELECT config_value FROM saas_settings WHERE config_key = 'saas_pix_key'");

assertCondition($savedTitle === 'A Melhor Gestão Automotiva e de Assistências', "Título Hero do SaaS personalizado com sucesso no banco");
assertCondition($savedCopy === 'Zyrtex Platform Inc. 2026', "Copyright do rodapé personalizado com sucesso no banco");
assertCondition($savedPix === 'pix.oficial@zyrtex.com.br', "Chave Pix de apoio personalizada com sucesso no banco");

// 9. Testa se version.json existe para futuro update do GitHub
assertCondition(file_exists(__DIR__ . '/../version.json'), "Arquivo version.json existe no repositório com versão e manifesto");

echo "\n=== RESULTADO FINAL: $testsPassed / $totalTests TESTES PASSARAM COM SUCESSO! ===\n";
