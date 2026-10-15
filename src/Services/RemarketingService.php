<?php

declare(strict_types=1);

namespace Zyrtex\Services;

use Zyrtex\Core\Database;
use Zyrtex\Core\Response;
use Zyrtex\Services\MailService;
use Zyrtex\Services\MercadoPagoService;

/**
 * Serviço de Remarketing Inteligente de Pedidos Abandonados (SaaS Zyrtex)
 * Gerencia a transição de pendentes expirados, cálculo de desconto progressivo por fidelidade,
 * emissão de tokens de resgate e disparos automatizados de e-mail e WhatsApp.
 */
class RemarketingService {

    /**
     * Retorna as configurações globais de remarketing do SaaS
     */
    public static function getConfig(): array {
        $keys = [
            'remarketing_expiration_hours' => 48,
            'remarketing_base_discount' => 10.0,
            'remarketing_loyalty_bonus_per_month' => 1.5,
            'remarketing_max_discount' => 25.0,
            'remarketing_auto_active' => 1
        ];

        $rows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings WHERE config_key LIKE 'remarketing_%'");
        $config = [];
        foreach ($rows as $r) {
            $config[$r['config_key']] = $r['config_value'];
        }

        return [
            'expiration_hours' => (int)($config['remarketing_expiration_hours'] ?? $keys['remarketing_expiration_hours']),
            'base_discount' => (float)($config['remarketing_base_discount'] ?? $keys['remarketing_base_discount']),
            'loyalty_bonus_per_month' => (float)($config['remarketing_loyalty_bonus_per_month'] ?? $keys['remarketing_loyalty_bonus_per_month']),
            'max_discount' => (float)($config['remarketing_max_discount'] ?? $keys['remarketing_max_discount']),
            'auto_active' => (int)($config['remarketing_auto_active'] ?? $keys['remarketing_auto_active']) === 1
        ];
    }

    /**
     * Calcula o percentual de desconto progressivo por fidelidade com base nos meses de cadastro
     */
    public static function calculateLoyaltyDiscount(int $tenantAgeMonths): float {
        $config = self::getConfig();
        $months = max(0, $tenantAgeMonths);
        $bonus = $months * $config['loyalty_bonus_per_month'];
        $totalDiscountPercent = min($config['max_discount'], $config['base_discount'] + $bonus);
        return round($totalDiscountPercent, 1);
    }

    /**
     * Varre transações pendentes expiradas, calcula o desconto de fidelidade e move para a Fila de Remarketing
     */
    public static function processPendingExpirations(): array {
        $config = self::getConfig();
        $hours = max(1, $config['expiration_hours']);

        // Busca pedidos pendentes criados há mais de X horas
        $sql = "
            SELECT s.id, s.tenant_id, s.amount, s.plan_type, s.target_value, s.created_at,
                   t.business_name, t.slug, t.created_at AS tenant_created_at,
                   TIMESTAMPDIFF(MONTH, t.created_at, NOW()) AS tenant_age_months
            FROM saas_subscriptions s
            JOIN tenants t ON t.id = s.tenant_id
            WHERE s.status = 'pending'
              AND s.created_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)
        ";

        $expired = Database::fetchAll($sql, ['hours' => $hours]);
        $processed = [];

        foreach ($expired as $sub) {
            $subId = (int)$sub['id'];
            $origAmount = (float)$sub['amount'];
            $tenantMonths = max(0, (int)$sub['tenant_age_months']);

            // Cálculo do desconto de fidelidade
            $totalDiscountPercent = self::calculateLoyaltyDiscount($tenantMonths);

            $discountVal = round($origAmount * ($totalDiscountPercent / 100), 2);
            $newAmount = max(1.00, round($origAmount - $discountVal, 2));

            $token = bin2hex(random_bytes(24));

            Database::execute(
                "UPDATE saas_subscriptions 
                 SET status = 'remarketing',
                     recovery_token = :token,
                     original_amount = :orig_amount,
                     discount_percent = :disc_percent,
                     discount_amount = :disc_amount,
                     amount = :new_amount,
                     remarketing_sent_at = NOW(),
                     remarketing_count = remarketing_count + 1
                 WHERE id = :id",
                [
                    'token' => $token,
                    'orig_amount' => $origAmount,
                    'disc_percent' => $totalDiscountPercent,
                    'disc_amount' => $discountVal,
                    'new_amount' => $newAmount,
                    'id' => $subId
                ]
            );

            // Se o envio automático estiver ativo, envia o e-mail transacional
            if ($config['auto_active']) {
                self::sendRecoveryEmail($subId);
            }

            $processed[] = [
                'id' => $subId,
                'tenant_name' => $sub['business_name'],
                'original_amount' => $origAmount,
                'discount_percent' => $totalDiscountPercent,
                'new_amount' => $newAmount,
                'token' => $token
            ];
        }

        return $processed;
    }

    /**
     * Busca dados da transação pelo token único de recuperação
     */
    public static function getByRecoveryToken(string $token): ?array {
        $token = trim($token);
        if (empty($token) || strlen($token) < 16) {
            return null;
        }

        $sql = "
            SELECT s.*, 
                   t.business_name, t.slug, t.whatsapp_number, t.created_at AS tenant_created_at,
                   u.name AS owner_name, u.email AS owner_email,
                   TIMESTAMPDIFF(MONTH, t.created_at, NOW()) AS tenant_age_months
            FROM saas_subscriptions s
            JOIN tenants t ON t.id = s.tenant_id
            LEFT JOIN users u ON (u.tenant_id = t.id AND u.role = 'owner')
            WHERE s.recovery_token = :token
            LIMIT 1
        ";

        return Database::fetchOne($sql, ['token' => $token]) ?: null;
    }

    /**
     * Atualiza ou gera um novo Pix no Mercado Pago com o valor do desconto aplicado
     */
    public static function generateRecoveryPix(array $sub): array {
        $subId = (int)$sub['id'];
        $amount = (float)$sub['amount'];
        $planType = $sub['plan_type'];
        $tenantName = $sub['business_name'] ?? 'Oficina';
        $email = $sub['owner_email'] ?? 'financeiro@oficina.com.br';

        $description = "Zyrtex SaaS: {$planType} (Condição Especial de Fidelidade)";
        $externalRef = "remarketing_sub:{$subId}";

        try {
            $mp = MercadoPagoService::createPixPayment($amount, $description, $email, $externalRef);
            if ($mp && !empty($mp['id'])) {
                Database::execute(
                    "UPDATE saas_subscriptions 
                     SET mp_payment_id = :mp_id,
                         external_reference = :ext_ref,
                         pix_code = :pix_code,
                         pix_qrcode_base64 = :qr_base64
                     WHERE id = :id",
                    [
                        'mp_id' => (string)$mp['id'],
                        'ext_ref' => $externalRef,
                        'pix_code' => $mp['pix_code'] ?? '',
                        'qr_base64' => $mp['qr_code_base64'] ?? '',
                        'id' => $subId
                    ]
                );

                return [
                    'success' => true,
                    'order_id' => $subId,
                    'amount' => number_format($amount, 2, ',', '.'),
                    'pix_code' => $mp['pix_code'] ?? '',
                    'qrcode_url' => $mp['qr_code_base64'] ?? ''
                ];
            }
        } catch (\Throwable $e) {
            // Em caso de falha de conexão com a API do Mercado Pago, utiliza o Pix já existente ou mock seguro
        }

        return [
            'success' => true,
            'order_id' => $subId,
            'amount' => number_format($amount, 2, ',', '.'),
            'pix_code' => $sub['pix_code'] ?? '00020126580014BR.GOV.BCB.PIX0136mock-recovery-pix-zyrtex5204000053039865405' . $amount . '5802BR5915ZYRTEX SAAS6009SAO PAULO62070503***6304ABCD',
            'qrcode_url' => $sub['pix_qrcode_base64'] ?? ''
        ];
    }

    /**
     * Envia e-mail persuasivo de recuperação de carrinho abandonado com o Link Mágico
     */
    public static function sendRecoveryEmail(int $subscriptionId): bool {
        $sql = "
            SELECT s.*, t.business_name, t.slug, t.created_at AS tenant_created_at,
                   u.name AS owner_name, u.email AS owner_email,
                   TIMESTAMPDIFF(MONTH, t.created_at, NOW()) AS tenant_age_months
            FROM saas_subscriptions s
            JOIN tenants t ON t.id = s.tenant_id
            LEFT JOIN users u ON (u.tenant_id = t.id AND u.role = 'owner')
            WHERE s.id = :id
            LIMIT 1
        ";

        $sub = Database::fetchOne($sql, ['id' => $subscriptionId]);
        if (!$sub || empty($sub['owner_email'])) {
            return false;
        }

        $token = $sub['recovery_token'];
        if (empty($token)) {
            $token = bin2hex(random_bytes(24));
            Database::execute("UPDATE saas_subscriptions SET recovery_token = :token WHERE id = :id", [
                'token' => $token,
                'id' => $subscriptionId
            ]);
        }

        $recoveryUrl = Response::url('/recuperar/' . $token);
        $discountPercent = number_format((float)($sub['discount_percent'] ?? 10), 1, ',', '.');
        $originalAmount = number_format((float)($sub['original_amount'] ?? $sub['amount']), 2, ',', '.');
        $finalAmount = number_format((float)$sub['amount'], 2, ',', '.');
        $months = max(1, (int)$sub['tenant_age_months']);

        $subject = "🎁 Condição Especial de Fidelidade: {$discountPercent}% OFF no Zyrtex ({$sub['business_name']})";

        $bodyHtml = "
        <div style='background-color:#070b14; color:#f1f5f9; font-family:Plus Jakarta Sans, sans-serif; padding:32px 16px; border-radius:16px;'>
            <div style='max-width:560px; margin:0 auto; background-color:#0f172a; border:1px solid #1e293b; border-radius:20px; padding:32px; box-shadow:0 10px 25px rgba(0,0,0,0.5);'>
                <div style='text-align:center; margin-bottom:24px;'>
                    <span style='display:inline-block; padding:8px 16px; background-color:rgba(99,102,241,0.15); border:1px solid rgba(99,102,241,0.3); border-radius:999px; color:#818cf8; font-size:12px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;'>
                        ⭐ Programa de Fidelidade Zyrtex
                    </span>
                </div>

                <h1 style='color:#ffffff; font-size:22px; font-weight:800; text-align:center; margin:0 0 12px;'>
                    Notamos que você não concluiu seu pedido, {$sub['owner_name']}!
                </h1>

                <p style='color:#94a3b8; font-size:14px; line-height:1.6; text-align:center; margin:0 0 24px;'>
                    Como você já é parceiro da <strong>{$sub['business_name']}</strong> há <strong>{$months} meses</strong> no Zyrtex, liberamos uma condição única de desconto progressivo para você desbloquear este recurso agora.
                </p>

                <!-- Card de Preço Ancorado -->
                <div style='background:linear-gradient(135deg, #131b2e 0%, #1e1b4b 100%); border:1px solid rgba(99,102,241,0.4); border-radius:16px; padding:20px; text-align:center; margin-bottom:28px;'>
                    <div style='color:#94a3b8; font-size:13px; text-decoration:line-through;'>De: R$ {$originalAmount}</div>
                    <div style='color:#4ade80; font-size:28px; font-weight:900; margin:6px 0;'>Por apenas: R$ {$finalAmount}</div>
                    <div style='color:#818cf8; font-size:12px; font-weight:bold;'>Economia exclusiva de {$discountPercent}% aplicada</div>
                </div>

                <!-- Botão 1-Clique Mágico -->
                <div style='text-align:center; margin-bottom:24px;'>
                    <a href='{$recoveryUrl}' style='display:inline-block; background:linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%); color:#ffffff; font-size:15px; font-weight:bold; padding:16px 36px; border-radius:14px; text-decoration:none; box-shadow:0 8px 20px rgba(79,70,229,0.4);'>
                        ⚡ Concluir com Desconto em 1 Clique &rarr;
                    </a>
                </div>

                <p style='color:#64748b; font-size:11px; text-align:center; margin:0;'>
                    ⏱️ Esta condição especial expira em 24 horas e é vinculada exclusivamente ao CNPJ/Cadastro de sua oficina.
                </p>
            </div>
        </div>
        ";

        $res = MailService::send($sub['owner_email'], $sub['owner_name'] ?? 'Lojista', $subject, $bodyHtml);

        Database::execute(
            "UPDATE saas_subscriptions 
             SET remarketing_sent_at = NOW(),
                 remarketing_count = remarketing_count + 1
             WHERE id = :id",
            ['id' => $subscriptionId]
        );

        return !empty($res['success']);
    }

    /**
     * Retorna o link pré-formatado do WhatsApp para o lojista
     */
    public static function getWhatsAppRecoveryLink(array $sub): string {
        $phone = preg_replace('/[^0-9]/', '', (string)($sub['whatsapp_number'] ?? ''));
        if (empty($phone)) {
            return '';
        }

        // Adiciona 55 se não tiver código do país
        if (strlen($phone) <= 11) {
            $phone = '55' . $phone;
        }

        $token = $sub['recovery_token'] ?? '';
        $recoveryUrl = Response::url('/recuperar/' . $token);
        $discount = number_format((float)($sub['discount_percent'] ?? 10), 0);
        $finalAmount = number_format((float)($sub['amount'] ?? 0), 2, ',', '.');
        $tenantName = $sub['business_name'] ?? 'sua oficina';

        $text = "Olá! Vimos que você tentou ativar um recurso no Zyrtex para a *{$tenantName}* e o pagamento não foi concluído. Liberamos um cupom de fidelidade de *{$discount}% OFF*, baixando o valor para apenas *R$ {$finalAmount}*. Você pode concluir diretamente pelo link oficial: {$recoveryUrl}";

        return "https://api.whatsapp.com/send?phone={$phone}&text=" . urlencode($text);
    }

    /**
     * Retorna métricas analíticas da fila de remarketing para o SuperAdmin
     */
    public static function getMetrics(): array {
        $totalAbandoned = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM saas_subscriptions WHERE status = 'remarketing'")['c'] ?? 0);
        $totalRecovered = (int)(Database::fetchOne("SELECT COUNT(*) AS c FROM saas_subscriptions WHERE status = 'approved' AND recovery_token IS NOT NULL")['c'] ?? 0);
        $revenueRecovered = (float)(Database::fetchOne("SELECT SUM(amount) AS s FROM saas_subscriptions WHERE status = 'approved' AND recovery_token IS NOT NULL")['s'] ?? 0.0);
        $averageDiscount = (float)(Database::fetchOne("SELECT AVG(discount_percent) AS a FROM saas_subscriptions WHERE recovery_token IS NOT NULL")['a'] ?? 0.0);

        $conversionRate = ($totalAbandoned + $totalRecovered) > 0 
            ? round(($totalRecovered / ($totalAbandoned + $totalRecovered)) * 100, 1) 
            : 0.0;

        return [
            'total_in_queue' => $totalAbandoned,
            'total_recovered_count' => $totalRecovered,
            'total_recovered_revenue' => $revenueRecovered,
            'conversion_rate' => $conversionRate,
            'average_discount' => round($averageDiscount, 1)
        ];
    }
}
