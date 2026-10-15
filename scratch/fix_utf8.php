<?php
require_once __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$tenant = \Zyrtex\Core\Database::fetchOne("SELECT id, business_name, slogan FROM tenants WHERE id = 1");
echo "Antes:\n";
var_dump($tenant);

// Atualiza para o texto correto com acentos UTF-8
\Zyrtex\Core\Database::execute(
    "UPDATE tenants 
     SET business_name = :bname, 
         slogan = :slogan
     WHERE id = 1",
    [
        'bname' => 'Zyrtex Smart Fix Assistência',
        'slogan' => 'Especialistas em reparos rápidos com peças originais e prontuário digital'
    ]
);

$user = \Zyrtex\Core\Database::fetchOne("SELECT id, name FROM users WHERE id = 1");
\Zyrtex\Core\Database::execute(
    "UPDATE users 
     SET name = 'Carlos Silva (Dono da Oficina)' 
     WHERE id = 1"
);

// Atualiza também os produtos se houver algum com caractere zoado
\Zyrtex\Core\Database::execute("UPDATE products_pos SET name = 'Mão de Obra de Troca de Tela' WHERE id = 4");
\Zyrtex\Core\Database::execute("UPDATE products_pos SET name = 'Desoxidação e Limpeza Química' WHERE id = 5");

$tenantAfter = \Zyrtex\Core\Database::fetchOne("SELECT id, business_name, slogan FROM tenants WHERE id = 1");
echo "\nDepois:\n";
var_dump($tenantAfter);
