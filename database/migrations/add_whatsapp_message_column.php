<?php

use Zyrtex\Core\Database;

try {
    Database::execute("ALTER TABLE `tenants` ADD COLUMN `whatsapp_message` TEXT NULL AFTER `whatsapp_number`");
    echo "Coluna whatsapp_message adicionada com sucesso a tenants.\n";
} catch (\Throwable $e) {
    echo "tenants whatsapp_message: " . $e->getMessage() . "\n";
}
