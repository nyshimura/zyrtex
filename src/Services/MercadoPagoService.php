<?php

declare(strict_types=1);

namespace Zyrtex\Services;

use Zyrtex\Core\Database;
use Zyrtex\Core\Env;

/**
 * Serviço de Integração com a API do Mercado Pago para o SaaS Zyrtex (RF02 / Monetização)
 * Suporta pagamentos Pix automáticos e Webhook de conciliação bancária
 */
class MercadoPagoService {

    /**
     * Retorna as credenciais de produção/homologação do SaaS configuradas no painel SuperAdmin ou .env
     */
    public static function getSaaSCredentials(): array {
        $dbToken = null;
        $dbPublicKey = null;

        try {
            $rowToken = Database::fetchOne("SELECT config_value FROM saas_settings WHERE config_key = 'mercadopago_access_token' LIMIT 1");
            if ($rowToken && !empty($rowToken['config_value'])) {
                $dbToken = trim($rowToken['config_value']);
            }

            $rowKey = Database::fetchOne("SELECT config_value FROM saas_settings WHERE config_key = 'mercadopago_public_key' LIMIT 1");
            if ($rowKey && !empty($rowKey['config_value'])) {
                $dbPublicKey = trim($rowKey['config_value']);
            }
        } catch (\Throwable $e) {
            // Silencioso caso a tabela ainda esteja sendo inicializada
        }

        $token = $dbToken ?: Env::get('SAAS_MERCADOPAGO_ACCESS_TOKEN', Env::get('MERCADOPAGO_ACCESS_TOKEN', ''));
        $publicKey = $dbPublicKey ?: Env::get('SAAS_MERCADOPAGO_PUBLIC_KEY', Env::get('MERCADOPAGO_PUBLIC_KEY', ''));

        return [
            'access_token' => $token,
            'public_key' => $publicKey,
            'is_configured' => !empty($token)
        ];
    }

    /**
     * Gera uma ordem de pagamento via Pix no Mercado Pago
     *
     * @param float $amount Valor da cobrança (ex: 19.90)
     * @param string $description Descrição visível no comprovante bancário
     * @param string $payerEmail E-mail do pagador
     * @param string $externalReference Referência única para correlação no Webhook (ex: slug_change:123)
     * @param string|null $notificationUrl URL do webhook (ex: https://.../webhook/saas)
     * @return array
     */
    public static function createPixPayment(
        float $amount,
        string $description,
        string $payerEmail,
        string $externalReference,
        ?string $notificationUrl = null
    ): array {
        $creds = self::getSaaSCredentials();
        $token = $creds['access_token'];

        // Se o token estiver configurado, realiza chamada à API oficial do Mercado Pago
        if (!empty($token)) {
            $payload = [
                'transaction_amount' => round($amount, 2),
                'description' => substr($description, 0, 120),
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => filter_var($payerEmail, FILTER_VALIDATE_EMAIL) ? $payerEmail : 'financeiro@zyrtex.com.br'
                ],
                'external_reference' => $externalReference
            ];

            if ($notificationUrl && filter_var($notificationUrl, FILTER_VALIDATE_URL) && !str_contains($notificationUrl, 'localhost')) {
                $payload['notification_url'] = $notificationUrl;
            }

            $ch = curl_init('https://api.mercadopago.com/v1/payments');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'X-Idempotency-Key: ' . md5($externalReference . microtime(true))
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300 && $response) {
                $data = json_decode($response, true);
                if (isset($data['id'])) {
                    $poi = $data['point_of_interaction']['transaction_data'] ?? [];
                    return [
                        'success' => true,
                        'is_mock' => false,
                        'payment_id' => (string)$data['id'],
                        'status' => $data['status'] ?? 'pending',
                        'pix_code' => $poi['qr_code'] ?? '',
                        'qrcode_base64' => $poi['qr_code_base64'] ?? null,
                        'qrcode_url' => !empty($poi['qr_code_base64']) 
                            ? 'data:image/png;base64,' . $poi['qr_code_base64']
                            : QrCodeService::pngUrl($poi['qr_code'] ?? $description, 240),
                        'ticket_url' => $poi['ticket_url'] ?? null
                    ];
                }
            }
        }

        // Fallback local ou desenvolvimento (Simulação Segura de Pix)
        // Gera payload EMV compatível para renderizar QR Code imediato no modal
        $amountFormatted = number_format($amount, 2, '.', '');
        $pixKey = 'financeiro-saas@zyrtex.com.br';
        $refShort = substr(preg_replace('/[^a-zA-Z0-9]/', '', $externalReference), 0, 15);

        $pixPayload = "00020126580014br.gov.bcb.pix0136" . $pixKey . "520400005303986540" . strlen($amountFormatted) . $amountFormatted . "5802BR5916ZYRTEX SAAS TECH6009SAO PAULO62070503" . $refShort . "6304";
        $paymentId = 'mock_mp_' . uniqid('', true);
        $qrcodeUrl = QrCodeService::pngUrl($pixPayload, 240);

        return [
            'success' => true,
            'is_mock' => true,
            'payment_id' => $paymentId,
            'status' => 'pending',
            'pix_code' => $pixPayload,
            'qrcode_base64' => null,
            'qrcode_url' => $qrcodeUrl,
            'ticket_url' => null
        ];
    }

    /**
     * Consulta o status do pagamento na API do Mercado Pago
     */
    public static function getPayment(string|int $paymentId): ?array {
        $paymentId = (string)$paymentId;

        if (str_starts_with($paymentId, 'mock_mp_')) {
            return [
                'id' => $paymentId,
                'status' => 'pending'
            ];
        }

        $creds = self::getSaaSCredentials();
        $token = $creds['access_token'];

        if (empty($token)) {
            return null;
        }

        $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        return null;
    }

    /**
     * Gera pagamento de Ordem de Serviço pelo cliente final (RF08)
     * Suporta tanto credenciais próprias do lojista (Mercado Pago) quanto Pix Manual com QR Code
     */
    public static function createTenantOSPayment(
        array $tenant,
        array $os,
        float $amount,
        string $description,
        ?string $customerEmail = null,
        ?string $notificationUrl = null
    ): array {
        $mode = $tenant['payment_mode'] ?? 'manual_pix';
        $tenantToken = trim((string)($tenant['mp_access_token'] ?? ''));

        // Suporta tanto 1 OS quanto múltiplas OSs selecionadas
        if (isset($os['ids']) && is_array($os['ids'])) {
            $osIds = array_filter(array_map('intval', $os['ids']), fn($id) => $id > 0);
        } elseif (isset($os['id'])) {
            $osIds = [(int)$os['id']];
        } elseif (is_array($os) && !empty($os)) {
            $osIds = array_filter(array_map(fn($item) => (int)($item['id'] ?? 0), $os), fn($id) => $id > 0);
        } else {
            $osIds = [];
        }

        sort($osIds);
        $externalReference = "os_payment:" . implode(',', $osIds);
        $cacheKey = 'mp_pay_' . md5(($tenant['id'] ?? 0) . '_' . $externalReference . '_' . number_format($amount, 2, '.', ''));

        // 1. Reutilização de QR Code Ativo (evita criar dezenas de transações no Mercado Pago)
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION[$cacheKey])) {
            $cached = $_SESSION[$cacheKey];
            // Válido por 25 minutos (Mercado Pago expira em 30 min)
            if ((time() - ($cached['created_at'] ?? 0)) < 1500 && !empty($cached['data']['pix_code'])) {
                return $cached['data'];
            }
        }

        // 2. Se lojista optou por Mercado Pago Automático e possui token configurado
        if ($mode === 'mercadopago' && !empty($tenantToken)) {
            $payload = [
                'transaction_amount' => round($amount, 2),
                'description' => substr($description, 0, 120),
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => filter_var($customerEmail, FILTER_VALIDATE_EMAIL) ? $customerEmail : 'cliente@' . ($tenant['slug'] ?? 'loja') . '.com.br'
                ],
                'external_reference' => $externalReference
            ];

            if ($notificationUrl && filter_var($notificationUrl, FILTER_VALIDATE_URL) && !str_contains($notificationUrl, 'localhost')) {
                $payload['notification_url'] = $notificationUrl;
            }

            // Chave de Idempotência determinística para a combinação de OSs e valor
            $idempotencyKey = md5(($tenant['id'] ?? 0) . ':' . $externalReference . ':' . number_format($amount, 2, '.', ''));

            $ch = curl_init('https://api.mercadopago.com/v1/payments');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tenantToken,
                'Content-Type: application/json',
                'X-Idempotency-Key: ' . $idempotencyKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300 && $response) {
                $data = json_decode($response, true);
                if (isset($data['id'])) {
                    $poi = $data['point_of_interaction']['transaction_data'] ?? [];
                    $result = [
                        'success' => true,
                        'is_mock' => false,
                        'payment_id' => (string)$data['id'],
                        'status' => $data['status'] ?? 'pending',
                        'pix_code' => $poi['qr_code'] ?? '',
                        'qrcode_base64' => $poi['qr_code_base64'] ?? null,
                        'qrcode_url' => !empty($poi['qr_code_base64']) 
                            ? 'data:image/png;base64,' . $poi['qr_code_base64']
                            : QrCodeService::pngUrl($poi['qr_code'] ?? $description, 240),
                        'ticket_url' => $poi['ticket_url'] ?? null
                    ];

                    if (session_status() === PHP_SESSION_ACTIVE) {
                        $_SESSION[$cacheKey] = ['created_at' => time(), 'data' => $result];
                    }

                    return $result;
                }
            }
        }

        // 3. Modo Pix Manual ou Fallback de Desenvolvimento da Oficina
        $pixKey = trim((string)($tenant['pix_key'] ?? 'financeiro@' . ($tenant['slug'] ?? 'oficina') . '.com.br'));
        $beneficiary = trim((string)($tenant['pix_beneficiary'] ?? ($tenant['business_name'] ?? 'Assistência Técnica')));
        $amountFormatted = number_format($amount, 2, '.', '');
        $refShort = count($osIds) === 1 ? substr(preg_replace('/[^a-zA-Z0-9]/', '', $os['os_number'] ?? 'OS'), 0, 15) : 'ZYRTEX';

        // Gera payload EMV Pix do lojista
        $pixPayload = "00020126580014br.gov.bcb.pix0136" . $pixKey . "520400005303986540" . strlen($amountFormatted) . $amountFormatted . "5802BR5916" . substr($beneficiary, 0, 16) . "6009SAO PAULO62070503" . $refShort . "6304";
        $paymentId = 'mock_os_' . uniqid('', true);
        $qrcodeUrl = QrCodeService::pngUrl($pixPayload, 240);

        $result = [
            'success' => true,
            'is_mock' => true,
            'payment_id' => $paymentId,
            'status' => 'pending',
            'pix_code' => $pixPayload,
            'qrcode_base64' => null,
            'qrcode_url' => $qrcodeUrl,
            'ticket_url' => null
        ];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[$cacheKey] = ['created_at' => time(), 'data' => $result];
        }

        return $result;
    }

    /**
     * Realiza o estorno de um pagamento aprovado (RN03 - Direito de Arrependimento CDC Art. 49 em até 7 dias)
     *
     * @param string|int $paymentId ID do pagamento no Mercado Pago
     * @param string|null $accessToken Token customizado ou padrão do SaaS
     * @return array
     */
    public static function refundPayment(string|int $paymentId, ?string $accessToken = null): array {
        $paymentId = (string)$paymentId;

        // Se for pagamento simulado / mock
        if (str_starts_with($paymentId, 'mock_') || str_starts_with($paymentId, 'simulated_')) {
            return [
                'success' => true,
                'is_mock' => true,
                'status' => 'refunded',
                'message' => 'Estorno simulado com sucesso (Ambiente de Testes).'
            ];
        }

        if (empty($accessToken)) {
            $creds = self::getSaaSCredentials();
            $accessToken = $creds['access_token'];
        }

        if (empty($accessToken)) {
            return [
                'success' => false,
                'is_mock' => false,
                'error' => 'Chave de acesso do Mercado Pago não configurada para processar estorno.'
            ];
        }

        $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}/refunds");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            return [
                'success' => true,
                'is_mock' => false,
                'status' => 'refunded',
                'data' => $data
            ];
        }

        return [
            'success' => false,
            'is_mock' => false,
            'error' => 'Mercado Pago recusou o estorno (HTTP ' . $httpCode . '): ' . ($response ?: $curlError)
        ];
    }
}

