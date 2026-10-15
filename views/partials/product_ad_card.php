<?php
/**
 * Partial: Card de Publicidade Nativa Disfarçado de Produto (In-Feed Ad Card)
 * Renderizado diretamente dentro do grid de produtos da Vitrine e do PDV Totem Express.
 * 
 * Regras:
 * 1. Supressão Total: Se a oficina for Premium (Plano Anual Sem Anúncios), NENHUM anúncio é renderizado.
 * 2. Visual Orgânico: Possui exatamente a mesma estrutura HTML, proporção, tipografia e bordas dos produtos reais.
 * 3. Conformidade: Inclui indicação sutil "Patrocinado / Oferta Parceira" para total conformidade com AdSense/Conar.
 */

$tenant = $tenant ?? \Zyrtex\Core\Tenant::current();
if (!empty($tenant['is_premium'])) {
    return;
}

static $saasAdsActive = null;
static $saasAdScript = null;
if ($saasAdsActive === null) {
    try {
        $rows = \Zyrtex\Core\Database::fetchAll(
            "SELECT config_key, config_value FROM saas_settings WHERE config_key IN ('adsense_active', 'adsense_script')"
        );
        $map = [];
        foreach ($rows as $r) { $map[$r['config_key']] = $r['config_value']; }
        $saasAdsActive = ($map['adsense_active'] ?? '1') === '1';
        $saasAdScript = trim($map['adsense_script'] ?? '');
    } catch (\Throwable) {
        $saasAdsActive = true;
        $saasAdScript = '';
    }
}

if (!$saasAdsActive) {
    return;
}

$context = $context ?? 'vitrine'; // 'vitrine' ou 'pos'
$adId = 'zyrtex-ad-prod-' . bin2hex(random_bytes(3));

// Verificação de Origem e Sandbox de Segurança
$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$isLocal = empty($host) || str_contains($host, 'localhost') || str_contains($host, '127.0.0.1') || str_starts_with($host, '192.168.') || PHP_SAPI === 'cli';
?>

<?php if ($context === 'vitrine'): ?>
    <!-- ===================================================================== -->
    <!-- CARD DE PRODUTO PATROCINADO: VITRINE PÚBLICA DO CLIENTE              -->
    <!-- ===================================================================== -->
    <?php if (!$isLocal && !empty($saasAdScript)): ?>
        <!-- Bloco Google AdSense Oficial In-Feed (Produção) -->
        <div id="<?= $adId ?>-adsense" class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-center items-center p-3 relative min-h-[320px]">
            <span class="absolute top-3 left-3 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                Anúncio Google
            </span>
            <div class="w-full h-full flex items-center justify-center">
                <?= $saasAdScript ?>
            </div>
        </div>
    <?php endif; ?>

    <div id="<?= $adId ?>" class="<?= (!$isLocal && !empty($saasAdScript)) ? 'hidden' : '' ?> bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-amber-500/40 dark:border-amber-500/30 shadow-sm hover:shadow-xl hover:border-amber-500/60 transition-all duration-300 flex flex-col justify-between group relative">
        
        <div>
            <!-- Header Visual com Imagem de Produto Tecnológico -->
            <div class="h-36 sm:h-44 w-full bg-gradient-to-tr from-slate-950 via-slate-900 to-indigo-950 overflow-hidden relative flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-500/10 via-transparent to-transparent pointer-events-none"></div>
                
                <div class="relative z-10 flex flex-col items-center text-center">
                    <span class="text-4xl sm:text-5xl drop-shadow-md group-hover:scale-110 transition-transform duration-300">
                        📱
                    </span>
                    <span class="text-[9px] font-bold text-amber-300/80 uppercase tracking-widest mt-1">Oferta Parceira</span>
                </div>

                <div class="absolute top-3 left-3 flex items-center gap-1.5">
                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 shadow-md">
                        Patrocinado
                    </span>
                </div>
            </div>

            <!-- Dados do Produto -->
            <div class="p-4 space-y-2">
                <div class="flex items-center justify-between gap-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-500/20">
                        Acessórios & Películas
                    </span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Pronta-Entrega
                    </span>
                </div>
                
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug">
                    Películas 3D, Cabos Turbo & Capas Pro • Atacado e Varejo
                </h4>
                
                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                    Acessórios homologados com garantia e alta durabilidade para proteger seu smartphone.
                </p>
            </div>
        </div>

        <!-- Preço e Botão de Ação -->
        <div class="p-4 pt-0">
            <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                <div>
                    <span class="text-[9px] text-slate-400 block font-semibold uppercase">A partir de</span>
                    <span class="text-sm sm:text-base font-black text-amber-600 dark:text-amber-400 font-mono">
                        R$ 14,90
                    </span>
                </div>

                <a href="<?= \Zyrtex\Core\Response::url('/termos#anuncios') ?>" target="_blank" rel="noopener sponsored"
                   class="p-2 sm:px-3 sm:py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 hover:from-amber-400 hover:to-amber-500 transition flex items-center gap-1.5 text-xs font-black shadow-md shadow-amber-500/20"
                   title="Conhecer oferta parceira">
                    <span>🛒</span>
                    <span class="hidden sm:inline">Ver Oferta</span>
                </a>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ===================================================================== -->
    <!-- CARD DE PRODUTO PATROCINADO: PDV TOTEM EXPRESS (BALCÃO DA OFICINA)    -->
    <!-- ===================================================================== -->
    <?php if (!$isLocal && !empty($saasAdScript)): ?>
        <!-- Bloco Google AdSense Oficial In-Feed p/ PDV (Produção) -->
        <div id="<?= $adId ?>-pos-adsense" class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-center items-center relative min-h-[260px]">
            <span class="absolute top-3 left-3 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-800 text-slate-400">
                Anúncio Google
            </span>
            <div class="w-full h-full flex items-center justify-center">
                <?= $saasAdScript ?>
            </div>
        </div>
    <?php endif; ?>

    <div id="<?= $adId ?>" class="<?= (!$isLocal && !empty($saasAdScript)) ? 'hidden' : '' ?> bg-slate-900 border border-amber-500/40 hover:border-amber-500/70 rounded-2xl p-4 flex flex-col justify-between transition-all duration-200 hover:shadow-xl hover:shadow-amber-950/40 relative group">
        
        <!-- Badges Superiores -->
        <div>
            <div class="flex items-center justify-between gap-1 mb-2">
                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    📢 Patrocinado
                </span>
                <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded">
                    Parceiro B2B
                </span>
            </div>

            <!-- Banner do Produto B2B -->
            <div class="w-full h-28 rounded-xl bg-gradient-to-tr from-slate-950 via-slate-900 to-amber-950/40 border border-amber-500/20 mb-2.5 flex flex-col items-center justify-center p-3 text-center relative overflow-hidden group-hover:border-amber-500/40 transition-colors">
                <span class="text-3xl drop-shadow-md group-hover:scale-110 transition-transform duration-300">
                    💳
                </span>
                <span class="text-[10px] font-bold text-amber-300 mt-1">Taxas Zero no Pix</span>
            </div>

            <!-- Nome do Produto / Solução -->
            <h3 class="text-sm font-bold text-white leading-snug line-clamp-2 mb-1">
                Maquininha de Cartão Balcão • Taxa 0,79% no Pix e Débito
            </h3>

            <span class="text-[10px] font-mono text-slate-500 block mb-2">
                PARCEIRO: DISTRIBUIDORA B2B
            </span>
            
            <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                Condição especial para assistências técnicas parceiras Zyrtex economizarem em cada venda.
            </p>
        </div>

        <!-- Preço & Botão de Toque B2B -->
        <div class="pt-3 border-t border-slate-800/80 mt-2 flex items-center justify-between gap-2">
            <div>
                <span class="text-[10px] text-slate-400 block font-medium">Condição</span>
                <span class="text-xs sm:text-sm font-black text-amber-400 font-mono leading-none">
                    Sem Aluguel
                </span>
            </div>

            <a href="<?= \Zyrtex\Core\Response::url('/termos#anuncios') ?>" target="_blank" rel="noopener sponsored"
               class="px-3 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition flex items-center gap-1 shadow-md shadow-amber-600/30"
               title="Ver condição exclusiva para sua assistência">
                <span>⚡</span>
                <span>Conferir</span>
            </a>
        </div>
    </div>
<?php endif; ?>
