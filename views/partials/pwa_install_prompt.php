<?php
/**
 * Partial: Banner Flutuante Inteligente de Instalação PWA (Android, iOS & Desktop)
 * Permite ao cliente e ao lojista instalar o aplicativo com ícone direto na tela inicial do smartphone.
 */
$tenant = $tenant ?? \Zyrtex\Core\Tenant::current();
$appName = !empty($tenant['business_name']) ? htmlspecialchars($tenant['business_name'], ENT_QUOTES, 'UTF-8') : 'Zyrtex';
$themeColor = !empty($tenant['theme_color']) ? htmlspecialchars($tenant['theme_color'], ENT_QUOTES, 'UTF-8') : '#4f46e5';
?>

<!-- PWA Install Banner Container -->
<div id="pwa-install-banner" class="hidden fixed bottom-24 left-4 right-4 sm:left-6 sm:right-auto sm:max-w-md z-40 animate-fade-in transition-all duration-300">
    <div class="p-4 rounded-2xl bg-slate-900/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-700/80 shadow-2xl shadow-black/50 text-white flex items-start gap-3.5">
        
        <!-- Ícone do Aplicativo -->
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-emerald-400 p-0.5 shrink-0 shadow-md">
            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center overflow-hidden">
                <?php if (!empty($tenant['logo_path'])): ?>
                    <img src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" 
                         alt="<?= $appName ?>" 
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <img src="<?= \Zyrtex\Core\Response::url('/assets/images/pwa-icon-192.png') ?>" 
                         alt="App Icon" 
                         class="w-8 h-8 object-contain">
                <?php endif; ?>
            </div>
        </div>

        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-extrabold text-white truncate">Instalar App <?= $appName ?></h4>
                <button type="button" onclick="dismissPwaBanner()" 
                        class="text-slate-400 hover:text-white text-sm leading-none p-1 transition"
                        title="Fechar">
                    ✕
                </button>
            </div>
            
            <p class="text-[11px] text-slate-300 mt-0.5 leading-snug">
                Adicione o ícone na tela do seu celular para acompanhar suas ordens de serviço em 1 toque!
            </p>

            <div class="mt-2.5 flex items-center gap-2">
                <!-- Botão Nativo Android/Chrome -->
                <button type="button" id="btn-pwa-install" onclick="installPwaApp()"
                        class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5">
                    <span>📲</span>
                    <span>Instalar no Celular</span>
                </button>

                <!-- Instrução para iOS Safari -->
                <div id="pwa-ios-instructions" class="hidden text-[10px] text-indigo-300 bg-indigo-950/60 border border-indigo-500/30 px-2.5 py-1 rounded-lg">
                    Toque em <span class="font-bold underline">Compartilhar [⎋]</span> e depois em <span class="font-bold">"Adicionar à Tela de Início"</span>.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    let deferredPrompt = null;
    const banner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('btn-pwa-install');
    const iosInstructions = document.getElementById('pwa-ios-instructions');

    // Verifica se já foi dispensado nas últimas 24h
    const lastDismissed = localStorage.getItem('zyrtex_pwa_dismissed_at');
    if (lastDismissed && (Date.now() - parseInt(lastDismissed, 10)) < 86400000) {
        return;
    }

    // Detecta se já está rodando em modo Standalone (PWA instalado)
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone) {
        return;
    }

    // 1. Android & Chrome (Suporte ao evento beforeinstallprompt)
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (banner) {
            banner.classList.remove('hidden');
        }
    });

    // 2. iOS Safari (iPhone / iPad)
    const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    const isSafari = /safari/.test(window.navigator.userAgent.toLowerCase()) && !/chrome|crios|fxios/.test(window.navigator.userAgent.toLowerCase());
    
    if (isIos && isSafari && !isStandalone) {
        setTimeout(() => {
            if (banner && (!lastDismissed || (Date.now() - parseInt(lastDismissed, 10)) >= 86400000)) {
                if (installBtn) installBtn.classList.add('hidden');
                if (iosInstructions) iosInstructions.classList.remove('hidden');
                banner.classList.remove('hidden');
            }
        }, 3000);
    }

    window.installPwaApp = function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                banner.classList.add('hidden');
            }
            deferredPrompt = null;
        });
    };

    window.dismissPwaBanner = function() {
        if (banner) {
            banner.classList.add('hidden');
            localStorage.setItem('zyrtex_pwa_dismissed_at', Date.now().toString());
        }
    };
})();
</script>
