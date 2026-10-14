<?php
$baseUrl = \Zyrtex\Core\Response::baseUrl();

// Carrega configurações globais do SaaS para cabeçalho, vitrine e rodapé
$saasSettings = $saasSettings ?? [];
if (empty($saasSettings)) {
    try {
        $rows = \Zyrtex\Core\Database::fetchAll("SELECT config_key, config_value FROM saas_settings WHERE config_key LIKE 'saas_%'");
        foreach ($rows as $r) {
            $saasSettings[$r['config_key']] = $r['config_value'];
        }
    } catch (\Throwable) {}
}

$topbarAnnouncement = $saasSettings['saas_topbar_announcement'] ?? '🎉 Novidade: Sistema 100% Gratuito sem limite de Ordens de Serviço!';
$footerAbout = $saasSettings['saas_footer_about'] ?? 'Plataforma server-side de alta performance para oficinas e assistências técnicas. Construída com arquitetura leve em PHP puro, sem JavaScript na regra de negócios e custo zero de infraestrutura para o lojista.';
$footerCopyright = $saasSettings['saas_footer_copyright'] ?? 'Zyrtex Software as a Service. Todos os direitos reservados.';
$footerNote = $saasSettings['saas_footer_note'] ?? 'Modelo Freemium financiado por publicidade e doações voluntárias.';
$contactEmail = $saasSettings['saas_contact_email'] ?? 'contato@zyrtex.com.br';
$contactWhatsapp = $saasSettings['saas_contact_whatsapp'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-900 text-slate-100 antialiased scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Zyrtex - Sistema SaaS de Gestão para Assistências Técnicas | 100% Gratuito', ENT_QUOTES, 'UTF-8') ?></title>
    
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
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        emerald: {
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-950 text-slate-100 selection:bg-indigo-500 selection:text-white">

    <!-- Flash Messages (Toasts com auto-fadeout) -->
    <?php \Zyrtex\Core\View::partial('flash_messages', ['flashes' => $flashes ?? []]); ?>

    <!-- Topbar Informativa -->
    <div class="bg-gradient-to-r from-indigo-900 via-indigo-700 to-emerald-700 text-white text-xs py-2 px-4 text-center font-medium shadow-inner">
        <span><?= htmlspecialchars($topbarAnnouncement, ENT_QUOTES, 'UTF-8') ?></span>
        <a href="<?= \Zyrtex\Core\Response::url('/') ?>#planos" class="underline hover:text-indigo-200 ml-1.5 font-bold">Conheça o Plano com Domínio Próprio &rarr;</a>
    </div>

    <!-- Header / Navbar Principal do SaaS -->
    <header class="sticky top-0 z-40 bg-slate-950/85 backdrop-blur-lg border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            
            <!-- Logo Zyrtex -->
            <a href="<?= \Zyrtex\Core\Response::url('/') ?>" class="flex items-center gap-3 group">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-emerald-400 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                    Z
                </span>
                <div>
                    <span class="font-black text-2xl tracking-tight text-white flex items-center gap-1.5">
                        ZYRTEX <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">SaaS</span>
                    </span>
                    <span class="text-[10px] text-slate-400 tracking-wider uppercase font-medium block -mt-1">
                        Gestão de Oficinas & Assistências
                    </span>
                </div>
            </a>

            <!-- Menu de Navegação -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-300">
                <a href="<?= \Zyrtex\Core\Response::url('/') ?>#recursos" class="hover:text-white transition">Recursos</a>
                <a href="<?= \Zyrtex\Core\Response::url('/') ?>#nichos" class="hover:text-white transition">Nichos</a>
                <a href="<?= \Zyrtex\Core\Response::url('/') ?>#por-que-gratis" class="hover:text-white transition">Por que é Grátis?</a>
                <a href="<?= \Zyrtex\Core\Response::url('/') ?>#planos" class="hover:text-white transition">Planos</a>
                <a href="<?= \Zyrtex\Core\Response::url('/') ?>#apoie" class="hover:text-emerald-400 flex items-center gap-1 text-emerald-400 font-bold transition">
                    ☕ Apoie o Projeto
                </a>
            </nav>

            <!-- Ações CTA -->
            <div class="flex items-center gap-3">
                <a href="<?= \Zyrtex\Core\Response::url('/login') ?>" 
                   class="px-4 py-2 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Entrar
                </a>
                <a href="<?= \Zyrtex\Core\Response::url('/cadastro') ?>" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5">
                    <span>Criar Conta Grátis</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

        </div>
    </header>

    <!-- Conteúdo da Landing Page do SaaS -->
    <main class="flex-1">
        <?= $content ?>
    </main>

    <!-- Rodapé Oficial Zyrtex SaaS -->
    <footer class="bg-slate-950 border-t border-slate-800/80 py-12 text-slate-400 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
            <div class="md:col-span-2 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white font-extrabold flex items-center justify-center text-sm">Z</span>
                    <span class="font-extrabold text-white text-lg">ZYRTEX SaaS</span>
                </div>
                <p class="text-xs text-slate-400 max-w-sm leading-relaxed">
                    <?= htmlspecialchars($footerAbout, ENT_QUOTES, 'UTF-8') ?>
                </p>
                <div class="space-y-1 text-xs text-slate-400 pt-1">
                    <?php if (!empty($contactEmail)): ?>
                        <div class="flex items-center gap-1.5">
                            <span>✉️</span>
                            <span>Contato: <a href="mailto:<?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?>" class="text-indigo-400 hover:underline"><?= htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8') ?></a></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($contactWhatsapp)): ?>
                        <div class="flex items-center gap-1.5">
                            <span>💬</span>
                            <span>Suporte: <span class="text-emerald-400 font-semibold"><?= htmlspecialchars($contactWhatsapp, ENT_QUOTES, 'UTF-8') ?></span></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 pt-1">
                    <span>🇧🇷 Feito no Brasil com conformidade ao Marco Civil, LGPD e CDC</span>
                </div>
            </div>

            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Navegação</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="<?= \Zyrtex\Core\Response::url('/') ?>#recursos" class="hover:text-white transition">Recursos & PDV</a></li>
                    <li><a href="<?= \Zyrtex\Core\Response::url('/') ?>#nichos" class="hover:text-white transition">Nichos Suportados</a></li>
                    <li><a href="<?= \Zyrtex\Core\Response::url('/') ?>#por-que-gratis" class="hover:text-white transition">Por que é Grátis?</a></li>
                    <li><a href="<?= \Zyrtex\Core\Response::url('/') ?>#planos" class="hover:text-white transition">Plano Domínio Customizado</a></li>
                    <li><a href="<?= \Zyrtex\Core\Response::url('/') ?>#apoie" class="hover:text-white transition">Apoie com um Pix (Coffee)</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">Legal & Acesso</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="<?= \Zyrtex\Core\Response::url('/termos') ?>" class="text-indigo-400 hover:text-indigo-300 font-semibold transition flex items-center gap-1"><span>⚖️</span> Termos de Uso & LGPD</a></li>
                    <li><a href="<?= \Zyrtex\Core\Response::url('/login') ?>" class="hover:text-white transition">Login do Lojista</a></li>
                    <li><a href="<?= \Zyrtex\Core\Response::url('/cadastro') ?>" class="hover:text-white transition">Cadastrar Oficina Grátis</a></li>
                    <?php 
                    $saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
                    if (\Zyrtex\Core\Auth::isSuperAdmin() || $saSlug === 'superadmin'): 
                    ?>
                        <li><a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug) ?>" class="hover:text-slate-600 transition">SuperAdmin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <div>
                &copy; <?= date('Y') ?> <?= htmlspecialchars($footerCopyright, ENT_QUOTES, 'UTF-8') ?>
                <span class="mx-2">&bull;</span>
                <a href="<?= \Zyrtex\Core\Response::url('/termos') ?>" class="text-slate-400 hover:text-slate-300 underline">Termos & Privacidade</a>
            </div>
            <div>
                <?= htmlspecialchars($footerNote, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    </footer>

    <!-- Banner de Consentimento de Cookies & LGPD -->
    <?php \Zyrtex\Core\View::partial('cookie_banner'); ?>

</body>
</html>
