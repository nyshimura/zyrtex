<?php
/**
 * Teste Automatizado de Verificação do Painel Financeiro, Termos e Cookies
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
require_once __DIR__ . '/../src/Controllers/SuperAdmin/SuperAdminController.php';
require_once __DIR__ . '/../src/Controllers/Public/HomeController.php';

echo "=== INICIANDO BATERIA DE TESTES FINANCEIRO & TERMOS ===\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertCheck(bool $condition, string $testName) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        echo " [PASS] $testName\n";
        $testsPassed++;
    } else {
        echo " [FAIL] $testName\n";
    }
}

// 1. Testa Renderização do Painel Financeiro
\Zyrtex\Core\Session::start();
\Zyrtex\Core\Session::set('user_id', 1);
\Zyrtex\Core\Session::set('user_role', 'superadmin');
\Zyrtex\Core\Session::set('superadmin_id', 1);

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/superadmin/financeiro';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/zyrtex/public/index.php';

$saController = new \Zyrtex\Controllers\SuperAdmin\SuperAdminController();
$request = new \Zyrtex\Core\Request();

ob_start();
$saController->financial($request);
$finHtml = ob_get_clean();

assertCheck(str_contains($finHtml, 'Painel Financeiro &amp; Gestão de Receita (MRR)') || str_contains($finHtml, 'Painel Financeiro & Gestão de Receita (MRR)'), "Painel Financeiro renderiza título principal");
assertCheck(str_contains($finHtml, 'MRR Recorrente') && str_contains($finHtml, 'ARR Anualizado'), "Métricas de MRR e ARR presentes");
assertCheck(str_contains($finHtml, 'Evolução de Faturamento Mensal'), "Gráfico de evolução semestral presente");
assertCheck(str_contains($finHtml, 'Origem das Receitas'), "Breakdown de origens de receita presente");
assertCheck(str_contains($finHtml, 'Extrato Detalhado de Assinaturas'), "Tabela de extrato detalhado presente");

// 2. Testa Termos de Uso atualizados com AdSense, Mercado Pago e Cookies
$homeController = new \Zyrtex\Controllers\Public\HomeController();
ob_start();
$homeController->terms($request);
$termsHtml = ob_get_clean();

assertCheck(str_contains($termsHtml, 'Google AdSense') && str_contains($termsHtml, 'cookie DART'), "Termos contêm cláusula formal de Google AdSense e cookie DART");
assertCheck(str_contains($termsHtml, 'Mercado Pago') && str_contains($termsHtml, 'PCI-DSS'), "Termos contêm cláusula de segurança e pagamentos Mercado Pago");
assertCheck(str_contains($termsHtml, 'Política Completa de Cookies'), "Termos contêm seção específica de Política de Cookies");
assertCheck(str_contains($termsHtml, 'Artigo 49 do Código de Defesa do Consumidor'), "Termos contêm garantia de estorno de 7 dias do CDC");

// 3. Testa Checkbox de Aceite no Cadastro
ob_start();
\Zyrtex\Core\View::render('public/register', [
    'niches' => [
        'cellphones' => ['name' => 'Smartphones & Tablets', 'identifier_name' => 'IMEI / Serial', 'checklist' => [1,2,3]],
    ],
    'csrfField' => '<input type="hidden" name="csrf" value="123">',
    'plan' => 'free',
    'planPrice' => 0.0,
    'errors' => [],
    'old' => []
], 'saas');
$registerHtml = ob_get_clean();

assertCheck(str_contains($registerHtml, 'name="terms"') && str_contains($registerHtml, 'required'), "Checkbox de aceite dos termos é obrigatório no formulário");
assertCheck(str_contains($registerHtml, '/termos') && str_contains($registerHtml, 'target="_blank"'), "Link do checkbox aponta diretamente para /termos em nova aba");
assertCheck(str_contains($registerHtml, 'Mercado Pago') && str_contains($registerHtml, 'Google AdSense'), "Texto do checkbox cita explicitamente Mercado Pago e AdSense");

// 4. Testa Banner de Consentimento de Cookies
ob_start();
\Zyrtex\Core\View::partial('cookie_banner');
$bannerHtml = ob_get_clean();

assertCheck(str_contains($bannerHtml, 'zyrtex-cookie-banner'), "Banner de consentimento de cookies renderiza com ID correto");
assertCheck(str_contains($bannerHtml, 'zyrtex_cookies_accepted'), "Script do banner salva consentimento no localStorage");
assertCheck(str_contains($bannerHtml, 'Aceitar Cookies'), "Botão de aceitar cookies presente no banner");

// 5. Testa version.json e README.md
$versionJson = json_decode(file_get_contents(__DIR__ . '/../version.json'), true);
assertCheck($versionJson['github_repo'] === 'nyshimura/zyrtex', "version.json aponta para o repositório correto: nyshimura/zyrtex");
assertCheck(file_exists(__DIR__ . '/../README.md'), "README.md existe na raiz do projeto");

$readmeContent = file_get_contents(__DIR__ . '/../README.md');
assertCheck(str_contains($readmeContent, 'nyshimura/zyrtex'), "README.md possui referências ao repositório nyshimura/zyrtex");
assertCheck(str_contains($readmeContent, 'Multi-Tenant') && str_contains($readmeContent, 'Mercado Pago'), "README.md descreve a arquitetura e integrações");

echo "\n=== RESULTADO FINAL: $testsPassed / $totalTests TESTES PASSARAM COM SUCESSO! ===\n";
