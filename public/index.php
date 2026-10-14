<?php

declare(strict_types=1);

// 1. Carrega Autoloader nativo
require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Session;
use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Tenant;
use Zyrtex\Core\Router;
use Zyrtex\Controllers\Public\HomeController;
use Zyrtex\Controllers\Public\AuthController;
use Zyrtex\Controllers\Public\TrackingController;
use Zyrtex\Controllers\Public\PortalController;
use Zyrtex\Controllers\Public\WebhookController;
use Zyrtex\Controllers\Admin\DashboardController;
use Zyrtex\Controllers\Admin\ClientController;
use Zyrtex\Controllers\Admin\ServiceOrderController;
use Zyrtex\Controllers\Admin\SettingsController;
use Zyrtex\Controllers\Admin\StockController;
use Zyrtex\Controllers\Admin\PosController;
use Zyrtex\Controllers\Admin\TeamController;
use Zyrtex\Controllers\Admin\SupportController;
use Zyrtex\Controllers\SuperAdmin\SuperAdminController;

// 2. Carrega Variáveis de Ambiente
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    Env::load($envFile);
}

// 3. Configura Tratamento de Erros e Timezone
$appConfig = require __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone'] ?? 'America/Sao_Paulo');

if ($appConfig['debug']) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 4. Inicia Sessão Segura
Session::start();

// 5. Normaliza Requisição HTTP e Resolve Multi-Tenant
$request = new Request();
$tenant = Tenant::resolve($request);

// 6. Configura Rotas da Aplicação
$router = new Router();

// --- Rotas Públicas (Landing Page da Loja e Rastreio) ---
$router->get('/', [HomeController::class, 'index']);
$router->get('/termos', [HomeController::class, 'terms']);

// Rastreio da OS e Portal do Cliente (RF15)
$router->get('/rastreio', [TrackingController::class, 'index']);
$router->get('/portal', [PortalController::class, 'index']);
$router->post('/portal/aprovar', [PortalController::class, 'approve']);
$router->post('/rastreio/aprovar-adicional', [TrackingController::class, 'approveAddition']);
$router->post('/rastreio/rejeitar-adicional', [TrackingController::class, 'rejectAddition']);
$router->post('/rastreio/aprovar-orcamento', [TrackingController::class, 'approveBudget']);

// --- Rotas de Autenticação & Onboarding (Fase 2) ---
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);

$router->get('/cadastro', [AuthController::class, 'showRegister']);
$router->post('/cadastro', [AuthController::class, 'register']);

$router->get('/logout', [AuthController::class, 'logout']);
$router->post('/logout', [AuthController::class, 'logout']);

// --- Rotas do Lojista (/admin - Fase 3) ---
$router->get('/admin', [DashboardController::class, 'index']);

// Clientes
$router->get('/admin/clientes', [ClientController::class, 'index']);
$router->get('/admin/clientes/novo', [ClientController::class, 'create']);
$router->post('/admin/clientes', [ClientController::class, 'store']);
$router->get('/admin/clientes/editar', [ClientController::class, 'edit']);
$router->post('/admin/clientes/editar', [ClientController::class, 'update']);
$router->get('/admin/clientes/ver', [ClientController::class, 'show']);

// Ordens de Serviço
$router->get('/admin/os', [ServiceOrderController::class, 'index']);
$router->get('/admin/os/nova', [ServiceOrderController::class, 'create']);
$router->post('/admin/os', [ServiceOrderController::class, 'store']);
$router->get('/admin/os/ver', [ServiceOrderController::class, 'show']);
$router->post('/admin/os/status', [ServiceOrderController::class, 'updateStatus']);
$router->post('/admin/os/diagnostico', [ServiceOrderController::class, 'updateDiagnosis']);
$router->post('/admin/os/peca', [ServiceOrderController::class, 'addPart']);
$router->post('/admin/os/peca/remover', [ServiceOrderController::class, 'removePart']);
$router->post('/admin/os/midia', [ServiceOrderController::class, 'uploadMedia']);
$router->post('/admin/os/midia/remover', [ServiceOrderController::class, 'deleteMedia']);
$router->post('/admin/os/ia-simplificar', [ServiceOrderController::class, 'simplifyDiagnosis']);
$router->get('/admin/os/ia-remarketing', [ServiceOrderController::class, 'predictiveRemarketing']);
$router->post('/admin/os/ia-remarketing', [ServiceOrderController::class, 'predictiveRemarketing']);
$router->get('/admin/os/imprimir', [ServiceOrderController::class, 'print']);
$router->get('/admin/os/imprimir-teste', [ServiceOrderController::class, 'printTest']);

// Equipe & Permissões Granulares (RF03 / Fase 5)
$router->get('/admin/equipe', [TeamController::class, 'index']);
$router->post('/admin/equipe', [TeamController::class, 'store']);
$router->post('/admin/equipe/editar', [TeamController::class, 'update']);
$router->post('/admin/equipe/toggle', [TeamController::class, 'toggle']);

// Peças & Estoque
$router->get('/admin/estoque', [StockController::class, 'index']);
$router->post('/admin/estoque', [StockController::class, 'store']);
$router->post('/admin/estoque/editar', [StockController::class, 'update']);
$router->post('/admin/estoque/excluir', [StockController::class, 'delete']);

// PDV Totem Fast-Food Touch Screen (RF04)
$router->get('/admin/pdv', [PosController::class, 'index']);
$router->post('/admin/pdv/adicionar', [PosController::class, 'addItem']);
$router->post('/admin/pdv/quantidade', [PosController::class, 'updateQty']);
$router->post('/admin/pdv/remover', [PosController::class, 'removeItem']);
$router->post('/admin/pdv/limpar', [PosController::class, 'clearCart']);
$router->post('/admin/pdv/finalizar', [PosController::class, 'checkout']);
$router->get('/admin/pdv/comprovante', [PosController::class, 'receipt']);

// Central de Configurações
$router->get('/admin/configuracoes', [SettingsController::class, 'index']);
$router->post('/admin/configuracoes/empresa', [SettingsController::class, 'updateCompany']);
$router->post('/admin/configuracoes/pagamentos', [SettingsController::class, 'updatePayments']);
$router->post('/admin/configuracoes/impressao', [SettingsController::class, 'updatePrinter']);
$router->post('/admin/configuracoes/ia', [SettingsController::class, 'updateAI']);
$router->post('/admin/configuracoes/drive', [SettingsController::class, 'updateGoogleDrive']);
$router->post('/admin/configuracoes/endereco', [SettingsController::class, 'updateAddress']);
$router->post('/admin/configuracoes/vitrine', [SettingsController::class, 'updatePublicPage']);
$router->post('/admin/configuracoes/upgrade-premium', [SettingsController::class, 'upgradePremium']);
$router->post('/admin/configuracoes/gerar-taxa-slug', [SettingsController::class, 'generateSlugFeeOrder']);
$router->get('/admin/configuracoes/status-taxa-slug', [SettingsController::class, 'checkSlugFeeStatus']);
$router->post('/admin/configuracoes/estornar-plano', [SettingsController::class, 'refundSubscription']);

// Suporte Técnico & Chamados do Lojista (/admin/suporte)
$router->get('/admin/suporte', [SupportController::class, 'index']);
$router->post('/admin/suporte/novo', [SupportController::class, 'store']);
$router->post('/admin/suporte/responder', [SupportController::class, 'reply']);

// Webhook Oficial Mercado Pago do SaaS (RF02 / Monetização Automatizada)
$router->post('/webhook/saas', [WebhookController::class, 'handleSaaS']);
$router->get('/webhook/saas', [WebhookController::class, 'handleSaaS']);
$router->post('/webhook/mercadopago', [WebhookController::class, 'handleSaaS']);
$router->get('/webhook/mercadopago', [WebhookController::class, 'handleSaaS']);
$router->post('/webhook/saas/simular', [WebhookController::class, 'simulate']);

// --- Rotas do Super Admin (Slug customizável via saas_settings: superadmin_slug) ---
$saSlug = SuperAdminController::slug();

$router->get('/' . $saSlug . '/login', [SuperAdminController::class, 'showLogin']);
$router->post('/' . $saSlug . '/login', [SuperAdminController::class, 'login']);
$router->get('/' . $saSlug . '/logout', [SuperAdminController::class, 'logout']);
$router->post('/' . $saSlug . '/logout', [SuperAdminController::class, 'logout']);

$router->get('/' . $saSlug, [SuperAdminController::class, 'dashboard']);

// Gestão de Oficinas (Tenants)
$router->get('/' . $saSlug . '/tenants', [SuperAdminController::class, 'tenants']);
$router->post('/' . $saSlug . '/tenants/status', [SuperAdminController::class, 'toggleTenantStatus']);
$router->post('/' . $saSlug . '/tenants/premium', [SuperAdminController::class, 'toggleTenantPremium']);

// Shadow Login (Impersonation RF)
$router->get('/' . $saSlug . '/shadow-login', [SuperAdminController::class, 'shadowLogin']);
$router->post('/' . $saSlug . '/shadow-login', [SuperAdminController::class, 'shadowLogin']);
$router->get('/' . $saSlug . '/sair-shadow', [SuperAdminController::class, 'exitShadow']);

// Parâmetros, Preços e Monetização
$router->get('/' . $saSlug . '/parametros', [SuperAdminController::class, 'settings']);
$router->post('/' . $saSlug . '/parametros', [SuperAdminController::class, 'updateSettings']);
$router->get('/' . $saSlug . '/verificar-atualizacao', [SuperAdminController::class, 'checkUpdates']);
$router->post('/' . $saSlug . '/verificar-atualizacao', [SuperAdminController::class, 'checkUpdates']);

// Central de Avisos Globais (Broadcast)
$router->get('/' . $saSlug . '/broadcast', [SuperAdminController::class, 'broadcast']);
$router->post('/' . $saSlug . '/broadcast', [SuperAdminController::class, 'updateBroadcast']);

// Central de Chamados & Suporte
$router->get('/' . $saSlug . '/tickets', [SuperAdminController::class, 'tickets']);
$router->post('/' . $saSlug . '/tickets/responder', [SuperAdminController::class, 'replyTicket']);

// Painel Financeiro & Métricas MRR
$router->get('/' . $saSlug . '/financeiro', [SuperAdminController::class, 'financial']);
$router->get('/' . $saSlug . '/financeiro/exportar', [SuperAdminController::class, 'exportFinancialCsv']);

// 7. Despacha Requisição
try {
    $router->dispatch($request);
} catch (\Throwable $e) {
    if ($appConfig['debug']) {
        http_response_code(500);
        echo "<div style='font-family:sans-serif; padding:30px; background:#fff1f2; color:#9f1239; border:2px solid #e11d48; border-radius:10px; margin:20px;'>";
        echo "<h2>Erro 500 no Zyrtex (Modo Debug Ativo)</h2>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . " na linha " . $e->getLine() . "</p>";
        echo "<pre style='background:#ffffff; padding:15px; border-radius:6px; overflow:auto;'>" . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . "</pre>";
        echo "</div>";
    } else {
        http_response_code(500);
        \Zyrtex\Core\View::render('errors/500', [], 'public');
    }
}
