<?php
require_once __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

// Verifica clients
$clients = \Zyrtex\Core\Database::fetchAll("SELECT id, name, address, notes FROM clients");
foreach ($clients as $c) {
    echo "Client #{$c['id']}: {$c['name']} | Endereço: {$c['address']} | Notas: {$c['notes']}\n";
}

// Verifica service_orders
$orders = \Zyrtex\Core\Database::fetchAll("SELECT id, os_number, equipment_info, defect_reported, technical_diagnosis FROM service_orders");
foreach ($orders as $o) {
    echo "OS #{$o['id']} ({$o['os_number']}): {$o['equipment_info']} | Defeito: {$o['defect_reported']}\n";
}

// Verifica products_pos
$products = \Zyrtex\Core\Database::fetchAll("SELECT id, name, category FROM products_pos");
foreach ($products as $p) {
    echo "Product #{$p['id']}: {$p['name']} | Categoria: {$p['category']}\n";
}
