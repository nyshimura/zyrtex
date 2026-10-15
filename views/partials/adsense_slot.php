<?php
/**
 * Partial: Slot de Anúncio AdSense Inteligente com Anti-AdBlock e Fallback de Monetização
 * 
 * Regras de Negócio e Segurança:
 * 1. Supressão Total: Se o tenant for Premium (Plano Anual Sem Anúncios), NENHUM anúncio ou script é carregado.
 * 2. Blindagem de Origem: Em ambiente local (localhost/127.0.0.1), renderiza em modo Sandbox para evitar cliques/impressões inválidas no Google.
 * 3. Detector Anti-AdBlock: Se o visitante usar bloqueador de anúncio, substitui instantaneamente por um card nativo de alta conversão
 *    (Apoio Voluntário via Pix no público, ou Upgrade do Plano Anual no painel da oficina) — sem buracos cinzas e SEM bloquear a usabilidade da OS.
 */

// 1. Supressão Rígida para Oficinas Premium
if (!empty($tenant['is_premium'])) {
    return;
}

// 2. Cache de Parâmetros do SaaS
static $saasSettingsCache = null;
if ($saasSettingsCache === null) {
    try {
        $rows = \Zyrtex\Core\Database::fetchAll(
            "SELECT config_key, config_value FROM saas_settings WHERE config_key IN ('adsense_active', 'adsense_script', 'saas_pix_key', 'saas_pix_beneficiary', 'annual_domain_price')"
        );
        $saasSettingsCache = [];
        foreach ($rows as $r) {
            $saasSettingsCache[$r['config_key']] = $r['config_value'];
        }
    } catch (\Throwable) {
        $saasSettingsCache = [];
    }
}

$adsActive = ($saasSettingsCache['adsense_active'] ?? '1') === '1';
if (!$adsActive) {
    return;
}

$adScript = trim($saasSettingsCache['adsense_script'] ?? '');
$saasPixKey = trim($saasSettingsCache['saas_pix_key'] ?? '');
$saasPixBeneficiary = trim($saasSettingsCache['saas_pix_beneficiary'] ?? 'Zyrtex SaaS');
$annualPrice = trim($saasSettingsCache['annual_domain_price'] ?? '197.00');

$slotContext = $slot_context ?? 'public'; // 'tracking', 'portal', 'home_hero', 'home_footer', 'admin', 'public'
$slotId = 'zyrtex-ad-' . bin2hex(random_bytes(3));

// Verificação de Origem e Sandbox de Desenvolvimento
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$isLocal = empty($host) || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_starts_with($host, '192.168.') || PHP_SAPI === 'cli';
?>

<div id="<?= $slotId ?>-wrapper" class="w-full my-6 transition-all duration-300">
    <div class="flex flex-col items-center justify-center text-center">
        
        <!-- Label de Transparência Conforme Diretrizes do Google AdSense -->
        <span class="mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 flex items-center gap-1.5 select-none">
            <span>📢</span>
            <span>Publicidade Patrocinada</span>
        </span>

        <!-- Contêiner do Anúncio Oficial -->
        <div id="<?= $slotId ?>-adbox" class="w-full max-w-4xl min-h-[90px] rounded-2xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-100/70 dark:bg-slate-900/50 p-2 sm:p-4 flex items-center justify-center overflow-hidden transition-all duration-200">
            <?php if ($isLocal): ?>
                <!-- Ambiente Sandbox (Local): Protege a conta do AdSense contra tráfego inválido -->
                <div class="flex flex-col sm:flex-row items-center gap-3 p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/40 text-left">
                    <span class="text-2xl">🛡️</span>
                    <div>
                        <div class="text-xs font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                            <span>Google AdSense Ativo</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-indigo-200/50 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-200">Sandbox Local</span>
                        </div>
                        <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">
                            Em ambiente de testes local, os anúncios externos são pausados para proteger sua conta contra infrações de tráfego inválido. Em produção no domínio oficial, o banner real do Google será renderizado aqui.
                        </p>
                    </div>
                </div>
            <?php elseif (!empty($adScript)): ?>
                <!-- Bloco de Script Oficial Cadastrado no SuperAdmin -->
                <div class="ad-banner-inner w-full flex items-center justify-center">
                    <?= $adScript ?>
                </div>
            <?php else: ?>
                <!-- Placeholder Padrão Responsivo -->
                <div class="flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 py-4">
                    <span>Espaço Reservado para Anúncio AdSense (Configure o script no SuperAdmin)</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Contêiner de Fallback Anti-AdBlock (Renderizado instantaneamente se o anúncio for bloqueado) -->
        <div id="<?= $slotId ?>-fallback" class="hidden w-full max-w-4xl rounded-2xl border border-amber-500/30 bg-gradient-to-r from-amber-500/10 via-slate-900/90 to-slate-900/90 p-4 sm:p-5 text-left shadow-sm transition-all duration-300">
            <?php if ($slotContext === 'admin'): ?>
                <!-- Fallback para o Painel do Lojista (Upsell do Plano Anual Sem Anúncios) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl shrink-0">✨</span>
                        <div>
                            <h4 class="text-xs sm:text-sm font-extrabold text-white flex items-center gap-2">
                                <span>Prefere trabalhar sem anúncios na sua oficina?</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">Plano Anual</span>
                            </h4>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                Assine o <strong>Plano Anual Sem Anúncios</strong> por apenas <strong>R$ <?= htmlspecialchars($annualPrice, ENT_QUOTES, 'UTF-8') ?>/ano</strong> e remova 100% das publicidades da sua vitrine, do rastreio de OS e do portal do seu cliente!
                            </p>
                        </div>
                    </div>
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/configuracoes#publica') ?>" 
                       class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-md shadow-amber-500/20 shrink-0 text-center">
                        Assinar Sem Anúncios &rarr;
                    </a>
                </div>
            <?php else: ?>
                <!-- Fallback para o Cliente Final / Público (Apoio Voluntário via Pix / Transparência) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl shrink-0">☕</span>
                        <div>
                            <h4 class="text-xs sm:text-sm font-extrabold text-white flex items-center gap-2">
                                <span>Você está usando um bloqueador de anúncios</span>
                            </h4>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                                O sistema de rastreio e acompanhamento de OS desta assistência técnica é mantido <strong>100% gratuito</strong> graças aos patrocinadores. 
                                Se você gosta da praticidade de acompanhar sua ordem de serviço pelo celular, considere fazer uma contribuição voluntária de qualquer valor via Pix!
                            </p>
                        </div>
                    </div>

                    <?php if (!empty($saasPixKey)): ?>
                        <div class="flex flex-col sm:flex-row items-center gap-2 shrink-0">
                            <button type="button" 
                                    onclick="copyPixFallback('<?= htmlspecialchars($saasPixKey, ENT_QUOTES, 'UTF-8') ?>', this)"
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                                <span>📋 Copiar Chave Pix de Apoio</span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- Script Sentinela de Detecção de AdBlock Não-Intrusivo (Zero impacto na navegação) -->
<script>
(function() {
    const slotId = '<?= $slotId ?>';
    const adBox = document.getElementById(slotId + '-adbox');
    const fallbackBox = document.getElementById(slotId + '-fallback');

    if (!adBox || !fallbackBox) return;

    // Função de ativação do fallback elegante
    function triggerAdblockFallback() {
        if (adBox && fallbackBox) {
            adBox.classList.add('hidden');
            fallbackBox.classList.remove('hidden');
        }
    }

    // 1. Teste de Sentinela (Isca com classes bloqueadas por filtros comuns de AdBlock)
    const bait = document.createElement('div');
    bait.setAttribute('class', 'pub_300x250 pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links banner-ad adsbygoogle');
    bait.setAttribute('style', 'width: 1px !important; height: 1px !important; position: absolute !important; left: -10000px !important; top: -1000px !important;');
    document.body.appendChild(bait);

    window.requestAnimationFrame(() => {
        const isBlocked = window.getComputedStyle(bait).getPropertyValue('display') === 'none' ||
                          bait.offsetParent === null ||
                          bait.offsetHeight === 0 ||
                          bait.clientHeight === 0;
        
        bait.remove();

        if (isBlocked) {
            triggerAdblockFallback();
        } else {
            // 2. Verificação Secundária por atraso de colapso do contêiner
            setTimeout(() => {
                if (adBox.offsetHeight < 20 && !adBox.classList.contains('hidden')) {
                    triggerAdblockFallback();
                }
            }, 1200);
        }
    });
})();

// Função auxiliar de cópia de chave Pix
if (typeof copyPixFallback === 'undefined') {
    window.copyPixFallback = function(key, btn) {
        if (!key) return;
        navigator.clipboard.writeText(key).then(() => {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>✓ Chave Pix Copiada!</span>';
            btn.classList.remove('bg-emerald-600');
            btn.classList.add('bg-slate-700');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('bg-slate-700');
                btn.classList.add('bg-emerald-600');
            }, 3000);
        }).catch(() => {
            prompt('Copie a chave Pix abaixo:', key);
        });
    };
}
</script>
