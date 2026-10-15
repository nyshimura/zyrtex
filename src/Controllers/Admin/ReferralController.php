<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\View;
use Zyrtex\Core\Auth;
use Zyrtex\Services\ReferralService;

/**
 * Controller do Programa de Indicação no Painel da Oficina (Tenant)
 * Rota: /admin/indicacoes
 */
class ReferralController {

    /**
     * Painel de Indique & Ganhe da Oficina
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $data = ReferralService::getTenantDashboardData($tenantId);

        View::render('admin/referrals/index', [
            'tenant' => $tenant,
            'user' => $user,
            'pageTitle' => 'Indique & Ganhe • Zyrtex Pro',
            'referralCode' => $data['referral_code'],
            'referralUrl' => $data['referral_url'],
            'whatsappShareUrl' => $data['whatsapp_share_url'],
            'whatsappShareText' => $data['whatsapp_share_text'],
            'totalInvited' => $data['total_invited'],
            'pendingValidation' => $data['pending_validation'],
            'rewardedCount' => $data['rewarded_count'],
            'totalDaysEarned' => $data['total_days_earned'],
            'rewardDaysPerReferral' => $data['reward_days_per_referral'],
            'welcomeDays' => $data['welcome_days'],
            'referralsList' => $data['referrals_list']
        ], 'admin');
    }
}
