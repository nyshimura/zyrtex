<?php

declare(strict_types=1);

namespace Zyrtex\Core;

/**
 * Sistema Central de Auditoria e Rastreabilidade Zyrtex
 * Registra todas as ações críticas em conformidade com LGPD e rastreio de bancada.
 */
class Audit {
    /**
     * Grava um log de auditoria no banco de dados.
     */
    public static function log(?int $tenantId, ?int $userId, string $action, array $details = []): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            Database::execute(
                "INSERT INTO audit_logs (tenant_id, user_id, action, details_json, ip_address) 
                 VALUES (:tenant_id, :user_id, :action, :details, :ip)",
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'action' => $action,
                    'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
                    'ip' => $ip
                ]
            );
        } catch (\Throwable $e) {
            // Log de erro interno silencioso para nunca interromper o fluxo da aplicação
            error_log("[Zyrtex Audit Error] " . $e->getMessage());
        }
    }
}
