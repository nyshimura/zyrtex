<?php
require_once __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$cols = \Zyrtex\Core\Database::fetchAll("SHOW COLUMNS FROM os_parts");
$existing = array_column($cols, 'Field');
echo "Colunas atuais em os_parts:\n" . implode(', ', $existing) . "\n";

if (!in_array('user_id', $existing)) {
    \Zyrtex\Core\Database::execute("ALTER TABLE os_parts ADD COLUMN `user_id` INT NULL AFTER `product_id`");
    \Zyrtex\Core\Database::execute("ALTER TABLE os_parts ADD CONSTRAINT `fk_os_parts_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL");
    echo "Coluna 'user_id' adicionada com sucesso!\n";
}

if (!in_array('approval_status', $existing)) {
    \Zyrtex\Core\Database::execute("ALTER TABLE os_parts ADD COLUMN `approval_status` ENUM('approved', 'pending_approval', 'rejected') NOT NULL DEFAULT 'approved' AFTER `unit_price`");
    echo "Coluna 'approval_status' adicionada com sucesso!\n";
}

$colsAfter = \Zyrtex\Core\Database::fetchAll("SHOW COLUMNS FROM os_parts");
$existingAfter = array_column($colsAfter, 'Field');
echo "Colunas finais em os_parts:\n" . implode(', ', $existingAfter) . "\n";
