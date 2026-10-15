<?php
require __DIR__ . '/../src/Core/Env.php';
require __DIR__ . '/../src/Core/Database.php';
require __DIR__ . '/../src/Core/Session.php';
require __DIR__ . '/../src/Core/Tenant.php';
require __DIR__ . '/../src/Core/Request.php';
require __DIR__ . '/../src/Core/Response.php';
require __DIR__ . '/../src/Controllers/Public/PortalController.php';

\Zyrtex\Core\Env::load(__DIR__ . '/../.env');
\Zyrtex\Core\Session::start();
\Zyrtex\Core\Session::set('client_id', 1);

echo "=== TESTE: ENDPOINT CHECK PAYMENT STATUS ===\n\n";

// 1. Ambas pagas
$_GET['os_ids'] = ['1', '3'];
$controller = new \Zyrtex\Controllers\Public\PortalController();

ob_start();
$controller->checkPaymentStatus(new \Zyrtex\Core\Request());
$output = ob_get_clean();

echo "1. Resposta quando ambas estão pagas:\n" . $output . "\n\n";

// 2. Colocar uma OS como pendente
\Zyrtex\Core\Database::execute("UPDATE service_orders SET payment_status = 'pending' WHERE id = 3");

ob_start();
$controller->checkPaymentStatus(new \Zyrtex\Core\Request());
$output2 = ob_get_clean();

echo "2. Resposta quando uma ainda está pendente:\n" . $output2 . "\n\n";

// Restaurar ambas como pending para Mariana testar no portal
\Zyrtex\Core\Database::execute("UPDATE service_orders SET payment_status = 'pending', payment_method = NULL WHERE id IN (1, 3)");
echo "3. OS 1 e 3 restauradas para pending para testes da Mariana no portal.\n";
