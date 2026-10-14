<!-- Banner de Consentimento de Cookies & LGPD (Zyrtex SaaS) -->
<div id="zyrtex-cookie-banner" class="hidden fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:max-w-md z-50 p-5 rounded-2xl bg-slate-900/95 border border-slate-700/80 shadow-2xl backdrop-blur-md text-xs text-slate-300 transition-all duration-300 transform translate-y-0 opacity-100">
    <div class="flex items-start gap-3 mb-3">
        <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-base flex-shrink-0">
            🍪
        </span>
        <div class="flex-1">
            <h4 class="font-bold text-white text-sm">Privacidade &amp; Cookies</h4>
            <p class="mt-1 text-slate-400 text-[11px] leading-relaxed">
                Utilizamos cookies essenciais para o funcionamento do sistema e tecnologias de parceiros confiáveis (como <strong>Google AdSense</strong> para anúncios e <strong>Mercado Pago</strong> para transações seguras) em total conformidade com a <strong>LGPD (Lei nº 13.709/18)</strong>.
            </p>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-800">
        <a href="<?= \Zyrtex\Core\Response::url('/termos#secao-4') ?>" target="_blank" 
           class="text-[11px] text-indigo-400 hover:text-indigo-300 underline font-semibold">
            Ler Política de Cookies &rarr;
        </a>
        <div class="flex items-center gap-2">
            <button type="button" id="zyrtex-cookie-accept" 
                    class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition">
                ✓ Aceitar Cookies
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    try {
        var banner = document.getElementById('zyrtex-cookie-banner');
        var acceptBtn = document.getElementById('zyrtex-cookie-accept');
        
        if (!banner || !acceptBtn) return;

        // Se o usuário já aceitou cookies anteriormente, não exibe
        if (!localStorage.getItem('zyrtex_cookies_accepted')) {
            banner.classList.remove('hidden');
        }

        acceptBtn.addEventListener('click', function() {
            localStorage.setItem('zyrtex_cookies_accepted', '1');
            banner.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(16px)';
            setTimeout(function() {
                banner.remove();
            }, 400);
        });
    } catch(e) {}
})();
</script>
