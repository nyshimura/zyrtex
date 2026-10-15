<?php
$currentSaSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
$csrfField = \Zyrtex\Core\CSRF::field();
$installedVersion = $versionData['version'] ?? '1.2.0';
$githubRepo = $versionData['github_repo'] ?? 'nyshimura/zyrtex';
$githubUrl = $versionData['github_url'] ?? 'https://github.com/nyshimura/zyrtex';
$releaseChannel = $versionData['release_channel'] ?? 'stable';
?>

<div class="space-y-6">

    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>⚙️</span>
                <span>Parâmetros Globais, Precificação & Atualizações</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Gerencie taxas de lojistas, credenciais de pagamento, anúncios do AdSense, vitrine pública e verificação do repositório GitHub.
            </p>
        </div>

        <!-- Badges de Status do Sistema -->
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-mono font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                <span>📦</span>
                <span>Zyrtex v<?= htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8') ?></span>
            </span>
            <a href="<?= htmlspecialchars($githubUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition">
                <span>🐙</span>
                <span><?= htmlspecialchars($githubRepo, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-[10px] text-slate-500">&nearr;</span>
            </a>
        </div>
    </div>

    <style>
        #superadminTabsTrack::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
    </style>

    <!-- Navegação em Abas (Tabs) com Controles de Navegação Integrados (Sem barra nativa feia) -->
    <div class="relative group bg-slate-900/90 backdrop-blur-md p-1.5 rounded-2xl border border-slate-800 shadow-sm flex flex-col">
        <div class="relative flex items-center">
            <!-- Botão Chevron Esquerda -->
            <button type="button" id="tabScrollLeftBtn" onclick="scrollSuperadminTabs('left')" aria-label="Rolar abas para a esquerda"
                    class="absolute left-1 z-20 w-8 h-8 rounded-xl bg-slate-800/95 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700/80 shadow-xl flex items-center justify-center transition-all duration-200 backdrop-blur-md opacity-0 pointer-events-none active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Gradiente de Fade Esquerdo para Indicar Transbordo -->
            <div id="tabFadeLeft" class="absolute left-0 top-0 bottom-0 w-12 bg-gradient-to-r from-slate-900 via-slate-900/90 to-transparent rounded-l-2xl pointer-events-none z-10 transition-opacity duration-200 opacity-0"></div>

            <!-- Trilha de Abas (Com rolagem oculta, suporte a touch e mousewheel) -->
            <div id="superadminTabsTrack"
                 style="scrollbar-width: none; -ms-overflow-style: none;"
                 class="flex-1 overflow-x-auto scroll-smooth flex items-center gap-1.5 no-scrollbar scrollbar-none py-0.5 px-1 select-none">
                <button type="button" onclick="switchSettingsTab('precos')" id="tab-btn-precos"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap bg-indigo-600 text-white shadow-md shadow-indigo-600/30 shrink-0">
                    <span>💰</span>
                    <span>Preços & Taxas</span>
                </button>

                <button type="button" onclick="switchSettingsTab('mercadopago')" id="tab-btn-mercadopago"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>💳</span>
                    <span>Mercado Pago Master</span>
                </button>

                <button type="button" onclick="switchSettingsTab('adsense')" id="tab-btn-adsense"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>📢</span>
                    <span>Google AdSense</span>
                </button>

                <button type="button" onclick="switchSettingsTab('vitrine')" id="tab-btn-vitrine"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>🌐</span>
                    <span>Vitrine & Rodapé</span>
                </button>

                <button type="button" onclick="switchSettingsTab('seguranca')" id="tab-btn-seguranca"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>🛡️</span>
                    <span>Segurança Master</span>
                </button>

                <button type="button" onclick="switchSettingsTab('smtp')" id="tab-btn-smtp"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>✉️</span>
                    <span>Servidor SMTP & E-mail</span>
                </button>

                <button type="button" onclick="switchSettingsTab('growth')" id="tab-btn-growth"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>📈</span>
                    <span>Remarketing & Indicações</span>
                </button>

                <button type="button" onclick="switchSettingsTab('updates')" id="tab-btn-updates"
                        class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80 shrink-0">
                    <span>🔄</span>
                    <span>Atualizações (GitHub)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </button>
            </div>

            <!-- Gradiente de Fade Direito para Indicar Transbordo -->
            <div id="tabFadeRight" class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-slate-900 via-slate-900/90 to-transparent rounded-r-2xl pointer-events-none z-10 transition-opacity duration-200 opacity-0"></div>

            <!-- Botão Chevron Direita -->
            <button type="button" id="tabScrollRightBtn" onclick="scrollSuperadminTabs('right')" aria-label="Rolar abas para a direita"
                    class="absolute right-1 z-20 w-8 h-8 rounded-xl bg-slate-800/95 hover:bg-indigo-600 text-slate-300 hover:text-white border border-slate-700/80 shadow-xl flex items-center justify-center transition-all duration-200 backdrop-blur-md opacity-0 pointer-events-none active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        <!-- Micro-barra de Progresso Elegante (Oculta nativa, exibe micro-linha suave apenas durante rolagem se houver transbordo) -->
        <div id="tabScrollIndicatorTrack" class="w-full h-1 bg-slate-800/40 rounded-full overflow-hidden mt-1 hidden">
            <div id="tabScrollIndicatorThumb" class="h-full bg-gradient-to-r from-indigo-500 via-purple-500 to-cyan-400 rounded-full transition-all duration-150" style="width: 20%; margin-left: 0%;"></div>
        </div>
    </div>

    <!-- Formulário Único Unificado com Todos os Parâmetros -->
    <form action="<?= \Zyrtex\Core\Response::url('/' . $currentSaSlug . '/parametros') ?>" method="POST" id="settingsForm">
        <?= $csrfField ?>

        <!-- ========================================== -->
        <!-- ABA 1: TABELA DE PREÇOS & TAXAS DO SAAS    -->
        <!-- ========================================== -->
        <div id="tab-panel-precos" class="settings-tab-panel space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>💰</span>
                            <span>Tabela de Precificação & Monetização do SaaS</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Valores cobrados das assistências técnicas e oficinas clientes via Pix Automático do Mercado Pago.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        Receita Líquida
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    
                    <!-- Card Preço Anuidade Sem Anúncios -->
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="annual_domain_price" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                    Plano Anual Sem Anúncios
                                </label>
                                <span class="text-base">✨</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Cobrado anualmente para remover 100% dos anúncios do Google AdSense da vitrine, do rastreio de OS e do portal do cliente da oficina.
                            </p>
                        </div>
                        <div class="space-y-1">
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 font-bold text-sm">R$</span>
                                <input type="text" id="annual_domain_price" name="annual_domain_price"
                                       value="<?= htmlspecialchars($settings['annual_domain_price'] ?? '197.00', ENT_QUOTES, 'UTF-8') ?>"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Renovação anual (365 dias)</span>
                        </div>
                    </div>

                    <!-- Card Taxa Troca de Nicho -->
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="niche_change_price" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                    Troca de Nicho
                                </label>
                                <span class="text-base">🔄</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Taxa cobrada caso a oficina queira mudar de ramo (ex: Celular para Informática ou Mecânica) após o cadastro inicial.
                            </p>
                        </div>
                        <div class="space-y-1">
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 font-bold text-sm">R$</span>
                                <input type="text" id="niche_change_price" name="niche_change_price"
                                       value="<?= htmlspecialchars($settings['niche_change_price'] ?? '97.00', ENT_QUOTES, 'UTF-8') ?>"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Cobrança avulsa por alteração</span>
                        </div>
                    </div>

                    <!-- Card Taxa Troca de Slug -->
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="slug_change_price" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                    Troca de Slug / Subdomínio
                                </label>
                                <span class="text-base">🔗</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Taxa para alterar o link da loja no plano gratuito (ex: de <code>/oficina-antiga</code> para <code>/minha-oficina</code>).
                            </p>
                        </div>
                        <div class="space-y-1">
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 font-bold text-sm">R$</span>
                                <input type="text" id="slug_change_price" name="slug_change_price"
                                       value="<?= htmlspecialchars($settings['slug_change_price'] ?? '19.90', ENT_QUOTES, 'UTF-8') ?>"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Cobrança avulsa por alteração</span>
                        </div>
                    </div>

                </div>

                <!-- Info Box de Negócios -->
                <div class="p-4 rounded-xl bg-slate-950/70 border border-slate-800 text-xs text-slate-400 space-y-1.5">
                    <span class="font-bold text-slate-200 flex items-center gap-1.5">
                        <span>💡</span>
                        <span>Modelo de Monetização Zyrtex (Zero Atrito)</span>
                    </span>
                    <p>
                        O sistema opera em modelo <strong>Freemium Radical</strong>: o lojista tem acesso irrestrito ao sistema sem mensalidade fixa. 
                        A receita da plataforma advém de taxas pontuais de upgrade, anuidades do Plano Sem Anúncios, publicidade Google AdSense e doações voluntárias.
                    </p>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30">
                        Salvar Preços & Taxas
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- ABA 2: MERCADO PAGO MASTER (SAAS)          -->
        <!-- ========================================== -->
        <div id="tab-panel-mercadopago" class="settings-tab-panel space-y-6 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>💳</span>
                            <span>Mercado Pago Master da Zyrtex (Conta do SaaS)</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Credenciais da conta do proprietário da Zyrtex para receber anuidades do plano sem anúncios, taxas de troca e upgrades.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                        Pix Automático
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="mercadopago_access_token" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Access Token de Produção (APP_USR-...):
                            </label>
                            <input type="password" id="mercadopago_access_token" name="mercadopago_access_token"
                                   value="<?= htmlspecialchars($settings['mercadopago_access_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="APP_USR-0000000000000000-000000-..."
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                            <span class="text-[10px] text-slate-500 mt-1 block">Token de acesso privado da sua aplicação no Mercado Pago</span>
                        </div>

                        <div>
                            <label for="mercadopago_public_key" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Public Key de Produção (APP_USR-...):
                            </label>
                            <input type="text" id="mercadopago_public_key" name="mercadopago_public_key"
                                   value="<?= htmlspecialchars($settings['mercadopago_public_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="APP_USR-..."
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                            <span class="text-[10px] text-slate-500 mt-1 block">Chave pública utilizada para checkout e validação</span>
                        </div>
                    </div>

                    <!-- URL do Webhook Pronta para Copiar -->
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            URL do Webhook do SaaS (Cadastre no Mercado Pago Developers):
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly 
                                   value="<?= \Zyrtex\Core\Response::url('/webhook/saas') ?>" 
                                   id="webhook_saas_url"
                                   class="flex-1 px-4 py-2.5 bg-slate-900 border border-slate-700 text-cyan-300 font-mono text-xs rounded-xl select-all">
                            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webhook_saas_url').value); zyrtexToast('URL do Webhook copiada com sucesso!');"
                                    class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                                <span>📋</span>
                                <span>Copiar URL</span>
                            </button>
                        </div>
                        <div class="text-[11px] text-slate-400 space-y-1">
                            <p><strong>Configuração recomendada:</strong> Acesse seu painel no Mercado Pago Developers, cadastre esta URL no menu <em>Webhooks</em> e marque os eventos <code>payment</code> e <code>merchant_order</code>.</p>
                            <p class="text-slate-500">Ao receber a confirmação do Pix, o Zyrtex ativa o Plano Anual Sem Anúncios ou atualiza o endereço automaticamente sem intervenção humana.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30">
                        Salvar Credenciais Mercado Pago
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- ABA 3: GOOGLE ADSENSE & PUBLICIDADE        -->
        <!-- ========================================== -->
        <div id="tab-panel-adsense" class="settings-tab-panel space-y-6 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>📢</span>
                            <span>Google AdSense & Monetização de Anúncios (Lojas Free)</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Monetize as páginas públicas, páginas de ordem de serviço e portal do cliente de lojas gratuitas.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="adsense_active" value="1" 
                                   <?= !empty($settings['adsense_active']) ? 'checked' : '' ?> 
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-2 text-xs font-bold text-slate-200">Anúncios Ativos</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="adsense_script" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Script / Bloco HTML do Google AdSense:
                        </label>
                        <textarea id="adsense_script" name="adsense_script" rows="5"
                                  placeholder='<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-..." crossorigin="anonymous"></script>'
                                  class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs placeholder-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($settings['adsense_script'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs text-slate-400 space-y-1.5">
                        <span class="font-bold text-slate-200 block flex items-center gap-1.5">
                            <span>🛡️</span>
                            <span>Regra de Negócio de Conformidade & Supressão Automática:</span>
                        </span>
                        <p>1. O script acima é injetado nas páginas públicas das lojas que operam no <strong>Plano Free</strong>.</p>
                        <p class="text-emerald-400 font-semibold">2. Oficinas que contratam o <strong>Plano Anual Sem Anúncios (Premium)</strong> têm os anúncios 100% suprimidos automaticamente (zero scripts e zero publicidades).</p>
                        <p class="text-amber-400 font-medium">3. Caso o visitante use AdBlock, o sistema exibe dinamicamente o card de apoio voluntário via Pix cadastrado na aba Vitrine & Pix, garantindo monetização contínua.</p>
                        <p class="text-slate-500">4. A política de privacidade e o banner de cookies (LGPD) já alertam automaticamente sobre os cookies DART do Google AdSense.</p>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30">
                        Salvar Configuração do AdSense
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- ABA 4: VITRINE PÚBLICA & RODAPÉ DO SAAS    -->
        <!-- ========================================== -->
        <div id="tab-panel-vitrine" class="settings-tab-panel space-y-6 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-8 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🌐</span>
                            <span>Personalização da Vitrine Pública & Rodapé Oficial</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Edite textos da landing page inicial, chamada de apoio voluntário via Pix e dados de contato do rodapé.
                        </p>
                    </div>
                    <a href="<?= \Zyrtex\Core\Response::url('/') ?>" target="_blank"
                       class="px-3.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold flex items-center gap-1.5 transition">
                        <span>👁️ Ver Vitrine Pública</span>
                        <span class="text-[10px]">&nearr;</span>
                    </a>
                </div>

                <!-- Grupo 1: Headline Hero & Apresentação -->
                <div class="space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-400 block flex items-center gap-1.5">
                        <span>1.</span>
                        <span>Apresentação Principal (Hero Section)</span>
                    </span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="saas_hero_badge" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Badge Superior (Destaque):
                            </label>
                            <input type="text" id="saas_hero_badge" name="saas_hero_badge"
                                   value="<?= htmlspecialchars($settings['saas_hero_badge'] ?? 'Software 100% Gratuito para Sempre • Sem Limite de OS', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="saas_topbar_announcement" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Aviso na Barra Superior (Topbar):
                            </label>
                            <input type="text" id="saas_topbar_announcement" name="saas_topbar_announcement"
                                   value="<?= htmlspecialchars($settings['saas_topbar_announcement'] ?? '🎉 Novidade: Sistema 100% Gratuito sem limite de Ordens de Serviço!', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="saas_hero_title" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Título Principal (Headline):
                        </label>
                        <input type="text" id="saas_hero_title" name="saas_hero_title"
                               value="<?= htmlspecialchars($settings['saas_hero_title'] ?? 'A Gestão Mais Rápida e Sem Travamentos para sua Assistência.', ENT_QUOTES, 'UTF-8') ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="saas_hero_subtitle" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Subtítulo Explicativo:
                        </label>
                        <textarea id="saas_hero_subtitle" name="saas_hero_subtitle" rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($settings['saas_hero_subtitle'] ?? 'Ordens de serviço dinâmicas por nicho, PDV estilo totem touch-screen, mini QR Code adesivo para garantia, inteligência artificial gratuita para laudos e portal do cliente com aprovação digital. Tudo em carregamento instantâneo.', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <!-- Grupo 2: Doações Voluntárias (Buy Me a Coffee) -->
                <div class="space-y-4 pt-6 border-t border-slate-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block flex items-center gap-1.5">
                        <span>2.</span>
                        <span>Apoio Voluntário via Pix (Buy Me a Coffee)</span>
                    </span>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="saas_pix_key" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Chave Pix Oficial de Apoio:
                            </label>
                            <input type="text" id="saas_pix_key" name="saas_pix_key"
                                   value="<?= htmlspecialchars($settings['saas_pix_key'] ?? 'apoio@zyrtex.com.br', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="ex: apoio@zyrtex.com.br ou CNPJ"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="saas_pix_beneficiary" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Titular / Beneficiário da Chave:
                            </label>
                            <input type="text" id="saas_pix_beneficiary" name="saas_pix_beneficiary"
                                   value="<?= htmlspecialchars($settings['saas_pix_beneficiary'] ?? 'Equipe de Desenvolvimento Zyrtex', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label for="saas_pix_message" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Mensagem de Incentivo ao Apoio:
                        </label>
                        <textarea id="saas_pix_message" name="saas_pix_message" rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($settings['saas_pix_message'] ?? 'O Zyrtex é mantido com muito carinho por desenvolvedores apaixonados pelo ecossistema de assistências técnicas. Se o sistema está economizando tempo e aumentando suas vendas, considere fazer uma contribuição espontânea via Pix de qualquer valor!', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <!-- Grupo 3: Rodapé Oficial da Plataforma -->
                <div class="space-y-4 pt-6 border-t border-slate-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-400 block flex items-center gap-1.5">
                        <span>3.</span>
                        <span>Rodapé Oficial da Plataforma</span>
                    </span>

                    <div>
                        <label for="saas_footer_about" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                            Texto "Sobre" no Rodapé:
                        </label>
                        <textarea id="saas_footer_about" name="saas_footer_about" rows="2"
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500"><?= htmlspecialchars($settings['saas_footer_about'] ?? 'Plataforma server-side de alta performance para oficinas e assistências técnicas. Construída com arquitetura leve em PHP puro, sem JavaScript na regra de negócios e custo zero de infraestrutura para o lojista.', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="saas_contact_email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                E-mail Oficial de Contato:
                            </label>
                            <input type="email" id="saas_contact_email" name="saas_contact_email"
                                   value="<?= htmlspecialchars($settings['saas_contact_email'] ?? 'contato@zyrtex.com.br', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="saas_contact_whatsapp" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                WhatsApp Oficial de Suporte:
                            </label>
                            <input type="text" id="saas_contact_whatsapp" name="saas_contact_whatsapp"
                                   value="<?= htmlspecialchars($settings['saas_contact_whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="Ex: (11) 99999-8888"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="saas_footer_copyright" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Linha de Copyright:
                            </label>
                            <input type="text" id="saas_footer_copyright" name="saas_footer_copyright"
                                   value="<?= htmlspecialchars($settings['saas_footer_copyright'] ?? 'Zyrtex Software as a Service. Todos os direitos reservados.', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="saas_footer_note" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                                Nota de Rodapé:
                            </label>
                            <input type="text" id="saas_footer_note" name="saas_footer_note"
                                   value="<?= htmlspecialchars($settings['saas_footer_note'] ?? 'Modelo Freemium financiado por publicidade e doações voluntárias.', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30">
                        Salvar Vitrine & Rodapé
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- ABA 5: SEGURANÇA MASTER & BLINDAGEM        -->
        <!-- ========================================== -->
        <div id="tab-panel-seguranca" class="settings-tab-panel space-y-6 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🛡️</span>
                            <span>Blindagem de URL & Slug Secreto do SuperAdmin</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Substitua a rota padrão <code>/superadmin</code> por uma URL personalizada e secreta, impedindo varreduras automáticas de bots.
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                        🔒 Proteção Anti-Scan
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <div class="space-y-3">
                        <label for="superadmin_slug" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Slug / Rota Secreta do Painel Master:
                        </label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 text-slate-500 font-bold text-sm">/</span>
                            <input type="text" id="superadmin_slug" name="superadmin_slug"
                                   value="<?= htmlspecialchars($settings['superadmin_slug'] ?? $currentSaSlug, ENT_QUOTES, 'UTF-8') ?>"
                                   placeholder="ex: painel-master-zyrtex"
                                   pattern="[a-zA-Z0-9_\-]+"
                                   class="w-full pl-8 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono font-bold text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <p class="text-[11px] text-slate-500">
                            Permitido apenas letras, números e hífens. Padrão inicial: <code>superadmin</code>.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5 text-xs">
                        <span class="font-bold text-slate-300 block flex items-center gap-1.5">
                            <span>🔗</span>
                            <span>URL Master Ativa Atualmente:</span>
                        </span>
                        <div class="flex items-center gap-2 font-mono text-[11px] text-indigo-300 bg-slate-900 px-3 py-2 rounded-lg border border-slate-800 break-all select-all">
                            <span><?= htmlspecialchars(\Zyrtex\Core\Response::baseUrl() . '/' . $currentSaSlug, ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p class="text-[10px] text-slate-400 leading-relaxed">
                            ⚠️ <strong>Atenção:</strong> Ao alterar e salvar este campo, a rota anterior passará a retornar <strong>Erro 404 (Não Encontrado)</strong> imediatamente e você será redirecionado para o novo endereço.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-rose-600/30">
                        Salvar Nova Rota de Segurança
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- ABA: SERVIDOR SMTP & E-MAILS DO SAAS       -->
        <!-- ========================================== -->
        <div id="tab-panel-smtp" class="settings-tab-panel space-y-6 hidden">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>✉️</span>
                            <span>Servidor SMTP Master & Envio Transacional</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Configuração nativa de socket RFC 5321 para disparo de ativações de contas e tokens 2FA (Zyrtex Guard).
                        </p>
                    </div>

                    <a href="<?= \Zyrtex\Core\Response::url('/' . $currentSaSlug . '/emails') ?>" 
                       class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-2 shrink-0">
                        <span>🎨</span>
                        <span>Abrir Templates & Construtor Drag & Drop &rarr;</span>
                    </a>
                </div>

                <!-- Toggle de Ativação do SMTP -->
                <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-white flex items-center gap-2">
                            <span>⚡</span>
                            <span>Status do Envio de E-mails</span>
                        </span>
                        <p class="text-[11px] text-slate-400">
                            Quando inativo, os e-mails são simulados com segurança e salvos no arquivo <code class="text-indigo-300">storage/logs/mail_simulated.log</code>, ideal para testes locais.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="smtp_active" value="0">
                            <input type="checkbox" name="smtp_active" value="1" <?= (!empty($settings['smtp_active']) && (int)$settings['smtp_active'] === 1) ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        </label>
                        <span class="text-xs font-bold <?= (!empty($settings['smtp_active']) && (int)$settings['smtp_active'] === 1) ? 'text-emerald-400' : 'text-slate-400' ?>">
                            <?= (!empty($settings['smtp_active']) && (int)$settings['smtp_active'] === 1) ? 'Ativo (Disparo Real)' : 'Modo Simulação Offline' ?>
                        </span>
                    </div>
                </div>

                <!-- Configurações do Host, Porta e Criptografia -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label for="smtp_host" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Host SMTP:
                        </label>
                        <input type="text" id="smtp_host" name="smtp_host" 
                               value="<?= htmlspecialchars($settings['smtp_host'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="ex: smtp.gmail.com ou mail.smtp2go.com"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[10px] text-slate-500 block">Endereço do servidor SMTP do seu provedor</span>
                    </div>

                    <div class="space-y-1.5">
                        <label for="smtp_port" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Porta de Conexão:
                        </label>
                        <input type="number" id="smtp_port" name="smtp_port" 
                               value="<?= htmlspecialchars($settings['smtp_port'] ?? '587', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="587"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[10px] text-slate-500 block">Porta padrão: 587 (TLS) ou 465 (SSL)</span>
                    </div>

                    <div class="space-y-1.5">
                        <label for="smtp_encryption" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Criptografia / Protocolo:
                        </label>
                        <select id="smtp_encryption" name="smtp_encryption"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                            <option value="tls" <?= (($settings['smtp_encryption'] ?? 'tls') === 'tls') ? 'selected' : '' ?>>STARTTLS (Recomendado - Porta 587)</option>
                            <option value="ssl" <?= (($settings['smtp_encryption'] ?? '') === 'ssl') ? 'selected' : '' ?>>SSL / TLS Direto (Porta 465)</option>
                            <option value="none" <?= (($settings['smtp_encryption'] ?? '') === 'none') ? 'selected' : '' ?>>Nenhuma / Conexão Plana (Porta 25)</option>
                        </select>
                        <span class="text-[10px] text-slate-500 block">STARTTLS garante transporte seguro com certificados</span>
                    </div>
                </div>

                <!-- Credenciais de Autenticação -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1.5">
                        <label for="smtp_username" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Usuário / Login SMTP:
                        </label>
                        <input type="text" id="smtp_username" name="smtp_username" 
                               value="<?= htmlspecialchars($settings['smtp_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="ex: contato@zyrtex.com.br"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[10px] text-slate-500 block">Normalmente o mesmo e-mail de envio</span>
                    </div>

                    <div class="space-y-1.5">
                        <label for="smtp_password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center justify-between">
                            <span>Senha SMTP:</span>
                            <?php if (!empty($settings['smtp_password'])): ?>
                                <span class="text-[10px] text-emerald-400 font-mono">🔒 Criptografada (AES-256)</span>
                            <?php endif; ?>
                        </label>
                        <input type="password" id="smtp_password" name="smtp_password" 
                               placeholder="<?= !empty($settings['smtp_password']) ? '•••••••••••••••• (deixe em branco para manter)' : 'Digite a senha ou token do app' ?>"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[10px] text-slate-500 block">Armazenada no banco com cifra simétrica AES-256-CBC</span>
                    </div>
                </div>

                <!-- Remetente Oficial -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1.5">
                        <label for="smtp_from_email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            E-mail Remetente (From):
                        </label>
                        <input type="email" id="smtp_from_email" name="smtp_from_email" 
                               value="<?= htmlspecialchars($settings['smtp_from_email'] ?? 'notificacoes@zyrtex.com.br', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="notificacoes@zyrtex.com.br"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[10px] text-slate-500 block">Endereço visível para os clientes e lojistas</span>
                    </div>

                    <div class="space-y-1.5">
                        <label for="smtp_from_name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Nome do Remetente (From Name):
                        </label>
                        <input type="text" id="smtp_from_name" name="smtp_from_name" 
                               value="<?= htmlspecialchars($settings['smtp_from_name'] ?? 'Zyrtex SaaS', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Zyrtex SaaS"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
                        <span class="text-[10px] text-slate-500 block">Nome exibido na caixa de entrada</span>
                    </div>
                </div>

                <input type="hidden" name="return_tab" value="smtp">

                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $currentSaSlug . '/emails') ?>" 
                       class="text-xs text-indigo-400 hover:text-indigo-300 font-bold transition flex items-center gap-1.5">
                        <span>✉️</span>
                        <span>Ver e Editar Templates de E-mail (Drag & Drop)</span>
                    </a>

                    <div class="flex items-center gap-3">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-emerald-600/30">
                            Salvar Configurações de SMTP
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- ABA: REMARKETING, FIDELIDADE & INDIQUE E GANHE (GROWTH) -->
        <!-- ======================================================= -->
        <div id="tab-panel-growth" class="settings-tab-panel space-y-6 hidden">
            
            <!-- Card 1: Motor de Remarketing & Desconto Progressivo por Tempo de Casa -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>⚡</span>
                            <span>Motor de Recuperação de Carrinho & Remarketing Automático</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Gera checkout com 1-clique e aplica descontos ancorados baseados na fidelidade e antiguidade de cadastro da oficina.
                        </p>
                    </div>

                    <!-- Status / Toggle Ativo -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="remarketing_auto_active" value="1" 
                               <?= !empty($settings['remarketing_auto_active']) ? 'checked' : '' ?> 
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                        <span class="ml-3 text-xs font-bold text-slate-300 peer-checked:text-amber-400">Varredura Automática</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    
                    <!-- Janela de Expiração -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="remarketing_expiration_hours" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                Tempo de Abandono
                            </label>
                            <span class="text-base">⏱️</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Horas com transação pendente parada antes de ser movida para a fila de recuperação.
                        </p>
                        <div class="relative">
                            <input type="number" id="remarketing_expiration_hours" name="remarketing_expiration_hours"
                                   min="1" max="720"
                                   value="<?= htmlspecialchars($settings['remarketing_expiration_hours'] ?? '48', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pr-12 pl-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500">
                            <span class="absolute right-3.5 top-2.5 text-slate-500 font-bold text-xs">horas</span>
                        </div>
                    </div>

                    <!-- Desconto Base Inicial -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="remarketing_base_discount" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                Desconto Base
                            </label>
                            <span class="text-base">🏷️</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Porcentagem de desconto imediato oferecida para qualquer transação abandonada.
                        </p>
                        <div class="relative">
                            <input type="number" id="remarketing_base_discount" name="remarketing_base_discount"
                                   min="0" max="90" step="1"
                                   value="<?= htmlspecialchars($settings['remarketing_base_discount'] ?? '10', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pr-10 pl-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500">
                            <span class="absolute right-3.5 top-2.5 text-slate-500 font-bold text-xs">%</span>
                        </div>
                    </div>

                    <!-- Bônus por Mês de Casa -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="remarketing_loyalty_bonus_per_month" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                Bônus Fidelidade / Mês
                            </label>
                            <span class="text-base">⭐</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Desconto extra somado para cada mês completo que a oficina já possui de cadastro no Zyrtex.
                        </p>
                        <div class="relative">
                            <input type="number" id="remarketing_loyalty_bonus_per_month" name="remarketing_loyalty_bonus_per_month"
                                   min="0" max="10" step="0.5"
                                   value="<?= htmlspecialchars($settings['remarketing_loyalty_bonus_per_month'] ?? '2', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pr-14 pl-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500">
                            <span class="absolute right-3.5 top-2.5 text-slate-500 font-bold text-xs">%/mês</span>
                        </div>
                    </div>

                    <!-- Teto Máximo de Desconto -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="remarketing_max_discount" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                Teto Máximo
                            </label>
                            <span class="text-base">🛡️</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Limite máximo absoluto de desconto que uma oficina parceira antiga pode atingir.
                        </p>
                        <div class="relative">
                            <input type="number" id="remarketing_max_discount" name="remarketing_max_discount"
                                   min="5" max="90" step="1"
                                   value="<?= htmlspecialchars($settings['remarketing_max_discount'] ?? '35', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pr-10 pl-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-amber-500">
                            <span class="absolute right-3.5 top-2.5 text-slate-500 font-bold text-xs">%</span>
                        </div>
                    </div>

                </div>

                <!-- Simulação Didática de Fidelidade -->
                <div class="p-4 rounded-xl bg-slate-950/80 border border-amber-500/20 text-xs space-y-2">
                    <span class="font-bold text-amber-400 flex items-center gap-2">
                        <span>💡</span>
                        <span>Fórmula Ativa de Fidelidade do Zyrtex</span>
                    </span>
                    <p class="text-slate-300 leading-relaxed">
                        <code>Desconto Final = MIN(Base + (Meses de Cadastro &times; Bônus Mensal), Teto Máximo)</code>
                    </p>
                    <p class="text-slate-400 text-[11px]">
                        <strong>Exemplo Prático:</strong> Uma oficina parceira cadastrada há <strong>6 meses</strong> que tentar comprar o Plano Anual (R$ 120,00) e não concluir receberá no checkout mágico: <code>10% + (6 &times; 2%) = 22% de desconto</code>, baixando a anuidade para <strong>R$ 93,60</strong>.
                    </p>
                </div>
            </div>

            <!-- Card 2: Programa de Indicação Anti-Fraude ("Indique & Ganhe") -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>🎁</span>
                            <span>Programa de Indicações ("Indique & Ganhe")</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Permite aos lojistas convidarem colegas de bancada e ganharem dias gratuitos de Zyrtex Pro.
                        </p>
                    </div>

                    <!-- Toggle Ativo -->
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="referral_active" value="1" 
                               <?= !empty($settings['referral_active']) ? 'checked' : '' ?> 
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ml-3 text-xs font-bold text-slate-300 peer-checked:text-indigo-400">Programa Ativo</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <!-- Bônus de Boas-Vindas para Convidado -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="referral_welcome_days" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                Boas-Vindas para o Convidado
                            </label>
                            <span class="text-base">🎉</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Dias de degustação de Zyrtex Pro concedidos imediatamente à nova oficina ao se cadastrar via link de convite.
                        </p>
                        <div class="relative">
                            <input type="number" id="referral_welcome_days" name="referral_welcome_days"
                                   min="0" max="365"
                                   value="<?= htmlspecialchars($settings['referral_welcome_days'] ?? '15', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pr-12 pl-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute right-3.5 top-2.5 text-slate-500 font-bold text-xs">dias Pro</span>
                        </div>
                    </div>

                    <!-- Recompensa para quem Indicou -->
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="referral_reward_days" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                Recompensa do Indicador
                            </label>
                            <span class="text-base">🏆</span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Dias de Zyrtex Pro creditados à oficina que convidou, <strong>somente após validação de transação real</strong>.
                        </p>
                        <div class="relative">
                            <input type="number" id="referral_reward_days" name="referral_reward_days"
                                   min="1" max="365"
                                   value="<?= htmlspecialchars($settings['referral_reward_days'] ?? '30', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full pr-12 pl-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            <span class="absolute right-3.5 top-2.5 text-slate-500 font-bold text-xs">dias Pro</span>
                        </div>
                    </div>

                </div>

                <!-- Blindagem Jurídica Anti-Fraude (LGPD & Marco Civil) -->
                <div class="p-5 rounded-xl bg-gradient-to-r from-emerald-950/40 via-slate-950 to-slate-950 border border-emerald-500/30 text-xs space-y-2.5">
                    <span class="font-bold text-emerald-400 flex items-center gap-2 text-sm">
                        <span>🛡️</span>
                        <span>Mecanismo Anti-Fraude com Blindagem Legal (LGPD Art. 7 & CDC)</span>
                    </span>
                    <p class="text-slate-300 leading-relaxed">
                        Para impedir a criação automatizada de lojas falsas por fraudadores querendo farmar meses grátis sem pagar nada, 
                        o bônus de <strong>+<?= (int)($settings['referral_reward_days'] ?? 30) ?> dias</strong> do indicador permanece com status <code>Pendente</code>.
                    </p>
                    <p class="text-slate-400 leading-relaxed text-[11px]">
                        A bonificação é desbloqueada <strong>automaticamente e em tempo real</strong> no exato momento em que a oficina convidada registrar sua primeira transação financeira legítima:
                        <br>&bull; <strong>Primeira OS Paga:</strong> quando um cliente final pagar um serviço na assistência via Pix Mercado Pago ou registro manual;
                        <br>&bull; <strong>Compra de Serviço SaaS:</strong> contratação de plano sem anúncios ou alteração de slug.
                    </p>
                    <p class="text-slate-500 text-[10px]">
                        🔒 <em>Privacidade Garantida:</em> A loja indicadora recebe apenas a confirmação do bônus ("Oficina Ativada"), sem acesso a valores de faturamento ou clientes da concorrência, respeitando integralmente a LGPD.
                    </p>
                </div>

                <input type="hidden" name="return_tab" value="growth">

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-indigo-600 hover:from-amber-500 hover:to-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/20">
                        Salvar Parâmetros de Growth & Indicações
                    </button>
                </div>
            </div>

        </div>

    </form>

    <!-- ========================================== -->
    <!-- ABA 6: VERIFICADOR DE ATUALIZAÇÕES GITHUB  -->
    <!-- ========================================== -->
    <div id="tab-panel-updates" class="settings-tab-panel space-y-6 hidden">
        
        <!-- Card Principal de Status do Repositório -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>🔄</span>
                        <span>Sincronização & Atualizações do Repositório GitHub</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Verifique em tempo real se existem novas versões, patches de segurança ou melhorias no repositório oficial.
                    </p>
                </div>

                <!-- Botão de Verificação Interativa -->
                <button type="button" id="btnCheckUpdates" onclick="performUpdateCheck()"
                        class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 flex items-center gap-2 shrink-0">
                    <span id="updateSpinner" class="hidden animate-spin">⏳</span>
                    <span id="updateIcon">🔍</span>
                    <span id="updateBtnText">Verificar Atualizações no GitHub</span>
                </button>
            </div>

            <!-- Grid de Indicadores de Versão -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Versão Instalada</span>
                    <div class="text-xl font-black text-white font-mono flex items-center gap-1.5">
                        <span>v<?= htmlspecialchars($installedVersion, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-sans uppercase">Local</span>
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Canal: <?= htmlspecialchars($releaseChannel, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Repositório Oficial</span>
                    <div class="text-sm font-bold text-indigo-300 font-mono truncate">
                        <a href="<?= htmlspecialchars($githubUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" class="hover:underline flex items-center gap-1">
                            <span><?= htmlspecialchars($githubRepo, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="text-xs">&nearr;</span>
                        </a>
                    </div>
                    <span class="text-[10px] text-slate-500 mt-1 block">Branch: <code>main</code></span>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Versão no GitHub</span>
                    <div id="remoteVersionDisplay" class="text-xl font-black text-slate-400 font-mono">
                        <span>--</span>
                    </div>
                    <span id="remoteStatusText" class="text-[10px] text-slate-500 mt-1 block">Aguardando verificação...</span>
                </div>

                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block mb-1">Status do Sistema</span>
                    <div id="systemUpdateBadge" class="flex items-center gap-1.5 mt-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-xs font-bold text-emerald-400">Pronto para Consulta</span>
                    </div>
                    <span id="lastCheckTime" class="text-[10px] text-slate-500 mt-1 block">Última checagem: Nunca</span>
                </div>
            </div>

            <!-- ESCUDO DE PROTEÇÃO TOTAL: GARANTIA DE ZERO PERDA DE DADOS -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-emerald-950/60 to-slate-950 border border-emerald-500/30 text-xs space-y-3">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-sm">
                    <span class="text-base">🛡️</span>
                    <span>Blindagem do Sistema & Garantia de Preservação Total de Dados</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-slate-300">
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="font-bold text-white flex items-center gap-1.5">
                            <span class="text-emerald-400">✓</span>
                            <span>Arquivo .env 100% Intocado</span>
                        </span>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            O arquivo <code>.env</code> é estritamente ignorado nas atualizações. A conexão com o banco de dados e as chaves de segurança nunca são modificadas.
                        </p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="font-bold text-white flex items-center gap-1.5">
                            <span class="text-emerald-400">✓</span>
                            <span>Clientes & Lojistas Intactos</span>
                        </span>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            Nenhum cliente, oficina, ordem de serviço, venda no PDV ou pagamento é apagado. O banco apenas recebe novas colunas opcionais (migração incremental).
                        </p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-1">
                        <span class="font-bold text-white flex items-center gap-1.5">
                            <span class="text-emerald-400">✓</span>
                            <span>Fotos & Uploads Preservados</span>
                        </span>
                        <p class="text-[11px] text-slate-400 leading-relaxed">
                            A pasta <code>public/uploads/</code> com fotos de aparelhos danificados, laudos de bancada e logos de oficinas nunca é substituída nem limpa.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Caixa de Alerta / Resultado da Verificação & Ação 1-Clique (Dinâmico) -->
            <div id="updateAlertBox" class="hidden p-5 rounded-2xl border text-xs">
                <!-- Preenchido via JavaScript -->
            </div>

            <!-- Modal / Caixa de Progresso da Atualização Automática -->
            <div id="autoUpdateProgressModal" class="hidden p-6 rounded-2xl bg-indigo-950/80 border border-indigo-500/40 text-xs space-y-4">
                <div class="flex items-center gap-3">
                    <span class="w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></span>
                    <div>
                        <h4 class="font-bold text-white text-sm">Atualizando Zyrtex Automaticamente...</h4>
                        <p class="text-indigo-300 text-[11px]" id="autoUpdateStatusMessage">Baixando arquivos do GitHub e aplicando com segurança...</p>
                    </div>
                </div>
                <div class="w-full bg-slate-900 rounded-full h-2 overflow-hidden border border-slate-800">
                    <div id="autoUpdateProgressBar" class="bg-gradient-to-r from-indigo-500 to-emerald-400 h-2 rounded-full transition-all duration-500" style="width: 45%;"></div>
                </div>
                <div class="text-[10px] text-slate-400 flex items-center justify-between">
                    <span>🛡️ Blindagem ativa: .env e uploads de clientes protegidos</span>
                    <span id="autoUpdatePercent">Aguarde...</span>
                </div>
            </div>

            <!-- Instruções de Atualização via Terminal -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <span>💻</span>
                        <span>Como Atualizar o Zyrtex em Produção (Terminal / Servidor)</span>
                    </span>
                    <button type="button" onclick="navigator.clipboard.writeText('git fetch origin && git pull origin main && php database/migrate.php'); zyrtexToast('Comandos de terminal copiados com sucesso!');"
                            class="px-3 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-[11px] font-bold transition">
                        Copiar Comandos
                    </button>
                </div>
                
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 font-mono text-xs text-slate-300 space-y-1">
                    <p class="text-slate-500"># 1. Obter as alterações mais recentes do repositório</p>
                    <p class="text-emerald-400 font-bold">git fetch origin && git pull origin main</p>
                    <p class="text-slate-500 mt-2"># 2. Executar migrações automáticas de banco de dados (se houver novas tabelas)</p>
                    <p class="text-cyan-400 font-bold">php database/migrate.php</p>
                </div>

                <p class="text-[11px] text-slate-500">
                    O Zyrtex foi projetado com arquitetura <strong>Zero Downtime</strong>. Novas atualizações de código não derrubam sessões de clientes nem apagam dados dos tenants.
                </p>
            </div>

        </div>

        <!-- Card Secundário: Diagnóstico de Integridade do Ambiente -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-4 shadow-sm">
            <div class="border-b border-slate-800 pb-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span>🩺</span>
                    <span>Diagnóstico de Integridade do Servidor Local</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">
                    Parâmetros essenciais do ambiente PHP/MySQL para operação estável do Zyrtex.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 pt-2">
                
                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="text-[10px] text-slate-400 block">PHP Versão</span>
                    <span class="font-mono font-bold text-xs <?= ($envHealth['php_ok'] ?? false) ? 'text-emerald-400' : 'text-rose-400' ?>">
                        <?= htmlspecialchars($envHealth['php_version'] ?? PHP_VERSION, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="text-[9px] block text-slate-500 mt-0.5">Min: 8.2</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="text-[10px] text-slate-400 block">PDO MySQL</span>
                    <span class="font-bold text-xs <?= ($envHealth['pdo_ok'] ?? false) ? 'text-emerald-400' : 'text-rose-400' ?>">
                        <?= ($envHealth['pdo_ok'] ?? false) ? '✓ Ativo' : '✗ Ausente' ?>
                    </span>
                    <span class="text-[9px] block text-slate-500 mt-0.5">Banco de dados</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="text-[10px] text-slate-400 block">cURL Client</span>
                    <span class="font-bold text-xs <?= ($envHealth['curl_ok'] ?? false) ? 'text-emerald-400' : 'text-rose-400' ?>">
                        <?= ($envHealth['curl_ok'] ?? false) ? '✓ Ativo' : '✗ Ausente' ?>
                    </span>
                    <span class="text-[9px] block text-slate-500 mt-0.5">APIs & Webhooks</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="text-[10px] text-slate-400 block">OpenSSL</span>
                    <span class="font-bold text-xs <?= ($envHealth['openssl_ok'] ?? false) ? 'text-emerald-400' : 'text-rose-400' ?>">
                        <?= ($envHealth['openssl_ok'] ?? false) ? '✓ Ativo' : '✗ Ausente' ?>
                    </span>
                    <span class="text-[9px] block text-slate-500 mt-0.5">Criptografia</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="text-[10px] text-slate-400 block">Storage Perms</span>
                    <span class="font-bold text-xs <?= ($envHealth['storage_writable'] ?? false) ? 'text-emerald-400' : 'text-amber-400' ?>">
                        <?= ($envHealth['storage_writable'] ?? false) ? '✓ Escrita OK' : '⚠️ Somente Leitura' ?>
                    </span>
                    <span class="text-[9px] block text-slate-500 mt-0.5">Uploads & Logs</span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="text-[10px] text-slate-400 block">Sistema / SO</span>
                    <span class="font-bold text-xs text-indigo-300">
                        <?= htmlspecialchars($envHealth['os'] ?? PHP_OS_FAMILY, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span class="text-[9px] block text-slate-500 mt-0.5">Plataforma</span>
                </div>

            </div>
        </div>

    </div>

</div>

<!-- Script Vanilla para Alternância de Abas e Verificação de Atualizações no GitHub -->
<script>
// Controle de Navegação em Abas com Setas e Auto-Rolagem
function scrollSuperadminTabs(direction) {
    const track = document.getElementById('superadminTabsTrack');
    if (!track) return;
    const scrollAmount = 260;
    track.scrollBy({
        left: direction === 'left' ? -scrollAmount : scrollAmount,
        behavior: 'smooth'
    });
}

function updateSuperadminTabsScrollState() {
    const track = document.getElementById('superadminTabsTrack');
    const leftBtn = document.getElementById('tabScrollLeftBtn');
    const rightBtn = document.getElementById('tabScrollRightBtn');
    const fadeLeft = document.getElementById('tabFadeLeft');
    const fadeRight = document.getElementById('tabFadeRight');
    const indicatorTrack = document.getElementById('tabScrollIndicatorTrack');
    const indicatorThumb = document.getElementById('tabScrollIndicatorThumb');

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

    // Estado Botão e Gradiente Esquerda
    if (track.scrollLeft > 6) {
        if (leftBtn) { leftBtn.classList.remove('opacity-0', 'pointer-events-none'); leftBtn.classList.add('opacity-100', 'pointer-events-auto'); }
        if (fadeLeft) fadeLeft.classList.remove('opacity-0');
    } else {
        if (leftBtn) { leftBtn.classList.add('opacity-0', 'pointer-events-none'); leftBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
        if (fadeLeft) fadeLeft.classList.add('opacity-0');
    }

    // Estado Botão e Gradiente Direita
    if (track.scrollLeft < maxScroll - 6) {
        if (rightBtn) { rightBtn.classList.remove('opacity-0', 'pointer-events-none'); rightBtn.classList.add('opacity-100', 'pointer-events-auto'); }
        if (fadeRight) fadeRight.classList.remove('opacity-0');
    } else {
        if (rightBtn) { rightBtn.classList.add('opacity-0', 'pointer-events-none'); rightBtn.classList.remove('opacity-100', 'pointer-events-auto'); }
        if (fadeRight) fadeRight.classList.add('opacity-0');
    }

    // Atualiza a micro-barra de progresso integrada
    if (indicatorThumb && maxScroll > 0) {
        const thumbWidthPercent = Math.max(15, Math.min(50, (track.clientWidth / track.scrollWidth) * 100));
        const availableMove = 100 - thumbWidthPercent;
        const progress = Math.max(0, Math.min(1, track.scrollLeft / maxScroll));
        const marginLeft = progress * availableMove;
        indicatorThumb.style.width = thumbWidthPercent + '%';
        indicatorThumb.style.marginLeft = marginLeft + '%';
    }
}

function switchSettingsTab(tabKey) {
    // Esconde todos os painéis
    document.querySelectorAll('.settings-tab-panel').forEach(panel => {
        panel.classList.add('hidden');
    });

    // Remove destaque de todos os botões de aba
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-md', 'shadow-indigo-600/30');
        btn.classList.add('text-slate-400');
    });

    // Mostra o painel selecionado
    const activePanel = document.getElementById('tab-panel-' + tabKey);
    if (activePanel) {
        activePanel.classList.remove('hidden');
    }

    // Destaca o botão ativo
    const activeBtn = document.getElementById('tab-btn-' + tabKey);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-400');
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-md', 'shadow-indigo-600/30');

        // Auto-centraliza a aba ativa suavemente no viewport visível da barra
        activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    // Atualiza o hash da URL para permitir navegação direta e voltar
    if (history.replaceState) {
        history.replaceState(null, null, '#tab-' + tabKey);
    }
}

// Inicializa a aba baseada no hash da URL ao carregar a página e configura listeners da trilha
window.addEventListener('DOMContentLoaded', () => {
    const track = document.getElementById('superadminTabsTrack');
    if (track) {
        track.addEventListener('scroll', updateSuperadminTabsScrollState, { passive: true });
        
        // Permite rolar horizontalmente com a roda tradicional do mouse (sem precisar de Shift)
        track.addEventListener('wheel', (e) => {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                e.preventDefault();
                track.scrollLeft += e.deltaY * 0.85;
            }
        }, { passive: false });
    }

    window.addEventListener('resize', updateSuperadminTabsScrollState);

    let hash = window.location.hash.replace('#tab-', '').replace('#', '');
    if (hash === 'vitrine' || hash === 'mercadopago' || hash === 'adsense' || hash === 'seguranca' || hash === 'updates' || hash === 'precos' || hash === 'smtp' || hash === 'growth') {
        switchSettingsTab(hash);
    } else {
        switchSettingsTab('precos');
    }

    setTimeout(updateSuperadminTabsScrollState, 150);
});

// Verificador Assíncrono de Atualizações no Repositório GitHub
async function performUpdateCheck() {
    const btn = document.getElementById('btnCheckUpdates');
    const spinner = document.getElementById('updateSpinner');
    const icon = document.getElementById('updateIcon');
    const btnText = document.getElementById('updateBtnText');
    const alertBox = document.getElementById('updateAlertBox');
    const remoteVer = document.getElementById('remoteVersionDisplay');
    const remoteStatus = document.getElementById('remoteStatusText');
    const systemBadge = document.getElementById('systemUpdateBadge');
    const lastCheckTime = document.getElementById('lastCheckTime');

    // Estado de Loading
    btn.disabled = true;
    spinner.classList.remove('hidden');
    icon.classList.add('hidden');
    btnText.textContent = 'Consultando GitHub...';

    try {
        const response = await fetch('<?= \Zyrtex\Core\Response::url('/' . $currentSaSlug . '/verificar-atualizacao') ?>', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });

        const data = await response.json();
        lastCheckTime.textContent = 'Última checagem: ' + data.checked_at;

        if (data.status === 'up_to_date') {
            remoteVer.innerHTML = `<span class="text-emerald-400">v${data.remote_version || data.local_version}</span>`;
            remoteStatus.textContent = 'Versão remota no GitHub coincide';
            systemBadge.innerHTML = `<span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span><span class="text-xs font-bold text-emerald-400">100% Atualizado</span>`;
            
            alertBox.className = 'p-5 rounded-2xl border border-emerald-500/30 bg-emerald-950/40 text-emerald-200 text-xs space-y-2';
            alertBox.innerHTML = `
                <div class="flex items-center gap-2 font-bold text-emerald-300 text-sm">
                    <span>✓</span>
                    <span>Seu Zyrtex está operando na versão mais recente disponível!</span>
                </div>
                <p class="text-slate-300">Versão instalada: <strong>v${data.local_version}</strong> no branch <strong>${data.branch}</strong> de <a href="${data.github_url}" target="_blank" class="underline text-emerald-400">${data.github_repo}</a>.</p>
            `;
            alertBox.classList.remove('hidden');

        } else if (data.status === 'update_available') {
            remoteVer.innerHTML = `<span class="text-indigo-400 font-bold">v${data.remote_version}</span>`;
            remoteStatus.textContent = 'Nova versão disponível!';
            systemBadge.innerHTML = `<span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-bounce"></span><span class="text-xs font-bold text-indigo-400">Atualização Disponível</span>`;

            alertBox.className = 'p-5 rounded-2xl border border-indigo-500/40 bg-indigo-950/50 text-indigo-200 text-xs space-y-3';
            alertBox.innerHTML = `
                <div class="flex items-center gap-2 font-bold text-white text-sm">
                    <span>🎉</span>
                    <span>Nova versão v${data.remote_version} lançada no repositório!</span>
                </div>
                <p class="text-slate-300">Uma atualização foi detectada no repositório oficial <a href="${data.github_url}" target="_blank" class="underline text-cyan-400">${data.github_repo}</a>.</p>
                <div class="p-3 bg-slate-950/80 rounded-xl font-mono text-[11px] text-slate-300 border border-indigo-500/20">
                    <strong>Changelog:</strong> ${data.changelog || 'Melhorias de estabilidade e novas funcionalidades.'}
                </div>
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="button" onclick="executeAutoUpdateNow()" id="btnAutoUpdateAction"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2 cursor-pointer">
                        <span>🚀</span>
                        <span>Atualizar Automaticamente Agora (1-Clique)</span>
                    </button>
                    <span class="text-[11px] text-emerald-300 flex items-center gap-1">
                        <span>🛡️</span>
                        <span>O .env e todos os dados dos clientes e lojas serão 100% preservados.</span>
                    </span>
                </div>
            `;
            alertBox.classList.remove('hidden');

        } else if (data.status === 'repo_pending') {
            remoteVer.innerHTML = `<span class="text-amber-400">Aguardando</span>`;
            remoteStatus.textContent = 'Repositório configurado';
            systemBadge.innerHTML = `<span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span><span class="text-xs font-bold text-amber-400">Repositório Conectado</span>`;

            alertBox.className = 'p-5 rounded-2xl border border-amber-500/30 bg-amber-950/40 text-amber-200 text-xs space-y-2';
            alertBox.innerHTML = `
                <div class="flex items-center gap-2 font-bold text-amber-300 text-sm">
                    <span>ℹ️</span>
                    <span>Repositório GitHub Conectado: ${data.github_repo}</span>
                </div>
                <p class="text-slate-300">O endereço oficial está vinculado a <a href="${data.github_url}" target="_blank" class="underline text-amber-400">${data.github_url}</a>. Assim que você publicar o primeiro <code>git push origin main</code> no seu GitHub, a sincronização de releases passará a exibir o status ao vivo automaticamente.</p>
            `;
            alertBox.classList.remove('hidden');

        } else {
            // Offline ou erro
            remoteVer.innerHTML = `<span class="text-slate-500">Indisponível</span>`;
            remoteStatus.textContent = 'Sem conexão com GitHub';
            systemBadge.innerHTML = `<span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span><span class="text-xs font-bold text-slate-400">Offline</span>`;

            alertBox.className = 'p-5 rounded-2xl border border-slate-700 bg-slate-950 text-slate-300 text-xs space-y-2';
            alertBox.innerHTML = `
                <div class="flex items-center gap-2 font-bold text-slate-200 text-sm">
                    <span>⚠️</span>
                    <span>${data.message || 'Não foi possível consultar o GitHub no momento.'}</span>
                </div>
                <p class="text-slate-400">Certifique-se de que seu servidor tem acesso externo à internet e à API do GitHub.</p>
            `;
            alertBox.classList.remove('hidden');
        }

    } catch (err) {
        alertBox.className = 'p-5 rounded-2xl border border-rose-500/30 bg-rose-950/40 text-rose-200 text-xs';
        alertBox.innerHTML = `<p><strong>Erro de Conexão:</strong> Não foi possível contatar o endpoint de atualização local.</p>`;
        alertBox.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
        icon.classList.remove('hidden');
        btnText.textContent = 'Verificar Atualizações no GitHub';
    }
}

// Execução da Atualização Automática com Modal Elegante
window.executeAutoUpdateNow = function() {
    if (typeof window.zyrtexConfirm !== 'function') {
        alert('Deseja iniciar a atualização automática agora?');
        window.startAutoUpdateProcess();
        return;
    }

    window.zyrtexConfirm({
        title: 'Atualização Automática do Zyrtex',
        badge: '🛡️ Preservação Total de Dados',
        icon: '🚀',
        messageHtml: `
            <div class="space-y-3">
                <p class="font-bold text-white text-sm">Deseja iniciar a sincronização com a versão mais recente do GitHub agora?</p>
                <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                    <span class="font-bold text-emerald-400 block flex items-center gap-1.5">
                        <span>🛡️</span>
                        <span>GARANTIA DE SEGURANÇA TOTAL:</span>
                    </span>
                    <ul class="space-y-1.5 text-slate-300">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>O arquivo <strong>.env</strong> NUNCA é sobrescrito ou alterado;</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>Todas as <strong>oficinas, clientes e ordens de serviço</strong> permanecem intactos;</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>As <strong>fotos e anexos de OS</strong> permanecem salvos em disco;</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>Apenas o <strong>código-fonte PHP</strong> recebe os novos recursos.</span>
                        </li>
                    </ul>
                </div>
            </div>
        `,
        confirmText: '✓ Iniciar Atualização Agora',
        cancelText: 'Cancelar',
        confirmClass: 'px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-500/20 cursor-pointer',
        onConfirm: () => window.startAutoUpdateProcess()
    });
};

window.startAutoUpdateProcess = async function() {
    const modal = document.getElementById('autoUpdateProgressModal');
    const msg = document.getElementById('autoUpdateStatusMessage');
    const bar = document.getElementById('autoUpdateProgressBar');
    const percent = document.getElementById('autoUpdatePercent');
    const alertBox = document.getElementById('updateAlertBox');

    if (modal) modal.classList.remove('hidden');
    if (alertBox) alertBox.classList.add('hidden');
    if (bar) bar.style.width = '30%';
    if (percent) percent.textContent = 'Baixando pacote do GitHub...';

    const csrfToken = document.querySelector('input[name="_csrf"]')?.value || 
                      document.querySelector('input[name="csrf_token"]')?.value || 
                      '<?= \Zyrtex\Core\CSRF::token() ?>';

    try {
        if (bar) bar.style.width = '60%';
        if (msg) msg.textContent = 'Extraindo arquivos e aplicando com proteção estrita ao .env e banco...';
        if (percent) percent.textContent = 'Sincronizando arquivos...';

        const formData = new FormData();
        formData.append('_csrf', csrfToken);
        formData.append('csrf_token', csrfToken);

        const response = await fetch('<?= \Zyrtex\Core\Response::url('/' . $currentSaSlug . '/executar-atualizacao') ?>', {
            method: 'POST',
            body: formData,
            headers: { 
                'Accept': 'application/json',
                'X-CSRF-Token': csrfToken
            }
        });

        let result;
        const responseText = await response.text();
        try {
            result = JSON.parse(responseText);
        } catch (jsonErr) {
            throw new Error(responseText.substring(0, 150) || 'Resposta do servidor não foi um JSON válido.');
        }

        if (result && result.success) {
            if (bar) {
                bar.style.width = '100%';
                bar.className = 'bg-emerald-500 h-2 rounded-full transition-all duration-500';
            }
            if (msg) msg.textContent = `✓ Sucesso! ${result.files_updated || 0} arquivos atualizados. Banco de dados e clientes 100% preservados!`;
            if (percent) percent.textContent = 'Concluído! Recarregando...';

            if (typeof window.zyrtexToast === 'function') {
                window.zyrtexToast('✓ Zyrtex atualizado com sucesso! Recarregando página...', 'success');
            }

            setTimeout(() => {
                window.location.reload();
            }, 2500);
        } else {
            if (bar) {
                bar.style.width = '100%';
                bar.className = 'bg-rose-500 h-2 rounded-full';
            }
            if (msg) msg.textContent = 'Erro na atualização: ' + (result?.message || 'Falha ao sincronizar.');
            if (percent) percent.textContent = 'Falha';
            
            if (typeof window.zyrtexAlert === 'function') {
                window.zyrtexAlert({
                    title: 'Falha na Atualização',
                    icon: '⚠️',
                    type: 'error',
                    buttonText: 'Fechar',
                    messageHtml: `<p class="text-rose-300 font-semibold mb-2">Não foi possível concluir a atualização automática:</p><p class="text-slate-300">${result?.message || 'Erro desconhecido retornado pelo servidor.'}</p>`
                });
            } else {
                alert('Falha na atualização: ' + (result?.message || 'Erro desconhecido.'));
            }
        }
    } catch (err) {
        if (bar) {
            bar.style.width = '100%';
            bar.className = 'bg-rose-500 h-2 rounded-full';
        }
        if (msg) msg.textContent = 'Erro de comunicação durante a atualização.';
        if (percent) percent.textContent = 'Erro';
        
        if (typeof window.zyrtexAlert === 'function') {
            window.zyrtexAlert({
                title: 'Erro de Comunicação',
                icon: '⚠️',
                type: 'error',
                buttonText: 'Fechar',
                messageHtml: `<p class="text-rose-300 font-semibold mb-2">Erro ao contatar o servidor para atualização:</p><p class="text-slate-400 font-mono text-[11px]">${err.message || 'Falha na requisição de rede ou resposta não-JSON.'}</p>`
            });
        } else {
            alert('Erro ao contatar o servidor para atualização: ' + err.message);
        }
    }
};

// Event delegation global para garantir que o clique no botão disparará mesmo se inserido dinamicamente
document.addEventListener('click', function(e) {
    const btn = e.target.closest('#btnAutoUpdateAction');
    if (btn) {
        e.preventDefault();
        window.executeAutoUpdateNow();
    }
});
</script>
