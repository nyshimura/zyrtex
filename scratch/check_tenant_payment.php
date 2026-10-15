<?php
require __DIR__ . '/../src/Core/Env.php';
require __DIR__ . '/../src/Core/Database.php';

\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$t = \Zyrtex\Core\Database::fetchOne('SELECT id, payment_mode, pix_key, pix_key_type, pix_beneficiary, mp_access_token FROM tenants WHERE id = 1');
print_r($t);
