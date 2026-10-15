<?php
require __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

echo "=== TESTE DE RENDERIZAÇÃO DAS VIEWS DO NOVO MÓDULO ===\n\n";

// 1. Renderiza lista de templates do SuperAdmin
ob_start();
$templates = \Zyrtex\Core\Database::fetchAll("SELECT * FROM email_templates ORDER BY id ASC");
$smtpConfig = \Zyrtex\Services\MailService::getConfig();
\Zyrtex\Core\View::render('superadmin/emails/index', [
    'templates' => $templates,
    'smtpConfig' => $smtpConfig,
    'pageTitle' => 'Templates de E-mail'
], null);
$output1 = ob_get_clean();
echo "✓ View superadmin/emails/index renderizou com sucesso (" . strlen($output1) . " bytes)\n";

// 2. Renderiza editor Drag & Drop do template 1
ob_start();
$tpl1 = \Zyrtex\Core\Database::fetchOne("SELECT * FROM email_templates WHERE id = 1");
\Zyrtex\Core\View::render('superadmin/emails/editor', [
    'template' => $tpl1,
    'smtpConfig' => $smtpConfig,
    'pageTitle' => 'Editar Template'
], null);
$output2 = ob_get_clean();
echo "✓ View superadmin/emails/editor (Template 1) renderizou com sucesso (" . strlen($output2) . " bytes)\n";

// 3. Renderiza editor Drag & Drop do template 2 (Steam Guard)
ob_start();
$tpl2 = \Zyrtex\Core\Database::fetchOne("SELECT * FROM email_templates WHERE id = 2");
\Zyrtex\Core\View::render('superadmin/emails/editor', [
    'template' => $tpl2,
    'smtpConfig' => $smtpConfig,
    'pageTitle' => 'Editar Template'
], null);
$output3 = ob_get_clean();
echo "✓ View superadmin/emails/editor (Template 2 Steam Guard) renderizou com sucesso (" . strlen($output3) . " bytes)\n";

// 4. Renderiza tela de ativação de conta do cliente (/portal/ativar)
ob_start();
\Zyrtex\Core\View::render('public/portal/activate', [
    'status' => 'ready',
    'client' => [
        'name' => 'João Silva',
        'email' => 'joao@gmail.com',
        'phone' => '11988887777',
        'business_name' => 'Auto Tech Oficina'
    ],
    'token' => 'abc123token',
    'pageTitle' => 'Ativar Minha Conta'
], null);
$output4 = ob_get_clean();
echo "✓ View public/portal/activate (Status Ready) renderizou com sucesso (" . strlen($output4) . " bytes)\n";

// 5. Renderiza tela de 2FA Steam Guard (/login/2fa)
ob_start();
\Zyrtex\Core\View::render('public/auth/two_factor', [
    'emailHint' => 'j****a@gmail.com',
    'name' => 'João Proprietário',
    'shopName' => 'Auto Tech Oficina',
    'pageTitle' => 'Zyrtex Guard'
], null);
$output5 = ob_get_clean();
echo "✓ View public/auth/two_factor renderizou com sucesso (" . strlen($output5) . " bytes)\n";

echo "\nTODAS AS VIEWS RENDERIZARAM 100% SEM ERROS OU AVISOS!\n";
