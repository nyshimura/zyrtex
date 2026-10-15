<?php
require_once __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

echo "=== TENANTS & USUÁRIOS (CLIENTES DO SAAS) ===\n";
$users = \Zyrtex\Core\Database::fetchAll(
    'SELECT u.id, u.tenant_id, t.slug, t.business_name, u.name, u.email, u.role 
     FROM users u 
     LEFT JOIN tenants t ON u.tenant_id = t.id'
);
foreach ($users as $u) {
    echo "ID: {$u['id']} | Tenant: {$u['slug']} ({$u['business_name']})\n";
    echo "Nome: {$u['name']} | E-mail: {$u['email']} | Cargo: {$u['role']}\n\n";
}

echo "=== CLIENTES DOS CLIENTES (CLIENTES FINAIS DAS OFICINAS) ===\n";
$clients = \Zyrtex\Core\Database::fetchAll(
    'SELECT c.id, c.tenant_id, t.slug as tenant_slug, c.name, c.document, c.phone, c.email 
     FROM clients c 
     LEFT JOIN tenants t ON c.tenant_id = t.id'
);
foreach ($clients as $c) {
    echo "ID: {$c['id']} | Loja: {$c['tenant_slug']} | Nome: {$c['name']}\n";
    echo "CPF/CNPJ: {$c['document']} | WhatsApp/Tel: {$c['phone']} | Email: {$c['email']}\n\n";
}

echo "=== ORDENS DE SERVIÇO ATIVAS ===\n";
$orders = \Zyrtex\Core\Database::fetchAll(
    'SELECT os.id, os.tenant_id, t.slug as tenant_slug, os.os_number, os.identifier_code, os.equipment_info, c.name as client_name 
     FROM service_orders os
     JOIN clients c ON os.client_id = c.id
     JOIN tenants t ON os.tenant_id = t.id'
);
foreach ($orders as $o) {
    echo "OS: {$o['os_number']} | Loja: {$o['tenant_slug']} | Cliente: {$o['client_name']}\n";
    echo "Equipamento: {$o['equipment_info']} | Identificador/IMEI: {$o['identifier_code']}\n\n";
}
