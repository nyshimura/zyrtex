<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Condição Especial de Fidelidade • Zyrtex SaaS</title>

    <!-- OpenGraph para Preview Atraente no WhatsApp -->
    <meta property="og:title" content="🎁 Condição Especial de Fidelidade Zyrtex">
    <meta property="og:description" content="Conclua seu upgrade com desconto exclusivo de fidelidade para <?= htmlspecialchars($sub['business_name'] ?? 'sua oficina', ENT_QUOTES, 'UTF-8') ?>.">
    <meta property="og:type" content="website">

    <!-- Tailwind CSS v3 via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @keyframes pulse-subtle {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.02); opacity: 0.95; }
        }
        .pulse-card { animation: pulse-subtle 4s ease-in-out infinite; }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 antialiased selection:bg-indigo-500 selection:text-white">

    <div class="w-full max-w-xl mx-auto space-y-6">

        <!-- Top Header & Logo -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 text-xs font-bold uppercase tracking-wider">
                <span>⭐</span>
                <span>Programa de Fidelidade de Parceiros</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Finalize seu Pedido com Desconto
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Condição liberada exclusivamente para <strong><?= htmlspecialchars($sub['business_name'] ?? 'Sua Oficina', ENT_QUOTES, 'UTF-8') ?></strong> (<?= max(1, (int)($sub['tenant_age_months'] ?? 1)) ?> meses de parceria).
            </p>
        </div>

        <?php if ($isApproved): ?>
            <!-- Card de Pedido Já Concluído -->
            <div class="bg-slate-900 border border-emerald-500/40 rounded-3xl p-8 text-center space-y-6 shadow-2xl shadow-emerald-500/10">
                <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center mx-auto text-3xl">
                    ✓
                </div>
                <div class="space-y-2">
                    <h2 class="text-xl font-black text-white">Pagamento Já Concluído!</h2>
                    <p class="text-sm text-slate-300">
                        Este pedido foi confirmado e o seu recurso já se encontra ativo no sistema Zyrtex.
                    </p>
                </div>
                <a href="<?= \Zyrtex\Core\Response::url('/login') ?>"
                   class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-xl shadow-indigo-600/30 transition">
                    <span>Acessar Painel da Oficina</span>
                    <span>&rarr;</span>
                </a>
            </div>
        <?php else: ?>

            <!-- Timer de Urgência Regressivo -->
            <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-amber-500/10 border border-amber-500/30 rounded-2xl px-4 py-3 flex items-center justify-between text-xs font-bold text-amber-300 shadow-sm">
                <div class="flex items-center gap-2">
                    <span class="text-base animate-pulse">⏱️</span>
                    <span>Oferta por tempo limitado:</span>
                </div>
                <div class="font-mono text-sm bg-black/40 px-3 py-1 rounded-lg border border-amber-500/30 text-amber-200" id="countdownTimer">
                    23:59:59
                </div>
            </div>

            <!-- Card Principal com Ancoragem de Preço e Pix -->
            <div class="bg-slate-900/95 backdrop-blur-md border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden pulse-card">
                
                <!-- Tag de Resumo do Item -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Recurso Selecionado</span>
                        <h3 class="text-base font-bold text-white"><?= htmlspecialchars($planTitle, ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-indigo-600/20 text-indigo-300 border border-indigo-500/30">
                        -<?= number_format($discountPercent, 0) ?>% OFF
                    </span>
                </div>

                <!-- Ancoragem de Preço e Economia -->
                <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950/40 border border-indigo-500/30 flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 block line-through">De: R$ <?= number_format($origAmount, 2, ',', '.') ?></span>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-400 mt-0.5">
                            R$ <?= number_format($finalAmount, 2, ',', '.') ?>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[11px] font-bold block">
                            Você economiza R$ <?= number_format($discountVal, 2, ',', '.') ?>
                        </span>
                        <span class="text-[10px] text-slate-400 mt-1 block">Pagamento único via Pix</span>
                    </div>
                </div>

                <!-- Seção Pix QR Code & Copia e Cola -->
                <div class="space-y-4 pt-2">
                    <div class="text-center space-y-2">
                        <span class="text-xs font-bold text-slate-300 block">Escaneie o QR Code abaixo no seu aplicativo bancário:</span>
                        
                        <div class="inline-block p-3 bg-white rounded-2xl shadow-xl">
                            <?php if (!empty($pixData['qrcode_url'])): ?>
                                <img src="data:image/png;base64,<?= $pixData['qrcode_url'] ?>" alt="Pix QR Code" class="w-48 h-48 sm:w-56 sm:h-56 mx-auto rounded-lg">
                            <?php else: ?>
                                <div class="w-48 h-48 sm:w-56 sm:h-56 flex items-center justify-center bg-slate-100 rounded-lg text-slate-800 text-xs font-mono font-bold">
                                    [QR Code Pix Pronto]
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Código Copia e Cola -->
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-slate-400 block">Ou copie o código Pix Copia e Cola:</label>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly id="pixCodeInput" value="<?= htmlspecialchars($pixData['pix_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                   class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-300 font-mono truncate focus:outline-none">
                            <button type="button" onclick="copyPixCode()" id="btnCopyPix"
                                    class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shrink-0 shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                                <span>📋</span>
                                <span>Copiar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Indicador de Status com Polling Ativo -->
                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-center gap-2 text-xs text-slate-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping"></span>
                        <span>Aguardando confirmação bancária em tempo real...</span>
                    </div>

                </div>

            </div>

            <!-- Card de Garantia & Segurança -->
            <div class="text-center text-[11px] text-slate-500 flex items-center justify-center gap-4">
                <span class="flex items-center gap-1">🔒 Conexão Segura 256-bit</span>
                <span>&bull;</span>
                <span class="flex items-center gap-1">⚡ Liberação Imediata</span>
                <span>&bull;</span>
                <span class="flex items-center gap-1">🛡️ Zyrtex SaaS Oficial</span>
            </div>

        <?php endif; ?>

    </div>

    <!-- Script de Polling Automático e Timer -->
    <script>
        function copyPixCode() {
            const input = document.getElementById('pixCodeInput');
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = document.getElementById('btnCopyPix');
                const orig = btn.innerHTML;
                btn.innerHTML = '<span>✓</span> <span>Copiado!</span>';
                btn.classList.add('bg-emerald-500');
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.classList.remove('bg-emerald-500');
                }, 2000);
            });
        }

        // Timer Regressivo Simulado (23h 59m)
        let totalSeconds = 23 * 3600 + 59 * 60 + 59;
        const timerEl = document.getElementById('countdownTimer');
        if (timerEl) {
            setInterval(() => {
                if (totalSeconds > 0) {
                    totalSeconds--;
                    const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                    const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                    const s = String(totalSeconds % 60).padStart(2, '0');
                    timerEl.textContent = `${h}:${m}:${s}`;
                }
            }, 1000);
        }

        // Polling de Confirmação do Pix em Segundo Plano
        const token = <?= json_encode($token) ?>;
        if (token && !<?= json_encode($isApproved) ?>) {
            const pollInterval = setInterval(() => {
                fetch('<?= \Zyrtex\Core\Response::url('/recuperar/') ?>' + encodeURIComponent(token) + '/status')
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.is_approved) {
                            clearInterval(pollInterval);
                            window.location.reload();
                        }
                    })
                    .catch(err => console.warn('Polling remarketing status:', err));
            }, 3000);
        }
    </script>
</body>
</html>
