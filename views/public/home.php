<?php
$promotions = [];
if (!empty($tenant['promotions_json'])) {
    $promotions = is_array($tenant['promotions_json']) ? $tenant['promotions_json'] : json_decode($tenant['promotions_json'], true);
}
$address = [];
if (!empty($tenant['address_json'])) {
    $address = is_array($tenant['address_json']) ? $tenant['address_json'] : json_decode($tenant['address_json'], true);
}
$hours = [];
if (!empty($tenant['opening_hours_json'])) {
    $hours = is_array($tenant['opening_hours_json']) ? $tenant['opening_hours_json'] : json_decode($tenant['opening_hours_json'], true);
}
$featuredProducts = $featuredProducts ?? [];

// Cálculo Dinâmico de "Aberto Agora" / "Fechado"
$timezone = new DateTimeZone('America/Sao_Paulo');
$now = new DateTime('now', $timezone);
$dayOfWeek = (int)$now->format('w'); // 0=Domingo, 1=Segunda, ..., 6=Sábado
$currentHourMin = (int)$now->format('Hi'); // ex: 1430 = 14:30

$isOpenNow = false;
if ($dayOfWeek >= 1 && $dayOfWeek <= 5) {
    // Segunda a Sexta: 08:30 às 18:30
    $isOpenNow = ($currentHourMin >= 830 && $currentHourMin <= 1830);
} elseif ($dayOfWeek === 6) {
    // Sábado: 09:00 às 13:00
    $isOpenNow = ($currentHourMin >= 900 && $currentHourMin <= 1300);
}
?>

<!-- ============================================================================== -->
<!-- 1. Hero Section com Mesh Ambient, Status Ao Vivo & Rastreio Rápido de OS       -->
<!-- ============================================================================== -->
<section class="relative overflow-hidden pt-12 pb-20 sm:pt-20 sm:pb-28 border-b border-slate-200/80 dark:border-slate-800/80">
    
    <!-- Background Ambient Glow Mesh -->
    <div class="absolute inset-0 pointer-events-none -z-10 overflow-hidden">
        <?php if (!empty($tenant['banner_path'])): ?>
            <div class="absolute inset-0">
                <img src="<?= htmlspecialchars(asset($tenant['banner_path']), ENT_QUOTES, 'UTF-8') ?>" 
                     alt="Banner da Loja" 
                     class="w-full h-full object-cover opacity-20 filter blur-sm">
                <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-950/90 to-slate-950"></div>
            </div>
        <?php else: ?>
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-gradient-to-b from-brand-500/15 via-indigo-500/10 to-transparent rounded-full blur-3xl opacity-70 dark:opacity-40"></div>
            <div class="absolute top-1/2 -left-20 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl opacity-60"></div>
            <div class="absolute top-1/3 -right-20 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl opacity-60"></div>
        <?php endif; ?>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-4xl mx-auto">
            
            <!-- Logo Grande no Hero se existir -->
            <?php if (!empty($tenant['logo_path'])): ?>
                <div class="mb-6 flex justify-center">
                    <img src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" 
                         alt="<?= htmlspecialchars($tenant['business_name'] ?? 'Logo') ?>" 
                         class="h-16 sm:h-20 w-auto max-w-[260px] object-contain drop-shadow-xl rounded-2xl bg-white/80 dark:bg-slate-900/80 p-2 border border-slate-200/80 dark:border-slate-700/80 backdrop-blur-md">
                </div>
            <?php endif; ?>

            <!-- Pill Badge de Status da Oficina -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold tracking-wide border shadow-sm mb-6 backdrop-blur-md
                <?= $isOpenNow 
                    ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30' 
                    : 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/30' ?>">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full <?= $isOpenNow ? 'bg-emerald-400' : 'bg-amber-400' ?> opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 <?= $isOpenNow ? 'bg-emerald-500' : 'bg-amber-500' ?>"></span>
                </span>
                <span>
                    <?= $isOpenNow ? 'Oficina Aberta Agora &bull; Atendimento em Bancada' : 'Fechado no Momento &bull; Atendimento Online pelo WhatsApp' ?>
                </span>
            </div>

            <!-- Título Principal de Alto Impacto -->
            <h1 class="text-4xl sm:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.15]">
                <?= htmlspecialchars($tenant['business_name'] ?? 'Assistência Técnica Especializada', ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <!-- Subtítulo Explicativo -->
            <p class="mt-5 text-base sm:text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed font-normal">
                <?= htmlspecialchars($tenant['slogan'] ?? 'Transparência total em cada reparo com prontuário digital, laudo com fotos em alta definição e garantia legal de 90 dias.', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <!-- Box Elevado de Rastreio de OS (Fintech Grade) -->
            <div id="rastreio" class="mt-10 max-w-2xl mx-auto p-3 sm:p-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl rounded-3xl shadow-2xl shadow-brand-500/10 dark:shadow-black/50 border border-slate-200/90 dark:border-slate-800 text-left transition-all">
                
                <div class="px-3 pt-2 pb-1 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-500"></span>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white tracking-tight">
                            Consulte sua Ordem de Serviço em Tempo Real
                        </h3>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-400 hidden sm:inline-block">Prontuário Antifraude</span>
                </div>

                <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio') ?>" method="GET" class="mt-3 flex flex-col sm:flex-row gap-2.5">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" name="os" required placeholder="Digite o número da sua OS (ex: OS-2026-001) ou IMEI" 
                               class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-sm font-mono focus:outline-none focus:ring-2 focus:ring-brand-500 transition placeholder:text-slate-400">
                    </div>
                    <button type="submit" 
                            class="px-7 py-3.5 rounded-2xl font-bold text-sm text-white bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 shadow-lg shadow-brand-600/25 hover:shadow-brand-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2 shrink-0">
                        <span>Rastrear OS</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- Tags de Confiança & Atalho para o Portal -->
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/80 px-2 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1 font-medium">📸 Laudo com Fotos</span>
                        <span class="inline-flex items-center gap-1 font-medium">🛡️ Garantia 90 Dias (CDC)</span>
                        <span class="inline-flex items-center gap-1 font-medium">⚡ Aceite em 1-Clique</span>
                    </div>
                    <div>
                        <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal') ?>" 
                           class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold inline-flex items-center gap-1">
                            <span>Ver todos os meus aparelhos</span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<!-- ============================================================================== -->
<!-- 2. Barra de Métricas & Prova Social (Dinâmica / Personalizada)                  -->
<!-- ============================================================================== -->
<?php if (!empty($metrics['visible']) && !empty($metrics['items'])): 
    $metricColors = [
        0 => 'bg-brand-50 dark:bg-brand-950/70 text-brand-600 dark:text-brand-400',
        1 => 'bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400',
        2 => 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400',
        3 => 'bg-purple-50 dark:bg-purple-950/70 text-purple-600 dark:text-purple-400',
    ];
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
        <?php foreach ($metrics['items'] as $idx => $m): 
            $colorClasses = $metricColors[$idx % 4];
        ?>
            <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-lg shadow-slate-200/40 dark:shadow-none flex items-center gap-3.5 transition-transform hover:-translate-y-1">
                <div class="w-11 h-11 rounded-xl <?= $colorClasses ?> flex items-center justify-center font-black text-xl shrink-0">
                    <?= htmlspecialchars($m['icon'] ?? '⚡', ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div>
                    <span class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white font-mono block leading-none">
                        <?= htmlspecialchars($m['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1 block">
                        <?= htmlspecialchars($m['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================================== -->
<!-- 3. Diferenciais da Oficina (Por que nos escolher)                              -->
<!-- ============================================================================== -->
<section class="py-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-14">
        <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950 px-3 py-1 rounded-full border border-brand-200/60 dark:border-brand-800/60">
            Diferenciais de Bancada
        </span>
        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight mt-3">
            O Padrão de Qualidade Que Seu Equipamento Merece
        </h2>
        <p class="mt-3 text-slate-600 dark:text-slate-400 text-sm sm:text-base">
            Eliminamos dúvidas e desconfianças. Do momento em que você entrega o equipamento até a retirada, cada detalhe fica gravado no seu prontuário digital.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1 -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-brand-500/40 transition-all duration-300 group flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-950/80 text-brand-600 dark:text-brand-400 flex items-center justify-center font-bold text-2xl group-hover:scale-110 transition-transform">
                    📋
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    Prontuário Antifraude
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Registramos o número de série da carcaça e de cada peça trocada para idoneidade absoluta e garantia documentada.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-brand-600 dark:text-brand-400 flex items-center gap-1">
                <span>Rastreabilidade Total</span> &rarr;
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-brand-500/40 transition-all duration-300 group flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-2xl group-hover:scale-110 transition-transform">
                    📸
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    Laudo com Fotos Macro
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Nossos técnicos anexam fotos em alta definição do defeito antes de qualquer intervenção, para você ver exatamente o que precisa de reparo.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                <span>Transparência Visual</span> &rarr;
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-brand-500/40 transition-all duration-300 group flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-2xl group-hover:scale-110 transition-transform">
                    ⚡
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    Aprovação em 1-Clique
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Receba o link direto no seu WhatsApp e aprove ou recuse orçamentos e itens aditivos sem precisar ligar ou comparecer à loja.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                <span>Agilidade sem Fila</span> &rarr;
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-brand-500/40 transition-all duration-300 group flex flex-col justify-between">
            <div class="space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-2xl group-hover:scale-110 transition-transform">
                    🏷️
                </div>
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    Selo QR Adesivo
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    Seu aparelho recebe uma mini-etiqueta inviolável com QR Code. Basta apontar a câmera do celular para consultar todo o histórico a qualquer hora.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] font-bold text-amber-600 dark:text-amber-400 flex items-center gap-1">
                <span>Histórico na Carcaça</span> &rarr;
            </div>
        </div>

    </div>
</section>

<!-- ============================================================================== -->
<!-- 4. Promoções & Serviços em Destaque (Cards Modernos com Fotos)                 -->
<!-- ============================================================================== -->
<section id="promocoes" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-200/80 dark:border-slate-800/80">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950 px-3 py-1 rounded-full border border-emerald-200/60 dark:border-emerald-800/60">
                Condições Especiais
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-2">
                Promoções & Serviços em Destaque
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Serviços laboratoriais de alta precisão com valor promocional por tempo limitado
            </p>
        </div>
        <?php if (!empty($tenant['whatsapp_number'])): 
            $whatsClean = preg_replace('/\D/', '', $tenant['whatsapp_number']);
            $shopName = $tenant['business_name'] ?? 'assistência técnica';
            $quoteMsg = !empty($tenant['whatsapp_message'])
                ? str_replace(['{oficina}', '{loja}'], $shopName, $tenant['whatsapp_message'])
                : "Olá! Gostaria de solicitar um orçamento personalizado para o meu aparelho.";
            $whatsCustomQuote = "https://wa.me/55{$whatsClean}?text=" . urlencode($quoteMsg);
        ?>
            <a href="<?= $whatsCustomQuote ?>" target="_blank" rel="noopener noreferrer" 
               class="inline-flex items-center gap-2 text-xs font-bold text-brand-600 dark:text-brand-400 hover:underline">
                <span>Pedir orçamento personalizado no WhatsApp</span> &rarr;
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($promotions)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <?php foreach ($promotions as $promo): ?>
                <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-2xl hover:border-brand-500/50 transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <?php if (!empty($promo['image_path'])): ?>
                            <div class="h-48 w-full bg-slate-950 overflow-hidden relative">
                                <img src="<?= htmlspecialchars(asset($promo['image_path']), ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="<?= htmlspecialchars($promo['title'] ?? 'Oferta', ENT_QUOTES, 'UTF-8') ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                     loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent"></div>
                                <span class="absolute top-4 left-4 text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-500 text-white shadow-md">
                                    Destaque de Bancada
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="h-28 w-full bg-gradient-to-tr from-brand-600/10 via-brand-500/10 to-indigo-500/10 dark:from-slate-800 dark:to-slate-900 flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-800">
                                <span class="text-xs font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    Oferta de Bancada
                                </span>
                                <span class="text-2xl">🔧</span>
                            </div>
                        <?php endif; ?>

                        <div class="p-6 space-y-3">
                            <div class="flex items-baseline justify-between gap-2">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white leading-snug">
                                    <?= htmlspecialchars($promo['title'] ?? 'Serviço Especializado', ENT_QUOTES, 'UTF-8') ?>
                                </h3>
                                <?php if (!empty($promo['price'])): ?>
                                    <div class="text-right shrink-0">
                                        <span class="text-[10px] text-slate-400 block -mb-1 font-semibold uppercase">A partir de</span>
                                        <span class="text-xl font-black text-slate-900 dark:text-white font-mono">
                                            R$ <?= htmlspecialchars($promo['price'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                <?= htmlspecialchars($promo['description'] ?? 'Serviço executado por técnicos certificados com testes de bancada e garantia legal.', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>

                    <?php if (!empty($tenant['whatsapp_number'])): 
                        $whatsClean = preg_replace('/\D/', '', $tenant['whatsapp_number']);
                        $msgPromo = "Olá! Vi a promoção '" . ($promo['title'] ?? '') . "' no site oficial da assistência e gostaria de agendar um atendimento.";
                        $whatsPromoUrl = "https://wa.me/55{$whatsClean}?text=" . urlencode($msgPromo);
                    ?>
                        <div class="p-6 pt-0">
                            <a href="<?= $whatsPromoUrl ?>" target="_blank" rel="noopener noreferrer"
                               class="w-full inline-flex items-center justify-center gap-2 py-3 px-4 rounded-2xl text-xs font-bold bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-emerald-950/70 dark:text-emerald-300 dark:hover:bg-emerald-600 dark:hover:text-white transition-all duration-200 shadow-sm">
                                <span>Aproveitar Condição no WhatsApp</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 p-12 text-center text-slate-500 dark:text-slate-400 space-y-3">
            <span class="text-3xl block">🛠️</span>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Nenhuma promoção ativa na vitrine no momento.</p>
            <p class="text-xs max-w-md mx-auto">Traga seu aparelho até nossa oficina para um orçamento rápido e detalhado na bancada ou fale conosco pelo WhatsApp!</p>
        </div>
    <?php endif; ?>
</section>

<!-- ============================================================================== -->
<!-- 5. Peças & Produtos a Pronta-Entrega (Vitrine Balcão)                          -->
<!-- ============================================================================== -->
<?php if (!empty($featuredProducts)): ?>
<section id="estoque-vitrine" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-200/80 dark:border-slate-800/80">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400 bg-brand-50 dark:bg-brand-950 px-3 py-1 rounded-full border border-brand-200/60 dark:border-brand-800/60">
                Estoque Disponível
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-2">
                Peças & Acessórios a Pronta-Entrega
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Componentes homologados disponíveis para reposição imediata e venda direta no balcão
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <?php foreach ($featuredProducts as $fp): 
            $isService = ((int)$fp['is_service'] === 1);
            $hasPhoto = !empty($fp['image_path']);
            $whatsClean = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? '');
            $msgProd = "Olá! Vi o item '{$fp['name']}' na vitrine e gostaria de consultar disponibilidade e aplicação.";
            $whatsProdUrl = "https://wa.me/55{$whatsClean}?text=" . urlencode($msgProd);
        ?>
            <div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:border-brand-500/40 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <?php if ($hasPhoto): ?>
                        <div class="h-36 sm:h-44 w-full bg-slate-950 overflow-hidden relative">
                            <img src="<?= htmlspecialchars(asset($fp['image_path']), ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($fp['name'], ENT_QUOTES, 'UTF-8') ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                 loading="lazy">
                        </div>
                    <?php else: ?>
                        <div class="h-28 sm:h-32 w-full bg-slate-100 dark:bg-slate-950 flex items-center justify-center text-slate-400">
                            <svg class="w-10 h-10 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                    <?php endif; ?>

                    <div class="p-4 space-y-2">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full <?= $isService ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' ?>">
                                <?= htmlspecialchars($fp['category'] ?? ($isService ? 'Serviço' : 'Peça'), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Em Estoque
                            </span>
                        </div>
                        <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-2" title="<?= htmlspecialchars($fp['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($fp['name'], ENT_QUOTES, 'UTF-8') ?>
                        </h4>
                    </div>
                </div>

                <div class="p-4 pt-0">
                    <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-[9px] text-slate-400 block font-semibold uppercase">Valor</span>
                            <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 font-mono">
                                R$ <?= number_format((float)$fp['price'], 2, ',', '.') ?>
                            </span>
                        </div>

                        <?php if (!empty($whatsClean)): ?>
                            <a href="<?= $whatsProdUrl ?>" target="_blank" rel="noopener noreferrer"
                               class="p-2 sm:px-3 sm:py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-emerald-950/70 dark:text-emerald-300 dark:hover:bg-emerald-600 dark:hover:text-white transition flex items-center gap-1.5 text-xs font-bold"
                               title="Consultar pelo WhatsApp">
                                <span>💬</span>
                                <span class="hidden sm:inline">Consultar</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================================== -->
<!-- 6. Informações de Atendimento & Localização com Mapa Interativo                 -->
<!-- ============================================================================== -->
<section id="localizacao" class="bg-slate-100/60 dark:bg-slate-900/40 py-20 border-t border-slate-200/80 dark:border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        
        <div class="text-center max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950 px-3 py-1 rounded-full border border-indigo-200/60 dark:border-indigo-800/60">
                Endereço & Atendimento
            </span>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-3">
                Onde Estamos & Horários
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Venha nos visitar para um diagnóstico rápido na bancada ou agende sua visita
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            <!-- Coluna da Esquerda: Cards de Endereço & Horários -->
            <div class="lg:col-span-5 flex flex-col justify-between space-y-6">
                
                <!-- Card 1: Localização Física -->
                <div class="bg-white dark:bg-slate-900 p-6 sm:p-7 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-start gap-3.5">
                        <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Endereço Físico da Oficina</span>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5">
                                <?= htmlspecialchars($tenant['business_name'] ?? 'Nossa Oficina', ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                <?= htmlspecialchars($address['street'] ?? 'Endereço a definir', ENT_QUOTES, 'UTF-8') ?><br>
                                <?= htmlspecialchars($address['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>

                    <?php
                    $fullAddr = trim(($address['street'] ?? '') . ' ' . ($address['city'] ?? ''));
                    $mapsUrl = !empty($address['maps_link']) && filter_var($address['maps_link'], FILTER_VALIDATE_URL) 
                        ? $address['maps_link'] 
                        : "https://www.google.com/maps/search/?api=1&query=" . urlencode($fullAddr);
                    $wazeUrl = "https://waze.com/ul?q=" . urlencode($fullAddr) . "&navigate=yes";
                    ?>

                    <!-- Botões de Ação de Trajeto -->
                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-2">
                        <a href="<?= htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/20 transition">
                            <span>🗺️ Google Maps</span>
                        </a>

                        <?php if (!empty($fullAddr)): ?>
                            <a href="<?= htmlspecialchars($wazeUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                                <span>🚗 Waze</span>
                            </a>

                            <button type="button" onclick="copyShopAddress('<?= htmlspecialchars(addslashes($fullAddr)) ?>')"
                                    id="btn_copy_addr"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-2xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                <span>📋 Copiar</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card 2: Horário de Atendimento com Status em Tempo Real -->
                <div class="bg-white dark:bg-slate-900 p-6 sm:p-7 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Expediente de Bancada</span>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Horário de Funcionamento</h3>
                            </div>
                        </div>

                        <!-- Badge Aberto/Fechado Agora -->
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $isOpenNow ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border-slate-300 dark:border-slate-700' ?>">
                            <?= $isOpenNow ? '🟢 Aberto' : '🌙 Fechado' ?>
                        </span>
                    </div>

                    <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <p class="flex justify-between items-center py-1 <?= ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? 'font-bold text-brand-600 dark:text-brand-400' : '' ?>">
                            <span class="flex items-center gap-1.5">
                                <?= ($dayOfWeek >= 1 && $dayOfWeek <= 5) ? '👉' : '' ?>
                                Segunda a Sexta:
                            </span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($hours['weekdays'] ?? '08:30 às 18:30', ENT_QUOTES, 'UTF-8') ?></span>
                        </p>
                        <p class="flex justify-between items-center py-1 <?= ($dayOfWeek === 6) ? 'font-bold text-brand-600 dark:text-brand-400' : '' ?>">
                            <span class="flex items-center gap-1.5">
                                <?= ($dayOfWeek === 6) ? '👉' : '' ?>
                                Sábados:
                            </span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($hours['saturday'] ?? '09:00 às 13:00', ENT_QUOTES, 'UTF-8') ?></span>
                        </p>
                        <p class="flex justify-between items-center py-1 text-slate-400 text-[11px]">
                            <span>Domingos e Feriados:</span>
                            <span>Fechado</span>
                        </p>
                    </div>
                </div>

            </div>

            <!-- Coluna da Direita: Mapa Interativo Embutido -->
            <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-sm flex flex-col min-h-[400px]">
                
                <div class="bg-slate-50 dark:bg-slate-800/60 px-5 py-3 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📍</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Localização Interativa</span>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium">Amplie e trace rotas pelo mapa</span>
                </div>

                <?php
                $embedSrc = '';
                $rawMap = $address['maps_link'] ?? '';
                
                // Se foi fornecido um iframe completo, extrai a URL de src
                if (preg_match('/src=["\']([^"\']+)["\']/', $rawMap, $matches)) {
                    $embedSrc = $matches[1];
                } elseif (str_contains($rawMap, 'embed')) {
                    $embedSrc = $rawMap;
                } elseif (!empty($fullAddr)) {
                    $embedSrc = "https://maps.google.com/maps?q=" . urlencode($fullAddr) . "&t=&z=15&ie=UTF8&iwloc=&output=embed";
                }
                ?>

                <?php if (!empty($embedSrc)): ?>
                    <iframe 
                        src="<?= htmlspecialchars($embedSrc, ENT_QUOTES, 'UTF-8') ?>" 
                        class="w-full flex-1 min-h-[380px] border-0" 
                        loading="lazy" 
                        allowfullscreen 
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Localização da Loja">
                    </iframe>
                <?php else: ?>
                    <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400 space-y-3">
                        <span class="text-4xl">🗺️</span>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Mapa ainda não configurado para esta oficina.</p>
                        <p class="text-xs text-slate-500 max-w-xs">O lojista pode cadastrar o link do Google Maps nas configurações do painel administrativo.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<?php
try {
    if (empty($tenant['is_premium'])) {
        $adsenseRow = \Zyrtex\Core\Database::fetchOne("SELECT config_value FROM saas_settings WHERE config_key = 'adsense_script' LIMIT 1");
        $adsenseActive = \Zyrtex\Core\Database::fetchOne("SELECT config_value FROM saas_settings WHERE config_key = 'adsense_active' LIMIT 1");
        if ($adsenseActive && $adsenseActive['config_value'] === '1' && !empty($adsenseRow['config_value'])):
?>
    <!-- Bloco Patrocinado / Google AdSense (Exclusivo Lojas Free) -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 my-10 text-center">
        <span class="text-[10px] uppercase font-bold tracking-wider text-slate-400 block mb-2">Publicidade Patrocinada</span>
        <div class="p-4 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 flex items-center justify-center min-h-[90px] overflow-hidden">
            <?= $adsenseRow['config_value'] ?>
        </div>
    </div>
<?php
        endif;
    }
} catch (\Throwable) {}
?>

<!-- Script para Copiar Endereço -->
<script>
function copyShopAddress(addr) {
    navigator.clipboard.writeText(addr).then(() => {
        const btn = document.getElementById('btn_copy_addr');
        if (btn) {
            const original = btn.innerHTML;
            btn.innerHTML = '<span>✓ Copiado!</span>';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        }
    });
}
</script>
