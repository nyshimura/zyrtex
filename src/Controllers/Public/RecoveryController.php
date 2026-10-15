<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Public;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\View;
use Zyrtex\Services\RemarketingService;

/**
 * Controller Público de Resgate de Pedidos Abandonados (Página do Link Mágico)
 * Rota: /recuperar/{token}
 */
class RecoveryController {

    /**
     * Exibe a página exclusiva de recuperação com desconto e Pix instantâneo
     */
    public function show(Request $request, string $token): void {
        $sub = RemarketingService::getByRecoveryToken($token);

        if (!$sub) {
            http_response_code(404);
            View::render('errors/404', [
                'message' => 'Este link de recuperação expirou ou não foi encontrado.'
            ], 'saas');
            return;
        }

        // Se já foi aprovado
        $isApproved = ($sub['status'] === 'approved');

        // Gera ou recupera dados do Pix
        $pixData = [];
        if (!$isApproved) {
            $pixData = RemarketingService::generateRecoveryPix($sub);
        }

        $origAmount = (float)($sub['original_amount'] ?: $sub['amount']);
        $finalAmount = (float)$sub['amount'];
        $discountPercent = (float)($sub['discount_percent'] ?: 0);
        $discountVal = (float)($sub['discount_amount'] ?: ($origAmount - $finalAmount));

        $planNames = [
            'slug_change' => 'Alteração de Subdomínio Personalizado',
            'annual_domain' => 'Domínio Próprio & Plano Anual Zyrtex Pro',
            'niche_change' => 'Migração de Nicho Especializado'
        ];

        $planTitle = $planNames[$sub['plan_type']] ?? 'Upgrade Zyrtex SaaS';

        View::render('public/recovery', [
            'token' => $token,
            'sub' => $sub,
            'isApproved' => $isApproved,
            'pixData' => $pixData,
            'planTitle' => $planTitle,
            'origAmount' => $origAmount,
            'finalAmount' => $finalAmount,
            'discountPercent' => $discountPercent,
            'discountVal' => $discountVal
        ], 'saas');
    }

    /**
     * Endpoint de Polling para verificar se o Pix do remarketing foi pago
     * (GET /recuperar/{token}/status)
     */
    public function checkStatus(Request $request, string $token): void {
        $sub = RemarketingService::getByRecoveryToken($token);

        if (!$sub) {
            Response::json(['success' => false, 'message' => 'Transação não encontrada'], 404);
            return;
        }

        $isApproved = ($sub['status'] === 'approved');

        Response::json([
            'success' => true,
            'is_approved' => $isApproved,
            'status' => $sub['status'],
            'redirect_url' => Response::url('/login?recovered=1')
        ]);
    }
}
