<?php if (!empty($flashes)): ?>
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2.5 max-w-md w-full px-4 pointer-events-none">
        <?php foreach ($flashes as $type => $messages): ?>
            <?php foreach ($messages as $msg): ?>
                <?php
                    $style = match($type) {
                        'success' => 'bg-slate-900/95 border-emerald-500/50 text-emerald-200 shadow-emerald-950/40',
                        'error' => 'bg-slate-900/95 border-rose-500/50 text-rose-200 shadow-rose-950/40',
                        'warning' => 'bg-slate-900/95 border-amber-500/50 text-amber-200 shadow-amber-950/40',
                        default => 'bg-slate-900/95 border-indigo-500/50 text-indigo-200 shadow-indigo-950/40',
                    };
                    $badgeStyle = match($type) {
                        'success' => 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
                        'error' => 'bg-rose-500/20 text-rose-400 border border-rose-500/30',
                        'warning' => 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
                        default => 'bg-indigo-500/20 text-indigo-400 border border-indigo-500/30',
                    };
                    $icon = match($type) {
                        'success' => '✓',
                        'error' => '✕',
                        'warning' => '⚠️',
                        default => 'ℹ️',
                    };
                ?>
                <div class="toast-flash-item pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border shadow-xl backdrop-blur-md <?= $style ?> transition-all duration-500 ease-out transform translate-y-0 opacity-100">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl font-bold text-xs <?= $badgeStyle ?> flex-shrink-0">
                        <?= $icon ?>
                    </span>
                    <div class="flex-1 text-xs font-semibold leading-relaxed pt-0.5">
                        <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <button type="button" 
                            class="toast-close-btn -mr-1 -mt-1 p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition text-xs font-bold leading-none"
                            aria-label="Fechar notificação">
                        ✕
                    </button>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <script>
    (function() {
        document.querySelectorAll('.toast-flash-item').forEach(function(el) {
            // Auto fade-out após 4.5 segundos
            var autoDismissTimer = setTimeout(function() {
                dismissToast(el);
            }, 4500);

            // Botão Fechar manual
            var closeBtn = el.querySelector('.toast-close-btn');
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    clearTimeout(autoDismissTimer);
                    dismissToast(el, 250);
                });
            }
        });

        function dismissToast(el, duration) {
            duration = duration || 500;
            el.style.transition = 'opacity ' + duration + 'ms ease, transform ' + duration + 'ms ease, margin-top ' + duration + 'ms ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-12px) scale(0.96)';
            setTimeout(function() {
                el.remove();
            }, duration);
        }
    })();
    </script>
<?php endif; ?>
