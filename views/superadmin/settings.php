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

    <!-- Navegação em Abas (Tabs) -->
    <div class="bg-slate-900 p-1.5 rounded-2xl border border-slate-800 overflow-x-auto flex items-center gap-1.5 no-scrollbar">
        <button type="button" onclick="switchSettingsTab('precos')" id="tab-btn-precos"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
            <span>💰</span>
            <span>Preços & Taxas</span>
        </button>

        <button type="button" onclick="switchSettingsTab('mercadopago')" id="tab-btn-mercadopago"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80">
            <span>💳</span>
            <span>Mercado Pago Master</span>
        </button>

        <button type="button" onclick="switchSettingsTab('adsense')" id="tab-btn-adsense"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80">
            <span>📢</span>
            <span>Google AdSense</span>
        </button>

        <button type="button" onclick="switchSettingsTab('vitrine')" id="tab-btn-vitrine"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80">
            <span>🌐</span>
            <span>Vitrine & Rodapé</span>
        </button>

        <button type="button" onclick="switchSettingsTab('seguranca')" id="tab-btn-seguranca"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80">
            <span>🛡️</span>
            <span>Segurança Master</span>
        </button>

        <button type="button" onclick="switchSettingsTab('updates')" id="tab-btn-updates"
                class="settings-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap text-slate-400 hover:text-white hover:bg-slate-800/80">
            <span>🔄</span>
            <span>Atualizações (GitHub)</span>
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
        </button>
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
                    
                    <!-- Card Preço Domínio -->
                    <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 hover:border-slate-700 transition flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label for="annual_domain_price" class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                                    Domínio Próprio Anual
                                </label>
                                <span class="text-base">🌐</span>
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Cobrado anualmente para conectar domínio personalizado (.com.br / .com). Inclui supressão de anúncios.
                            </p>
                        </div>
                        <div class="space-y-1">
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-500 font-bold text-sm">R$</span>
                                <input type="text" id="annual_domain_price" name="annual_domain_price"
                                       value="<?= htmlspecialchars($settings['annual_domain_price'] ?? '197.00', ENT_QUOTES, 'UTF-8') ?>"
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white font-mono font-bold text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <span class="text-[10px] text-slate-500 block">Renovação a cada 365 dias</span>
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
                        A receita da plataforma advém de taxas pontuais de upgrade, anuidades de domínio próprio, publicidade Google AdSense e doações voluntárias.
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
                            Credenciais da conta do proprietário da Zyrtex para receber anuidades de domínio, taxas de troca e upgrades.
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
                            <p class="text-slate-500">Ao receber a confirmação do Pix, o Zyrtex ativa o domínio próprio ou atualiza a rota automaticamente sem intervenção humana.</p>
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
                        <p class="text-emerald-400 font-semibold">2. Oficinas que contratam <strong>Domínio Próprio ou Plano Pro</strong> têm os anúncios 100% suprimidos automaticamente.</p>
                        <p class="text-slate-500">3. A política de privacidade e o banner de cookies (LGPD) já alertam automaticamente sobre os cookies DART do Google AdSense.</p>
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
    }

    // Atualiza o hash da URL para permitir navegação direta e voltar
    if (history.replaceState) {
        history.replaceState(null, null, '#tab-' + tabKey);
    }
}

// Inicializa a aba baseada no hash da URL ao carregar a página
window.addEventListener('DOMContentLoaded', () => {
    let hash = window.location.hash.replace('#tab-', '').replace('#', '');
    if (hash === 'vitrine' || hash === 'mercadopago' || hash === 'adsense' || hash === 'seguranca' || hash === 'updates' || hash === 'precos') {
        switchSettingsTab(hash);
    } else {
        switchSettingsTab('precos');
    }
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
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/30 flex items-center justify-center gap-2">
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

// Execução da Atualização Automática com Modal Elegante (Substitui confirm/alert nativo)
function executeAutoUpdateNow() {
    zyrtexConfirm({
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
        confirmClass: 'px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-500/20',
        onConfirm: () => startAutoUpdateProcess()
    });
}

async function startAutoUpdateProcess() {
    const modal = document.getElementById('autoUpdateProgressModal');
    const msg = document.getElementById('autoUpdateStatusMessage');
    const bar = document.getElementById('autoUpdateProgressBar');
    const percent = document.getElementById('autoUpdatePercent');
    const alertBox = document.getElementById('updateAlertBox');

    modal.classList.remove('hidden');
    alertBox.classList.add('hidden');
    bar.style.width = '30%';
    percent.textContent = 'Baixando pacote do GitHub...';

    const csrfToken = document.querySelector('input[name="_csrf"]')?.value || 
                      document.querySelector('input[name="csrf_token"]')?.value || 
                      '<?= \Zyrtex\Core\CSRF::token() ?>';

    try {
        bar.style.width = '60%';
        msg.textContent = 'Extraindo arquivos e aplicando com proteção estrita ao .env e banco...';
        percent.textContent = 'Sincronizando arquivos...';

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

        const result = await response.json();

        if (result.success) {
            bar.style.width = '100%';
            bar.className = 'bg-emerald-500 h-2 rounded-full transition-all duration-500';
            msg.textContent = `✓ Sucesso! ${result.files_updated || 0} arquivos atualizados. Banco de dados e clientes 100% preservados!`;
            percent.textContent = 'Concluído! Recarregando...';

            zyrtexToast('✓ Zyrtex atualizado com sucesso! Recarregando página...', 'success');

            setTimeout(() => {
                window.location.reload();
            }, 2500);
        } else {
            bar.style.width = '100%';
            bar.className = 'bg-rose-500 h-2 rounded-full';
            msg.textContent = 'Erro na atualização: ' + (result.message || 'Falha ao sincronizar.');
            percent.textContent = 'Falha';
            
            zyrtexAlert({
                title: 'Falha na Atualização',
                icon: '⚠️',
                type: 'error',
                buttonText: 'Fechar',
                messageHtml: `<p class="text-rose-300 font-semibold mb-2">Não foi possível concluir a atualização automática:</p><p class="text-slate-300">${result.message || 'Erro desconhecido retornado pelo servidor.'}</p>`
            });
        }
    } catch (err) {
        bar.style.width = '100%';
        bar.className = 'bg-rose-500 h-2 rounded-full';
        msg.textContent = 'Erro de comunicação durante a atualização.';
        percent.textContent = 'Erro';
        
        zyrtexAlert({
            title: 'Erro de Comunicação',
            icon: '⚠️',
            type: 'error',
            buttonText: 'Fechar',
            messageHtml: `<p class="text-rose-300 font-semibold mb-2">Erro ao contatar o servidor para atualização:</p><p class="text-slate-400 font-mono text-[11px]">${err.message || 'Falha na requisição de rede ou resposta não-JSON.'}</p>`
        });
    }
}
</script>
