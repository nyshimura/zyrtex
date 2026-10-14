-- ==============================================================================
-- Zyrtex SaaS - Seeds & Dados Iniciais (MySQL / MariaDB)
-- ==============================================================================

USE `zyrtex_db`;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 1. Configurações Globais do SaaS Zyrtex
INSERT INTO `saas_settings` (`config_key`, `config_value`) VALUES
('annual_domain_price', '197.00'),
('niche_change_price', '97.00'),
('mercadopago_access_token', ''),
('mercadopago_public_key', ''),
('adsense_active', '1'),
('adsense_script', ''),
('maintenance_mode', '0')
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

-- 2. Tenant Padrão de Demonstração (Oficina Modelo)
INSERT INTO `tenants` (
    `id`, `business_name`, `slug`, `default_url`, `custom_domain`, `niche`, `is_premium`, 
    `theme_color`, `active`, `slogan`, `whatsapp_number`, `address_json`, `opening_hours_json`, 
    `promotions_json`, `printer_settings_json`, `payment_mode`, `pix_key`, `pix_key_type`, `pix_beneficiary`,
    `ai_provider`, `ai_api_key`
) VALUES (
    1,
    'Zyrtex Smart Fix Assistência',
    'demo-assistencia',
    'demo-assistencia.zyrtex.com.br',
    NULL,
    'cellphones',
    0,
    'indigo',
    1,
    'Especialistas em reparos rápidos com peças originais e prontuário digital',
    '11999998888',
    '{"street": "Av. Paulista, 1000 - Loja 4", "city": "São Paulo - SP", "maps_link": "https://maps.google.com"}',
    '{"weekdays": "Segunda a Sexta: 08:30 às 18:30", "saturday": "Sábado: 09:00 às 13:00"}',
    '[{"title": "Troca de Tela com Película 3D Grátis", "description": "Display original com aplicação de película protetora e garantia legal de 90 dias.", "price": "199,90"}, {"title": "Banho Químico Ultrassônico", "description": "Desoxidação profunda de placa para celulares molhados ou com umidade.", "price": "130,00"}, {"title": "Troca de Bateria com Saúde 100%", "description": "Bateria homologada com selo de procedência e calibração de ciclo.", "price": "149,90"}]',
    '{"default_format": "80mm", "show_logo": 1, "show_pix": 1, "show_qr": 1, "show_terms": 1, "font_size": "normal"}',
    'manual_pix',
    '11999998888',
    'phone',
    'Zyrtex Smart Fix LTDA',
    'huggingface',
    ''
) ON DUPLICATE KEY UPDATE `business_name` = VALUES(`business_name`);

-- 3. Usuário Administrador da Loja Modelo
-- Senha padrão: admin123
INSERT INTO `users` (`id`, `tenant_id`, `name`, `email`, `password_hash`, `role`, `active`) VALUES
(1, 1, 'Carlos Silva (Dono da Oficina)', 'admin@oficina.com.br', '$2y$10$uraZRdppaCR9S9sDZoKfxe6R8CytO.ecAgM7R9tQeMdAw/oOhlXAS', 'owner', 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 4. Clientes de Exemplo
INSERT INTO `clients` (`id`, `tenant_id`, `name`, `document`, `phone`, `email`, `address`, `notes`) VALUES
(1, 1, 'Mariana Oliveira', '12345678901', '11988887777', 'mariana@email.com', 'Rua das Flores, 123 - Apto 42', 'Cliente prefere contato via WhatsApp'),
(2, 1, 'Roberto Santos', '98765432100', '11977776666', 'roberto@email.com', 'Rua Augusta, 500', 'Indicado por Carlos')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 5. Peças e Produtos no Estoque do PDV
INSERT INTO `products_pos` (`id`, `tenant_id`, `name`, `sku`, `category`, `price`, `cost_price`, `stock`, `is_service`) VALUES
(1, 1, 'Tela Display iPhone 11 Original China', 'TEL-IP11', 'Telas', 189.90, 85.00, 8, 0),
(2, 1, 'Bateria iPhone 11 Homologada', 'BAT-IP11', 'Baterias', 129.90, 45.00, 12, 0),
(3, 1, 'Conector de Carga Tipo C Samsung A54', 'CON-A54', 'Conectores', 49.90, 12.00, 15, 0),
(4, 1, 'Mão de Obra de Troca de Tela', 'SRV-TELA', 'Serviços', 90.00, 0.00, 0, 1),
(5, 1, 'Desoxidação e Limpeza Química', 'SRV-DESOX', 'Serviços', 130.00, 15.00, 0, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 6. Ordem de Serviço de Demonstração (Prontuário Antifraude Ativo)
INSERT INTO `service_orders` (
    `id`, `tenant_id`, `os_number`, `client_id`, `identifier_code`, `equipment_info`,
    `checklist_json`, `defect_reported`, `technical_diagnosis`, `simplified_diagnosis_ai`,
    `status`, `approval_status`, `labor_value`, `parts_value`, `total_value`, `payment_status`,
    `payment_method`, `warranty_days`
) VALUES (
    1, 1, 'OS-2026-001', 1, '356987123456789', 'Apple iPhone 11 128GB Preto',
    '{"powers_on": "Sim", "screen_touch": "Não (Touch falhando e vidro trincado)", "charging": "Sim", "cameras": "OK", "biometrics": "Face ID OK", "housing_condition": "Pequenas marcas de uso no aro"}',
    'Aparelho caiu no chão. Tela trincou e metade do touch parou de responder.',
    'Constatado rompimento das trilhas do flex do digitalizador. Necessária substituição completa do módulo frontal.',
    'Olá Mariana! Identificamos que a queda danificou a peça responsável pelo toque na tela. Faremos a troca da tela completa por uma nova com garantia de 90 dias.',
    'approved', 'approved', 90.00, 189.90, 279.90, 'pending', 'manual_pix', 90
) ON DUPLICATE KEY UPDATE `os_number` = VALUES(`os_number`);

-- 7. Peça Vinculada à OS
INSERT INTO `os_parts` (`id`, `tenant_id`, `os_id`, `product_id`, `part_name`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 1, 'Tela Display iPhone 11 Original China', 1, 189.90)
ON DUPLICATE KEY UPDATE `part_name` = VALUES(`part_name`);
