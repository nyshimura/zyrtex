<?php
/**
 * View: Central de Configurações da Oficina (Abas Lógicas & Toggle Exclusivo Pix/MP)
 */
$csrf = \Zyrtex\Core\CSRF::token();
$tenant = $tenant ?? [];
$printerSettings = $printerSettings ?? [];
$address = $address ?? [];
$hours = $hours ?? [];
$printFormats = $printFormats ?? [];

$paymentMode = $tenant['payment_mode'] ?? 'manual_pix';
$defaultFormat = $printerSettings['default_format'] ?? '80mm';

$publicUrl = \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? 'demo-assistencia');
$whatsappShareText = "Olá! Acompanhe o status do seu aparelho, diagnósticos e orçamentos da nossa oficina pelo link: " . $publicUrl;
$whatsappShareUrl = "https://wa.me/?text=" . urlencode($whatsappShareText);
?>

<div class="space-y-6">

    <!-- Header com Identificação e Link Rápido -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Configurações da Oficina
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Gerencie sua empresa, meios de pagamento, impressão térmica, IA e página pública.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs px-3 py-2 rounded-xl font-bold bg-slate-900 border border-slate-800 text-slate-400">
                Oficina ID #<?= (int)$tenant['id'] ?>
            </span>
            <?php if (!empty($tenant['is_premium'])): ?>
                <span class="text-xs px-3 py-2 rounded-xl font-bold bg-amber-500/10 border border-amber-500/30 text-amber-400 flex items-center gap-1.5">
                    <span>★</span>
                    <span>Plano Premium</span>
                </span>
            <?php else: ?>
                <span class="text-xs px-3 py-2 rounded-xl font-bold bg-slate-800 border border-slate-700 text-slate-300">
                    Plano Gratuito (Free)
                </span>
            <?php endif; ?>
        </div>
    </div>

    <style>
        #adminTabsTrack::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>

    <!-- NAVEGAÇÃO POR ABAS LÓGICAS (Sem barra nativa, com setinhas laterais) -->
    <div class="relative group bg-slate-900/90 backdrop-blur-md p-1.5 rounded-2xl border border-slate-800 shadow-sm flex flex-col">
        <div class="relative flex items-center">
            <!-- Botão Chevron Esquerda -->
            <button type="button" id="adminTabScrollLeftBtn" onclick="scrollAdminTabs('left')" aria-label="Rolar abas para a esquerda"
                    class="absolute left-1 z-20 w-8 h-8 rounded-xl bg-slate-800/95 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700/80 shadow-xl flex items-center justify-center transition-all duration-200 backdrop-blur-md opacity-0 pointer-events-none active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Gradiente de Fade Esquerdo -->
            <div id="adminTabFadeLeft" class="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-slate-900 via-slate-900/90 to-transparent rounded-l-2xl pointer-events-none z-10 transition-opacity duration-200 opacity-0"></div>

            <!-- Trilha de Abas (Com rolagem suave e sem scrollbar) -->
            <div id="adminTabsTrack"
                 style="scrollbar-width: none; -ms-overflow-style: none;"
                 class="flex-1 overflow-x-auto scroll-smooth flex items-center gap-1.5 no-scrollbar scrollbar-none py-0.5 px-1 text-xs font-bold select-none">
                <button type="button" onclick="switchSettingsTab('empresa')" id="tab-btn-empresa"
                        class="settings-tab-btn shrink-0 min-w-[130px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 whitespace-nowrap">
                    <span>🏢</span>
                    <span>Empresa & Local</span>
                </button>

                <button type="button" onclick="switchSettingsTab('pagamentos')" id="tab-btn-pagamentos"
                        class="settings-tab-btn shrink-0 min-w-[140px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800/60 whitespace-nowrap">
                    <span>💳</span>
                    <span>Recebimento (Pix / MP)</span>
                </button>

                <button type="button" onclick="switchSettingsTab('impressao')" id="tab-btn-impressao"
                        class="settings-tab-btn shrink-0 min-w-[130px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800/60 whitespace-nowrap">
                    <span>🖨️</span>
                    <span>Impressão & Balcão</span>
                </button>

                <button type="button" onclick="switchSettingsTab('ia')" id="tab-btn-ia"
                        class="settings-tab-btn shrink-0 min-w-[130px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800/60 whitespace-nowrap">
                    <span>🤖</span>
                    <span>Inteligência Artificial</span>
                </button>

                <button type="button" onclick="switchSettingsTab('drive')" id="tab-btn-drive"
                        class="settings-tab-btn shrink-0 min-w-[140px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800/60 whitespace-nowrap">
                    <span>💾</span>
                    <span>Google Drive (Backup)</span>
                </button>

                <button type="button" onclick="switchSettingsTab('publica')" id="tab-btn-publica"
                        class="settings-tab-btn shrink-0 min-w-[140px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800/60 whitespace-nowrap">
                    <span>🌐</span>
                    <span>Página Pública & Link</span>
                </button>

                <button type="button" onclick="switchSettingsTab('seguranca')" id="tab-btn-seguranca"
                        class="settings-tab-btn shrink-0 min-w-[140px] py-3 px-4 rounded-xl text-center transition flex items-center justify-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800/60 whitespace-nowrap">
                    <span>🛡️</span>
                    <span>Segurança & 2FA</span>
                </button>
            </div>

            <!-- Gradiente de Fade Direito -->
            <div id="adminTabFadeRight" class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-slate-900 via-slate-900/90 to-transparent rounded-r-2xl pointer-events-none z-10 transition-opacity duration-200 opacity-0"></div>

            <!-- Botão Chevron Direita -->
            <button type="button" id="adminTabScrollRightBtn" onclick="scrollAdminTabs('right')" aria-label="Rolar abas para a direita"
                    class="absolute right-1 z-20 w-8 h-8 rounded-xl bg-slate-800/95 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700/80 shadow-xl flex items-center justify-center transition-all duration-200 backdrop-blur-md opacity-0 pointer-events-none active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Micro-barra de Progresso Integrada -->
        <div id="adminTabScrollIndicatorTrack" class="w-full h-1 bg-slate-800/40 rounded-full overflow-hidden mt-1 hidden">
            <div id="adminTabScrollIndicatorThumb" class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400 rounded-full transition-all duration-150" style="width: 20%; margin-left: 0%;"></div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 1: DADOS DA EMPRESA & HORÁRIOS                                     -->
    <!-- ===================================================================== -->
    <div id="tab-pane-empresa" class="settings-tab-pane bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="border-b border-slate-800 pb-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>🏢</span>
                <span>Identidade da Oficina & Horários</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Estes dados aparecem no cabeçalho das impressões térmicas e na sua Landing Page pública.</p>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/empresa') ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Logotipo Oficial da Oficina -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                            <span>🏢</span>
                            <span>Logotipo Oficial da Oficina</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Faça o upload do arquivo do logotipo ou informe um link direto (PNG, JPG, WebP ou SVG).
                        </p>
                    </div>
                    <?php if (!empty($tenant['logo_path'])): ?>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ✓ Logo Ativo
                        </span>
                    <?php else: ?>
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
                            Sem logotipo
                        </span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col sm:flex-row gap-5 items-start sm:items-center">
                    <!-- Preview do Logotipo -->
                    <div class="w-28 h-20 rounded-xl border border-slate-700 bg-slate-900 flex items-center justify-center p-2 shrink-0 overflow-hidden shadow-inner relative group">
                        <?php if (!empty($tenant['logo_path'])): ?>
                            <img id="preview_empresa_logo" src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Logotipo da Oficina" class="max-h-full max-w-full object-contain">
                        <?php else: ?>
                            <img id="preview_empresa_logo" src="" alt="Preview Logo" class="max-h-full max-w-full object-contain hidden">
                            <span id="placeholder_empresa_logo" class="text-3xl text-slate-600">🏢</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 w-full space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Opção 1: Upload de Arquivo -->
                            <div>
                                <label for="logo_file" class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">
                                    📁 Enviar Arquivo do Computador:
                                </label>
                                <input type="file" id="logo_file" name="logo_file" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                       onchange="previewImageInput(this, 'preview_empresa_logo', 'placeholder_empresa_logo')"
                                       class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer bg-slate-900 border border-slate-700 rounded-xl p-1">
                            </div>

                            <!-- Opção 2: Link Direto / URL -->
                            <div>
                                <label for="logo_url" class="block text-[11px] font-semibold text-slate-400 uppercase mb-1">
                                    🔗 Ou Colar Link Direto da Imagem:
                                </label>
                                <input type="url" id="logo_url" name="logo_url"
                                       value="<?= htmlspecialchars($tenant['logo_path'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       oninput="previewUrlInput(this.value, 'preview_empresa_logo', 'placeholder_empresa_logo')"
                                       placeholder="https://exemplo.com/meu-logo.png"
                                       class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1">
                            <span>Dica: Usado no topo dos comprovantes térmicos, laudo A4 e na vitrine pública.</span>
                            <?php if (!empty($tenant['logo_path'])): ?>
                                <label class="inline-flex items-center gap-1.5 text-rose-400 hover:text-rose-300 cursor-pointer font-semibold">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-700 text-rose-600 focus:ring-rose-500">
                                    <span>Remover Logotipo</span>
                                </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="business_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Nome Fantasia da Oficina <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="business_name" name="business_name" required
                           value="<?= htmlspecialchars($tenant['business_name'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        WhatsApp de Atendimento <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" required
                           value="<?= htmlspecialchars($tenant['whatsapp_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: 11999998888"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="slogan" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Slogan ou Especialidade
                </label>
                <input type="text" id="slogan" name="slogan"
                       value="<?= htmlspecialchars($tenant['slogan'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Ex: Especialistas em reparos rápidos com peças originais e prontuário digital"
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="street" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Endereço Físico (Rua e Nº)
                    </label>
                    <input type="text" id="street" name="street"
                           value="<?= htmlspecialchars($address['street'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: Av. Paulista, 1000 - Loja 4"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="city" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Cidade e Estado (UF)
                    </label>
                    <input type="text" id="city" name="city"
                           value="<?= htmlspecialchars($address['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: São Paulo - SP"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="maps_link" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    <span>Link do Google Maps / Waze ou Código Embed (Iframe)</span>
                    <span class="text-[10px] text-slate-500 font-normal">Exibido na Vitrine Pública</span>
                </label>
                <input type="text" id="maps_link" name="maps_link"
                       value="<?= htmlspecialchars($address['maps_link'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Ex: https://maps.google.com/?q=... ou código &lt;iframe...&gt;"
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-[11px] text-slate-500 mt-1">
                    Cole o link de localização ou o código de incorporação do Google Maps para exibir o mapa interativo aos clientes na sua página inicial.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="weekdays" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Horário Seg. a Sex.
                    </label>
                    <input type="text" id="weekdays" name="weekdays"
                           value="<?= htmlspecialchars($hours['weekdays'] ?? '08:30 às 18:30', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                </div>

                <div>
                    <label for="saturday" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Horário Sábado
                    </label>
                    <input type="text" id="saturday" name="saturday"
                           value="<?= htmlspecialchars($hours['saturday'] ?? '09:00 às 13:00', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                    Salvar Dados da Empresa &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 2: FORMAS DE RECEBIMENTO (TOGGLE EXCLUSIVO: MOSTRA OU UM OU OUTRO) -->
    <!-- ===================================================================== -->
    <div id="tab-pane-pagamentos" class="settings-tab-pane hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span>💳</span>
                    <span>Hub Financeiro de Recebimento</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Defina como sua assistência recebe dos clientes. Alterne abaixo para configurar.</p>
            </div>

            <!-- Status do Modo Ativo -->
            <div id="active-mode-badge">
                <?php if ($paymentMode === 'mercadopago'): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                        Modo Ativo: Mercado Pago Automático
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Modo Ativo: Pix Manual Direto
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/pagamentos') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Chave Seletora (Toggle) Intuitiva -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Escolha o Modo de Recebimento (Mostra apenas os campos do modo selecionado):
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Opção 1: Pix Manual -->
                    <label id="label-mode-pix" onclick="togglePaymentFields('manual_pix')"
                           class="p-4 rounded-2xl border-2 cursor-pointer transition flex items-start justify-between <?= $paymentMode === 'manual_pix' ? 'border-emerald-500 bg-emerald-950/20 ring-2 ring-emerald-500/20' : 'border-slate-800 bg-slate-950 hover:border-slate-700' ?>">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-white">1. Chave Pix Manual</span>
                                <span id="tag-pix-status" class="text-[10px] bg-emerald-500 text-slate-950 px-2 py-0.5 rounded font-black <?= $paymentMode === 'manual_pix' ? '' : 'hidden' ?>">ATIVO</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">O cliente escaneia seu QR Code estático ou copia sua chave. Você dá baixa manual no caixa.</p>
                        </div>
                        <input type="radio" name="payment_mode" value="manual_pix" id="radio-mode-pix" <?= $paymentMode === 'manual_pix' ? 'checked' : '' ?> class="mt-1">
                    </label>

                    <!-- Opção 2: Mercado Pago Automático -->
                    <label id="label-mode-mp" onclick="togglePaymentFields('mercadopago')"
                           class="p-4 rounded-2xl border-2 cursor-pointer transition flex items-start justify-between <?= $paymentMode === 'mercadopago' ? 'border-blue-500 bg-blue-950/20 ring-2 ring-blue-500/20' : 'border-slate-800 bg-slate-950 hover:border-slate-700' ?>">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-white">2. Mercado Pago Automático</span>
                                <span id="tag-mp-status" class="text-[10px] bg-blue-500 text-slate-950 px-2 py-0.5 rounded font-black <?= $paymentMode === 'mercadopago' ? '' : 'hidden' ?>">ATIVO</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Gera Pix dinâmico com confirmação automática via Webhook 24h sem taxa de adesão.</p>
                        </div>
                        <input type="radio" name="payment_mode" value="mercadopago" id="radio-mode-mp" <?= $paymentMode === 'mercadopago' ? 'checked' : '' ?> class="mt-1">
                    </label>

                </div>
            </div>

            <!-- ============================================================= -->
            <!-- BLOCO 1: EXCLUSIVO PIX MANUAL (Oculto se Mercado Pago ativo)    -->
            <!-- ============================================================= -->
            <div id="box-pix-fields" class="p-6 rounded-2xl bg-slate-950 border border-emerald-500/30 space-y-4 <?= $paymentMode === 'manual_pix' ? '' : 'hidden' ?>">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <span>⚡</span>
                        <span>Configurações da sua Chave Pix Manual</span>
                    </h3>
                    <span class="text-[11px] text-slate-400">Exibida no cupom térmico e balcão</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="pix_key_type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Tipo de Chave Pix
                        </label>
                        <select id="pix_key_type" name="pix_key_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                            <option value="phone" <?= ($tenant['pix_key_type'] ?? '') === 'phone' ? 'selected' : '' ?>>Celular / WhatsApp</option>
                            <option value="cpf" <?= ($tenant['pix_key_type'] ?? '') === 'cpf' ? 'selected' : '' ?>>CPF</option>
                            <option value="cnpj" <?= ($tenant['pix_key_type'] ?? '') === 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
                            <option value="email" <?= ($tenant['pix_key_type'] ?? '') === 'email' ? 'selected' : '' ?>>E-mail</option>
                            <option value="random" <?= ($tenant['pix_key_type'] ?? '') === 'random' ? 'selected' : '' ?>>Chave Aleatória (EVP)</option>
                        </select>
                    </div>

                    <div>
                        <label for="pix_key" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Sua Chave Pix
                        </label>
                        <input type="text" id="pix_key" name="pix_key" value="<?= htmlspecialchars($tenant['pix_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Ex: 11999998888 ou sua chave"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                    </div>

                    <div>
                        <label for="pix_beneficiary" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Nome do Favorecido / Razão Social
                        </label>
                        <input type="text" id="pix_beneficiary" name="pix_beneficiary" value="<?= htmlspecialchars($tenant['pix_beneficiary'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Ex: Carlos Silva Assistência ME"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                    </div>
                </div>

                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-xs text-emerald-300 flex items-center gap-2">
                    <span>💡</span>
                    <span>Ao escolher Pix Manual, o comprovante impresso e o balcão apresentarão sua chave e o cliente transfere diretamente para a sua conta bancária sem intermediários.</span>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- BLOCO 2: EXCLUSIVO MERCADO PAGO (Oculto se Pix Manual ativo)    -->
            <!-- ============================================================= -->
            <div id="box-mp-fields" class="p-6 rounded-2xl bg-slate-950 border border-blue-500/30 space-y-4 <?= $paymentMode === 'mercadopago' ? '' : 'hidden' ?>">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-400 flex items-center gap-2">
                        <span>🤖</span>
                        <span>Credenciais Mercado Pago Developers (Baixa Automática)</span>
                    </h3>
                    <a href="https://www.mercadopago.com.br/developers/panel" target="_blank" class="text-xs font-bold text-blue-400 hover:underline">
                        Abrir Painel Mercado Pago Developers &nearr;
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="mp_public_key" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Public Key (Chave Pública)
                        </label>
                        <input type="text" id="mp_public_key" name="mp_public_key" value="<?= htmlspecialchars($tenant['mp_public_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="APP_USR-..."
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                    </div>

                    <div>
                        <label for="mp_access_token" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Access Token (Token de Produção)
                        </label>
                        <input type="password" id="mp_access_token" name="mp_access_token" value="<?= htmlspecialchars($tenant['mp_access_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="APP_USR-..."
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                    </div>
                </div>

                <!-- URL do Webhook Pronta -->
                <div class="p-3.5 bg-slate-900 rounded-xl border border-slate-800 text-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <span class="text-slate-400 block font-semibold mb-0.5">Sua URL de Notificação Webhook (cole no painel do MP):</span>
                        <code class="text-blue-300 font-mono select-all text-[11px]"><?= \Zyrtex\Core\Response::url('/webhook/mercadopago?tenant=' . urlencode($tenant['slug'])) ?></code>
                    </div>
                    <span class="text-[11px] text-slate-500 whitespace-nowrap">Marcar evento: Pagamentos</span>
                </div>

                <!-- Mini Tutorial Embutido (Tips) -->
                <?php 
                \Zyrtex\Core\View::partial('tips_card', [
                    'title' => 'Mini Tutorial: Como obter suas chaves do Mercado Pago em 2 minutos:',
                    'description' => '1. Acesse o Painel de Desenvolvedores do Mercado Pago com sua conta.<br>2. Crie uma aplicação para sua oficina.<br>3. Vá na aba "Credenciais de Produção" e copie a Public Key e o Access Token.<br>4. Em "Webhooks", cadastre a URL indicada acima. Pronto! Seus clientes pagarão com QR Code dinâmico e a OS baixa sozinha.',
                    'link' => 'https://www.mercadopago.com.br/developers/panel',
                    'linkText' => 'Acessar Mercado Pago Developers &rarr;'
                ]);
                ?>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                    Salvar Meios de Recebimento &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 3: FORMATOS DE IMPRESSÃO                                          -->
    <!-- ===================================================================== -->
    <div id="tab-pane-impressao" class="settings-tab-pane hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="border-b border-slate-800 pb-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>🖨️</span>
                <span>Modelos de Impressora & Etiquetas Adesivas</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Selecione o formato padrão da sua impressora física para emissão instantânea de cupons de balcão e adesivos.</p>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/impressao') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <!-- 4 Opções de Formato Físico -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Formato Padrão de Impressão:
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php 
                    $formats = [
                        '80mm' => ['icon' => '🧾', 'name' => 'Térmica 80mm', 'desc' => 'Bobina padrão balcão (Epson, Daruma, Elgin, Bematech). Espaço completo para laudo e QR.'],
                        '58mm' => ['icon' => '🧾', 'name' => 'Térmica 58mm', 'desc' => 'Bobina mini econômica (Mini POS, maquininhas portáteis bluetooth).'],
                        'label' => ['icon' => '🏷️', 'name' => 'Adesivos Mini QR', 'desc' => 'Etiquetas 50x30 / 40x25mm para colar direto na carcaça ou peça trocada.'],
                        'a4' => ['icon' => '📄', 'name' => 'Folha A4 (Oficial)', 'desc' => 'Contrato completo com termos legais em folha inteira para arquivamento.']
                    ];
                    foreach ($formats as $fKey => $fInfo): 
                        $isDef = ($defaultFormat === $fKey);
                    ?>
                        <label class="p-4 rounded-2xl border-2 cursor-pointer transition flex flex-col justify-between <?= $isDef ? 'border-indigo-500 bg-indigo-950/20' : 'border-slate-800 bg-slate-950 hover:border-slate-700' ?>">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-2xl"><?= $fInfo['icon'] ?></span>
                                    <input type="radio" name="default_format" value="<?= $fKey ?>" <?= $isDef ? 'checked' : '' ?> onchange="updatePrintTestLink(this.value)">
                                </div>
                                <h4 class="text-sm font-bold text-white"><?= $fInfo['name'] ?></h4>
                                <p class="text-[11px] text-slate-400 mt-1 leading-relaxed"><?= $fInfo['desc'] ?></p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SESSÃO DEDICADA: ELEMENTOS A INCLUIR NO CUPOM FÍSICO -->
            <div class="rounded-2xl bg-slate-950 border border-slate-800 p-6 space-y-5">
                <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>🧾</span>
                            <span>Elementos a Incluir no Cupom</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Personalize os blocos e códigos inteligentes impressos no cupom físico entregue ao cliente.
                        </p>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 hidden sm:inline-block">
                        Personalização por Lojista
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- 1. Logotipo no Cabeçalho -->
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/60 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="check_show_logo" class="font-bold text-xs text-white flex items-center gap-2 cursor-pointer">
                                    <span>🏢</span>
                                    <span>Imprimir Logotipo no Cabeçalho</span>
                                </label>
                                <input type="checkbox" id="check_show_logo" name="show_logo" value="1" <?= !empty($printerSettings['show_logo']) ? 'checked' : '' ?>
                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700 cursor-pointer">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Imprime a logomarca da sua assistência no topo do cupom térmico e da folha A4.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[10px]">
                            <?php if (!empty($tenant['logo_path'])): ?>
                                <span class="text-emerald-400 font-semibold flex items-center gap-1">
                                    <span>✓</span> Logotipo configurado
                                </span>
                                <span class="text-slate-500">Definido em Empresa & Local</span>
                            <?php else: ?>
                                <span class="text-amber-400 font-semibold flex items-center gap-1">
                                    <span>⚠️</span> Sem logo cadastrado
                                </span>
                                <button type="button" onclick="switchSettingsTab('empresa')" class="text-indigo-400 hover:underline font-bold">
                                    Cadastrar na Identidade &rarr;
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 2. QR Code do Pix para Pagamento -->
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/60 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="check_show_pix" class="font-bold text-xs text-white flex items-center gap-2 cursor-pointer">
                                    <span>💳</span>
                                    <span>Imprimir QR Code do Pix para Pagamento</span>
                                </label>
                                <input type="checkbox" id="check_show_pix" name="show_pix" value="1" <?= !empty($printerSettings['show_pix']) ? 'checked' : '' ?>
                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700 cursor-pointer">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Imprime o QR Code oficial do Pix com o valor da OS ou venda para recebimento imediato pelo celular do cliente.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-800 flex items-center justify-between text-[10px]">
                            <?php if (($tenant['payment_mode'] ?? 'manual_pix') === 'mercadopago' && !empty($tenant['mp_access_token'])): ?>
                                <span class="text-emerald-400 font-semibold">✓ Mercado Pago Integrado</span>
                            <?php elseif (!empty($tenant['pix_key'])): ?>
                                <span class="text-emerald-400 font-semibold">✓ Chave Pix: <?= htmlspecialchars($tenant['pix_key']) ?></span>
                            <?php else: ?>
                                <span class="text-amber-400 font-semibold">⚠️ Chave Pix não configurada</span>
                            <?php endif; ?>
                            <button type="button" onclick="switchSettingsTab('pagamentos')" class="text-indigo-400 hover:underline font-bold">
                                Configurar Chave &rarr;
                            </button>
                        </div>
                    </div>

                    <!-- 3. QR Code de Rastreio Rápido pelo Celular -->
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/60 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="check_show_qr" class="font-bold text-xs text-white flex items-center gap-2 cursor-pointer">
                                    <span>📱</span>
                                    <span>Imprimir QR Code de Rastreio Rápido pelo Celular</span>
                                </label>
                                <input type="checkbox" id="check_show_qr" name="show_qr" value="1" <?= !empty($printerSettings['show_qr']) ? 'checked' : '' ?>
                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700 cursor-pointer">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Imprime o QR Code dinâmico do portal do cliente para acompanhamento do laudo técnico e fotos em tempo real.
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-800 text-[10px] text-indigo-300 font-medium flex items-center gap-1">
                            <span>✓</span> Portal do Cliente 24h &bull; Aprovação Digital de Orçamentos
                        </div>
                    </div>

                    <!-- 4. Termos Legais de 90 Dias e Abandono -->
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-900/60 flex flex-col justify-between space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="check_show_terms" class="font-bold text-xs text-white flex items-center gap-2 cursor-pointer">
                                    <span>⚖️</span>
                                    <span>Incluir Termos Legais de 90 Dias e Abandono</span>
                                </label>
                                <input type="checkbox" id="check_show_terms" name="show_terms" value="1" <?= !empty($printerSettings['show_terms']) ? 'checked' : '' ?>
                                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-700 cursor-pointer">
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Imprime os termos de garantia legal de 90 dias (Código de Defesa do Consumidor Art. 26) e cláusula de abandono de bens após 90 dias (Código Civil Art. 1.275).
                            </p>
                        </div>
                        <div class="pt-2 border-t border-slate-800 text-[10px] text-emerald-400 font-medium flex items-center gap-1">
                            <span>✓</span> Blindagem Jurídica & Proteção contra Inadimplência
                        </div>
                    </div>

                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
                <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir-teste?formato=' . urlencode($defaultFormat)) ?>" target="_blank"
                   id="btn-print-test"
                   class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs border border-slate-700 transition flex items-center justify-center gap-2">
                    <span>🖨️ Imprimir Teste de Alinhamento</span>
                </a>

                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                    Salvar Configurações de Impressora &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 4: INTELIGÊNCIA ARTIFICIAL (HUGGING FACE / BYOK)                   -->
    <!-- ===================================================================== -->
    <div id="tab-pane-ia" class="settings-tab-pane hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="border-b border-slate-800 pb-4">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
                <span>🤖</span>
                <span>Inteligência Artificial Descentralizada (BYOK - Custo Zero)</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Traduza laudos técnicos difíceis em linguagem clara e amigável para o cliente sem gastar nada.</p>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/ia') ?>" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <div>
                <label for="ai_api_key" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Chave de API / Token da Inteligência Artificial
                </label>
                <input type="password" id="ai_api_key" name="ai_api_key" 
                       value="<?= htmlspecialchars($tenant['ai_api_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="gsk_... (Groq Grátis) | AIza... (Gemini Grátis) | sk-... (OpenAI) | hf_... (Hugging Face)"
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-sm">
                <span class="text-[11px] text-slate-400 mt-1 block">
                    O Zyrtex detecta automaticamente seu provedor (Groq, Gemini, OpenAI ou Hugging Face).
                </span>
            </div>

            <!-- Dicas de Provedores Gratuitos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-emerald-400 font-bold">
                        <span>⚡</span>
                        <span>Groq Cloud (100% Grátis & Ultrarrápido)</span>
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        Recomendado! Gera respostas em milissegundos sem custo e sem cartão de crédito.
                        Crie sua chave iniciada com <code>gsk_...</code>.
                    </p>
                    <a href="https://console.groq.com/keys" target="_blank" class="inline-block text-indigo-400 hover:underline font-bold text-[11px]">
                        Criar chave no Groq (Grátis) &rarr;
                    </a>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center gap-2 text-blue-400 font-bold">
                        <span>✨</span>
                        <span>Google Gemini (Grátis via AI Studio)</span>
                    </div>
                    <p class="text-slate-400 text-[11px] leading-relaxed">
                        Cota gratuita de 15 requisições/minuto no Google AI Studio. Chaves iniciadas com <code>AIza...</code>.
                    </p>
                    <a href="https://aistudio.google.com/app/apikey" target="_blank" class="inline-block text-indigo-400 hover:underline font-bold text-[11px]">
                        Criar chave no Google AI Studio &rarr;
                    </a>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                    Salvar Token de IA &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 5: GOOGLE DRIVE (BYOS - ARMAZENAMENTO & BACKUP - RF10)              -->
    <!-- ===================================================================== -->
    <div id="tab-pane-drive" class="settings-tab-pane hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span>💾</span>
                    <span>Armazenamento & Backup no Google Drive (BYOS)</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Envie automaticamente todas as fotos das OSs, laudos técnicos e comprovantes para a sua pasta do Google Drive.
                </p>
            </div>
            <?php if (!empty($tenant['gdrive_folder_id'])): ?>
                <span class="text-xs px-3 py-1.5 rounded-full font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1.5 self-start sm:self-auto">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Google Drive Conectado
                </span>
            <?php else: ?>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold bg-slate-800 text-slate-400 border border-slate-700 self-start sm:self-auto">
                    Armazenamento Local Ativo
                </span>
            <?php endif; ?>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/drive') ?>" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                <div>
                    <label for="gdrive_folder_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        ID da Pasta no Google Drive (Folder ID):
                    </label>
                    <input type="text" id="gdrive_folder_id" name="gdrive_folder_id"
                           value="<?= htmlspecialchars($tenant['gdrive_folder_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: 1A2b3C4d5E6f7G8h9I0jK_LmNoP"
                           class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <p class="text-[11px] text-slate-500 mt-1">
                        O ID da pasta é o código que aparece no link do seu navegador após <code>drive.google.com/drive/folders/<strong>[ID_AQUI]</strong></code>.
                    </p>
                </div>

                <div>
                    <label for="gdrive_token_json" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Credenciais da Conta de Serviço (JSON do Google Cloud):
                    </label>
                    <textarea id="gdrive_token_json" name="gdrive_token_json" rows="6"
                              placeholder='{"type": "service_account", "project_id": "...", "private_key": "...", "client_email": "..."}'
                              class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-300 font-mono text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"><?= htmlspecialchars($tenant['gdrive_token_json'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Chave JSON gerada na sua Service Account do Google Cloud com permissão da Google Drive API.
                    </p>
                </div>
            </div>

            <!-- Mini Tutorial Embutido (Tips Card - RF12) -->
            <?php
            \Zyrtex\Core\View::partial('tips_card', [
                'title' => 'Tutorial: Como conectar sua pasta do Google Drive em 3 passos',
                'description' => 'A Zyrtex não cobra por espaço de fotos nem armazena arquivos sensíveis de clientes. Você mantém controle total das suas mídias conectando sua própria conta (BYOS - Bring Your Own Storage).',
                'steps' => [
                    'Acesse o <a href="https://drive.google.com" target="_blank" class="text-indigo-400 font-bold underline">Google Drive</a> e crie uma nova pasta chamada <strong>"Zyrtex - Fotos das OSs"</strong>.',
                    'Abra a pasta recém-criada e copie o código final da URL (tudo o que estiver depois de <code>/folders/</code>). Cole no campo <strong>ID da Pasta</strong> acima.',
                    'No <a href="https://console.cloud.google.com" target="_blank" class="text-indigo-400 font-bold underline">Google Cloud Console</a>, ative a <strong>Google Drive API</strong>, crie uma <strong>Conta de Serviço</strong>, gere a chave JSON e compartilhe sua pasta do Drive com o e-mail da conta de serviço com permissão de Editor.'
                ],
                'externalUrl' => 'https://drive.google.com',
                'buttonLabel' => 'Abrir Google Drive &rarr;'
            ]);
            ?>

            <div class="pt-3 border-t border-slate-800 flex justify-end">
                <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                    Salvar Conexão do Google Drive &rarr;
                </button>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 6: PÁGINA PÚBLICA, ENDEREÇO & SUBDOMÍNIO                           -->
    <!-- ===================================================================== -->
    <div id="tab-pane-publica" class="settings-tab-pane hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
        
        <?php $isPremium = (int)($tenant['is_premium'] ?? 0) === 1; ?>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span>🌐</span>
                    <span>Endereço da Vitrine & Subdomínio</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Configure seu link de acesso exclusivo por subdomínio ou URL amigável e acompanhamento digital de ordens de serviço.
                </p>
            </div>

            <!-- Badge de Status do Plano -->
            <?php if ($isPremium): ?>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-amber-500/20 to-amber-600/10 border border-amber-500/30 text-amber-300 text-xs font-bold">
                    <span>★</span>
                    <span>Plano Anual Sem Anúncios Ativo &bull; Experiência Limpa</span>
                </div>
            <?php else: ?>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 text-xs font-semibold">
                    <span>Plano Free &bull; Anúncios Patrocinados Ativos</span>
                </div>
            <?php endif; ?>
        </div>

        <!-- BOX 1: Endereço Atual & Ações Rápidas de Compartilhamento -->
        <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-950/40 via-slate-950 to-slate-950 border border-indigo-500/30 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block">Link Oficial da sua Loja</span>
                    <h3 class="text-base font-extrabold text-white mt-0.5"><?= htmlspecialchars($tenant['business_name'], ENT_QUOTES, 'UTF-8') ?></h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        Disponível 24h na Internet
                    </span>
                    <a href="<?= $publicUrl ?>" target="_blank"
                       class="px-3 py-1 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-sm">
                        Abrir Vitrine &nearr;
                    </a>
                </div>
            </div>

            <!-- Campo de Copiar Link e Envio WhatsApp -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <input type="text" id="public_url_input" readonly value="<?= $publicUrl ?>"
                       class="flex-1 px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-indigo-300 font-mono text-sm select-all">
                
                <button type="button" onclick="copyPublicUrl()"
                        id="btn-copy-public"
                        class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider transition border border-slate-700 shrink-0">
                    📋 Copiar Link
                </button>

                <a href="<?= $whatsappShareUrl ?>" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/30 transition shrink-0">
                    <span>💬 WhatsApp do Cliente</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">

            <!-- BOX 2: Personalização do Endereço (Slug) -->
            <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>✏️</span>
                            <span>Personalizar Nome do Endereço (Slug)</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Seu identificador exclusivo no link da plataforma (ex: /<?= htmlspecialchars($tenant['slug']) ?>).
                        </p>
                    </div>
                    <?php if ($isPremium): ?>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            Ilimitado & Grátis
                        </span>
                    <?php else: ?>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            Taxa R$ 19,90 no Free
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($isPremium): ?>
                    <!-- Formulário Direto para Usuários Premium (Sem Cobrança) -->
                    <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/endereco') ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                        
                        <div>
                            <label for="premium_slug" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Novo Nome de Endereço:
                            </label>
                            <div class="flex items-center rounded-xl bg-slate-900 border border-slate-700 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 px-3">
                                <span class="text-xs text-slate-500 select-none">.../</span>
                                <input type="text" id="premium_slug" name="slug" 
                                       value="<?= htmlspecialchars($tenant['slug']) ?>" 
                                       oninput="formatSlugInput(this, 'preview_premium_url')"
                                       required
                                       class="w-full py-2.5 px-1 bg-transparent text-white font-mono text-xs focus:outline-none">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1.5">
                                Pré-visualização: <span id="preview_premium_url" class="text-indigo-400 font-mono">.../<?= htmlspecialchars($tenant['slug']) ?></span>
                            </p>
                        </div>

                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30">
                            ✓ Salvar Novo Endereço
                        </button>
                    </form>

                <?php else: ?>
                    <!-- Opção para Plano Free com Cobrança de Taxa para o SaaS -->
                    <div class="space-y-3.5 text-xs">
                        <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-slate-300 leading-relaxed">
                            <p>
                                No <strong>Plano Free</strong>, o endereço exclusivo é fixado no momento do cadastro. 
                                Caso queira trocar de marca ou mudar o link de acesso, você pode:
                            </p>
                            <ul class="list-disc pl-4 mt-2 space-y-1 text-slate-400">
                                <li>Pagar a <strong>taxa única de R$ 19,90 ao SaaS</strong> por cada alteração; ou</li>
                                <li>Fazer <strong>Upgrade para o Plano Anual Sem Anúncios</strong> com navegação limpa e alterações liberadas.</li>
                            </ul>
                        </div>

                        <div>
                            <label for="free_desired_slug" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Digite o Novo Endereço Desejado:
                            </label>
                            <div class="flex items-center rounded-xl bg-slate-900 border border-slate-700 focus-within:border-amber-500 focus-within:ring-1 focus-within:ring-amber-500 px-3">
                                <span class="text-xs text-slate-500 select-none">.../</span>
                                <input type="text" id="free_desired_slug" 
                                       placeholder="ex: nova-oficina-smart" 
                                       oninput="formatSlugInput(this, 'preview_free_url')"
                                       class="w-full py-2.5 px-1 bg-transparent text-white font-mono text-xs focus:outline-none">
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1.5">
                                Novo link será: <span id="preview_free_url" class="text-amber-400 font-mono">.../<?= htmlspecialchars($tenant['slug']) ?></span>
                            </p>
                        </div>

                        <div class="pt-1 flex flex-col gap-2">
                            <button type="button" id="btn_generate_slug_fee" onclick="generateSlugFeePix()"
                                    class="w-full py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-amber-600/30">
                                ⚡ Gerar Pix para Alteração (R$ 19,90)
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- BOX 3: Identidade Digital & Subdomínio Exclusivo -->
            <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                
                <?php if ($isPremium): ?>
                    <!-- Status do Plano Anual Sem Anúncios Ativo -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                                <span>✨</span>
                                <span>Subdomínio Exclusivo & Link da Loja</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Sua oficina com endereço profissional e 100% livre de qualquer anúncio.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ★ Anual Ativo
                        </span>
                    </div>

                    <div class="space-y-3 pt-1">
                        <!-- Card Subdomínio Wildcard -->
                        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400 block">Opção 1: Seu Subdomínio Exclusivo</span>
                            <div class="flex items-center justify-between gap-2">
                                <code class="text-xs text-white font-mono font-bold select-all">https://<?= htmlspecialchars($tenant['slug']) ?>.zyrtex.com.br</code>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">SSL Wildcard Ativo</span>
                            </div>
                        </div>

                        <!-- Card URL Amigável -->
                        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block">Opção 2: URL Amigável</span>
                            <div class="flex items-center justify-between gap-2">
                                <code class="text-xs text-white font-mono font-bold select-all">https://zyrtex.com.br/<?= htmlspecialchars($tenant['slug']) ?></code>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Disponível</span>
                            </div>
                        </div>

                        <!-- Vantagens Ativas -->
                        <div class="p-3.5 rounded-xl bg-emerald-950/20 border border-emerald-500/20 text-xs text-emerald-300 space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-emerald-200">
                                <span>🛡️</span>
                                <span>Benefícios Ativos no seu Plano Anual:</span>
                            </div>
                            <p class="text-[11px] text-slate-300">✓ <strong>Zero Anúncios:</strong> Vitrine, rastreio e portal dos clientes sem propagandas.</p>
                            <p class="text-[11px] text-slate-300">✓ <strong>Alterações Livres de Taxa:</strong> Troque o nome da loja/slug quando quiser.</p>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Bloco Bloqueado: Exclusivo do Plano Anual Sem Anúncios (Upsell Elegante) -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-300 flex items-center gap-2">
                                <span>✨</span>
                                <span>Plano Anual Sem Anúncios (Experiência Limpa)</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Remova 100% das publicidades da sua loja e passe máxima autoridade para seus clientes.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-slate-400 border border-slate-700">
                            Plano Free
                        </span>
                    </div>

                    <div class="p-4 rounded-xl bg-gradient-to-br from-amber-950/20 via-slate-900 to-slate-900 border border-amber-500/20 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">⭐</span>
                            <div>
                                <h4 class="text-xs font-bold text-amber-300">Deseja uma experiência 100% profissional e sem anúncios?</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                                    Ao assinar o <strong>Plano Anual Sem Anúncios</strong>, você elimina todas as publicidades do Google da sua vitrine, do rastreio de OS enviado aos clientes e do portal de aprovação de orçamentos!
                                </p>
                            </div>
                        </div>

                        <div class="text-[11px] text-slate-300 space-y-1 pt-1">
                            <div>✓ <strong>Zero Anúncios</strong> na sua vitrine comercial, rastreio de OS e portal do cliente</div>
                            <div>✓ <strong>Alterações de endereço e slug ilimitadas</strong> (sem taxa avulsa de R$ 19,90)</div>
                            <div>✓ <strong>Selo de Oficina Verificada</strong> com maior credibilidade</div>
                            <div>✓ <strong>Apenas R$ <?= htmlspecialchars($annualPrice ?? '197,00', ENT_QUOTES, 'UTF-8') ?> por ano</strong> (menos de R$ 0,55 por dia!)</div>
                        </div>

                        <!-- Formulário de Ativação / Upgrade -->
                        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/upgrade-premium') ?>" method="POST" class="pt-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" 
                                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 text-slate-950 font-extrabold text-xs uppercase tracking-wider transition shadow-lg shadow-amber-600/30 flex items-center justify-center gap-2">
                                <span>★ Assinar Plano Anual Sem Anúncios (R$ <?= htmlspecialchars($annualPrice ?? '197,00', ENT_QUOTES, 'UTF-8') ?>/ano)</span>
                            </button>
                        </form>
                    </div>

                <?php endif; ?>

            </div>

        </div>

        <!-- BOX 4: Personalização Visual da Vitrine Pública (Fase 5) -->
        <div class="mt-6 p-6 rounded-2xl bg-slate-950 border border-slate-800 space-y-6">
            <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>🎨</span>
                        <span>Personalização da Vitrine Pública (Fase 5)</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Defina a identidade visual, paleta de cores e serviços promocionais em destaque exibidos aos seus clientes na vitrine online.
                    </p>
                </div>
                <a href="<?= $publicUrl ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 hover:bg-indigo-500/20 transition self-start sm:self-auto">
                    <span>👁️ Ver Vitrine ao Vivo</span>
                    <span>&nearr;</span>
                </a>
            </div>

            <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/vitrine') ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                <!-- Banner de Capa da Loja (Hero Banner - Fase 5) -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white uppercase tracking-wider block">Banner de Capa da Vitrine (Hero Banner):</span>
                            <span class="text-[11px] text-slate-400">Imagem ampla de fundo para o topo da sua página pública (recomendado: 1920x600px).</span>
                        </div>
                        <?php if (!empty($tenant['banner_path'])): ?>
                            <label class="text-xs text-rose-400 hover:text-rose-300 flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="remove_banner" value="1" class="rounded border-slate-700 text-rose-600">
                                <span>Remover Banner Atual</span>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="w-full sm:w-48 h-24 rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shrink-0 flex items-center justify-center text-slate-500 font-bold text-xs" id="vitrine_banner_preview_box">
                            <?php if (!empty($tenant['banner_path'])): ?>
                                <img id="vitrine_banner_img" src="<?= htmlspecialchars(asset($tenant['banner_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Banner" class="w-full h-full object-cover">
                                <span id="vitrine_banner_placeholder" class="hidden">Sem Banner</span>
                            <?php else: ?>
                                <img id="vitrine_banner_img" src="" alt="Banner" class="w-full h-full object-cover hidden">
                                <span id="vitrine_banner_placeholder">Sem Banner</span>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 space-y-2 w-full">
                            <div>
                                <label class="block text-[10px] text-slate-400 font-medium mb-1">Upload da Imagem de Capa:</label>
                                <input type="file" name="banner_file" accept="image/*"
                                       onchange="previewImageInput(this, 'vitrine_banner_img', 'vitrine_banner_placeholder')"
                                       class="block w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                            </div>

                            <div>
                                <label class="block text-[10px] text-slate-400 font-medium mb-1">Ou Cole a URL Direta do Banner:</label>
                                <input type="url" name="banner_url" placeholder="https://exemplo.com/banner.jpg"
                                       oninput="previewUrlInput(this.value, 'vitrine_banner_img', 'vitrine_banner_placeholder')"
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 1. Paleta de Cores de Destaque -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                        Cor de Destaque da Vitrine (Tema do Lojista):
                    </label>
                    <p class="text-[11px] text-slate-500 mb-3">
                        Substitui o padrão Indigo na visão do seu cliente, personalizando botões, destaques e cards da sua página.
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                        <?php
                        $currentColor = $tenant['theme_color'] ?? 'indigo';
                        $colorOptions = [
                            'indigo'  => ['name' => 'Indigo Oficial', 'bg' => 'bg-indigo-600'],
                            'emerald' => ['name' => 'Esmeralda / Ação', 'bg' => 'bg-emerald-600'],
                            'blue'    => ['name' => 'Azul Safira', 'bg' => 'bg-blue-600'],
                            'amber'   => ['name' => 'Âmbar / Dourado', 'bg' => 'bg-amber-600'],
                            'rose'    => ['name' => 'Rosa / Carmesim', 'bg' => 'bg-rose-600'],
                            'violet'  => ['name' => 'Violeta Moderno', 'bg' => 'bg-violet-600'],
                            'cyan'    => ['name' => 'Ciano Tech', 'bg' => 'bg-cyan-600']
                        ];
                        foreach ($colorOptions as $cKey => $cInfo):
                            $isSelected = ($currentColor === $cKey);
                        ?>
                            <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col items-center text-center gap-2 <?= $isSelected ? 'border-white bg-slate-800 ring-2 ring-indigo-500' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700' ?>">
                                <span class="w-6 h-6 rounded-full <?= $cInfo['bg'] ?> shadow-sm"></span>
                                <span class="text-[11px] font-bold text-white"><?= $cInfo['name'] ?></span>
                                <input type="radio" name="theme_color" value="<?= $cKey ?>" <?= $isSelected ? 'checked' : '' ?> class="sr-only">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 2. Modo de Exibição do Tema (Claro / Escuro / Automático) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                            Modo de Exibição do Tema (Vitrine Pública & Portal):
                        </label>
                        <span class="text-[10px] text-slate-400">Define a aparência padrão para os clientes</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mb-3">
                        Escolha se a vitrine pública e o portal do cliente devem respeitar a preferência do dispositivo do usuário (automático) ou se você prefere fixar um visual específico.
                    </p>

                    <?php
                    $currentMode = $tenant['theme_mode'] ?? 'auto';
                    $themeModes = [
                        'auto' => [
                            'icon' => '🖥️',
                            'name' => 'Automático (Padrão)',
                            'desc' => 'Segue a preferência do sistema/celular do visitante (Dark ou Light)',
                            'badge' => 'Recomendado'
                        ],
                        'light' => [
                            'icon' => '☀️',
                            'name' => 'Forçar Modo Claro',
                            'desc' => 'Fundo branco limpo, ideal para visual clássico e luminoso',
                            'badge' => 'Claro Fixo'
                        ],
                        'dark' => [
                            'icon' => '🌙',
                            'name' => 'Forçar Modo Escuro',
                            'desc' => 'Fundo escuro moderno (Dark Slate) de alta visibilidade',
                            'badge' => 'Escuro Fixo'
                        ]
                    ];
                    ?>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <?php foreach ($themeModes as $mKey => $mInfo): 
                            $isModeSelected = ($currentMode === $mKey);
                        ?>
                            <label class="p-4 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between gap-3 <?= $isModeSelected ? 'border-indigo-500 bg-indigo-950/20 ring-2 ring-indigo-500/20' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700' ?>">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-xl"><?= $mInfo['icon'] ?></span>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $isModeSelected ? 'bg-indigo-500/30 text-indigo-300 border border-indigo-500/40' : 'bg-slate-800 text-slate-400' ?>">
                                            <?= $mInfo['badge'] ?>
                                        </span>
                                    </div>
                                    <h4 class="text-xs font-bold text-white mt-2"><?= $mInfo['name'] ?></h4>
                                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed"><?= $mInfo['desc'] ?></p>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-800/80">
                                    <span class="text-[10px] text-slate-500 font-mono"><?= $mKey ?></span>
                                    <input type="radio" name="theme_mode" value="<?= $mKey ?>" <?= $isModeSelected ? 'checked' : '' ?> class="text-indigo-600 focus:ring-indigo-500">
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Slogan e Chamada no Hero -->
                <div>
                    <label for="vitrine_slogan" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Chamada Principal / Slogan de Destaque na Vitrine:
                    </label>
                    <input type="text" id="vitrine_slogan" name="slogan"
                           value="<?= htmlspecialchars($tenant['slogan'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: Transparência total em cada reparo com laudos digitais e garantia comprovada."
                           class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- 4. Mensagem Personalizada do Botão do WhatsApp (Atendimento 1-Clique) -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <label for="vitrine_whatsapp_message" class="block text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span>💬</span>
                                <span>Mensagem Padrão do Botão do WhatsApp na Vitrine:</span>
                            </label>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Este texto será aberto automaticamente no aplicativo do WhatsApp do cliente ao clicar no botão de contato da sua vitrine.
                            </p>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 self-start sm:self-auto flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Atendimento 1-Clique
                        </span>
                    </div>

                    <div>
                        <textarea id="vitrine_whatsapp_message" name="whatsapp_message" rows="3"
                                  placeholder="Ex: Olá! Vi a vitrine da {oficina} e gostaria de tirar uma dúvida sobre reparos e orçamentos."
                                  class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs placeholder-slate-500 focus:ring-2 focus:ring-emerald-500 focus:outline-none leading-relaxed font-sans"><?= htmlspecialchars($tenant['whatsapp_message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-[11px] text-slate-500 mt-2">
                            <span>💡 Use a tag <code class="text-indigo-400 font-mono font-bold bg-slate-900 px-1 py-0.5 rounded border border-slate-800">{oficina}</code> para inserir o nome da sua loja automaticamente. Deixe em branco para a saudação padrão.</span>
                            <?php if (!empty($tenant['whatsapp_number'])): ?>
                                <span class="text-emerald-400 font-semibold text-[11px] flex items-center gap-1 shrink-0">
                                    <span>📱 Vinculado ao WhatsApp:</span>
                                    <strong class="font-mono"><?= htmlspecialchars($tenant['whatsapp_number'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                            <?php else: ?>
                                <span class="text-amber-400 font-semibold text-[11px] shrink-0">
                                    ⚠️ Nenhum WhatsApp cadastrado na aba "Empresa & Local"
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. Gestão de Promoções em Destaque na Vitrine com Foto (Fase 5) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                            Promoções & Serviços em Destaque na Vitrine (Até 3 Ofertas com Foto):
                        </label>
                        <span class="text-[10px] text-slate-500">Exibidas com foto de capa e botão de WhatsApp na sua vitrine</span>
                    </div>

                    <?php
                    $promos = json_decode($tenant['promotions_json'] ?? '[]', true) ?: [];
                    for ($i = 0; $i < 3; $i++):
                        $p = $promos[$i] ?? ['title' => '', 'description' => '', 'price' => '', 'image_path' => ''];
                        $hasPromoImg = !empty($p['image_path']);
                    ?>
                        <div class="p-4 mb-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                <div class="sm:col-span-4">
                                    <label class="block text-[10px] text-slate-400 font-semibold uppercase mb-1">Título da Oferta #<?= $i + 1 ?>:</label>
                                    <input type="text" name="promo_title[]" value="<?= htmlspecialchars($p['title'] ?? '') ?>"
                                           placeholder="Ex: Troca de Tela Express"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-white text-xs">
                                </div>
                                <div class="sm:col-span-5">
                                    <label class="block text-[10px] text-slate-400 font-semibold uppercase mb-1">Descrição Breve:</label>
                                    <input type="text" name="promo_desc[]" value="<?= htmlspecialchars($p['description'] ?? '') ?>"
                                           placeholder="Ex: Peça original com 1 ano de garantia"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-white text-xs">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-[10px] text-slate-400 font-semibold uppercase mb-1">Preço Promocional (R$):</label>
                                    <input type="text" name="promo_price[]" value="<?= htmlspecialchars($p['price'] ?? '') ?>"
                                           placeholder="Ex: 149,90"
                                           class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-slate-700 text-emerald-400 font-mono font-bold text-xs">
                                </div>
                            </div>

                            <!-- Foto da Promoção (Dual: Upload OU URL) -->
                            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2 border-t border-slate-800/60">
                                <input type="hidden" name="promo_existing_image[]" value="<?= htmlspecialchars($p['image_path'] ?? '') ?>">

                                <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-950 border border-slate-700 shrink-0 flex items-center justify-center text-slate-500 text-[9px]" id="promo_preview_box_<?= $i ?>">
                                    <?php if ($hasPromoImg): ?>
                                        <img id="promo_img_<?= $i ?>" src="<?= htmlspecialchars(asset($p['image_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Promo" class="w-full h-full object-cover">
                                        <span id="promo_placeholder_<?= $i ?>" class="hidden">Sem Foto</span>
                                    <?php else: ?>
                                        <img id="promo_img_<?= $i ?>" src="" alt="Promo" class="w-full h-full object-cover hidden">
                                        <span id="promo_placeholder_<?= $i ?>">Sem Foto</span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-2 w-full">
                                    <div>
                                        <label class="block text-[9px] text-slate-400 mb-0.5">Upload da Foto de Capa:</label>
                                        <input type="file" name="promo_image_file_<?= $i ?>" accept="image/*"
                                               onchange="previewImageInput(this, 'promo_img_<?= $i ?>', 'promo_placeholder_<?= $i ?>')"
                                               class="block w-full text-[11px] text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-slate-800 file:text-slate-300 hover:file:bg-slate-700 cursor-pointer">
                                    </div>
                                    <div>
                                        <label class="block text-[9px] text-slate-400 mb-0.5">Ou URL da Foto:</label>
                                        <input type="url" name="promo_image_url[]" placeholder="https://exemplo.com/foto.jpg"
                                               oninput="previewUrlInput(this.value, 'promo_img_<?= $i ?>', 'promo_placeholder_<?= $i ?>')"
                                               class="w-full px-2 py-1 rounded bg-slate-950 border border-slate-700 text-white text-[11px] font-mono">
                                    </div>
                                </div>

                                <?php if ($hasPromoImg): ?>
                                    <label class="text-[10px] text-rose-400 hover:text-rose-300 flex items-center gap-1 cursor-pointer whitespace-nowrap self-end sm:self-center">
                                        <input type="checkbox" name="remove_promo_image_<?= $i ?>" value="1" class="rounded border-slate-700 text-rose-600">
                                        <span>Remover Foto</span>
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- 4. Barra de Métricas & Prova Social na Vitrine (Dinâmico ou Personalizável) -->
                <?php
                $metricsCfg = json_decode($tenant['public_metrics_json'] ?? '[]', true) ?: [];
                $showMetrics = !isset($metricsCfg['show_metrics']) || !empty($metricsCfg['show_metrics']);
                $metricsMode = $metricsCfg['mode'] ?? 'auto';
                ?>
                <div class="p-5 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span>📊</span>
                                <span>Barra de Métricas & Prova Social na Vitrine</span>
                            </h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Os 4 cartões com números de destaque que aparecem no topo da sua página pública.
                            </p>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer bg-slate-950 px-3 py-1.5 rounded-xl border border-slate-800">
                            <input type="checkbox" name="show_metrics" value="1" <?= $showMetrics ? 'checked' : '' ?>
                                   class="rounded border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-white">Exibir Barra de Métricas</span>
                        </label>
                    </div>

                    <!-- Seletor de Modo: Automático vs Personalizado -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-800/80">
                        <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3 <?= $metricsMode === 'auto' ? 'border-indigo-500 bg-indigo-950/20' : 'border-slate-800 bg-slate-950 hover:border-slate-700' ?>">
                            <input type="radio" name="metrics_mode" value="auto" <?= $metricsMode === 'auto' ? 'checked' : '' ?>
                                   onchange="toggleMetricsCustomFields('auto')" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-white block">Automático (Recomendado)</span>
                                <span class="text-[11px] text-slate-400 leading-relaxed block mt-0.5">
                                    O sistema calcula dinamicamente com base nas suas Ordens de Serviço (reparos concluídos e aprovações). Lojas novas recebem fallback inteligente para nunca exibir "0 aparelhos".
                                </span>
                            </div>
                        </label>

                        <label class="p-3.5 rounded-xl border cursor-pointer transition flex items-start gap-3 <?= $metricsMode === 'custom' ? 'border-indigo-500 bg-indigo-950/20' : 'border-slate-800 bg-slate-950 hover:border-slate-700' ?>">
                            <input type="radio" name="metrics_mode" value="custom" <?= $metricsMode === 'custom' ? 'checked' : '' ?>
                                   onchange="toggleMetricsCustomFields('custom')" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <span class="text-xs font-bold text-white block">Personalizado</span>
                                <span class="text-[11px] text-slate-400 leading-relaxed block mt-0.5">
                                    Defina seus próprios números e rótulos. Ideal para oficinas com anos de mercado ou que queiram destacar avaliações do Google (ex: 4.9 ★).
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Campos Customizados (Exibidos se Modo = Personalizado) -->
                    <div id="metrics_custom_fields" class="space-y-3 pt-2 <?= $metricsMode === 'custom' ? '' : 'hidden' ?>">
                        <div class="p-3 rounded-xl bg-indigo-950/30 border border-indigo-500/20 text-[11px] text-indigo-300">
                            💡 <strong>Dica:</strong> Preencha os campos abaixo com os números e textos que você quer que apareçam nos 4 cards da sua vitrine pública.
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Card 1 -->
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">🔧 Card 1 (Aparelhos / Reparos)</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Valor em Destaque:</label>
                                        <input type="text" name="metric1_value" value="<?= htmlspecialchars($metricsCfg['metric1_value'] ?? '+2.400') ?>"
                                               placeholder="Ex: +2.400 ou Ágil"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white font-mono font-bold text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Rótulo / Descrição:</label>
                                        <input type="text" name="metric1_label" value="<?= htmlspecialchars($metricsCfg['metric1_label'] ?? 'Aparelhos Reparados') ?>"
                                               placeholder="Ex: Aparelhos Reparados"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">⭐ Card 2 (Satisfação / Avaliação)</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Valor em Destaque:</label>
                                        <input type="text" name="metric2_value" value="<?= htmlspecialchars($metricsCfg['metric2_value'] ?? '99.4%') ?>"
                                               placeholder="Ex: 99.4% ou 4.9 ★"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white font-mono font-bold text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Rótulo / Descrição:</label>
                                        <input type="text" name="metric2_label" value="<?= htmlspecialchars($metricsCfg['metric2_label'] ?? 'Satisfação dos Clientes') ?>"
                                               placeholder="Ex: Satisfação dos Clientes"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3 -->
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">🛡️ Card 3 (Garantia)</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Valor em Destaque:</label>
                                        <input type="text" name="metric3_value" value="<?= htmlspecialchars($metricsCfg['metric3_value'] ?? '90 Dias') ?>"
                                               placeholder="Ex: 90 Dias ou 1 Ano"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white font-mono font-bold text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Rótulo / Descrição:</label>
                                        <input type="text" name="metric3_label" value="<?= htmlspecialchars($metricsCfg['metric3_label'] ?? 'Garantia Legal CDC') ?>"
                                               placeholder="Ex: Garantia Legal CDC"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                    </div>
                                </div>
                            </div>

                            <!-- Card 4 -->
                            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">⚡ Card 4 (Agilidade / WhatsApp)</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Valor em Destaque:</label>
                                        <input type="text" name="metric4_value" value="<?= htmlspecialchars($metricsCfg['metric4_value'] ?? '1-Clique') ?>"
                                               placeholder="Ex: 1-Clique ou 24h"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white font-mono font-bold text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-slate-400 mb-0.5">Rótulo / Descrição:</label>
                                        <input type="text" name="metric4_label" value="<?= htmlspecialchars($metricsCfg['metric4_label'] ?? 'Aprovação no WhatsApp') ?>"
                                               placeholder="Ex: Aprovação no WhatsApp"
                                               class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-800">
                    <button type="submit"
                            class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30 flex items-center gap-2">
                        <span>✓ Salvar Personalização da Vitrine</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Card: Histórico de Assinaturas & Garantia CDC Art. 49 -->
        <div class="p-6 rounded-2xl bg-slate-950 border border-slate-800 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <span>📜</span>
                        <span>Transações de Planos & Assinaturas Zyrtex</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        Histórico de assinaturas do Plano Anual Sem Anúncios, taxas de migração e suporte ao Direito de Arrependimento (CDC Art. 49).
                    </p>
                </div>
                <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    🛡️ Garantia Legal de 7 Dias
                </span>
            </div>

            <?php if (empty($subscriptions)): ?>
                <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">
                    Nenhuma assinatura ou taxa de serviço contratada até o momento.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-900 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Serviço / Plano</th>
                                <th class="py-2.5 px-3">Data</th>
                                <th class="py-2.5 px-3">Valor</th>
                                <th class="py-2.5 px-3">Situação</th>
                                <th class="py-2.5 px-3 text-right">Ações (CDC Art. 49)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            <?php foreach ($subscriptions as $sub): 
                                $planNames = [
                                    'annual_domain' => 'Plano Anual Sem Anúncios',
                                    'slug_change' => 'Alteração de Endereço',
                                    'niche_change' => 'Alteração de Nicho'
                                ];
                                $name = $planNames[$sub['plan_type']] ?? $sub['plan_type'];
                                $purchasedAt = !empty($sub['purchased_at']) ? strtotime($sub['purchased_at']) : strtotime($sub['created_at']);
                                $daysPassed = (time() - $purchasedAt) / 86400;
                                $canRefund = ($sub['status'] === 'approved' && (int)$sub['is_refunded'] === 0 && $daysPassed <= 7);
                            ?>
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-white">
                                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($sub['target_value'])): ?>
                                            <code class="text-[10px] text-indigo-400 block font-mono"><?= htmlspecialchars($sub['target_value'], ENT_QUOTES, 'UTF-8') ?></code>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-400">
                                        <?= date('d/m/Y H:i', $purchasedAt) ?>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-white">
                                        R$ <?= number_format((float)$sub['amount'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <?php if ($sub['status'] === 'approved'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                ✓ Aprovado
                                            </span>
                                        <?php elseif ($sub['status'] === 'refunded' || (int)$sub['is_refunded'] === 1): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                                ↩ Estornado
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                ⏳ <?= htmlspecialchars(ucfirst($sub['status']), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <?php if ($canRefund): ?>
                                            <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/estornar-plano') ?>" method="POST" class="inline"
                                                  onsubmit="return confirm('Deseja realmente solicitar o estorno integral deste plano conforme o Art. 49 do CDC? O recurso será desativado e o valor devolvido.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="subscription_id" value="<?= (int)$sub['id'] ?>">
                                                <button type="submit" 
                                                        class="px-2.5 py-1 rounded-lg bg-rose-600/80 hover:bg-rose-600 text-white font-bold text-[10px] transition border border-rose-500/40 shadow-sm">
                                                    Solicitar Estorno (CDC)
                                                </button>
                                            </form>
                                        <?php elseif ((int)$sub['is_refunded'] === 1): ?>
                                            <span class="text-[10px] text-purple-400">Estorno concluído</span>
                                        <?php else: ?>
                                            <span class="text-[10px] text-slate-500">Prazo CDC encerrado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- MODAL DE CHECKOUT PIX: TAXA DE ALTERAÇÃO DE SLUG (PLANO FREE)          -->
    <!-- ===================================================================== -->
    <div id="modal-slug-fee" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
        <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl relative">
            
            <button type="button" onclick="closeSlugFeeModal()" 
                    class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg font-bold">
                ✕
            </button>

            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400 block">SaaS Zyrtex &bull; Pagamento de Taxa</span>
                <h3 class="text-lg font-extrabold text-white mt-1">Alteração de Endereço Exclusivo</h3>
                <p class="text-xs text-slate-400 mt-1">
                    Taxa única de manutenção de rota e atualização de DNS no Plano Free.
                </p>
            </div>

            <!-- Resumo da Mudança -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                <div class="flex justify-between items-center text-slate-400">
                    <span>Endereço Atual:</span>
                    <span class="font-mono text-white">/<?= htmlspecialchars($tenant['slug']) ?></span>
                </div>
                <div class="flex justify-between items-center text-slate-400">
                    <span>Novo Endereço Solicitado:</span>
                    <span id="modal_new_slug_display" class="font-mono font-bold text-amber-400">...</span>
                </div>
                <div class="border-t border-slate-800 pt-2 flex justify-between items-center text-sm font-bold text-white">
                    <span>Valor da Taxa:</span>
                    <span id="modal_fee_amount" class="text-emerald-400 font-mono text-base">R$ 19,90</span>
                </div>
            </div>

            <!-- Bloco de Pagamento Pix (QR Code + Copia e Cola) -->
            <div id="modal_pix_container" class="space-y-4">
                <!-- QR Code Pix Gerado -->
                <div class="text-center space-y-2">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md">
                        <img id="modal_pix_qrcode" 
                             src="" 
                             alt="QR Code Pix R$ 19,90" 
                             class="w-36 h-36 mx-auto">
                    </div>
                    <p class="text-[11px] text-slate-400">Escaneie o QR Code com o aplicativo do seu banco</p>
                </div>

                <!-- Código Pix Copia e Cola -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Pix Copia e Cola:
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" id="modal_pix_code" readonly 
                               value=""
                               class="flex-1 px-3 py-2 bg-slate-950 border border-slate-700 text-slate-300 font-mono text-[11px] rounded-xl select-all">
                        <button type="button" onclick="copyPixFeeCode()" 
                                id="btn_copy_pix_fee"
                                class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl border border-slate-700 transition shrink-0">
                            Copiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Dinâmico: Aguardando Webhook do Mercado Pago -->
            <div id="modal_status_box" class="p-3.5 rounded-xl bg-amber-950/20 border border-amber-500/30 flex items-center gap-3">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <div class="text-xs">
                    <p class="font-bold text-amber-300">Aguardando confirmação do Pix via Mercado Pago...</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Identificação 100% automática. Não é necessário clicar em nada após pagar.</p>
                </div>
            </div>

            <!-- Card de Sucesso (Exibido quando o Webhook Aprova) -->
            <div id="modal_success_box" class="hidden p-4 rounded-xl bg-emerald-950/40 border border-emerald-500/40 text-center space-y-2">
                <div class="w-10 h-10 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl font-bold">
                    ✓
                </div>
                <h4 class="text-sm font-extrabold text-emerald-400">Pagamento Aprovado com Sucesso!</h4>
                <p class="text-xs text-slate-300">
                    Seu novo endereço <span id="modal_success_slug" class="font-mono text-emerald-300 font-bold"></span> já está ativo!
            </div>

        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- ABA 7: SEGURANÇA & 2FA STEAM GUARD                                    -->
    <!-- ===================================================================== -->
    <div id="tab-pane-seguranca" class="settings-tab-pane bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm hidden">
        <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center text-base">🛡️</span>
                    <span>Segurança da Conta & Autenticação em Dois Fatores (2FA)</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">Proteja sua oficina contra acessos indevidos com tokens criptográficos de segundo fator estilo Steam Guard.</p>
            </div>

            <span class="px-3 py-1.5 rounded-full text-xs font-bold <?= !empty($user['two_factor_enabled']) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' ?> flex items-center gap-2 shrink-0">
                <span class="w-2 h-2 rounded-full <?= !empty($user['two_factor_enabled']) ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' ?>"></span>
                <span><?= !empty($user['two_factor_enabled']) ? '2FA Zyrtex Guard Ativo' : '2FA Desativado' ?></span>
            </span>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/configuracoes/seguranca') ?>" method="POST" class="space-y-6">
            <?= $csrf ?>

            <!-- Card Explicativo Estilo Steam Guard -->
            <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950/30 border border-cyan-500/30 space-y-4 shadow-xl">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 flex items-center justify-center text-2xl shrink-0">
                        🛡️
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                            <span>Zyrtex Guard</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 uppercase">Steam Style 2FA</span>
                        </h3>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Quando ativado, sempre que você tentar entrar no sistema com seu e-mail e senha, um código alfanumérico seguro de 5 caracteres será gerado imediatamente e enviado para o seu e-mail cadastrado (<strong><?= htmlspecialchars($user['email'] ?? '') ?></strong>). O acesso à sua oficina só é liberado após a digitação desse código de 5 dígitos.
                        </p>
                    </div>
                </div>

                <!-- Toggle Switch -->
                <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                    <div class="space-y-0.5">
                        <span class="text-xs font-bold text-white block">Ativar verificação em duas etapas no login</span>
                        <span class="text-[11px] text-slate-400 block">Exige o código de segurança a cada nova sessão iniciada</span>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="two_factor_enabled" value="0">
                        <input type="checkbox" name="two_factor_enabled" value="1" <?= (!empty($user['two_factor_enabled'])) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-12 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-500 shadow-inner"></div>
                    </label>
                </div>
            </div>

            <!-- Dicas de Segurança Adicionais -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-slate-400">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                    <span class="font-bold text-slate-200 block flex items-center gap-1.5">
                        <span>⏱️</span>
                        <span>Expiração de 10 Minutos</span>
                    </span>
                    <p class="text-[11px]">Cada código gerado possui validade estrita de 10 minutos. Códigos não utilizados são descartados automaticamente.</p>
                </div>
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                    <span class="font-bold text-slate-200 block flex items-center gap-1.5">
                        <span>🔒</span>
                        <span>Criptografia Hashed</span>
                    </span>
                    <p class="text-[11px]">Os tokens nunca são salvos em texto plano na base de dados, utilizando hash SHA-256 irreversível para máxima blindagem.</p>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-cyan-600/30 flex items-center gap-2">
                    <span>💾</span>
                    <span>Salvar Configuração de Segurança</span>
                </button>
            </div>
        </form>
    </div>

</div>

<!-- SCRIPT DE ABAS, TOGGLE EXCLUSIVO & MODAL DE TAXA -->
<script>
function scrollAdminTabs(direction) {
    const track = document.getElementById('adminTabsTrack');
    if (!track) return;
    const scrollAmount = 260;
    track.scrollBy({
        left: direction === 'left' ? -scrollAmount : scrollAmount,
        behavior: 'smooth'
    });
}

function updateAdminTabsScrollState() {
    const track = document.getElementById('adminTabsTrack');
    const leftBtn = document.getElementById('adminTabScrollLeftBtn');
    const rightBtn = document.getElementById('adminTabScrollRightBtn');
    const fadeLeft = document.getElementById('adminTabFadeLeft');
    const fadeRight = document.getElementById('adminTabFadeRight');
    const indicatorTrack = document.getElementById('adminTabScrollIndicatorTrack');
    const indicatorThumb = document.getElementById('adminTabScrollIndicatorThumb');

    if (!track) return;

    const maxScroll = track.scrollWidth - track.clientWidth;
    const hasOverflow = maxScroll > 8;

    if (!hasOverflow) {
        if (leftBtn) { leftBtn.classList.add('opacity-0', 'pointer-events-none'); leftBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
        if (rightBtn) { rightBtn.classList.add('opacity-0', 'pointer-events-none'); rightBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
        if (fadeLeft) fadeLeft.classList.add('opacity-0');
        if (fadeRight) fadeRight.classList.add('opacity-0');
        if (indicatorTrack) indicatorTrack.classList.add('hidden');
        return;
    }

    if (indicatorTrack) indicatorTrack.classList.remove('hidden');

    // Estado Esquerda
    if (track.scrollLeft > 6) {
        if (leftBtn) { leftBtn.classList.remove('opacity-0', 'pointer-events-none'); leftBtn.classList.add('opacity-100', 'pointer-events-auto'); }
        if (fadeLeft) fadeLeft.classList.remove('opacity-0');
    } else {
        if (leftBtn) { leftBtn.classList.add('opacity-0', 'pointer-events-none'); leftBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
        if (fadeLeft) fadeLeft.classList.add('opacity-0');
    }

    // Estado Direita
    if (track.scrollLeft < maxScroll - 6) {
        if (rightBtn) { rightBtn.classList.remove('opacity-0', 'pointer-events-none'); rightBtn.classList.add('opacity-100', 'pointer-events-auto'); }
        if (fadeRight) fadeRight.classList.remove('opacity-0');
    } else {
        if (rightBtn) { rightBtn.classList.add('opacity-0', 'pointer-events-none'); rightBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
        if (fadeRight) fadeRight.classList.add('opacity-0');
    }

    // Micro-barra de progresso
    if (indicatorThumb && maxScroll > 0) {
        const thumbWidthPercent = Math.max(15, Math.min(50, (track.clientWidth / track.scrollWidth) * 100));
        const availableMove = 100 - thumbWidthPercent;
        const progress = Math.max(0, Math.min(1, track.scrollLeft / maxScroll));
        const marginLeft = progress * availableMove;
        indicatorThumb.style.width = thumbWidthPercent + '%';
        indicatorThumb.style.marginLeft = marginLeft + '%';
    }
}

function switchSettingsTab(tabId) {
    // Esconde todos os painéis
    document.querySelectorAll('.settings-tab-pane').forEach(el => el.classList.add('hidden'));

    // Reseta estilo de todos os botões de aba
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-600/30');
        btn.classList.add('text-slate-400');
    });

    // Exibe o painel selecionado
    const activePane = document.getElementById('tab-pane-' + tabId);
    if (activePane) {
        activePane.classList.remove('hidden');
    }

    // Ativa o botão selecionado
    const activeBtn = document.getElementById('tab-btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-400');
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-lg', 'shadow-indigo-600/30');

        // Auto-centraliza a aba suavemente no viewport visível
        activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    // Atualiza o hash na URL
    window.location.hash = '#' + tabId;
}

function togglePaymentFields(mode) {
    const pixBox = document.getElementById('box-pix-fields');
    const mpBox = document.getElementById('box-mp-fields');
    const radioPix = document.getElementById('radio-mode-pix');
    const radioMp = document.getElementById('radio-mode-mp');
    const labelPix = document.getElementById('label-mode-pix');
    const labelMp = document.getElementById('label-mode-mp');
    const tagPix = document.getElementById('tag-pix-status');
    const tagMp = document.getElementById('tag-mp-status');
    const activeBadge = document.getElementById('active-mode-badge');

    if (mode === 'manual_pix') {
        // Mostra Pix e esconde MP
        pixBox.classList.remove('hidden');
        mpBox.classList.add('hidden');
        radioPix.checked = true;

        labelPix.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex items-start justify-between border-emerald-500 bg-emerald-950/20 ring-2 ring-emerald-500/20';
        labelMp.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex items-start justify-between border-slate-800 bg-slate-950 hover:border-slate-700';

        tagPix.classList.remove('hidden');
        tagMp.classList.add('hidden');

        activeBadge.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>Modo Ativo: Pix Manual Direto</span>';
    } else {
        // Mostra MP e esconde Pix
        mpBox.classList.remove('hidden');
        pixBox.classList.add('hidden');
        radioMp.checked = true;

        labelMp.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex items-start justify-between border-blue-500 bg-blue-950/20 ring-2 ring-blue-500/20';
        labelPix.className = 'p-4 rounded-2xl border-2 cursor-pointer transition flex items-start justify-between border-slate-800 bg-slate-950 hover:border-slate-700';

        tagMp.classList.remove('hidden');
        tagPix.classList.add('hidden');

        activeBadge.innerHTML = '<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30"><span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>Modo Ativo: Mercado Pago Automático</span>';
    }
}

function copyPublicUrl() {
    const input = document.getElementById('public_url_input');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('btn-copy-public');
        const orig = btn.innerText;
        btn.innerText = '✓ Copiado!';
        btn.classList.add('bg-emerald-600');
        setTimeout(() => {
            btn.innerText = orig;
            btn.classList.remove('bg-emerald-600');
        }, 2000);
    });
}

function formatSlugInput(input, previewId) {
    let raw = input.value;
    // Converte para minúsculas, troca espaços por hífen, remove caracteres não-alfanuméricos
    let formatted = raw.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9\-]/g, '');
    input.value = formatted;
    
    const previewEl = document.getElementById(previewId);
    if (previewEl) {
        previewEl.innerText = '.../' + (formatted || 'seu-endereco');
    }
}

let currentSlugOrderId = null;
let slugPollingInterval = null;

function generateSlugFeePix() {
    const desiredInput = document.getElementById('free_desired_slug');
    const desired = desiredInput.value.trim();

    if (!desired || desired.length < 3) {
        alert('Por favor, informe um endereço válido com no mínimo 3 caracteres.');
        desiredInput.focus();
        return;
    }

    const btn = document.getElementById('btn_generate_slug_fee');
    const btnText = document.getElementById('btn_generate_slug_fee_text');
    const origHtml = btnText.innerHTML;

    btn.disabled = true;
    btnText.innerHTML = '⏳ Gerando Pix no Mercado Pago...';

    const formData = new URLSearchParams();
    formData.append('csrf_token', '<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>');
    formData.append('desired_slug', desired);

    fetch('<?= \Zyrtex\Core\Response::url('/admin/configuracoes/gerar-taxa-slug') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btnText.innerHTML = origHtml;

        if (!data.success) {
            alert(data.message || 'Erro ao gerar Pix no Mercado Pago.');
            return;
        }

        currentSlugOrderId = data.order_id;

        // Atualiza campos do modal
        document.getElementById('modal_new_slug_display').innerText = '/' + data.target_slug;
        document.getElementById('modal_fee_amount').innerText = 'R$ ' + data.amount;
        document.getElementById('modal_pix_qrcode').src = data.qrcode_url;
        document.getElementById('modal_pix_code').value = data.pix_code;

        // Restaura estados visuais
        document.getElementById('modal_pix_container').classList.remove('hidden');
        document.getElementById('modal_status_box').classList.remove('hidden');
        document.getElementById('modal_success_box').classList.add('hidden');

        // Abre o modal
        document.getElementById('modal-slug-fee').classList.remove('hidden');

        // Inicia pooling automático de conciliação do Mercado Pago
        startSlugStatusPolling(data.order_id, data.target_slug);
    })
    .catch(err => {
        btn.disabled = false;
        btnText.innerHTML = origHtml;
        alert('Falha na comunicação com o servidor ao gerar Pix.');
        console.error(err);
    });
}

function startSlugStatusPolling(orderId, targetSlug) {
    stopSlugStatusPolling();

    // Consulta status a cada 2.5 segundos
    slugPollingInterval = setInterval(() => {
        fetch('<?= \Zyrtex\Core\Response::url('/admin/configuracoes/status-taxa-slug') ?>?order_id=' + encodeURIComponent(orderId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.is_approved) {
                stopSlugStatusPolling();

                // Transição de sucesso visual
                document.getElementById('modal_pix_container').classList.add('hidden');
                document.getElementById('modal_status_box').classList.add('hidden');
                
                const successBox = document.getElementById('modal_success_box');
                document.getElementById('modal_success_slug').innerText = '/' + (data.new_slug || targetSlug);
                successBox.classList.remove('hidden');

                // Redireciona automaticamente após 2 segundos
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2000);
            }
        })
        .catch(err => {
            console.warn('Polling status taxa slug:', err);
        });
    }, 2500);
}

function stopSlugStatusPolling() {
    if (slugPollingInterval) {
        clearInterval(slugPollingInterval);
        slugPollingInterval = null;
    }
}

function closeSlugFeeModal() {
    stopSlugStatusPolling();
    document.getElementById('modal-slug-fee').classList.add('hidden');
}

function copyPixFeeCode() {
    const input = document.getElementById('modal_pix_code');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('btn_copy_pix_fee');
        const orig = btn.innerText;
        btn.innerText = '✓ Copiado!';
        btn.classList.add('bg-emerald-600');
        setTimeout(() => {
            btn.innerText = orig;
            btn.classList.remove('bg-emerald-600');
        }, 2000);
    });
}

function updatePrintTestLink(format) {
    const btn = document.getElementById('btn-print-test');
    if (btn) {
        const baseUrl = btn.href.split('?')[0];
        btn.href = baseUrl + '?formato=' + encodeURIComponent(format);
    }
}

function previewImageInput(input, imgId, placeholderId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgId);
            const placeholder = document.getElementById(placeholderId);
            if (img) {
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewUrlInput(url, imgId, placeholderId) {
    const img = document.getElementById(imgId);
    const placeholder = document.getElementById(placeholderId);
    if (url && url.trim().length > 5) {
        if (img) {
            img.src = url.trim();
            img.classList.remove('hidden');
        }
        if (placeholder) {
            placeholder.classList.add('hidden');
        }
    } else {
        if (img) {
            img.classList.add('hidden');
        }
        if (placeholder) {
            placeholder.classList.remove('hidden');
        }
    }
}

function toggleMetricsCustomFields(mode) {
    const fields = document.getElementById('metrics_custom_fields');
    if (fields) {
        if (mode === 'custom') {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }
}

// Inicia com a aba apontada pelo hash na URL (ex: #publica) ou por padrão 'empresa'
document.addEventListener('DOMContentLoaded', function() {
    const track = document.getElementById('adminTabsTrack');
    if (track) {
        track.addEventListener('scroll', updateAdminTabsScrollState, { passive: true });
        track.addEventListener('wheel', (e) => {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                e.preventDefault();
                track.scrollLeft += e.deltaY * 0.85;
            }
        }, { passive: false });
    }

    window.addEventListener('resize', updateAdminTabsScrollState);

    let hash = window.location.hash.replace('#', '');
    if (['empresa', 'pagamentos', 'impressao', 'ia', 'drive', 'publica', 'seguranca'].includes(hash)) {
        switchSettingsTab(hash);
    } else {
        switchSettingsTab('empresa');
    }

    setTimeout(updateAdminTabsScrollState, 150);
});
</script>
