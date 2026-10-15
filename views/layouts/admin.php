<!DOCTYPE html>
<html lang="pt-BR" class="dark h-full bg-slate-950 text-slate-100 antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Oficina - Zyrtex SaaS</title>
    
    <!-- PWA & Mobile Meta Tags -->
    <?php $baseUrl = $baseUrl ?? \Zyrtex\Core\Response::baseUrl(); ?>
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($tenant['business_name'] ?? 'Zyrtex Admin', ENT_QUOTES, 'UTF-8') ?>">
    <link rel="manifest" href="<?= !empty($tenant['slug']) ? htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '/manifest.json?slug=' . urlencode($tenant['slug']) : htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '/manifest.json' ?>">
    <link rel="apple-touch-icon" href="<?= !empty($tenant['logo_path']) ? htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') : htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') . '/assets/images/pwa-icon-192.png' ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/assets/images/pwa-icon-192.png">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Oculta completamente a barra de rolagem nativa feia do sistema operacional */
        .no-scrollbar::-webkit-scrollbar,
        .scrollbar-none::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .no-scrollbar,
        .scrollbar-none {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* Scrollbar fina elegante e escura quando necessária */
        .thin-scrollbar::-webkit-scrollbar {
            height: 4px;
            width: 4px;
        }
        .thin-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
            border-radius: 9999px;
        }
        .thin-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.4);
            border-radius: 9999px;
        }
        .thin-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.8);
        }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100">

    <!-- Modo Shadow (Impersonation RF-Shadow) -->
    <?php if (\Zyrtex\Core\Session::has('shadow_superadmin_id')): ?>
        <div class="bg-gradient-to-r from-amber-600 via-orange-600 to-amber-700 text-white px-4 py-2 text-xs font-bold flex items-center justify-between shadow-lg sticky top-0 z-50">
            <div class="flex items-center gap-2">
                <span class="text-base animate-pulse">👁️</span>
                <span>MODO SHADOW ATIVO: Você está acessando como <strong><?= htmlspecialchars($tenant['business_name'] ?? 'Esta Oficina') ?></strong>. Todas as ações são auditadas.</span>
            </div>
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/sair-shadow') ?>" 
               class="px-3 py-1 rounded-lg bg-black/40 hover:bg-black/60 text-white border border-white/20 transition flex items-center gap-1 font-bold">
                <span>✕ Sair do Modo Shadow</span>
            </a>
        </div>
    <?php endif; ?>

    <!-- Aviso Global Broadcast da Plataforma (RF) -->
    <?php
    try {
        $broadcastCfg = \Zyrtex\Core\Database::fetchAll("SELECT config_key, config_value FROM saas_settings WHERE config_key LIKE 'broadcast_%'");
        $bcMap = [];
        foreach ($broadcastCfg as $bc) { $bcMap[$bc['config_key']] = $bc['config_value']; }
        if (!empty($bcMap['broadcast_active']) && $bcMap['broadcast_active'] === '1' && !empty($bcMap['broadcast_message'])):
            $bcType = $bcMap['broadcast_type'] ?? 'info';
            $bcClasses = [
                'warning' => 'bg-amber-950/90 border-amber-500/40 text-amber-200',
                'danger'  => 'bg-rose-950/90 border-rose-500/40 text-rose-200',
                'info'    => 'bg-blue-950/90 border-blue-500/40 text-blue-200'
            ];
            $bcClass = $bcClasses[$bcType] ?? $bcClasses['info'];
    ?>
        <div class="px-4 py-2 border-b text-xs font-bold flex items-center justify-between <?= $bcClass ?>">
            <div class="flex items-center gap-2 max-w-5xl mx-auto w-full">
                <span class="text-base">📢</span>
                <span><?= htmlspecialchars($bcMap['broadcast_message'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        </div>
    <?php 
        endif;
    } catch (\Throwable) {}
    ?>

    <!-- Flash Messages (Toasts) -->
    <?php \Zyrtex\Core\View::partial('flash_messages', ['flashes' => $flashes ?? []]); ?>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- SIDEBAR DE NAVEGAÇÃO -->
        <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col justify-between hidden md:flex shrink-0">
            <div>
                <!-- Brand / Nome da Oficina -->
                <div class="h-20 flex items-center px-6 border-b border-slate-800 gap-3">
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-400 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/20">
                        Z
                    </span>
                    <div class="overflow-hidden">
                        <span class="font-extrabold text-base text-white truncate block">
                            <?= htmlspecialchars($tenant['business_name'] ?? 'Minha Oficina', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="text-[11px] text-indigo-400 font-mono truncate block">
                            /<?= htmlspecialchars($tenant['slug'] ?? 'demo-assistencia', ENT_QUOTES, 'UTF-8') ?>/admin
                        </span>
                    </div>
                </div>

                <?php
                $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

                $isConfiguracoes = str_contains($requestUri, '/admin/configuracoes');
                $isIndicacoes    = str_contains($requestUri, '/admin/indicacoes');
                $isOs            = str_contains($requestUri, '/admin/os');
                $isClientes      = str_contains($requestUri, '/admin/clientes');
                $isPdv           = str_contains($requestUri, '/admin/pdv');
                $isEstoque       = str_contains($requestUri, '/admin/estoque');
                $isEquipe        = str_contains($requestUri, '/admin/equipe');
                $isSuporte       = str_contains($requestUri, '/admin/suporte');
                $isDashboard     = !$isConfiguracoes && !$isIndicacoes && !$isOs && !$isClientes && !$isPdv && !$isEstoque && !$isEquipe && !$isSuporte && str_contains($requestUri, '/admin');

                $activeClass   = 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-white bg-indigo-600/20 border border-indigo-500/30 font-semibold shadow-sm';
                $inactiveClass = 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition';
                ?>
                <!-- Menu de Navegação -->
                <nav class="p-4 space-y-1.5 text-sm font-medium">
                    <a href="<?= \Zyrtex\Core\Response::url('/admin') ?>" 
                       class="<?= $isDashboard ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">📊</span>
                        <span>Visão Geral</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" 
                       class="<?= $isOs ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">📋</span>
                        <span>Ordens de Serviço</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes') ?>" 
                       class="<?= $isClientes ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">👥</span>
                        <span>Clientes</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/pdv') ?>" 
                       class="<?= $isPdv ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">🛒</span>
                        <span>PDV / Venda Balcão</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/estoque') ?>" 
                       class="<?= $isEstoque ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">📦</span>
                        <span>Peças & Estoque</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/equipe') ?>" 
                       class="<?= $isEquipe ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">👥</span>
                        <span>Equipe & Acessos</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/indicacoes') ?>" 
                       class="<?= $isIndicacoes ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">🎁</span>
                        <span>Indique & Ganhe</span>
                        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Pro Grátis</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/configuracoes') ?>" 
                       class="<?= $isConfiguracoes ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">⚙️</span>
                        <span>Configurações</span>
                    </a>

                    <a href="<?= \Zyrtex\Core\Response::url('/admin/suporte') ?>" 
                       class="<?= $isSuporte ? $activeClass : $inactiveClass ?>">
                        <span class="text-lg">💬</span>
                        <span>Suporte & Ajuda</span>
                    </a>
                </nav>
            </div>

            <!-- Footer da Sidebar: Vitrine Pública e Suporte -->
            <div class="p-4 border-t border-slate-800 space-y-2">
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? 'demo-assistencia') ?>" target="_blank"
                   class="flex items-center justify-between w-full px-3 py-2 rounded-xl text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 transition">
                    <span>🌐 Ver Minha Vitrine</span>
                    <span>&nearr;</span>
                </a>

                <div class="flex items-center justify-between pt-2 px-1">
                    <span class="text-[11px] text-slate-500">Zyrtex v1.0.0</span>
                    <a href="<?= \Zyrtex\Core\Response::url('/logout') ?>" 
                       class="text-xs text-rose-400 hover:text-rose-300 font-semibold">
                        Sair
                    </a>
                </div>
            </div>
        </aside>

        <!-- ÁREA PRINCIPAL -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            
            <!-- TOPBAR -->
            <header class="h-20 bg-slate-900/80 backdrop-blur-md border-b border-slate-800 flex items-center justify-between px-6 sm:px-8 shrink-0">
                
                <!-- Identificação do Nicho Ativo -->
                <div class="flex items-center gap-3">
                    <?php 
                    $niche = $tenant['niche'] ?? 'cellphones';
                    $nicheLabels = [
                        'cellphones' => ['icon' => '📱', 'name' => 'Smartphones & Tablets'],
                        'computers' => ['icon' => '💻', 'name' => 'Computadores & Notebooks'],
                        'automotive' => ['icon' => '🚗', 'name' => 'Oficina Mecânica'],
                        'appliances' => ['icon' => '🔌', 'name' => 'Eletrodomésticos']
                    ];
                    $nicheInfo = $nicheLabels[$niche] ?? ['icon' => '🛠️', 'name' => 'Assistência'];
                    ?>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-800 border border-slate-700 text-slate-200">
                        <span><?= $nicheInfo['icon'] ?></span>
                        <span><?= $nicheInfo['name'] ?></span>
                    </div>

                    <?php if (!empty($tenant['is_premium'])): ?>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            ★ Sem Anúncios
                        </span>
                    <?php else: ?>
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                            ✓ Plano Grátis
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Link da Página Pública & Perfil -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? 'demo-assistencia') ?>" target="_blank"
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 transition"
                       title="Abrir a Landing Page e Rastreio da Loja para clientes">
                        <span>🌐</span>
                        <span>Ver Minha Vitrine</span>
                        <span>&nearr;</span>
                    </a>

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full overflow-hidden bg-slate-800 border border-slate-700 shrink-0 flex items-center justify-center text-white font-bold text-xs">
                            <?php if (!empty($user['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars(asset($user['avatar_url']), ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="<?= htmlspecialchars($user['name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>"
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <span><?= mb_strtoupper(mb_substr($user['name'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="text-right hidden sm:block">
                            <span class="text-sm font-bold text-white block">
                                <?= htmlspecialchars($user['name'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <span class="text-xs text-indigo-400 capitalize block">
                                <?= htmlspecialchars($user['role'] ?? 'owner', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </div>
                    </div>

                    <a href="<?= \Zyrtex\Core\Response::url('/logout') ?>" 
                       class="p-2.5 rounded-xl bg-slate-800 text-slate-300 hover:text-white hover:bg-rose-500/20 hover:text-rose-400 border border-slate-700 transition"
                       title="Encerrar Sessão">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                </div>

            </header>

            <!-- CONTEÚDO DA PÁGINA (VIEW) -->
            <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
                <?= $content ?? '' ?>
            </main>

            <!-- AdSense Freemium Slot (Quando ativo) -->
            <?php \Zyrtex\Core\View::partial('adsense_slot', ['tenant' => $tenant ?? null, 'slot_context' => 'admin']); ?>

            <!-- FOOTER -->
            <footer class="p-6 border-t border-slate-900 text-center text-xs text-slate-500">
                <p>Zyrtex SaaS &bull; Plataforma Multi-Tenant de Gestão &bull; Todos os direitos reservados</p>
            </footer>

        </div>

    </div>

    <!-- PWA Service Worker Registration & Install Prompt -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/sw.js')
                    .then(reg => console.log('Zyrtex Service Worker registrado:', reg.scope))
                    .catch(err => console.warn('Falha no Service Worker:', err));
            });
        }
    </script>
    <?php \Zyrtex\Core\View::partial('pwa_install_prompt', ['tenant' => $tenant ?? null]); ?>
</body>
</html>
