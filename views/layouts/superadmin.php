<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'SuperAdmin &bull; Zyrtex SaaS', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Tailwind CSS (v3 via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#131b2e',
                            950: '#070b14'
                        }
                    }
                }
            }
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Topbar Mobile / Indicador Master -->
    <header class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-40 px-4 sm:px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin') ?>" class="flex items-center gap-2 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-400 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                    Z
                </div>
                <div class="flex flex-col">
                    <span class="font-extrabold text-sm tracking-tight text-white flex items-center gap-1.5">
                        ZYRTEX <span class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">SuperAdmin</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium">Controle Global da Plataforma</span>
                </div>
            </a>
        </div>

        <!-- Badges e Logout -->
        <div class="flex items-center gap-3">
            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                SaaS Online
            </span>

            <div class="text-xs text-right hidden md:block">
                <span class="font-bold text-white block"><?= htmlspecialchars(\Zyrtex\Core\Session::get('superadmin_email') ?? 'superadmin@zyrtex.com.br') ?></span>
                <span class="text-[10px] text-slate-400">Master Level Access</span>
            </div>

            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/logout') ?>" 
               class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-rose-950/40 text-slate-300 hover:text-rose-400 border border-slate-700 hover:border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <span>🚪</span>
                <span>Sair</span>
            </a>
        </div>
    </header>

    <div class="flex-1 flex">
        
        <!-- Sidebar de Navegação SuperAdmin -->
        <?php 
        $saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug(); 
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        ?>
        <aside class="w-64 bg-slate-900 border-r border-slate-800 p-4 space-y-6 hidden lg:block shrink-0">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 block mb-2">Visão Geral</span>
                <nav class="space-y-1">
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug) ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= (str_ends_with(rtrim(strtok($currentUri, '?'), '/'), '/' . $saSlug)) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">📊</span>
                        <span>Dashboard Global</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/tenants') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= str_contains($currentUri, '/' . $saSlug . '/tenants') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">🏢</span>
                        <span>Oficinas & Lojas</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/financeiro') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= str_contains($currentUri, '/' . $saSlug . '/financeiro') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">💰</span>
                        <span>Painel Financeiro & MRR</span>
                    </a>
                </nav>
            </div>

            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-3 block mb-2">Plataforma & SaaS</span>
                <nav class="space-y-1">
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/parametros') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= (str_contains($currentUri, '/' . $saSlug . '/parametros') && !str_contains($currentUri, '#vitrine')) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">⚙️</span>
                        <span>Preços & Parâmetros</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/parametros#vitrine') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= str_contains($currentUri, 'vitrine') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">🎨</span>
                        <span>Vitrine & Rodapé SaaS</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/broadcast') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= str_contains($currentUri, '/' . $saSlug . '/broadcast') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">📢</span>
                        <span>Avisos Globais (Broadcast)</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/tickets') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= str_contains($currentUri, '/' . $saSlug . '/tickets') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' ?>">
                        <span class="text-base">💬</span>
                        <span>Suporte & Chamados</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/parametros#tab-updates') ?>" 
                       class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition text-slate-400 hover:text-white hover:bg-slate-800/60">
                        <span class="text-base">🔄</span>
                        <span class="flex items-center justify-between flex-1">
                            <span>Atualizações (GitHub)</span>
                            <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono">v1.2</span>
                        </span>
                    </a>
                </nav>
            </div>

            <div class="pt-4 border-t border-slate-800">
                <a href="<?= \Zyrtex\Core\Response::url('/') ?>" target="_blank"
                   class="flex items-center justify-between px-3 py-2 rounded-xl text-xs text-slate-400 hover:text-white hover:bg-slate-800/60 transition">
                    <span class="flex items-center gap-2"><span>🌐</span> Ver Vitrine Pública</span>
                    <span class="text-[10px] text-slate-500">&nearr;</span>
                </a>
            </div>
        </aside>

        <!-- Área de Conteúdo Principal -->
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">

            <!-- Flash Messages -->
            <?php 
            $flashSuccess = $flashes['success'][0] ?? \Zyrtex\Core\Session::getFlash('success');
            $flashError = $flashes['error'][0] ?? \Zyrtex\Core\Session::getFlash('error');
            $flashWarning = $flashes['warning'][0] ?? \Zyrtex\Core\Session::getFlash('warning');
            $flashInfo = $flashes['info'][0] ?? \Zyrtex\Core\Session::getFlash('info');
            ?>

            <?php if ($flashSuccess): ?>
                <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 text-xs font-bold flex items-center justify-between shadow-lg shadow-emerald-950/30">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">✓</span>
                        <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($flashError): ?>
                <div class="p-4 rounded-2xl bg-rose-950/60 border border-rose-500/40 text-rose-200 text-xs font-bold flex items-center justify-between shadow-lg shadow-rose-950/30">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">✗</span>
                        <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($flashWarning): ?>
                <div class="p-4 rounded-2xl bg-amber-950/60 border border-amber-500/40 text-amber-200 text-xs font-bold flex items-center justify-between shadow-lg shadow-amber-950/30">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">⚠️</span>
                        <span><?= htmlspecialchars($flashWarning, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($flashInfo): ?>
                <div class="p-4 rounded-2xl bg-blue-950/60 border border-blue-500/40 text-blue-200 text-xs font-bold flex items-center justify-between shadow-lg shadow-blue-950/30">
                    <div class="flex items-center gap-2.5">
                        <span class="text-base">ℹ️</span>
                        <span><?= htmlspecialchars($flashInfo, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Renderiza a view filha -->
            <?= $content ?? '' ?>

        </main>

    </div>

    <!-- ========================================== -->
    <!-- ZYRTEX GLOBAL MODAL DIALOG (Dark SaaS)     -->
    <!-- ========================================== -->
    <div id="zyrtexModalBackdrop" 
         class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md hidden flex items-center justify-center p-4 transition-opacity duration-200 opacity-0 pointer-events-none"
         role="dialog" aria-modal="true" aria-labelledby="zyrtexModalTitle">
        <div id="zyrtexModalCard" 
             class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl shadow-indigo-950/50 transform scale-95 transition-transform duration-200 space-y-5">
            
            <!-- Topo com Ícone e Badge -->
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div id="zyrtexModalIconBox" class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center text-2xl shrink-0 shadow-inner">
                        <span id="zyrtexModalIcon">🚀</span>
                    </div>
                    <div>
                        <span id="zyrtexModalBadge" class="hidden text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                            Zyrtex Shield
                        </span>
                        <h3 id="zyrtexModalTitle" class="text-base sm:text-lg font-black text-white tracking-tight mt-0.5">
                            Confirmação
                        </h3>
                    </div>
                </div>

                <button type="button" onclick="zyrtexCloseModal(false)" 
                        class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition text-sm font-bold cursor-pointer"
                        aria-label="Fechar">
                    ✕
                </button>
            </div>

            <!-- Corpo da Mensagem (Suporta HTML Rico) -->
            <div id="zyrtexModalBody" class="text-xs sm:text-sm text-slate-300 space-y-3 leading-relaxed">
                <!-- Conteúdo dinâmico injetado aqui -->
            </div>

            <!-- Ações / Botões -->
            <div id="zyrtexModalActions" class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-3 border-t border-slate-800/80">
                <button type="button" id="zyrtexModalCancelBtn" onclick="zyrtexCloseModal(false)"
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="zyrtexModalConfirmBtn"
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 cursor-pointer">
                    Confirmar
                </button>
            </div>

        </div>
    </div>

    <!-- Container de Toasts Globais -->
    <div id="zyrtexToastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none p-4"></div>

    <script>
    (function() {
        let currentOnConfirm = null;
        let currentOnCancel = null;

        window.zyrtexConfirm = function(options) {
            options = options || {};
            const backdrop = document.getElementById('zyrtexModalBackdrop');
            const card = document.getElementById('zyrtexModalCard');
            const icon = document.getElementById('zyrtexModalIcon');
            const badge = document.getElementById('zyrtexModalBadge');
            const title = document.getElementById('zyrtexModalTitle');
            const body = document.getElementById('zyrtexModalBody');
            const cancelBtn = document.getElementById('zyrtexModalCancelBtn');
            const confirmBtn = document.getElementById('zyrtexModalConfirmBtn');

            if (!backdrop) return;

            // Configura ícone
            if (icon) icon.textContent = options.icon || '🚀';
            
            // Configura badge
            if (badge) {
                if (options.badge) {
                    badge.textContent = options.badge;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }

            // Título
            if (title) title.textContent = options.title || 'Confirmação';

            // Mensagem (HTML ou texto)
            if (body) {
                if (options.messageHtml) {
                    body.innerHTML = options.messageHtml;
                } else {
                    body.textContent = options.message || 'Deseja prosseguir com esta ação?';
                }
            }

            // Botão Cancelar
            if (cancelBtn) {
                cancelBtn.classList.remove('hidden');
                cancelBtn.textContent = options.cancelText || 'Cancelar';
                if (options.cancelClass) {
                    cancelBtn.className = options.cancelClass;
                } else {
                    cancelBtn.className = 'px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs uppercase tracking-wider transition cursor-pointer';
                }
            }

            // Botão Confirmar
            if (confirmBtn) {
                confirmBtn.textContent = options.confirmText || 'Confirmar';
                if (options.confirmClass) {
                    confirmBtn.className = options.confirmClass;
                } else {
                    confirmBtn.className = 'px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 cursor-pointer';
                }
            }

            currentOnConfirm = options.onConfirm || null;
            currentOnCancel = options.onCancel || null;

            if (confirmBtn) {
                confirmBtn.onclick = function() {
                    zyrtexCloseModal(true);
                    if (typeof currentOnConfirm === 'function') {
                        currentOnConfirm();
                    }
                };
            }

            // Exibe modal com transição suave
            backdrop.classList.remove('hidden', 'pointer-events-none');
            requestAnimationFrame(() => {
                backdrop.classList.remove('opacity-0');
                if (card) {
                    card.classList.remove('scale-95');
                    card.classList.add('scale-100');
                }
            });
        };

        window.zyrtexAlert = function(options) {
            options = options || {};
            const cancelBtn = document.getElementById('zyrtexModalCancelBtn');
            const prevOnConfirm = options.onConfirm;

            window.zyrtexConfirm({
                title: options.title || 'Aviso do Sistema',
                badge: options.badge || (options.type === 'error' ? '⚠️ Erro' : 'ℹ️ Zyrtex SaaS'),
                icon: options.icon || (options.type === 'error' ? '⚠️' : '✓'),
                messageHtml: options.messageHtml || (options.message ? `<p>${options.message}</p>` : ''),
                confirmText: options.buttonText || 'Entendido',
                confirmClass: options.type === 'error' 
                    ? 'px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-rose-600/20 cursor-pointer'
                    : 'px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 cursor-pointer',
                onConfirm: prevOnConfirm
            });

            if (cancelBtn) cancelBtn.classList.add('hidden');
        };

        window.zyrtexCloseModal = function(isConfirm) {
            const backdrop = document.getElementById('zyrtexModalBackdrop');
            const card = document.getElementById('zyrtexModalCard');
            if (!backdrop) return;

            backdrop.classList.add('opacity-0');
            if (card) {
                card.classList.remove('scale-100');
                card.classList.add('scale-95');
            }

            setTimeout(() => {
                backdrop.classList.add('hidden', 'pointer-events-none');
                if (isConfirm !== true && typeof currentOnCancel === 'function') {
                    currentOnCancel();
                }
                currentOnConfirm = null;
                currentOnCancel = null;
            }, 200);
        };

        window.zyrtexToast = function(message, type) {
            type = type || 'info';
            const container = document.getElementById('zyrtexToastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'pointer-events-auto p-4 rounded-2xl border text-xs font-bold flex items-center justify-between gap-3 shadow-xl transition-all duration-300 transform translate-y-4 opacity-0 ' + 
                (type === 'success' ? 'bg-emerald-950/95 border-emerald-500/40 text-emerald-200 shadow-emerald-950/40' :
                 type === 'error' ? 'bg-rose-950/95 border-rose-500/40 text-rose-200 shadow-rose-950/40' :
                 'bg-slate-900/95 border-slate-700 text-white shadow-black/40');

            toast.innerHTML = `
                <div class="flex items-center gap-2.5">
                    <span class="text-base">${type === 'success' ? '✓' : type === 'error' ? '⚠️' : 'ℹ️'}</span>
                    <span>${message}</span>
                </div>
                <button type="button" class="text-slate-400 hover:text-white transition ml-2 cursor-pointer">✕</button>
            `;

            toast.querySelector('button').onclick = () => {
                toast.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => toast.remove(), 300);
            };

            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-4');
            });

            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('opacity-0', 'translate-y-4');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        };

        // Fechar ao pressionar ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                zyrtexCloseModal(false);
            }
        });

        // Fechar ao clicar no backdrop (fora do card)
        document.addEventListener('click', function(e) {
            const backdrop = document.getElementById('zyrtexModalBackdrop');
            if (backdrop && !backdrop.classList.contains('hidden') && e.target === backdrop) {
                zyrtexCloseModal(false);
            }
        });
    })();
    </script>
</body>
</html>
