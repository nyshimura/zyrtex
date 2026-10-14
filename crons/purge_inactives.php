<?php

declare(strict_types=1);

/**
 * Script de Varredura de Contas Inativas do SaaS Zyrtex (RN-Shield01)
 * Executável via CLI: php crons/purge_inactives.php
 * Regra:
 *  - 90 dias sem nenhuma OS e sem login: Emite alerta de inatividade
 *  - 120 dias sem nenhuma atividade (Plano Free): Suspende automaticamente a oficina para proteção de recursos
 */

require_once __DIR__ . '/../src/autoload.php';

use Zyrtex\Core\Env;
use Zyrtex\Core\Database;

// Carrega .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    Env::load($envFile);
}

$isCli = (php_sapi_name() === 'cli');

echo "========================================================\n";
echo "  ZYRTEX SAAS - CRON DE LIMPEZA DE INATIVOS (RN-Shield01)\n";
echo "  Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "========================================================\n\n";

try {
    // 1. Busca todas as oficinas no Plano Free
    $freeTenants = Database::fetchAll("SELECT id, business_name, slug, active, created_at FROM tenants WHERE is_premium = 0");

    $warnCount = 0;
    $suspendCount = 0;
    $checkedCount = count($freeTenants);

    echo "Total de oficinas no Plano Free avaliadas: {$checkedCount}\n\n";

    foreach ($freeTenants as $t) {
        $tenantId = (int)$t['id'];

        // 2. Busca data da última OS criada
        $lastOsDate = Database::fetchValue(
            "SELECT MAX(created_at) FROM service_orders WHERE tenant_id = :id",
            ['id' => $tenantId]
        );

        // 3. Busca data do último login ou evento de auditoria
        $lastAuditDate = Database::fetchValue(
            "SELECT MAX(created_at) FROM audit_logs WHERE tenant_id = :id",
            ['id' => $tenantId]
        );

        $mostRecent = $lastOsDate;
        if (!empty($lastAuditDate) && ($mostRecent === null || strtotime($lastAuditDate) > strtotime($mostRecent))) {
            $mostRecent = $lastAuditDate;
        }
        if ($mostRecent === null) {
            $mostRecent = $t['created_at'];
        }

        $lastTimestamp = strtotime($mostRecent);
        $daysInactive = (int)floor((time() - $lastTimestamp) / 86400);

        echo sprintf("Oficina ID %d [%s / %s]: %d dias inativo (Última atividade: %s)\n",
            $tenantId,
            $t['business_name'],
            $t['slug'],
            $daysInactive,
            date('Y-m-d', $lastTimestamp)
        );

        // Alerta de 90 dias
        if ($daysInactive >= 90 && $daysInactive < 120) {
            $warnCount++;
            Database::execute(
                "INSERT INTO audit_logs (tenant_id, action, details_json, ip_address) 
                 VALUES (:tenant_id, 'cron_shield_90d_warning', :details, '127.0.0.1')",
                [
                    'tenant_id' => $tenantId,
                    'details' => json_encode([
                        'days_inactive' => $daysInactive,
                        'last_activity' => $mostRecent,
                        'notice' => 'Oficina inativa há mais de 90 dias. Sujeita a suspensão aos 120 dias.'
                    ])
                ]
            );
            echo "  -> [ALERTA 90 DIAS] Marcado para notificação de inatividade.\n";
        }

        // Suspensão aos 120 dias
        if ($daysInactive >= 120 && (int)$t['active'] === 1) {
            $suspendCount++;
            Database::execute("UPDATE tenants SET active = 0 WHERE id = :id", ['id' => $tenantId]);

            Database::execute(
                "INSERT INTO audit_logs (tenant_id, action, details_json, ip_address) 
                 VALUES (:tenant_id, 'cron_shield_120d_suspended', :details, '127.0.0.1')",
                [
                    'tenant_id' => $tenantId,
                    'details' => json_encode([
                        'days_inactive' => $daysInactive,
                        'last_activity' => $mostRecent,
                        'action' => 'Oficina suspensa automaticamente por inatividade prolongada (RN-Shield01).'
                    ])
                ]
            );
            echo "  -> [SUSPENSÃO 120 DIAS] Oficina suspensa automaticamente para preservar recursos.\n";
        }
    }

    echo "\n--------------------------------------------------------\n";
    echo "Resumo da Execução:\n";
    echo " - Oficinas checadas: {$checkedCount}\n";
    echo " - Notificadas (90-119 dias): {$warnCount}\n";
    echo " - Suspensas (120+ dias): {$suspendCount}\n";
    echo "Status: SUCESSO\n";
    echo "--------------------------------------------------------\n";

} catch (\Throwable $e) {
    echo "\nERRO FATAL NO CRON: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
