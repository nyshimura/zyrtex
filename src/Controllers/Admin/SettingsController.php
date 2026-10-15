<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;
use Zyrtex\Services\MercadoPagoService;
use Zyrtex\Services\QrCodeService;
use Zyrtex\Services\ImageCompressor;
use Zyrtex\Core\Audit;

class SettingsController {
    /**
     * Tela Principal de Configurações da Oficina (/admin/configuracoes)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $printerSettings = json_decode($tenant['printer_settings_json'] ?? '{}', true) ?: [
            'default_format' => '80mm',
            'show_logo' => 1,
            'show_pix' => 1,
            'show_qr' => 1,
            'show_terms' => 1,
            'font_size' => 'normal'
        ];

        $address = json_decode($tenant['address_json'] ?? '{}', true) ?: [
            'street' => '',
            'city' => '',
            'maps_link' => ''
        ];

        $hours = json_decode($tenant['opening_hours_json'] ?? '{}', true) ?: [
            'weekdays' => 'Segunda a Sexta: 08:30 às 18:30',
            'saturday' => 'Sábado: 09:00 às 13:00'
        ];

        $printFormatsConfig = require __DIR__ . '/../../../config/print_formats.php';

        $subscriptions = Database::fetchAll(
            "SELECT * FROM saas_subscriptions WHERE tenant_id = :id ORDER BY id DESC LIMIT 10",
            ['id' => $tenantId]
        );

        View::render('admin/settings', [
            'tenant' => $tenant,
            'user' => $user,
            'printerSettings' => $printerSettings,
            'address' => $address,
            'hours' => $hours,
            'printFormats' => $printFormatsConfig,
            'subscriptions' => $subscriptions
        ], 'admin');
    }

    /**
     * Atualização dos Dados da Empresa (POST /admin/configuracoes/empresa)
     */
    public function updateCompany(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $businessName = trim((string)$request->post('business_name', ''));
        $slogan = trim((string)$request->post('slogan', ''));
        $whatsapp = trim((string)$request->post('whatsapp_number', ''));
        $logoUrl = trim((string)$request->post('logo_url', ''));
        $removeLogo = !empty($request->post('remove_logo'));
        $street = trim((string)$request->post('street', ''));
        $city = trim((string)$request->post('city', ''));
        $mapsLink = trim((string)$request->post('maps_link', ''));
        $weekdays = trim((string)$request->post('weekdays', ''));
        $saturday = trim((string)$request->post('saturday', ''));

        // Busca logo atual
        $currentTenant = Database::fetchOne("SELECT logo_path FROM tenants WHERE id = :id", ['id' => $tenantId]);
        $logoPath = $currentTenant['logo_path'] ?? null;

        if ($removeLogo) {
            $logoPath = null;
        } elseif (!empty($_FILES['logo_file']['tmp_name'])) {
            $uploadResult = \Zyrtex\Services\ImageCompressor::processUpload($_FILES['logo_file'], 'logos', 800, 800);
            if ($uploadResult['success']) {
                $logoPath = $uploadResult['url'];
            } else {
                Session::setFlash('error', 'Erro no upload do logotipo: ' . ($uploadResult['error'] ?? 'Formato inválido'));
                Response::redirect('/admin/configuracoes#empresa');
                return;
            }
        } elseif (!empty($logoUrl)) {
            $logoPath = $logoUrl;
        }

        if (empty($businessName) || empty($whatsapp)) {
            Session::setFlash('error', 'Nome da oficina e WhatsApp são obrigatórios.');
            Response::redirect('/admin/configuracoes#empresa');
            return;
        }

        $addressJson = json_encode(['street' => $street, 'city' => $city, 'maps_link' => $mapsLink], JSON_UNESCAPED_UNICODE);
        $hoursJson = json_encode(['weekdays' => $weekdays, 'saturday' => $saturday], JSON_UNESCAPED_UNICODE);

        try {
            Database::execute(
                "UPDATE tenants 
                 SET business_name = :business_name, slogan = :slogan, whatsapp_number = :whatsapp, 
                     logo_path = :logo_path, address_json = :address, opening_hours_json = :hours 
                 WHERE id = :id",
                [
                    'business_name' => $businessName,
                    'slogan' => $slogan ?: null,
                    'whatsapp' => $whatsapp,
                    'logo_path' => $logoPath ?: null,
                    'address' => $addressJson,
                    'hours' => $hoursJson,
                    'id' => $tenantId
                ]
            );

            Session::setFlash('success', 'Dados da empresa, logotipo e horários atualizados com sucesso!');
            Response::redirect('/admin/configuracoes#empresa');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar dados: ' . $e->getMessage());
            Response::redirect('/admin/configuracoes#empresa');
        }
    }

    /**
     * Atualização da Vitrine Pública do Lojista (Fase 5) (POST /admin/configuracoes/vitrine)
     */
    public function updatePublicPage(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $themeColor = (string)$request->post('theme_color', 'indigo');
        $slogan = trim((string)$request->post('slogan', ''));
        $whatsappMessage = trim((string)$request->post('whatsapp_message', ''));
        $bannerUrl = trim((string)$request->post('banner_url', ''));
        $removeBanner = !empty($request->post('remove_banner'));

        $currentTenant = Database::fetchOne(
            "SELECT banner_path, promotions_json FROM tenants WHERE id = :id",
            ['id' => $tenantId]
        );
        $bannerPath = $currentTenant['banner_path'] ?? null;

        // Processa Banner de Capa da Loja (Upload OU URL)
        if ($removeBanner) {
            $bannerPath = null;
        } elseif (!empty($_FILES['banner_file']['tmp_name'])) {
            $uploadResult = \Zyrtex\Services\ImageCompressor::processUpload($_FILES['banner_file'], 'banners', 1920, 800);
            if ($uploadResult['success']) {
                $bannerPath = $uploadResult['url'] ?? $uploadResult['path'];
            }
        } elseif (!empty($bannerUrl)) {
            $urlResult = \Zyrtex\Services\ImageCompressor::processUrl($bannerUrl, 'banners');
            $bannerPath = $urlResult['success'] ? ($urlResult['url'] ?? $urlResult['path']) : $bannerUrl;
        }
        
        // Promoções em destaque (JSON com imagem)
        $promoTitles = $request->post('promo_title', []);
        $promoDescs = $request->post('promo_desc', []);
        $promoPrices = $request->post('promo_price', []);
        $promoImageUrls = $request->post('promo_image_url', []);
        $promoExistingImages = $request->post('promo_existing_image', []);

        $promotions = [];
        if (is_array($promoTitles)) {
            for ($i = 0; $i < count($promoTitles); $i++) {
                $t = trim((string)($promoTitles[$i] ?? ''));
                $d = trim((string)($promoDescs[$i] ?? ''));
                $p = trim((string)($promoPrices[$i] ?? ''));
                $existingImg = trim((string)($promoExistingImages[$i] ?? ''));
                $imgUrl = trim((string)($promoImageUrls[$i] ?? ''));
                $removeImg = !empty($request->post("remove_promo_image_{$i}"));

                $promoImg = $existingImg ?: null;

                if ($removeImg) {
                    $promoImg = null;
                } elseif (!empty($_FILES["promo_image_file_{$i}"]['tmp_name'])) {
                    $upResult = \Zyrtex\Services\ImageCompressor::processUpload($_FILES["promo_image_file_{$i}"], 'promotions', 800, 600);
                    if ($upResult['success']) {
                        $promoImg = $upResult['url'] ?? $upResult['path'];
                    }
                } elseif (!empty($imgUrl)) {
                    $upResult = \Zyrtex\Services\ImageCompressor::processUrl($imgUrl, 'promotions');
                    $promoImg = $upResult['success'] ? ($upResult['url'] ?? $upResult['path']) : $imgUrl;
                }

                if (!empty($t)) {
                    $promotions[] = [
                        'title' => $t,
                        'description' => $d,
                        'price' => $p,
                        'image_path' => $promoImg
                    ];
                }
            }
        }
        $promotionsJson = json_encode($promotions, JSON_UNESCAPED_UNICODE);

        $allowedColors = ['indigo', 'emerald', 'blue', 'amber', 'rose', 'violet', 'cyan'];
        if (!in_array($themeColor, $allowedColors, true)) {
            $themeColor = 'indigo';
        }

        $themeMode = (string)$request->post('theme_mode', 'auto');
        $allowedModes = ['auto', 'light', 'dark'];
        if (!in_array($themeMode, $allowedModes, true)) {
            $themeMode = 'auto';
        }

        // 4. Métricas e Destaques de Prova Social (Automático do Banco ou Personalizado)
        $showMetrics = !empty($request->post('show_metrics')) ? 1 : 0;
        $metricsMode = (string)$request->post('metrics_mode', 'auto');
        if (!in_array($metricsMode, ['auto', 'custom'], true)) {
            $metricsMode = 'auto';
        }

        $publicMetrics = [
            'show_metrics' => $showMetrics,
            'mode' => $metricsMode,
            'metric1_value' => trim((string)$request->post('metric1_value', '')),
            'metric1_label' => trim((string)$request->post('metric1_label', '')),
            'metric2_value' => trim((string)$request->post('metric2_value', '')),
            'metric2_label' => trim((string)$request->post('metric2_label', '')),
            'metric3_value' => trim((string)$request->post('metric3_value', '')),
            'metric3_label' => trim((string)$request->post('metric3_label', '')),
            'metric4_value' => trim((string)$request->post('metric4_value', '')),
            'metric4_label' => trim((string)$request->post('metric4_label', '')),
        ];
        $publicMetricsJson = json_encode($publicMetrics, JSON_UNESCAPED_UNICODE);

        try {
            Database::execute(
                "UPDATE tenants 
                 SET theme_color = :theme_color, 
                     theme_mode = :theme_mode, 
                     slogan = :slogan, 
                     whatsapp_message = :whatsapp_message, 
                     banner_path = :banner_path, 
                     promotions_json = :promotions, 
                     public_metrics_json = :public_metrics 
                 WHERE id = :id",
                [
                    'theme_color' => $themeColor,
                    'theme_mode' => $themeMode,
                    'slogan' => $slogan ?: null,
                    'whatsapp_message' => $whatsappMessage ?: null,
                    'banner_path' => $bannerPath ?: null,
                    'promotions' => $promotionsJson,
                    'public_metrics' => $publicMetricsJson,
                    'id' => $tenantId
                ]
            );

            // Atualiza a sessão ativa do lojista
            $updatedTenant = Database::fetchOne("SELECT * FROM tenants WHERE id = :id LIMIT 1", ['id' => $tenantId]);
            if ($updatedTenant) {
                Session::set('current_tenant', $updatedTenant);
            }

            Session::setFlash('success', 'Personalização da Vitrine Pública (banner, tema e promoções) salva com sucesso!');
            Response::redirect('/admin/configuracoes#publica');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar personalização da vitrine: ' . $e->getMessage());
            Response::redirect('/admin/configuracoes#publica');
        }
    }

    /**
     * Atualização de Formas de Pagamento - Toggle Pix Manual vs Mercado Pago (POST /admin/configuracoes/pagamentos)
     */
    public function updatePayments(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $paymentMode = (string)$request->post('payment_mode', 'manual_pix');
        $pixKey = trim((string)$request->post('pix_key', ''));
        $pixKeyType = (string)$request->post('pix_key_type', 'random');
        $pixBeneficiary = trim((string)$request->post('pix_beneficiary', ''));

        $mpPublicKey = trim((string)$request->post('mp_public_key', ''));
        $mpAccessToken = trim((string)$request->post('mp_access_token', ''));

        if (!in_array($paymentMode, ['manual_pix', 'mercadopago'], true)) {
            $paymentMode = 'manual_pix';
        }

        try {
            Database::execute(
                "UPDATE tenants 
                 SET payment_mode = :payment_mode, pix_key = :pix_key, pix_key_type = :pix_key_type, 
                     pix_beneficiary = :pix_beneficiary, mp_public_key = :mp_public_key, mp_access_token = :mp_access_token 
                 WHERE id = :id",
                [
                    'payment_mode' => $paymentMode,
                    'pix_key' => $pixKey ?: null,
                    'pix_key_type' => $pixKeyType ?: null,
                    'pix_beneficiary' => $pixBeneficiary ?: null,
                    'mp_public_key' => $mpPublicKey ?: null,
                    'mp_access_token' => $mpAccessToken ?: null,
                    'id' => $tenantId
                ]
            );

            $modeName = ($paymentMode === 'mercadopago') ? 'Mercado Pago Automático' : 'Pix Manual';
            Session::setFlash('success', "Configurações de recebimento atualizadas! Modo ativo: {$modeName}.");
            Response::redirect('/admin/configuracoes#pagamentos');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar meios de pagamento: ' . $e->getMessage());
            Response::redirect('/admin/configuracoes#pagamentos');
        }
    }

    /**
     * Atualização de Formatos de Impressão (POST /admin/configuracoes/impressao)
     */
    public function updatePrinter(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $defaultFormat = (string)$request->post('default_format', '80mm');
        $showLogo = (int)$request->post('show_logo', 0);
        $showPix = (int)$request->post('show_pix', 0);
        $showQr = (int)$request->post('show_qr', 0);
        $showTerms = (int)$request->post('show_terms', 0);
        $fontSize = (string)$request->post('font_size', 'normal');

        $printerJson = json_encode([
            'default_format' => $defaultFormat,
            'show_logo' => $showLogo,
            'show_pix' => $showPix,
            'show_qr' => $showQr,
            'show_terms' => $showTerms,
            'font_size' => $fontSize
        ], JSON_UNESCAPED_UNICODE);

        try {
            Database::execute(
                "UPDATE tenants SET printer_settings_json = :settings WHERE id = :id",
                ['settings' => $printerJson, 'id' => $tenantId]
            );

            Session::setFlash('success', "Configurações de impressora salvas com sucesso! Formato padrão: {$defaultFormat}.");
            Response::redirect('/admin/configuracoes#impressao');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar impressora: ' . $e->getMessage());
            Response::redirect('/admin/configuracoes#impressao');
        }
    }

    /**
     * Atualização de IA (Hugging Face) (POST /admin/configuracoes/ia)
     */
    public function updateAI(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $aiProvider = (string)$request->post('ai_provider', 'huggingface');
        $apiKey = trim((string)$request->post('ai_api_key', ''));

        try {
            Database::execute(
                "UPDATE tenants SET ai_provider = :provider, ai_api_key = :api_key WHERE id = :id",
                ['provider' => $aiProvider, 'api_key' => $apiKey ?: null, 'id' => $tenantId]
            );

            Session::setFlash('success', 'Token de Inteligência Artificial salvo com sucesso!');
            Response::redirect('/admin/configuracoes#ia');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar IA: ' . $e->getMessage());
            Response::redirect('/admin/configuracoes#ia');
        }
    }

    /**
     * Atualização do Endereço da Vitrine / Domínio Próprio (POST /admin/configuracoes/endereco)
     */
    public function updateAddress(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        if (!$tenant) {
            Response::redirect('/login');
            return;
        }

        $isPremium = (int)($tenant['is_premium'] ?? 0) === 1;

        $rawSlug = trim((string)$request->post('slug', ''));
        $rawDomain = trim((string)$request->post('custom_domain', ''));
        $feePaid = $request->post('fee_paid') === '1';

        $cleanSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $rawSlug)));
        $cleanSlug = trim(preg_replace('/-+/', '-', $cleanSlug), '-');

        $cleanDomain = preg_replace('#^https?://#i', '', rtrim(strtolower($rawDomain), '/'));

        $reservedSlugs = [
            'admin', 'superadmin', 'login', 'cadastro', 'logout', 
            'rastreio', 'portal', 'webhook', 'ajax', 'assets', 'css', 
            'js', 'images', 'favicon.ico', 'api', 'saas'
        ];

        // 1. Se tentou salvar Domínio Próprio
        if (!empty($cleanDomain)) {
            if (!$isPremium) {
                Session::setFlash('error', 'Domínio próprio (.com.br) é um recurso exclusivo do Plano Premium. Faça upgrade para conectar seu domínio.');
                Response::redirect('/admin/configuracoes#publica');
                return;
            }

            // Verifica se o domínio já está em uso por outra loja
            $domainExists = Database::fetchOne(
                "SELECT id, business_name FROM tenants WHERE custom_domain = :domain AND id != :id LIMIT 1",
                ['domain' => $cleanDomain, 'id' => $tenantId]
            );
            if ($domainExists) {
                Session::setFlash('error', "O domínio '{$cleanDomain}' já está cadastrado em outra oficina no Zyrtex.");
                Response::redirect('/admin/configuracoes#publica');
                return;
            }

            Database::execute(
                "UPDATE tenants SET custom_domain = :domain WHERE id = :id",
                ['domain' => $cleanDomain, 'id' => $tenantId]
            );
            Session::setFlash('success', "Domínio próprio '{$cleanDomain}' salvo com sucesso! Aponte as entradas DNS conforme as instruções.");
        } elseif ($isPremium && empty($cleanDomain) && !empty($tenant['custom_domain'])) {
            // Removeu o domínio customizado
            Database::execute("UPDATE tenants SET custom_domain = NULL WHERE id = :id", ['id' => $tenantId]);
            Session::setFlash('info', 'Domínio próprio desvinculado. Sua vitrine agora opera no endereço padrão.');
        }

        // 2. Se tentou alterar o Slug
        if (!empty($cleanSlug) && $cleanSlug !== $tenant['slug']) {
            if (!$isPremium) {
                Session::setFlash('warning', 'No Plano Free, a alteração de endereço exige o pagamento da taxa de R$ 19,90 ao SaaS via Mercado Pago. O novo endereço é ativado automaticamente pelo Webhook.');
                Response::redirect('/admin/configuracoes#publica');
                return;
            }

            if (strlen($cleanSlug) < 3) {
                Session::setFlash('error', 'O novo endereço deve conter no mínimo 3 caracteres.');
                Response::redirect('/admin/configuracoes#publica');
                return;
            }

            if (in_array($cleanSlug, $reservedSlugs, true)) {
                Session::setFlash('error', "O termo '{$cleanSlug}' é reservado pelo sistema e não pode ser utilizado.");
                Response::redirect('/admin/configuracoes#publica');
                return;
            }

            // Verifica unicidade do slug
            $slugExists = Database::fetchOne(
                "SELECT id FROM tenants WHERE slug = :slug AND id != :id LIMIT 1",
                ['slug' => $cleanSlug, 'id' => $tenantId]
            );
            if ($slugExists) {
                Session::setFlash('error', "O endereço '{$cleanSlug}' já está em uso por outra oficina. Escolha outro nome.");
                Response::redirect('/admin/configuracoes#publica');
                return;
            }

            // Atualiza o slug no banco para usuário Premium
            Database::execute(
                "UPDATE tenants SET slug = :slug, default_url = :default_url WHERE id = :id",
                [
                    'slug' => $cleanSlug,
                    'default_url' => "{$cleanSlug}.zyrtex.com.br",
                    'id' => $tenantId
                ]
            );

            // Atualiza imediatamente a sessão ativa do lojista
            Session::set('current_tenant_slug', $cleanSlug);
            $user = Session::get('user');
            if (is_array($user)) {
                $user['tenant_slug'] = $cleanSlug;
                Session::set('user', $user);
            }

            Session::setFlash('success', "Endereço atualizado com sucesso para: /{$cleanSlug}");
            Response::redirect('/' . $cleanSlug . '/admin/configuracoes#publica');
            return;
        }

        Response::redirect('/admin/configuracoes#publica');
    }

    /**
     * Geração de Cobrança Pix para Alteração de Endereço / Slug (POST /admin/configuracoes/gerar-taxa-slug)
     */
    public function generateSlugFeeOrder(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        if (!$tenant) {
            Response::json(['success' => false, 'message' => 'Oficina não autenticada'], 401);
            return;
        }

        $rawSlug = trim((string)$request->post('desired_slug', ''));
        $cleanSlug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $rawSlug)));
        $cleanSlug = trim(preg_replace('/-+/', '-', $cleanSlug), '-');

        if (strlen($cleanSlug) < 3) {
            Response::json(['success' => false, 'message' => 'O novo endereço deve conter no mínimo 3 caracteres alfanuméricos.'], 400);
            return;
        }

        if ($cleanSlug === $tenant['slug']) {
            Response::json(['success' => false, 'message' => 'O endereço solicitado é idêntico ao seu endereço atual.'], 400);
            return;
        }

        $reservedSlugs = [
            'admin', 'superadmin', 'login', 'cadastro', 'logout', 
            'rastreio', 'portal', 'webhook', 'ajax', 'assets', 'css', 
            'js', 'images', 'favicon.ico', 'api', 'saas'
        ];

        if (in_array($cleanSlug, $reservedSlugs, true)) {
            Response::json(['success' => false, 'message' => "O endereço '{$cleanSlug}' é reservado pelo sistema."], 400);
            return;
        }

        // Verifica se já está em uso por outro lojista
        $slugExists = Database::fetchOne(
            "SELECT id FROM tenants WHERE slug = :slug AND id != :id LIMIT 1",
            ['slug' => $cleanSlug, 'id' => $tenantId]
        );
        if ($slugExists) {
            Response::json(['success' => false, 'message' => "O endereço '{$cleanSlug}' já está em uso por outra loja."], 400);
            return;
        }

        // Busca o preço da taxa em saas_settings
        $feeSetting = Database::fetchOne("SELECT config_value FROM saas_settings WHERE config_key = 'slug_change_price' LIMIT 1");
        $feeAmount = $feeSetting ? (float)$feeSetting['config_value'] : 19.90;

        // Cria a ordem de assinatura/serviço no banco de dados
        Database::execute(
            "INSERT INTO saas_subscriptions (tenant_id, plan_type, target_value, amount, status) 
             VALUES (:tenant_id, 'slug_change', :target_value, :amount, 'pending')",
            [
                'tenant_id' => $tenantId,
                'target_value' => $cleanSlug,
                'amount' => $feeAmount
            ]
        );

        $orderId = (int)Database::lastInsertId();
        $externalRef = "slug_change:{$orderId}";

        Database::execute(
            "UPDATE saas_subscriptions SET external_reference = :ref WHERE id = :id",
            ['ref' => $externalRef, 'id' => $orderId]
        );

        // Gera o Pix com Mercado Pago (ou simulação com QR Code)
        $currentUser = Auth::user();
        $payerEmail = $currentUser['email'] ?? 'financeiro@' . $cleanSlug . '.com.br';
        $notificationUrl = Response::url('/webhook/saas');

        $mpResponse = MercadoPagoService::createPixPayment(
            $feeAmount,
            "Taxa Alteracao Slug Zyrtex - /{$cleanSlug}",
            $payerEmail,
            $externalRef,
            $notificationUrl
        );

        // Atualiza a assinatura com os dados do Pix
        Database::execute(
            "UPDATE saas_subscriptions 
             SET mp_payment_id = :mp_id, pix_code = :pix_code, pix_qrcode_base64 = :qr_base64 
             WHERE id = :id",
            [
                'mp_id' => $mpResponse['payment_id'] ?? null,
                'pix_code' => $mpResponse['pix_code'] ?? null,
                'qr_base64' => $mpResponse['qrcode_base64'] ?? null,
                'id' => $orderId
            ]
        );

        Response::json([
            'success' => true,
            'order_id' => $orderId,
            'target_slug' => $cleanSlug,
            'current_slug' => $tenant['slug'],
            'amount' => number_format($feeAmount, 2, ',', '.'),
            'amount_raw' => $feeAmount,
            'pix_code' => $mpResponse['pix_code'],
            'qrcode_url' => $mpResponse['qrcode_url'] ?? QrCodeService::pngUrl($mpResponse['pix_code'], 240),
            'qrcode_base64' => $mpResponse['qrcode_base64'] ?? null,
            'is_mock' => $mpResponse['is_mock'] ?? false
        ]);
    }

    /**
     * Consulta em tempo real o status do pagamento via Mercado Pago (GET /admin/configuracoes/status-taxa-slug)
     */
    public function checkSlugFeeStatus(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $orderId = (int)$request->get('order_id', 0);
        if ($orderId <= 0) {
            Response::json(['success' => false, 'status' => 'invalid_order'], 400);
            return;
        }

        $sub = Database::fetchOne(
            "SELECT id, plan_type, target_value, status FROM saas_subscriptions WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $orderId, 'tenant_id' => $tenantId]
        );

        if (!$sub) {
            Response::json(['success' => false, 'status' => 'not_found'], 404);
            return;
        }

        $isApproved = ($sub['status'] === 'approved');
        $newSlug = $sub['target_value'];

        Response::json([
            'success' => true,
            'order_id' => $sub['id'],
            'status' => $sub['status'],
            'is_approved' => $isApproved,
            'new_slug' => $newSlug,
            'redirect_url' => '/' . $newSlug . '/admin/configuracoes#publica'
        ]);
    }

    /**
     * Ativação / Upgrade para o Plano Premium (POST /admin/configuracoes/upgrade-premium)
     */
    public function upgradePremium(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        try {
            $nextYear = date('Y-m-d H:i:s', strtotime('+1 year'));
            Database::execute(
                "UPDATE tenants SET is_premium = 1, expires_at = :expires_at WHERE id = :id",
                ['expires_at' => $nextYear, 'id' => $tenantId]
            );

            Session::setFlash('success', '★ Parabéns! Seu Plano Premium foi ativado com sucesso! Você agora tem alterações de endereço ilimitadas e domínio .com.br próprio liberado.');
            Response::redirect('/admin/configuracoes#publica');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Falha ao ativar Plano Premium: ' . $e->getMessage());
            Response::redirect('/admin/configuracoes#publica');
        }
    }

    /**
     * Atualização das Configurações do Google Drive (BYOS - RF10)
     * POST /admin/configuracoes/drive
     */
    public function updateGoogleDrive(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();

        $folderId = trim((string)$request->post('gdrive_folder_id', ''));
        $tokenJsonRaw = trim((string)$request->post('gdrive_token_json', ''));

        // Se informou JSON, valida a estrutura
        if ($tokenJsonRaw !== '') {
            $parsed = json_decode($tokenJsonRaw, true);
            if (!is_array($parsed)) {
                Session::setFlash('error', 'O conteúdo da Chave JSON é inválido. Certifique-se de copiar o conteúdo completo gerado no Google Cloud Console.');
                Response::redirect('/admin/configuracoes#drive');
                return;
            }

            // Se for Service Account, checa a integridade da chave privada
            if (!empty($parsed['private_key'])) {
                $pk = @openssl_pkey_get_private($parsed['private_key']);
                if (!$pk) {
                    Session::setFlash('error', 'A chave privada (private_key) contida no JSON não é válida ou está corrompida.');
                    Response::redirect('/admin/configuracoes#drive');
                    return;
                }
            }
        }

        try {
            Database::execute(
                "UPDATE tenants SET gdrive_folder_id = :folder_id, gdrive_token_json = :token_json WHERE id = :id",
                [
                    'folder_id' => $folderId !== '' ? $folderId : null,
                    'token_json' => $tokenJsonRaw !== '' ? $tokenJsonRaw : null,
                    'id' => $tenantId
                ]
            );

            \Zyrtex\Core\Audit::log($tenantId, (int)$user['id'], 'gdrive_settings_updated', [
                'has_folder_id' => !empty($folderId),
                'has_token' => !empty($tokenJsonRaw)
            ]);

            Session::setFlash('success', 'Configurações de armazenamento no Google Drive (BYOS) salvas com sucesso!');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar configurações do Google Drive: ' . $e->getMessage());
        }

        Response::redirect('/admin/configuracoes#drive');
    }

    /**
     * Solicitação de Estorno / Cancelamento de Plano (RN03 - Direito de Arrependimento CDC Art. 49)
     * POST /admin/configuracoes/estornar-plano
     */
    public function refundSubscription(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $subId = (int)$request->post('subscription_id', 0);
        $sub = Database::fetchOne(
            "SELECT * FROM saas_subscriptions WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $subId, 'tenant_id' => $tenantId]
        );

        if (!$sub) {
            Session::setFlash('error', 'Assinatura ou pagamento não encontrado.');
            Response::redirect('/admin/configuracoes#publica');
            return;
        }

        if ($sub['status'] !== 'approved') {
            Session::setFlash('error', 'Apenas pagamentos aprovados podem ser estornados.');
            Response::redirect('/admin/configuracoes#publica');
            return;
        }

        if ((int)$sub['is_refunded'] === 1) {
            Session::setFlash('warning', 'Este pagamento já foi estornado anteriormente.');
            Response::redirect('/admin/configuracoes#publica');
            return;
        }

        // Validação da Janela de 7 Dias do CDC Art. 49
        $purchasedAt = strtotime($sub['purchased_at'] ?? $sub['created_at']);
        $daysPassed = (time() - $purchasedAt) / 86400;

        if ($daysPassed > 7) {
            Session::setFlash('error', 'O prazo de 7 dias para cancelamento com estorno integral (Art. 49 do CDC) expirou.');
            Response::redirect('/admin/configuracoes#publica');
            return;
        }

        // Executa o estorno no Mercado Pago
        $refundResult = MercadoPagoService::refundPayment($sub['mp_payment_id'] ?? '');

        if ($refundResult['success']) {
            Database::beginTransaction();

            // Atualiza status da assinatura
            Database::execute(
                "UPDATE saas_subscriptions 
                 SET status = 'refunded', is_refunded = 1, refunded_at = NOW() 
                 WHERE id = :id",
                ['id' => $subId]
            );

            // Reverte os efeitos do recurso adquirido
            if ($sub['plan_type'] === 'annual_domain') {
                Database::execute(
                    "UPDATE tenants SET custom_domain = NULL, is_premium = 0 WHERE id = :id",
                    ['id' => $tenantId]
                );
            }

            // Grava Auditoria
            Audit::log($tenantId, (int)$user['id'], 'saas_subscription_refunded_cdc49', [
                'subscription_id' => $subId,
                'plan_type' => $sub['plan_type'],
                'amount' => (float)$sub['amount'],
                'mp_payment_id' => $sub['mp_payment_id'],
                'days_passed' => round($daysPassed, 2)
            ]);

            Database::commit();

            Session::setFlash('success', '✓ Estorno de R$ ' . number_format((float)$sub['amount'], 2, ',', '.') . ' solicitado com sucesso conforme CDC Art. 49! O valor retornará à sua conta de origem.');
        } else {
            Session::setFlash('error', 'Falha ao processar estorno: ' . ($refundResult['error'] ?? 'Erro desconhecido'));
        }

        Response::redirect('/admin/configuracoes#publica');
    }
}

