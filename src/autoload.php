<?php

spl_autoload_register(function ($class) {
    $prefix = 'Zyrtex\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

if (!function_exists('asset')) {
    function asset(string $path = ''): string {
        return \Zyrtex\Core\Response::asset($path);
    }
}
