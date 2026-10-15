<?php
require_once __DIR__ . '/../src/autoload.php';
Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$clients = Zyrtex\Core\Database::fetchAll("SELECT * FROM clients WHERE name LIKE :name", ['name' => '%Mariana%']);
echo "CLIENTS FOUND: " . count($clients) . "\n";
foreach ($clients as $c) {
    echo json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

$columns = Zyrtex\Core\Database::fetchAll("SHOW COLUMNS FROM clients");
echo "\nCOLUMNS IN CLIENTS TABLE:\n";
foreach ($columns as $col) {
    echo "- {$col['Field']} ({$col['Type']})\n";
}
