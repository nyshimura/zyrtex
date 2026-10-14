<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Zyrtex',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim($_ENV['APP_URL'] ?? 'http://localhost/zyrtex', '/'),
    'timezone' => 'America/Sao_Paulo',
    'secret' => $_ENV['APP_SECRET'] ?? 'zyrtex_secret_2026',
    'default_tenant' => $_ENV['DEFAULT_TENANT_SLUG'] ?? 'demo-assistencia',
    'superadmin_pass' => $_ENV['SUPERADMIN_MASTER_PASS'] ?? 'zyrtex@master2026',
    'ai_model_default' => $_ENV['HUGGINGFACE_DEFAULT_MODEL'] ?? 'Qwen/Qwen2.5-7B-Instruct',
];
