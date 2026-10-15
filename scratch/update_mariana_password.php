<?php
require_once __DIR__ . '/../src/autoload.php';
Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$passHash = password_hash('mariana123', PASSWORD_BCRYPT);

Zyrtex\Core\Database::execute("
    UPDATE clients 
    SET password_hash = :hash,
        status = 'active',
        activated_at = NOW(),
        activation_token_hash = NULL,
        activation_expires_at = NULL
    WHERE id = 1
", ['hash' => $passHash]);

$updated = Zyrtex\Core\Database::fetchOne("SELECT id, name, email, document, status, activated_at, password_hash FROM clients WHERE id = 1");
echo "MARIANA UPDATED:\n" . json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
echo "PASSWORD VERIFY mariana123: " . (password_verify('mariana123', $updated['password_hash']) ? 'TRUE' : 'FALSE') . "\n";
