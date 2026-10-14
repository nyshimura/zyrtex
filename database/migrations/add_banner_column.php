<?php

$db = new PDO('mysql:host=localhost;dbname=zyrtex_db;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $db->exec("ALTER TABLE `tenants` ADD COLUMN `banner_path` VARCHAR(255) NULL AFTER `logo_path`");
    echo "Coluna banner_path adicionada a tenants com sucesso.\n";
} catch (Exception $e) {
    echo "tenants banner_path: " . $e->getMessage() . "\n";
}
