<?php
require_once __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$t = \Zyrtex\Core\Database::fetchOne("SELECT promotions_json, address_json, opening_hours_json FROM tenants WHERE id = 1");
echo "Promotions:\n" . $t['promotions_json'] . "\n\n";
echo "Address:\n" . $t['address_json'] . "\n\n";
echo "Hours:\n" . $t['opening_hours_json'] . "\n\n";
