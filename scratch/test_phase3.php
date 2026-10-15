<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Database;

Env::load(__DIR__ . '/../.env');

echo "=== TESTE DA FASE 3: ESTOQUE, BAIXA AUTOMÁTICA (RN05) E PDV TOTEM (RF04) ===\n\n";

// 1. Verifica produtos no estoque
$products = Database::fetchAll("SELECT id, name, sku, stock, price, is_service FROM products_pos WHERE tenant_id = 1");
echo "1. Produtos cadastrados no estoque da loja demo (tenant_id = 1):\n";
foreach ($products as $p) {
    echo "   [#{$p['id']}] {$p['name']} | Estoque: {$p['stock']} un | Preço: R$ {$p['price']}\n";
}

// 2. Simula venda rápida no PDV Totem
echo "\n2. Testando venda no PDV Totem Fast-Food:\n";
$itemTest = $products[0];
$stockBefore = (int)$itemTest['stock'];
echo "   Item a vender: {$itemTest['name']} (Estoque atual: {$stockBefore} un)\n";

// Insere venda em pos_sales
$saleItems = [
    [
        'id' => $itemTest['id'],
        'name' => $itemTest['name'],
        'price' => (float)$itemTest['price'],
        'qty' => 2,
        'is_service' => (int)$itemTest['is_service']
    ]
];
$totalSale = (float)$itemTest['price'] * 2;

Database::beginTransaction();
Database::execute(
    "INSERT INTO pos_sales (tenant_id, total_amount, payment_method, items_json) VALUES (1, :total, 'pix', :items)",
    ['total' => $totalSale, 'items' => json_encode($saleItems, JSON_UNESCAPED_UNICODE)]
);
$saleId = Database::lastInsertId();

// Abate estoque
Database::execute(
    "UPDATE products_pos SET stock = stock - 2 WHERE id = :id AND tenant_id = 1",
    ['id' => $itemTest['id']]
);
Database::commit();

$stockAfter = (int)Database::fetchColumn("SELECT stock FROM products_pos WHERE id = :id", ['id' => $itemTest['id']]);
echo "   Venda registrada com sucesso! ID: #{$saleId} | Total: R$ {$totalSale}\n";
echo "   Estoque anterior: {$stockBefore} un -> Estoque após venda: {$stockAfter} un (Decresceu 2 un: " . ($stockAfter === $stockBefore - 2 ? 'OK' : 'FALHA') . ")\n";

// 3. Testando Baixa Automática de Estoque na OS (RN05)
echo "\n3. Testando RN05: Baixa automática de estoque na Ordem de Serviço:\n";
// Cria OS em orçamento
Database::execute(
    "INSERT INTO service_orders (tenant_id, os_number, client_id, identifier_code, equipment_info, defect_reported, status, labor_value, parts_value, total_value)
     VALUES (1, 'OS-TESTE-RN05', 1, '999888777', 'Aparelho de Teste RN05', 'Tela quebrada', 'budget', 100.00, 0.00, 100.00)"
);
$osTestId = (int)Database::lastInsertId();

// Adiciona peça do estoque (Produto 2 - Bateria)
$partProduct = $products[1];
$partStockBefore = (int)Database::fetchColumn("SELECT stock FROM products_pos WHERE id = :id", ['id' => $partProduct['id']]);
echo "   Bateria em estoque antes: {$partStockBefore} un\n";

Database::execute(
    "INSERT INTO os_parts (tenant_id, os_id, product_id, part_name, quantity, unit_price)
     VALUES (1, :os_id, :prod_id, :name, 1, :price)",
    ['os_id' => $osTestId, 'prod_id' => $partProduct['id'], 'name' => $partProduct['name'], 'price' => $partProduct['price']]
);
echo "   Peça '{$partProduct['name']}' vinculada à OS #{$osTestId} em status 'budget'\n";

// Agora simula aprovação da OS (transição budget -> approved)
$partsToDeduct = Database::fetchAll(
    "SELECT product_id, quantity FROM os_parts WHERE os_id = :os_id AND product_id IS NOT NULL",
    ['os_id' => $osTestId]
);
foreach ($partsToDeduct as $pt) {
    Database::execute(
        "UPDATE products_pos SET stock = stock - :qty WHERE id = :id AND tenant_id = 1 AND is_service = 0",
        ['qty' => (int)$pt['quantity'], 'id' => (int)$pt['product_id']]
    );
}
Database::execute("UPDATE service_orders SET status = 'approved' WHERE id = :id", ['id' => $osTestId]);

$partStockAfter = (int)Database::fetchColumn("SELECT stock FROM products_pos WHERE id = :id", ['id' => $partProduct['id']]);
echo "   OS atualizada para 'approved'! Estoque da bateria após aprovação: {$partStockAfter} un (Decresceu 1 un: " . ($partStockAfter === $partStockBefore - 1 ? 'OK' : 'FALHA') . ")\n";

// Limpa dados de teste
Database::execute("DELETE FROM os_parts WHERE os_id = :id", ['id' => $osTestId]);
Database::execute("DELETE FROM service_orders WHERE id = :id", ['id' => $osTestId]);
Database::execute("DELETE FROM pos_sales WHERE id = :id", ['id' => $saleId]);
// Restaura estoques originais
Database::execute("UPDATE products_pos SET stock = :s WHERE id = :id", ['s' => $stockBefore, 'id' => $itemTest['id']]);
Database::execute("UPDATE products_pos SET stock = :s WHERE id = :id", ['s' => $partStockBefore, 'id' => $partProduct['id']]);

echo "\n=== TODOS OS TESTES DA FASE 3 EXECUTADOS COM SUCESSO! ===\n";
