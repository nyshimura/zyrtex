<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Public;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Database;
use Zyrtex\Core\Session;
use Zyrtex\Services\MercadoPagoService;

/**
 * Controller de Webhooks do Mercado Pago para o SaaS Zyrtex (RF02 / Monetização Automatizada)
 * Responsável por receber a notificação bancária, validar o pagamento e ativar recursos automaticamente.
 */
class WebhookController {

    /**
     * Webhook Global do SaaS Zyrtex (POST /webhook/saas ou POST /webhook/mercadopago)
     */
    public function handleSaaS(Request $request): void {
        $rawBody = file_get_contents('php://input');
        $json = json_decode($rawBody, true) ?: [];
        if (empty($json)) {
            $json = $request->all();
        }

        // Identifica o ID do pagamento enviado pelo Mercado Pago (via JSON body ou query params)
        $paymentId = $json['data']['id'] 
            ?? $json['id'] 
            ?? $request->post('id')
            ?? $request->get('id') 
            ?? $request->get('data_id') 
            ?? null;

        // Se veio no formato de recurso URL (ex: /v1/payments/12345678)
        if (empty($paymentId) && !empty($json['resource'])) {
            if (preg_match('#payments/([0-9a-zA-Z_\-]+)#', (string)$json['resource'], $m)) {
                $paymentId = $m[1];
            }
        }

        $type = $json['type'] ?? $json['topic'] ?? $request->get('type') ?? $request->get('topic') ?? 'payment';

        // Registra log técnico de auditoria para rastreabilidade
        try {
            Database::execute(
                "INSERT INTO audit_logs (action, details_json, ip_address) VALUES ('webhook_saas_received', :details, :ip)",
                [
                    'details' => json_encode([
                        'payment_id' => $paymentId,
                        'type' => $type,
                        'get' => $request->query('id') ?? $_GET,
                        'payload' => $json
                    ]),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]
            );
        } catch (\Throwable $e) {
            // Silencioso
        }

        if (empty($paymentId)) {
            // Notificação de teste do Mercado Pago ou ping sem dados de pagamento
            Response::json(['status' => 'ignored', 'message' => 'Nenhum payment_id identificado no webhook'], 200);
            return;
        }

        // 1. Consulta dados do pagamento no Mercado Pago
        $paymentData = MercadoPagoService::getPayment($paymentId);

        $status = $paymentData['status'] ?? $json['status'] ?? null;
        $externalReference = $paymentData['external_reference'] ?? $json['external_reference'] ?? null;

        // Se não obteve referência externa pela API, busca no banco pelo mp_payment_id
        if (empty($externalReference)) {
            $subByPayment = Database::fetchOne(
                "SELECT id, external_reference, plan_type, target_value FROM saas_subscriptions WHERE mp_payment_id = :mp_id LIMIT 1",
                ['mp_id' => (string)$paymentId]
            );
            if ($subByPayment) {
                $externalReference = $subByPayment['external_reference'];
            }
        }

        // Se o pagamento foi aprovado
        if ($status === 'approved' && !empty($externalReference)) {
            $this->processApprovedOrder($externalReference, (string)$paymentId, $paymentData);
            Response::json(['status' => 'approved', 'message' => 'Pagamento processado e serviço ativado com sucesso'], 200);
            return;
        }

        Response::json(['status' => 'received', 'current_payment_status' => $status], 200);
    }

    /**
     * Endpoint para simulação de webhook no ambiente de desenvolvimento/testes locais
     * (POST /webhook/saas/simular)
     */
    public function simulate(Request $request): void {
        $orderId = (int)($request->post('order_id') ?? $request->get('order_id') ?? 0);
        $externalRef = (string)($request->post('external_reference') ?? $request->get('external_reference') ?? '');

        if ($orderId > 0 && empty($externalRef)) {
            $sub = Database::fetchOne("SELECT id, plan_type, external_reference FROM saas_subscriptions WHERE id = :id LIMIT 1", ['id' => $orderId]);
            if ($sub) {
                $externalRef = $sub['external_reference'] ?: "slug_change:{$sub['id']}";
            }
        }

        if (empty($externalRef)) {
            Response::json(['status' => 'error', 'message' => 'Identificador da ordem ou external_reference não informado'], 400);
            return;
        }

        $mockPaymentId = 'simulated_' . uniqid();
        $this->processApprovedOrder($externalRef, $mockPaymentId, ['status' => 'approved', 'simulated' => true]);

        Response::json([
            'status' => 'approved',
            'message' => 'Simulação de webhook executada com sucesso! Pedido aprovado.',
            'external_reference' => $externalRef
        ], 200);
    }

    /**
     * Processa a ativação da assinatura ou serviço adquirido após aprovação bancária
     */
    private function processApprovedOrder(string $externalReference, string $paymentId, ?array $paymentDetails): void {
        // Se for pagamento de Ordem de Serviço (OS) pelo cliente da oficina
        if (str_starts_with($externalReference, 'os_payment:') || str_starts_with($externalReference, 'os:')) {
            $parts = explode(':', $externalReference);
            $rawIds = explode(',', $parts[1] ?? '');
            $osIds = array_filter(array_map('intval', $rawIds), fn($id) => $id > 0);

            if (!empty($osIds)) {
                Database::beginTransaction();
                try {
                    foreach ($osIds as $osId) {
                        $os = Database::fetchOne("SELECT * FROM service_orders WHERE id = :id LIMIT 1", ['id' => $osId]);
                        if ($os) {
                            Database::execute(
                                "UPDATE service_orders 
                                 SET payment_status = 'paid', 
                                     payment_method = 'mercadopago',
                                     status = CASE WHEN status = 'budget' THEN 'approved' ELSE status END
                                 WHERE id = :id",
                                ['id' => $osId]
                            );

                            try {
                                Database::execute(
                                    "INSERT INTO audit_logs (tenant_id, action, details_json, ip_address) 
                                     VALUES (:tenant_id, 'os_payment_approved_mercadopago', :details, :ip)",
                                    [
                                        'tenant_id' => (int)$os['tenant_id'],
                                        'details' => json_encode([
                                            'os_id' => $osId,
                                            'os_number' => $os['os_number'],
                                            'total_value' => (float)$os['total_value'],
                                            'mp_payment_id' => $paymentId,
                                            'external_reference' => $externalReference,
                                            'details' => $paymentDetails
                                        ]),
                                        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                                    ]
                                );
                            } catch (\Throwable $e) {}

                            // Valida e Concede Bônus de Indicação Anti-Fraude (1ª Transação Real da Oficina)
                            \Zyrtex\Services\ReferralService::qualifyReferral((int)$os['tenant_id'], 'first_os_paid', 'os:' . $osId);
                        }
                    }
                    Database::commit();
                } catch (\Throwable $e) {
                    Database::rollBack();
                    error_log("Erro ao dar baixa em lote de OS no webhook: " . $e->getMessage());
                }
            }
            return;
        }

        // Formato esperado: "slug_change:{subscription_id}" ou "niche_change:{subscription_id}"
        $parts = explode(':', $externalReference);
        $type = $parts[0] ?? '';
        $subId = isset($parts[1]) ? (int)$parts[1] : 0;

        $sub = null;
        if ($subId > 0) {
            $sub = Database::fetchOne(
                "SELECT * FROM saas_subscriptions WHERE id = :id LIMIT 1",
                ['id' => $subId]
            );
        }

        if (!$sub && !empty($externalReference)) {
            $sub = Database::fetchOne(
                "SELECT * FROM saas_subscriptions WHERE external_reference = :ref LIMIT 1",
                ['ref' => $externalReference]
            );
        }

        if (!$sub && !empty($paymentId)) {
            $sub = Database::fetchOne(
                "SELECT * FROM saas_subscriptions WHERE mp_payment_id = :mp_id LIMIT 1",
                ['mp_id' => $paymentId]
            );
        }

        if (!$sub) {
            return;
        }

        // Idempotência: Se já foi aprovado, não reprocessa
        if ($sub['status'] === 'approved') {
            return;
        }

        $tenantId = (int)$sub['tenant_id'];

        // Processa de acordo com o tipo de pedido

        if ($sub['plan_type'] === 'slug_change') {
            $newSlug = trim((string)$sub['target_value']);

            if (!empty($newSlug)) {
                // Atualiza o slug oficial do Tenant no banco de dados
                Database::execute(
                    "UPDATE tenants SET slug = :slug, default_url = :default_url WHERE id = :id",
                    [
                        'slug' => $newSlug,
                        'default_url' => "{$newSlug}.zyrtex.com.br",
                        'id' => $tenantId
                    ]
                );

                // Se houver sessão ativa do lojista no navegador atual, atualiza
                if (Session::get('tenant_id') === $tenantId) {
                    Session::set('current_tenant_slug', $newSlug);
                    $user = Session::get('user');
                    if (is_array($user)) {
                        $user['tenant_slug'] = $newSlug;
                        Session::set('user', $user);
                    }
                }
            }
        } elseif ($sub['plan_type'] === 'niche_change') {
            $newNiche = trim((string)$sub['target_value']);
            if (!empty($newNiche)) {
                Database::execute(
                    "UPDATE tenants SET niche = :niche WHERE id = :id",
                    ['niche' => $newNiche, 'id' => $tenantId]
                );
            }
        } elseif ($sub['plan_type'] === 'annual_domain' || $sub['plan_type'] === 'annual_premium') {
            Database::execute(
                "UPDATE tenants 
                 SET is_premium = 1, 
                     expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR) 
                 WHERE id = :id",
                [
                    'id' => $tenantId
                ]
            );
        }

        // Marca a assinatura/taxa como Aprovada
        Database::execute(
            "UPDATE saas_subscriptions 
             SET status = 'approved', mp_payment_id = :mp_id, purchased_at = NOW() 
             WHERE id = :id",
            [
                'mp_id' => $paymentId,
                'id' => (int)$sub['id']
            ]
        );

        // Valida e Concede Bônus de Indicação Anti-Fraude (Oficina pagou plano/serviço no SaaS)
        \Zyrtex\Services\ReferralService::qualifyReferral($tenantId, 'subscription_approved', 'sub:' . $sub['id']);

        // Registra log de auditoria
        try {
            Database::execute(
                "INSERT INTO audit_logs (tenant_id, action, details_json, ip_address) 
                 VALUES (:tenant_id, :action, :details, :ip)",
                [
                    'tenant_id' => $tenantId,
                    'action' => 'saas_payment_approved_' . $sub['plan_type'],
                    'details' => json_encode([
                        'subscription_id' => $sub['id'],
                        'plan_type' => $sub['plan_type'],
                        'target_value' => $sub['target_value'],
                        'amount' => $sub['amount'],
                        'mp_payment_id' => $paymentId,
                        'webhook_details' => $paymentDetails
                    ]),
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]
            );
        } catch (\Throwable $e) {
            // Silencioso
        }
    }
}
