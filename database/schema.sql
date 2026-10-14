-- ==============================================================================
-- Zyrtex SaaS - Multi-Tenant Architecture Schema (MySQL / MariaDB)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `zyrtex_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `zyrtex_db`;

-- 1. Lojistas (Tenants)
CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `business_name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(80) NOT NULL UNIQUE,
    `default_url` VARCHAR(150) NULL,
    `custom_domain` VARCHAR(150) NULL UNIQUE,
    `niche` ENUM('cellphones', 'computers', 'automotive', 'appliances') NOT NULL DEFAULT 'cellphones',
    `is_premium` TINYINT(1) NOT NULL DEFAULT 0,
    `theme_color` VARCHAR(30) NOT NULL DEFAULT 'indigo',
    `theme_mode` ENUM('auto', 'light', 'dark') NOT NULL DEFAULT 'auto',
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `logo_path` VARCHAR(255) NULL,
    `banner_path` VARCHAR(255) NULL,
    `slogan` VARCHAR(255) NULL,
    `whatsapp_number` VARCHAR(30) NULL,
    `address_json` JSON NULL,
    `opening_hours_json` JSON NULL,
    `promotions_json` JSON NULL,
    `public_metrics_json` JSON NULL,
    `printer_settings_json` JSON NULL,
    `payment_mode` ENUM('manual_pix', 'mercadopago') NOT NULL DEFAULT 'manual_pix',
    `pix_key` VARCHAR(100) NULL,
    `pix_key_type` ENUM('cpf', 'cnpj', 'email', 'phone', 'random') NULL,
    `pix_beneficiary` VARCHAR(150) NULL,
    `mp_public_key` VARCHAR(255) NULL,
    `mp_access_token` VARCHAR(255) NULL,
    `gdrive_folder_id` VARCHAR(150) NULL,
    `gdrive_token_json` JSON NULL,
    `ai_provider` ENUM('huggingface', 'openai', 'groq') NOT NULL DEFAULT 'huggingface',
    `ai_api_key` VARCHAR(255) NULL,
    `expires_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tenant_slug` (`slug`),
    INDEX `idx_tenant_domain` (`custom_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Usuários do Lojista (ACL)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('owner', 'manager', 'technician', 'attendant', 'superadmin') NOT NULL DEFAULT 'owner',
    `permissions_json` JSON NULL,
    `avatar_url` VARCHAR(255) NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_user_email` (`tenant_id`, `email`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Clientes da Oficina
CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `document` VARCHAR(30) NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(150) NULL,
    `address` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_client_tenant` (`tenant_id`),
    INDEX `idx_client_doc` (`tenant_id`, `document`),
    INDEX `idx_client_phone` (`tenant_id`, `phone`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Peças e Produtos de Balcão (Estoque PDV)
CREATE TABLE IF NOT EXISTS `products_pos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `sku` VARCHAR(60) NULL,
    `category` VARCHAR(60) NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cost_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `stock` INT NOT NULL DEFAULT 0,
    `is_service` TINYINT(1) NOT NULL DEFAULT 0,
    `image_path` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_product_tenant` (`tenant_id`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Ordens de Serviço (OS Dinâmica)
CREATE TABLE IF NOT EXISTS `service_orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `os_number` VARCHAR(30) NOT NULL,
    `client_id` INT NOT NULL,
    `identifier_code` VARCHAR(80) NOT NULL,
    `equipment_info` VARCHAR(200) NOT NULL,
    `checklist_json` JSON NULL,
    `defect_reported` TEXT NOT NULL,
    `technical_diagnosis` TEXT NULL,
    `simplified_diagnosis_ai` TEXT NULL,
    `status` ENUM('budget', 'approved', 'in_progress', 'waiting_parts', 'ready', 'delivered', 'cancelled') NOT NULL DEFAULT 'budget',
    `approval_status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `approval_ip` VARCHAR(45) NULL,
    `approval_date` DATETIME NULL,
    `approval_user_agent` TEXT NULL,
    `labor_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `parts_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('pending', 'paid', 'refunded') NOT NULL DEFAULT 'pending',
    `payment_method` ENUM('manual_pix', 'mercadopago', 'cash', 'card') NULL,
    `warranty_days` INT NOT NULL DEFAULT 90,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_tenant_os_number` (`tenant_id`, `os_number`),
    INDEX `idx_os_identifier` (`tenant_id`, `identifier_code`),
    INDEX `idx_os_client` (`tenant_id`, `client_id`),
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Peças Vinculadas à OS (com baixa automática de estoque)
CREATE TABLE IF NOT EXISTS `os_parts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `os_id` INT NOT NULL,
    `product_id` INT NULL,
    `user_id` INT NULL,
    `part_name` VARCHAR(150) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `approval_status` ENUM('approved', 'pending_approval', 'rejected') NOT NULL DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`os_id`) REFERENCES `service_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products_pos`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Mídias e Fotos da OS (BYOS Google Drive + Thumbnail Base64)
CREATE TABLE IF NOT EXISTS `os_media` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `os_id` INT NOT NULL,
    `media_type` VARCHAR(50) NOT NULL,
    `file_name` VARCHAR(150) NOT NULL,
    `file_path` VARCHAR(255) NULL,
    `gdrive_file_id` VARCHAR(150) NULL,
    `thumbnail_base64` MEDIUMTEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`os_id`) REFERENCES `service_orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Vendas Rápidas de Balcão (PDV Fast-Food Totem)
CREATE TABLE IF NOT EXISTS `pos_sales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `payment_method` VARCHAR(30) NOT NULL,
    `items_json` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Assinaturas e Pagamentos do SaaS Zyrtex (Monetização)
CREATE TABLE IF NOT EXISTS `saas_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `plan_type` ENUM('annual_domain', 'niche_change', 'slug_change') NOT NULL,
    `target_value` VARCHAR(255) NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `mp_payment_id` VARCHAR(100) NULL,
    `external_reference` VARCHAR(150) NULL,
    `pix_code` TEXT NULL,
    `pix_qrcode_base64` MEDIUMTEXT NULL,
    `status` ENUM('pending', 'approved', 'cancelled', 'refunded') NOT NULL DEFAULT 'pending',
    `purchased_at` DATETIME NULL,
    `expires_at` DATETIME NULL,
    `is_refunded` TINYINT(1) NOT NULL DEFAULT 0,
    `refunded_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Configurações Globais do SaaS Zyrtex (SuperAdmin)
CREATE TABLE IF NOT EXISTS `saas_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `config_key` VARCHAR(100) NOT NULL UNIQUE,
    `config_value` TEXT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tickets de Suporte Interno
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `status` ENUM('open', 'answered', 'closed') NOT NULL DEFAULT 'open',
    `messages_json` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Logs de Auditoria e Segurança (LGPD)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT NULL,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `details_json` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
