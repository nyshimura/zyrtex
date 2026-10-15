<?php

namespace Zyrtex\Controllers\Public;

use Zyrtex\Core\Request;
use Zyrtex\Core\Tenant;
use Zyrtex\Core\View;
use Zyrtex\Core\Database;

class HomeController {
    public function index(Request $request): void {
        // Se estamos no contexto da plataforma SaaS (raiz do zyrtex.com.br ou localhost sem tenant)
        if (Tenant::isSaaS()) {
            $saasSettings = [];
            try {
                $rows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings");
                foreach ($rows as $r) {
                    $saasSettings[$r['config_key']] = $r['config_value'];
                }
            } catch (\Throwable) {}

            View::render('saas/home', [
                'pageTitle' => $saasSettings['saas_page_title'] ?? 'Zyrtex - Gestão Gratuita para Assistências Técnicas',
                'saasSettings' => $saasSettings
            ], 'saas');
            return;
        }

        // Se estamos no contexto de uma oficina específica (subdomínio ou ?tenant=slug)
        $tenant = Tenant::current();
        $featuredProducts = [];
        $metrics = self::resolveMetrics($tenant);

        if ($tenant && !empty($tenant['id'])) {
            $featuredProducts = Database::fetchAll(
                "SELECT id, name, category, price, image_path, is_service, stock 
                 FROM products_pos 
                 WHERE tenant_id = :tenant_id AND (stock > 0 OR is_service = 1) 
                 ORDER BY (image_path IS NOT NULL AND image_path != '') DESC, id DESC LIMIT 8",
                ['tenant_id' => (int)$tenant['id']]
            );
        }

        View::render('public/home', [
            'pageTitle' => ($tenant['business_name'] ?? 'Vitrine da Oficina'),
            'tenant' => $tenant,
            'featuredProducts' => $featuredProducts,
            'metrics' => $metrics
        ], 'public');
    }

    /**
     * Resolve as 4 métricas exibidas na vitrine:
     * - Se o lojista configurou valores personalizados, respeita a customização dele.
     * - Se estiver no modo automático, calcula os dados reais do banco (OSs concluídas, aprovações, etc.)
     *   com fallback inteligente para não exibir '0 aparelhos' caso a oficina seja nova.
     * - Se o lojista optou por ocultar a barra, retorna visible = false.
     */
    private static function resolveMetrics(?array $tenant): array {
        if (!$tenant || empty($tenant['id'])) {
            return [
                'visible' => true,
                'items' => [
                    ['icon' => '🔧', 'value' => 'Ágil', 'label' => 'Diagnóstico em até 24h'],
                    ['icon' => '⭐', 'value' => '99.4%', 'label' => 'Satisfação dos Clientes'],
                    ['icon' => '🛡️', 'value' => '90 Dias', 'label' => 'Garantia Legal CDC'],
                    ['icon' => '⚡', 'value' => '1-Clique', 'label' => 'Aprovação no WhatsApp'],
                ]
            ];
        }

        $raw = $tenant['public_metrics_json'] ?? null;
        $cfg = [];
        if (!empty($raw)) {
            $cfg = is_array($raw) ? $raw : json_decode($raw, true);
        }

        // Se o lojista desmarcou a exibição da barra de métricas
        if (isset($cfg['show_metrics']) && empty($cfg['show_metrics'])) {
            return ['visible' => false, 'items' => []];
        }

        $mode = $cfg['mode'] ?? 'auto';

        // 1. Modo Personalizado: o lojista define seus próprios números e rótulos
        if ($mode === 'custom') {
            return [
                'visible' => true,
                'items' => [
                    [
                        'icon' => '🔧',
                        'value' => !empty($cfg['metric1_value']) ? $cfg['metric1_value'] : 'Ágil',
                        'label' => !empty($cfg['metric1_label']) ? $cfg['metric1_label'] : 'Aparelhos Reparados'
                    ],
                    [
                        'icon' => '⭐',
                        'value' => !empty($cfg['metric2_value']) ? $cfg['metric2_value'] : '99.4%',
                        'label' => !empty($cfg['metric2_label']) ? $cfg['metric2_label'] : 'Satisfação dos Clientes'
                    ],
                    [
                        'icon' => '🛡️',
                        'value' => !empty($cfg['metric3_value']) ? $cfg['metric3_value'] : '90 Dias',
                        'label' => !empty($cfg['metric3_label']) ? $cfg['metric3_label'] : 'Garantia Legal CDC'
                    ],
                    [
                        'icon' => '⚡',
                        'value' => !empty($cfg['metric4_value']) ? $cfg['metric4_value'] : '1-Clique',
                        'label' => !empty($cfg['metric4_label']) ? $cfg['metric4_label'] : 'Aprovação no WhatsApp'
                    ],
                ]
            ];
        }

        // 2. Modo Automático: Dinâmico do Banco de Dados
        $tenantId = (int)$tenant['id'];
        $dbStats = Database::fetchOne(
            "SELECT 
                COUNT(CASE WHEN status IN ('ready', 'delivered') THEN 1 END) as repaired_count,
                COUNT(*) as total_count,
                COUNT(CASE WHEN approval_status = 'approved' OR status IN ('approved', 'in_progress', 'ready', 'delivered') THEN 1 END) as approved_count
             FROM service_orders 
             WHERE tenant_id = :tenant_id",
            ['tenant_id' => $tenantId]
        );

        $repaired = (int)($dbStats['repaired_count'] ?? 0);
        $total = (int)($dbStats['total_count'] ?? 0);
        $approved = (int)($dbStats['approved_count'] ?? 0);

        // Métrica 1: Aparelhos Concluídos / Reparados
        if ($repaired >= 10) {
            $m1Value = '+' . number_format($repaired, 0, ',', '.');
            $m1Label = 'Aparelhos Reparados';
        } elseif ($repaired > 0) {
            $m1Value = '+' . $repaired;
            $m1Label = 'Aparelhos Concluídos';
        } else {
            // Fallback elegante para loja nova: nunca expõe "0 aparelhos"
            $m1Value = 'Ágil';
            $m1Label = 'Diagnóstico em até 24h';
        }

        // Métrica 2: Satisfação / Taxa de Aprovação
        if ($total >= 3 && $total > 0) {
            $rate = round(($approved / $total) * 100, 1);
            $m2Value = $rate . '%';
            $m2Label = 'Taxa de Aprovação';
        } else {
            $m2Value = '100%';
            $m2Label = 'Dedicação & Qualidade';
        }

        // Métrica 3: Garantia Legal CDC
        $m3Value = '90 Dias';
        $m3Label = 'Garantia Legal CDC';

        // Métrica 4: Aprovação 1-Clique WhatsApp
        $m4Value = '1-Clique';
        $m4Label = 'Aprovação no WhatsApp';

        return [
            'visible' => true,
            'items' => [
                ['icon' => '🔧', 'value' => $m1Value, 'label' => $m1Label],
                ['icon' => '⭐', 'value' => $m2Value, 'label' => $m2Label],
                ['icon' => '🛡️', 'value' => $m3Value, 'label' => $m3Label],
                ['icon' => '⚡', 'value' => $m4Value, 'label' => $m4Label],
            ]
        ];
    }

    /**
     * Termos de Uso e Política de Privacidade da Plataforma (/termos)
     */
    public function terms(Request $request): void {
        View::render('public/terms', [
            'pageTitle' => 'Termos de Uso & Política de Privacidade (LGPD) • Zyrtex SaaS'
        ], 'saas');
    }

    /**
     * Retorna o Manifesto PWA dinâmico (/manifest.json e /manifest.webmanifest)
     * Personaliza o nome da loja, cor temática e ícone para instalação no smartphone.
     */
    public function manifest(Request $request): void {
        header('Content-Type: application/manifest+json; charset=utf-8');

        $tenant = Tenant::current();
        if (!$tenant) {
            $slugParam = $request->query('slug');
            if (!empty($slugParam)) {
                try {
                    $tenant = Database::fetchOne("SELECT * FROM tenants WHERE slug = :slug AND active = 1 LIMIT 1", ['slug' => $slugParam]);
                } catch (\Throwable) {}
            }
        }

        $appConfig = require __DIR__ . '/../../../config/app.php';
        $baseUrl = rtrim($appConfig['url'] ?? 'http://localhost/zyrtex', '/');

        $paletteMap = [
            'indigo'  => '#4f46e5',
            'emerald' => '#059669',
            'blue'    => '#2563eb',
            'amber'   => '#d97706',
            'rose'    => '#e11d48',
            'violet'  => '#7c3aed',
            'cyan'    => '#0891b2',
        ];

        if ($tenant && !empty($tenant['business_name'])) {
            $name = $tenant['business_name'];
            $shortName = mb_substr($tenant['business_name'], 0, 15);
            $startUrl = Tenant::publicUrl($tenant['slug'] ?? null);
            $rawColor = $tenant['theme_color'] ?? 'indigo';
            $themeColor = $paletteMap[$rawColor] ?? (str_starts_with($rawColor, '#') ? $rawColor : '#4f46e5');
        } else {
            $name = 'Zyrtex - Assistência Técnica & Gestão';
            $shortName = 'Zyrtex';
            $startUrl = $baseUrl . '/';
            $themeColor = '#4f46e5';
        }

        $manifest = [
            'name' => $name,
            'short_name' => $shortName,
            'description' => 'Sistema de Gestão, Ordens de Serviço e Prontuário Digital para Assistências Técnicas.',
            'start_url' => $startUrl,
            'scope' => $baseUrl . '/',
            'display' => 'standalone',
            'background_color' => '#0f172a',
            'theme_color' => $themeColor,
            'orientation' => 'portrait-primary',
            'lang' => 'pt-BR',
            'categories' => ['business', 'productivity', 'utilities'],
            'icons' => [
                [
                    'src' => $baseUrl . '/assets/images/pwa-icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $baseUrl . '/assets/images/pwa-icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $baseUrl . '/assets/images/pwa-icon.svg',
                    'sizes' => 'any',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any'
                ]
            ]
        ];

        echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Retorna o Service Worker (/sw.js)
     */
    public function serviceWorker(Request $request): void {
        header('Content-Type: application/javascript; charset=utf-8');
        header('Service-Worker-Allowed: /');
        $swPath = __DIR__ . '/../../../public/sw.js';
        if (file_exists($swPath)) {
            readfile($swPath);
        } else {
            echo "console.log('Zyrtex Service Worker Active');";
        }
        exit;
    }
}
