<?php
$themeColor = $tenant['theme_color'] ?? 'indigo';
$themeMode = $tenant['theme_mode'] ?? 'auto';
$colorPalettes = [
    'indigo'  => ['50' => '#eef2ff', '100' => '#e0e7ff', '500' => '#6366f1', '600' => '#4f46e5', '700' => '#4338ca'],
    'emerald' => ['50' => '#ecfdf5', '100' => '#d1fae5', '500' => '#10b981', '600' => '#059669', '700' => '#047857'],
    'blue'    => ['50' => '#eff6ff', '100' => '#dbeafe', '500' => '#3b82f6', '600' => '#2563eb', '700' => '#1d4ed8'],
    'amber'   => ['50' => '#fffbeb', '100' => '#fef3c7', '500' => '#f59e0b', '600' => '#d97706', '700' => '#b45309'],
    'rose'    => ['50' => '#fff1f2', '100' => '#ffe4e6', '500' => '#f43f5e', '600' => '#e11d48', '700' => '#be123c'],
    'violet'  => ['50' => '#f5f3ff', '100' => '#ede9fe', '500' => '#8b5cf6', '600' => '#7c3aed', '700' => '#6d28d9'],
    'cyan'    => ['50' => '#ecfeff', '100' => '#cffafe', '500' => '#06b6d4', '600' => '#0891b2', '700' => '#0e7490'],
];
$activePalette = $colorPalettes[$themeColor] ?? $colorPalettes['indigo'];
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased selection:bg-brand-500 selection:text-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tenant['business_name'] ?? 'Assistência Técnica Especializada', ENT_QUOTES, 'UTF-8') ?> - Zyrtex</title>
    
    <!-- Gerenciador Imediato de Tema (Prevenção de FOUC) -->
    <script>
        (function() {
            const tenantMode = <?= json_encode($themeMode) ?>;
            let applyDark = false;

            if (tenantMode === 'dark') {
                applyDark = true;
            } else if (tenantMode === 'light') {
                applyDark = false;
            } else {
                // Modo 'auto' (Padrão): Segue preferência salva ou detecta SO do visitante
                const userSaved = localStorage.getItem('zyrtex_public_theme');
                if (userSaved === 'dark') {
                    applyDark = true;
                } else if (userSaved === 'light') {
                    applyDark = false;
                } else {
                    applyDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }
            }

            if (applyDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN com Suporte a Paleta Dinâmica -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: <?= json_encode($activePalette) ?>,
                        action: {
                            500: '#10b981',
                            600: '#059669',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 40px -10px rgba(99, 102, 241, 0.25)',
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 dark:bg-slate-950 transition-colors duration-200">

    <!-- Flash Messages (Toasts) -->
    <?php \Zyrtex\Core\View::partial('flash_messages', ['flashes' => $flashes ?? []]); ?>

    <!-- Barra de Contexto Superior / Acesso SaaS -->
    <div class="bg-slate-900 text-slate-300 text-xs py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center justify-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-semibold text-[11px] border border-emerald-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Vitrine Oficial
                </span>
                <span class="text-slate-300 truncate">
                    <?= htmlspecialchars($tenant['business_name'] ?? 'Assistência Técnica Modelo', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/?saas=1" class="text-slate-400 hover:text-white transition flex items-center gap-1 text-[11px] font-medium">
                    <span>Você é dono de assistência?</span>
                    <strong class="text-brand-400 hover:text-brand-300 underline font-bold">Crie seu Zyrtex Grátis &rarr;</strong>
                </a>
            </div>
        </div>
    </div>

    <!-- Header Público da Oficina (Glassmorphism) -->
    <header class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo & Nome da Oficina -->
            <div class="flex items-center gap-4">
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null) ?>" class="flex items-center gap-3 group">
                    <?php if (!empty($tenant['logo_path'])): ?>
                        <img src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" 
                             alt="<?= htmlspecialchars($tenant['business_name'] ?? 'Logo') ?>" 
                             class="h-11 w-auto max-w-[150px] object-contain rounded-xl drop-shadow-sm group-hover:scale-105 transition-transform duration-200">
                    <?php else: ?>
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 via-brand-500 to-indigo-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-brand-500/25 group-hover:scale-105 transition-transform duration-200">
                            <?= mb_substr($tenant['business_name'] ?? 'Z', 0, 1) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <span class="font-extrabold text-base sm:text-lg text-slate-900 dark:text-white leading-tight block tracking-tight group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
                            <?= htmlspecialchars($tenant['business_name'] ?? 'Zyrtex Oficina', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block -mt-0.5 font-medium truncate max-w-[200px] sm:max-w-none">
                            <?= htmlspecialchars($tenant['slogan'] ?? 'Ordem de Serviço & Transparência Digital', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>
                </a>
            </div>

            <!-- Navegação & Ações -->
            <nav class="flex items-center gap-2 sm:gap-4">
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '#rastreio') ?>" 
                   class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 transition hidden md:inline-block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                    Rastrear OS
                </a>
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '#promocoes') ?>" 
                   class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 transition hidden md:inline-block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                    Promoções
                </a>
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '#localizacao') ?>" 
                   class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-brand-600 dark:text-slate-300 dark:hover:text-brand-400 transition hidden lg:inline-block px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800">
                    Onde Estamos
                </a>

                <!-- Portal do Cliente (Destaque) -->
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal') ?>" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:hover:bg-indigo-900 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/80 shadow-sm transition">
                    <span>🛡️ Portal do Cliente</span>
                </a>

                <!-- Botão Área do Lojista -->
                <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 transition hidden sm:inline-flex">
                    <span>Acesso Lojista</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>

                <!-- Alternador de Tema (Claro / Escuro) -->
                <button type="button" onclick="togglePublicTheme()" id="btn-theme-toggle"
                        class="p-2.5 rounded-xl text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white bg-slate-100 hover:bg-slate-200/80 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition"
                        title="Alternar entre Modo Escuro e Claro">
                    <span class="dark:hidden text-base leading-none block">🌙</span>
                    <span class="hidden dark:block text-base leading-none">☀️</span>
                </button>
            </nav>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- AdSense Slot (Contas Freemium) -->
    <div class="max-w-7xl mx-auto px-4 w-full">
        <?php \Zyrtex\Core\View::partial('adsense_slot', ['tenant' => $tenant, 'app' => $app]); ?>
    </div>

    <!-- Botão Flutuante de WhatsApp com Radar Animado -->
    <?php if (!empty($tenant['whatsapp_number'])): 
        $whatsClean = preg_replace('/\D/', '', $tenant['whatsapp_number']);
        $whatsUrl = "https://wa.me/55{$whatsClean}?text=" . urlencode("Olá! Vi a vitrine da assistência e gostaria de tirar uma dúvida sobre reparos e orçamentos.");
    ?>
        <div class="fixed bottom-6 right-6 z-40">
            <a href="<?= $whatsUrl ?>" target="_blank" rel="noopener noreferrer"
               aria-label="Fale conosco no WhatsApp"
               class="relative group flex items-center gap-3 px-5 py-3.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-full shadow-2xl shadow-emerald-500/40 transition-all duration-300 hover:scale-105 active:scale-95">
                <!-- Ping Radar Animation -->
                <span class="absolute -inset-1 rounded-full bg-emerald-400 opacity-40 animate-ping pointer-events-none"></span>
                
                <svg class="w-6 h-6 fill-current relative z-10" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <div class="text-left relative z-10 hidden sm:block">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-100 block -mb-0.5">Atendimento Online</span>
                    <span class="font-extrabold text-sm block">WhatsApp Direto</span>
                </div>
            </a>
        </div>
    <?php endif; ?>

    <!-- Rodapé Público Modernizado -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200/80 dark:border-slate-800 py-12 mt-16 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <!-- Coluna 1: Sobre a Loja -->
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center text-white font-extrabold text-sm">
                            <?= mb_substr($tenant['business_name'] ?? 'Z', 0, 1) ?>
                        </div>
                        <h4 class="font-black text-base text-slate-900 dark:text-white">
                            <?= htmlspecialchars($tenant['business_name'] ?? 'Oficina Modelo', ENT_QUOTES, 'UTF-8') ?>
                        </h4>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md leading-relaxed">
                        <?= htmlspecialchars($tenant['slogan'] ?? 'Transparência total em cada reparo, laudo fotográfico em alta resolução e garantia legal conforme o Código de Defesa do Consumidor.', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <div class="flex items-center gap-4 text-xs font-semibold text-slate-600 dark:text-slate-400 pt-2">
                        <span class="inline-flex items-center gap-1">🔒 Conexão Segura SSL</span>
                        <span class="inline-flex items-center gap-1">⚖️ Garantia 90 Dias (CDC)</span>
                        <span class="inline-flex items-center gap-1">⚡ Prontuário Antifraude</span>
                    </div>
                </div>

                <!-- Coluna 2: Acesso Rápido -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Serviços ao Cliente</h5>
                    <ul class="space-y-2 text-xs text-slate-500 dark:text-slate-400">
                        <li>
                            <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '#rastreio') ?>" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                                Rastrear Ordem de Serviço
                            </a>
                        </li>
                        <li>
                            <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal') ?>" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                                Portal do Cliente (Histórico & Faturas)
                            </a>
                        </li>
                        <li>
                            <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '#promocoes') ?>" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                                Promoções de Bancada
                            </a>
                        </li>
                        <li>
                            <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '#localizacao') ?>" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                                Endereço & Horário
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Coluna 3: Gestão & Plataforma -->
                <div class="space-y-2.5">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Lojista & Gestão</h5>
                    <ul class="space-y-2 text-xs text-slate-500 dark:text-slate-400">
                        <li>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/admin" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                                Acessar Painel da Oficina (/admin)
                            </a>
                        </li>
                        <li>
                            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/?saas=1" class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                                Conhecer a Plataforma Zyrtex SaaS
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <div class="pt-8 border-t border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
                <div>
                    &copy; <?= date('Y') ?> <?= htmlspecialchars($tenant['business_name'] ?? 'Oficina', ENT_QUOTES, 'UTF-8') ?>. Todos os direitos reservados.
                </div>
                <div class="flex items-center gap-1.5">
                    <span>Desenvolvido na plataforma</span>
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/?saas=1" class="font-extrabold text-brand-600 dark:text-brand-400 hover:underline">
                        Zyrtex SaaS
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Script do Tema Público (Lojista + Usuário) -->
    <script>
        function togglePublicTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('zyrtex_public_theme', isDark ? 'dark' : 'light');
        }

        // Listener reativo se o lojista definiu 'auto' e o visitante mudar o tema no sistema operacional
        if (<?= json_encode($themeMode) ?> === 'auto' && !localStorage.getItem('zyrtex_public_theme')) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                if (e.matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            });
        }
    </script>

    <!-- Banner de Consentimento de Cookies & LGPD -->
    <?php \Zyrtex\Core\View::partial('cookie_banner'); ?>
</body>
</html>

