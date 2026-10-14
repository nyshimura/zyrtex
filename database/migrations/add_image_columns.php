<?php

$db = new PDO('mysql:host=localhost;dbname=zyrtex_db;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. products_pos: image_path
try {
    $db->exec("ALTER TABLE `products_pos` ADD COLUMN `image_path` VARCHAR(255) NULL AFTER `is_service`");
    echo "Coluna image_path adicionada a products_pos com sucesso.\n";
} catch (Exception $e) {
    echo "products_pos: " . $e->getMessage() . "\n";
}

// 2. users: avatar_url
try {
    $db->exec("ALTER TABLE `users` ADD COLUMN `avatar_url` VARCHAR(255) NULL AFTER `permissions_json`");
    echo "Coluna avatar_url adicionada a users com sucesso.\n";
} catch (Exception $e) {
    echo "users: " . $e->getMessage() . "\n";
}

// 3. os_media: file_path
try {
    $db->exec("ALTER TABLE `os_media` ADD COLUMN `file_path` VARCHAR(255) NULL AFTER `file_name`");
    echo "Coluna file_path adicionada a os_media com sucesso.\n";
} catch (Exception $e) {
    echo "os_media: " . $e->getMessage() . "\n";
}
