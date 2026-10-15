<?php

declare(strict_types=1);

namespace Zyrtex\Services;

use Zyrtex\Core\Database;
use Zyrtex\Core\Response;
use Zyrtex\Services\MailService;

/**
 * Serviço de Programa de Indicação Anti-Fraude com Validação por Pagamento Real
 * Garante que bônus e meses grátis só sejam creditados quando o novo lojista
 * tiver uma transação bancária confirmada (OS paga por cliente ou plano contratado).
 */
class ReferralService {

    /**
     * Retorna as configurações globais de indicação do SaaS
     */
    public static function getConfig(): array {
        $keys = [
            'referral_active' => 1,
            'referral_reward_days' => 30,
            'referral_welcome_days' => 15
        ];

        $rows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings WHERE config_key LIKE 'referral_%'");
        $config = [];
        foreach ($rows as $r) {
            $config[$r['config_key']] = $r['config_value'];
        }

        return [
            'active' => (int)($config['referral_active'] ?? $keys['referral_active']) === 1,
            'reward_days' => (int)($config['referral_reward_days'] ?? $keys['referral_reward_days']),
            'welcome_days' => (int)($config['referral_welcome_days'] ?? $keys['referral_welcome_days'])
        ];
    }

    /**
     * Retorna o código único de indicação do lojista (gera automaticamente se não existir)
     */
    public static function getOrCreateReferralCode(int $tenantId): string {
        $tenant = Database::fetchOne("SELECT id, slug, referral_code FROM tenants WHERE id = :id LIMIT 1", ['id' => $tenantId]);
        if (!$tenant) {
            return '';
        }

        if (!empty($tenant['referral_code'])) {
            return $tenant['referral_code'];
        }

        // Gera código mnemônico legível
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $tenant['slug']), 0, 8));
        $code = ($prefix ?: 'ZYRTEX') . '-' . substr(bin2hex(random_bytes(3)), 0, 4);

        Database::execute("UPDATE tenants SET referral_code = :code WHERE id = :id", [
            'code' => $code,
            'id' => $tenantId
        ]);

        return $code;
    }

    /**
     * Retorna o resumo completo do programa de indicação para o painel do lojista
     */
    public static function getTenantDashboardData(int $tenantId): array {
        $code = self::getOrCreateReferralCode($tenantId);
        $referralUrl = Response::url('/convite/' . $code);
        $config = self::getConfig();

        // Contagens
        $totalInvited = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS c FROM tenant_referrals WHERE referrer_tenant_id = :id",
            ['id' => $tenantId]
        )['c'] ?? 0);

        $pendingValidation = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS c FROM tenant_referrals WHERE referrer_tenant_id = :id AND status = 'pending'",
            ['id' => $tenantId]
        )['c'] ?? 0);

        $rewardedCount = (int)(Database::fetchOne(
            "SELECT COUNT(*) AS c FROM tenant_referrals WHERE referrer_tenant_id = :id AND status IN ('qualified', 'rewarded')",
            ['id' => $tenantId]
        )['c'] ?? 0);

        $totalDaysEarned = $rewardedCount * $config['reward_days'];

        // Lista de indicados (100% blindada pela LGPD - sem vazar faturamento de ninguém)
        $sql = "
            SELECT r.id, r.status, r.qualification_type, r.created_at, r.qualified_at, r.reward_value,
                   t.business_name, t.slug, t.niche
            FROM tenant_referrals r
            JOIN tenants t ON t.id = r.referred_tenant_id
            WHERE r.referrer_tenant_id = :id
            ORDER BY r.created_at DESC
            LIMIT 50
        ";
        $referrals = Database::fetchAll($sql, ['id' => $tenantId]);

        // Texto persuasivo pré-formatado para WhatsApp
        $tenant = Database::fetchOne("SELECT business_name FROM tenants WHERE id = :id LIMIT 1", ['id' => $tenantId]);
        $shopName = $tenant['business_name'] ?? 'nossa oficina';
        $whatsAppShareText = "Fala parceiro! Estou usando o sistema Zyrtex na {$shopName} para controle de OS, ordem de serviço online e laudos automáticos com IA. Acessando pelo meu link de parceiro você ganha {$config['welcome_days']} dias de teste Pro liberados na faixa: {$referralUrl}";
        $whatsAppShareUrl = "https://api.whatsapp.com/send?text=" . urlencode($whatsAppShareText);

        return [
            'referral_code' => $code,
            'referral_url' => $referralUrl,
            'whatsapp_share_url' => $whatsAppShareUrl,
            'whatsapp_share_text' => $whatsAppShareText,
            'total_invited' => $totalInvited,
            'pending_validation' => $pendingValidation,
            'rewarded_count' => $rewardedCount,
            'total_days_earned' => $totalDaysEarned,
            'reward_days_per_referral' => $config['reward_days'],
            'welcome_days' => $config['welcome_days'],
            'referrals_list' => $referrals
        ];
    }

    /**
     * Vincula a indicação quando uma nova oficina se cadastra via link de convite
     */
    public static function linkReferralOnRegister(int $newTenantId, string $codeOrSlug): bool {
        $codeOrSlug = trim($codeOrSlug);
        if (empty($codeOrSlug)) {
            return false;
        }

        $config = self::getConfig();
        if (!$config['active']) {
            return false;
        }

        // Localiza a oficina indicadora
        $sql = "SELECT id, business_name FROM tenants WHERE (referral_code = :c OR slug = :c) AND id != :new_id LIMIT 1";
        $referrer = Database::fetchOne($sql, ['c' => $codeOrSlug, 'new_id' => $newTenantId]);

        if (!$referrer) {
            return false;
        }

        $referrerId = (int)$referrer['id'];

        // Atualiza o tenant indicado
        Database::execute(
            "UPDATE tenants SET referred_by_tenant_id = :ref_id WHERE id = :id",
            ['ref_id' => $referrerId, 'id' => $newTenantId]
        );

        // Concede o bônus de boas-vindas do convidado
        $welcomeDays = $config['welcome_days'];
        Database::execute(
            "UPDATE tenants 
             SET is_premium = 1,
                 expires_at = DATE_ADD(GREATEST(COALESCE(expires_at, NOW()), NOW()), INTERVAL :days DAY)
             WHERE id = :id",
            ['days' => $welcomeDays, 'id' => $newTenantId]
        );

        // Registra a indicação com status 'pending' (Aguardando 1º pagamento real para liberar o bônus do indicador)
        Database::execute(
            "INSERT INTO tenant_referrals (
                referrer_tenant_id, referred_tenant_id, referral_code, status,
                reward_type, reward_value, notes
            ) VALUES (
                :referrer_id, :referred_id, :code, 'pending',
                'premium_days', :reward_days, :notes
            )",
            [
                'referrer_id' => $referrerId,
                'referred_id' => $newTenantId,
                'code' => $codeOrSlug,
                'reward_days' => $config['reward_days'],
                'notes' => "Cadastro realizado via convite da oficina: {$referrer['business_name']}"
            ]
        );

        return true;
    }

    /**
     * Valida e Concede o Bônus de Indicação assim que o novo lojista conclui um pagamento real
     * (Disparado quando uma OS é paga por Pix ou quando uma assinatura SaaS é aprovada)
     */
    public static function qualifyReferral(int $referredTenantId, string $eventType, string $eventReference): bool {
        $config = self::getConfig();
        if (!$config['active']) {
            return false;
        }

        // Localiza a indicação pendente
        $sql = "
            SELECT r.*, 
                   ref.business_name AS referrer_name,
                   u_ref.email AS referrer_email, u_ref.name AS referrer_owner_name,
                   t_new.business_name AS new_shop_name
            FROM tenant_referrals r
            JOIN tenants ref ON ref.id = r.referrer_tenant_id
            JOIN tenants t_new ON t_new.id = r.referred_tenant_id
            LEFT JOIN users u_ref ON (u_ref.tenant_id = ref.id AND u_ref.role = 'owner')
            WHERE r.referred_tenant_id = :new_id
              AND r.status = 'pending'
            LIMIT 1
        ";

        $referral = Database::fetchOne($sql, ['new_id' => $referredTenantId]);
        if (!$referral) {
            return false;
        }

        $referralId = (int)$referral['id'];
        $referrerId = (int)$referral['referrer_tenant_id'];
        $rewardDays = (int)($referral['reward_value'] ?: $config['reward_days']);

        // 1. Concede os dias de Zyrtex Pro ao indicador
        Database::execute(
            "UPDATE tenants 
             SET is_premium = 1,
                 expires_at = DATE_ADD(GREATEST(COALESCE(expires_at, NOW()), NOW()), INTERVAL :days DAY)
             WHERE id = :id",
            ['days' => $rewardDays, 'id' => $referrerId]
        );

        // 2. Atualiza a indicação para 'rewarded'
        Database::execute(
            "UPDATE tenant_referrals 
             SET status = 'rewarded',
                 qualification_type = :event_type,
                 qualification_reference = :event_ref,
                 qualified_at = NOW(),
                 reward_granted_at = NOW()
             WHERE id = :id",
            [
                'event_type' => $eventType,
                'event_ref' => $eventReference,
                'id' => $referralId
            ]
        );

        // 3. Registra auditoria de sistema
        try {
            Database::execute(
                "INSERT INTO audit_logs (tenant_id, action, details_json, ip_address) 
                 VALUES (:t_id, 'referral_reward_granted', :details, '127.0.0.1')",
                [
                    't_id' => $referrerId,
                    'details' => json_encode([
                        'referral_id' => $referralId,
                        'event_type' => $eventType,
                        'event_ref' => $eventReference,
                        'reward_days' => $rewardDays,
                        'referred_shop' => $referral['new_shop_name']
                    ])
                ]
            );
        } catch (\Throwable $e) {}

        // 4. Envia e-mail comemorativo ao lojista indicador
        if (!empty($referral['referrer_email'])) {
            $subject = "🎉 Parabéns! Sua indicação foi confirmada: +{$rewardDays} dias Pro grátis!";
            $bodyHtml = "
            <div style='background-color:#070b14; color:#f1f5f9; font-family:Plus Jakarta Sans, sans-serif; padding:32px 16px;'>
                <div style='max-width:540px; margin:0 auto; background-color:#0f172a; border:1px solid #1e293b; border-radius:20px; padding:32px; text-align:center;'>
                    <div style='font-size:40px; margin-bottom:12px;'>🎁</div>
                    <h1 style='color:#ffffff; font-size:22px; font-weight:800; margin:0 0 10px;'>
                        Bônus de Indicação Liberado!
                    </h1>
                    <p style='color:#94a3b8; font-size:14px; line-height:1.6; margin:0 0 24px;'>
                        A oficina parceira <strong>{$referral['new_shop_name']}</strong> foi validada com sucesso através de uma transação real na plataforma Zyrtex!
                    </p>
                    <div style='background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); border-radius:14px; padding:18px; margin-bottom:24px;'>
                        <div style='color:#34d399; font-size:24px; font-weight:900;'>+{$rewardDays} Dias Grátis</div>
                        <div style='color:#94a3b8; font-size:12px;'>Adicionados diretamente à sua assinatura do Zyrtex Pro</div>
                    </div>
                    <p style='color:#64748b; font-size:12px;'>Continue indicando outros colegas de bancada e acumule meses gratuitos sem limites.</p>
                </div>
            </div>
            ";
            MailService::send($referral['referrer_email'], $referral['referrer_owner_name'] ?? 'Lojista', $subject, $bodyHtml);
        }

        return true;
    }

    /**
     * Retorna métricas globais para a gestão do SuperAdmin
     */
    public static function getSuperAdminMetrics(): array {
        $totalReferrals = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM tenant_referrals")['c'] ?? 0);
        $totalQualified = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM tenant_referrals WHERE status IN ('qualified','rewarded')")['c'] ?? 0);
        $totalPending = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM tenant_referrals WHERE status = 'pending'")['c'] ?? 0);
        $totalDaysGiven = (int)(Database::fetchOne("SELECT SUM(reward_value) AS s FROM tenant_referrals WHERE status IN ('qualified','rewarded')")['s'] ?? 0);

        // Ranking dos top 5 lojistas que mais indicam
        $sql = "
            SELECT ref.id, ref.business_name, ref.slug, ref.referral_code,
                   COUNT(r.id) AS total_invites,
                   SUM(CASE WHEN r.status IN ('qualified','rewarded') THEN 1 ELSE 0 END) AS qualified_invites
            FROM tenants ref
            JOIN tenant_referrals r ON r.referrer_tenant_id = ref.id
            GROUP BY ref.id, ref.business_name, ref.slug, ref.referral_code
            ORDER BY qualified_invites DESC, total_invites DESC
            LIMIT 5
        ";
        $topReferrers = Database::fetchAll($sql);

        return [
            'total_referrals' => $totalReferrals,
            'total_qualified' => $totalQualified,
            'total_pending' => $totalPending,
            'total_days_given' => $totalDaysGiven,
            'top_referrers' => $topReferrers
        ];
    }
}
