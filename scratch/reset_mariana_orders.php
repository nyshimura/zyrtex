<?php
require __DIR__ . '/../src/Core/Env.php';
require __DIR__ . '/../src/Core/Database.php';

\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

\Zyrtex\Core\Database::execute("UPDATE service_orders SET payment_status = 'pending', payment_method = NULL WHERE client_id = 1");

$orders = \Zyrtex\Core\Database::fetchAll("SELECT id, os_number, total_value, payment_status FROM service_orders WHERE client_id = 1");
print_r($orders);
