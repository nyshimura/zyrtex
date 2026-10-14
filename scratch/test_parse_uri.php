<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Request;

$_SERVER['REQUEST_URI'] = '/zyrtex/demo-assistencia';
$_SERVER['SCRIPT_NAME'] = '/zyrtex/public/index.php';
$_SERVER['REQUEST_METHOD'] = 'GET';

$req = new Request();
echo "URI parsed: " . $req->getPath() . "\n";

$_SERVER['REQUEST_URI'] = '/zyrtex/demo-assistencia/rastreio';
$req2 = new Request();
echo "URI parsed 2: " . $req2->getPath() . "\n";
