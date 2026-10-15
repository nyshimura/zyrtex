<?php

declare(strict_types=1);

/**
 * Migração: Módulo de Envio de E-mails, Templates Personalizáveis e 2FA Steam Guard
 */

use Zyrtex\Core\Database;

// 1. Tabela de Templates de E-mail
Database::execute("
    CREATE TABLE IF NOT EXISTS `email_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `slug` VARCHAR(60) NOT NULL UNIQUE,
        `name` VARCHAR(150) NOT NULL,
        `description` VARCHAR(255) NULL,
        `subject` VARCHAR(200) NOT NULL,
        `blocks_json` JSON NOT NULL,
        `body_html` LONGTEXT NOT NULL,
        `available_placeholders_json` JSON NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Tabela email_templates verificada/criada com sucesso.\n";

// 2. Colunas em users para 2FA Steam Guard
try {
    Database::execute("ALTER TABLE `users` ADD COLUMN `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `avatar_url`");
    echo "Coluna two_factor_enabled adicionada a users.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

try {
    Database::execute("ALTER TABLE `users` ADD COLUMN `two_factor_code_hash` VARCHAR(64) NULL AFTER `two_factor_enabled`");
    echo "Coluna two_factor_code_hash adicionada a users.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

try {
    Database::execute("ALTER TABLE `users` ADD COLUMN `two_factor_expires_at` DATETIME NULL AFTER `two_factor_code_hash`");
    echo "Coluna two_factor_expires_at adicionada a users.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

// 3. Colunas em clients para Ativação de Conta e Portal
try {
    Database::execute("ALTER TABLE `clients` ADD COLUMN `status` ENUM('pending_activation', 'active') NOT NULL DEFAULT 'active' AFTER `notes`");
    echo "Coluna status adicionada a clients.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

try {
    Database::execute("ALTER TABLE `clients` ADD COLUMN `activation_token_hash` VARCHAR(64) NULL AFTER `status`");
    echo "Coluna activation_token_hash adicionada a clients.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

try {
    Database::execute("ALTER TABLE `clients` ADD COLUMN `activation_expires_at` DATETIME NULL AFTER `activation_token_hash`");
    echo "Coluna activation_expires_at adicionada a clients.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

try {
    Database::execute("ALTER TABLE `clients` ADD COLUMN `activated_at` DATETIME NULL AFTER `activation_expires_at`");
    echo "Coluna activated_at adicionada a clients.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

try {
    Database::execute("ALTER TABLE `clients` ADD COLUMN `password_hash` VARCHAR(255) NULL AFTER `activated_at`");
    echo "Coluna password_hash adicionada a clients.\n";
} catch (\Throwable $e) {
    // Coluna já existe
}

// 4. Seed de Templates Iniciais se não existirem
$initialTemplates = [
    [
        'slug' => 'client_activation',
        'name' => 'Ativação de Conta do Cliente',
        'description' => 'Disparado automaticamente quando um novo cliente com e-mail é cadastrado pelo lojista.',
        'subject' => 'Ative sua conta no Portal do Cliente - {{shop_name}}',
        'available_placeholders_json' => json_encode([
            'client_name' => 'Nome completo do cliente',
            'client_email' => 'E-mail do cliente',
            'activation_url' => 'Link seguro de ativação com token',
            'shop_name' => 'Nome da assistência técnica / oficina',
            'shop_phone' => 'Telefone/WhatsApp da oficina',
            'current_year' => 'Ano atual (ex: 2026)',
            'app_url' => 'URL do sistema Zyrtex'
        ], JSON_UNESCAPED_UNICODE),
        'blocks_json' => json_encode([
            [
                'id' => 'blk_header_1',
                'type' => 'header',
                'title' => '{{shop_name}}',
                'subtitle' => 'Portal de Atendimento & Rastreio de Equipamentos',
                'bgColor' => '#4f46e5',
                'textColor' => '#ffffff'
            ],
            [
                'id' => 'blk_text_1',
                'type' => 'text',
                'heading' => 'Olá, {{client_name}}! Seja bem-vindo(a).',
                'content' => 'Seu cadastro na <strong>{{shop_name}}</strong> foi realizado com sucesso! Para sua conveniência e total transparência, disponibilizamos o nosso Portal do Cliente exclusivo para você acompanhar o status dos seus aparelhos, laudos de bancada e orçamentos em tempo real.',
                'align' => 'left'
            ],
            [
                'id' => 'blk_box_1',
                'type' => 'details_box',
                'title' => 'O que você pode fazer no Portal:',
                'items' => [
                    '⚡ Aprovar ou recusar orçamentos em 1 clique do seu celular',
                    '📸 Visualizar fotos do prontuário da bancada e peças substituídas',
                    '💳 Pagar serviços com Pix Copia e Cola instantâneo',
                    '📜 Consultar termo de garantia e histórico completo de serviços'
                ]
            ],
            [
                'id' => 'blk_button_1',
                'type' => 'button',
                'text' => 'Ativar Minha Conta no Portal &rarr;',
                'url' => '{{activation_url}}',
                'bgColor' => '#10b981',
                'textColor' => '#ffffff',
                'align' => 'center'
            ],
            [
                'id' => 'blk_divider_1',
                'type' => 'divider'
            ],
            [
                'id' => 'blk_footer_1',
                'type' => 'footer',
                'text' => 'Se você não solicitou este cadastro na {{shop_name}}, por favor desconsidere este e-mail.<br>Em caso de dúvidas, fale conosco pelo WhatsApp: {{shop_phone}}.<br>&copy; {{current_year}} {{shop_name}} &bull; Plataforma Zyrtex SaaS.'
            ]
        ], JSON_UNESCAPED_UNICODE),
        'body_html' => ''
    ],
    [
        'slug' => 'lojista_2fa_steam',
        'name' => 'Token de Segurança 2FA (Estilo Steam Guard)',
        'description' => 'Disparado no momento do login quando o lojista ou operador possui autenticação de dois fatores ativa.',
        'subject' => '🛡️ {{token_2fa}} - Seu Código de Acesso Zyrtex Guard',
        'available_placeholders_json' => json_encode([
            'user_name' => 'Nome do lojista ou funcionário',
            'user_email' => 'E-mail da conta',
            'token_2fa' => 'Código de segurança de 5 caracteres estilo Steam Guard',
            'shop_name' => 'Nome da oficina do lojista',
            'ip_address' => 'Endereço IP que solicitou o login',
            'date_time' => 'Data e hora da tentativa',
            'current_year' => 'Ano atual (ex: 2026)'
        ], JSON_UNESCAPED_UNICODE),
        'blocks_json' => json_encode([
            [
                'id' => 'blk_header_2',
                'type' => 'header',
                'title' => '🛡️ ZYRTEX GUARD',
                'subtitle' => 'Autenticação em Dois Fatores &bull; Segurança Avançada',
                'bgColor' => '#0f172a',
                'textColor' => '#38bdf8'
            ],
            [
                'id' => 'blk_text_2',
                'type' => 'text',
                'heading' => 'Olá, {{user_name}}!',
                'content' => 'Detectamos uma tentativa de login na sua conta da oficina <strong>{{shop_name}}</strong>. Para prosseguir com segurança máxima, informe o código do Zyrtex Guard abaixo na tela de login:',
                'align' => 'left'
            ],
            [
                'id' => 'blk_token_1',
                'type' => 'token_box',
                'label' => 'SEU CÓDIGO DE ACESSO ZYRTEX GUARD:',
                'token' => '{{token_2fa}}',
                'hint' => 'Este código é válido por 10 minutos. Nunca o compartilhe com ninguém.',
                'bgColor' => '#1e293b',
                'textColor' => '#38bdf8',
                'borderColor' => '#0284c7'
            ],
            [
                'id' => 'blk_details_2',
                'type' => 'details_box',
                'title' => 'Detalhes da Solicitação de Acesso:',
                'items' => [
                    '📍 Endereço IP: {{ip_address}}',
                    '⏰ Data e Horário: {{date_time}}',
                    '🏢 Oficina: {{shop_name}}'
                ]
            ],
            [
                'id' => 'blk_divider_2',
                'type' => 'divider'
            ],
            [
                'id' => 'blk_footer_2',
                'type' => 'footer',
                'text' => 'Se você não reconhece esta tentativa de acesso, altere sua senha imediatamente na central de configurações da oficina.<br>&copy; {{current_year}} Zyrtex SaaS Security Shield.'
            ]
        ], JSON_UNESCAPED_UNICODE),
        'body_html' => ''
    ]
];

foreach ($initialTemplates as $tpl) {
    $exists = Database::fetchOne("SELECT id FROM email_templates WHERE slug = :s LIMIT 1", ['s' => $tpl['slug']]);
    if (!$exists) {
        Database::execute("
            INSERT INTO email_templates (slug, name, description, subject, blocks_json, body_html, available_placeholders_json)
            VALUES (:slug, :name, :description, :subject, :blocks_json, :body_html, :placeholders)
        ", [
            'slug' => $tpl['slug'],
            'name' => $tpl['name'],
            'description' => $tpl['description'],
            'subject' => $tpl['subject'],
            'blocks_json' => $tpl['blocks_json'],
            'body_html' => $tpl['body_html'],
            'placeholders' => $tpl['available_placeholders_json']
        ]);
        echo "Template '{$tpl['slug']}' criado com sucesso.\n";
    }
}
