<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Access &bull; SuperAdmin Zyrtex SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white">

    <div class="max-w-md w-full space-y-6">

        <div class="text-center space-y-2">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-indigo-600 via-purple-600 to-cyan-400 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-indigo-500/30">
                Z
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Painel Master SuperAdmin</h1>
            <p class="text-xs text-slate-400">Controle Operacional & Gestão Global da Plataforma Zyrtex</p>
        </div>

        <?php 
        $flashError = $flashes['error'][0] ?? \Zyrtex\Core\Session::getFlash('error');
        $flashWarning = $flashes['warning'][0] ?? \Zyrtex\Core\Session::getFlash('warning');
        $flashInfo = $flashes['info'][0] ?? \Zyrtex\Core\Session::getFlash('info');
        ?>

        <?php if ($flashError): ?>
            <div class="p-4 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 text-xs font-bold">
                <?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($flashWarning): ?>
            <div class="p-4 rounded-xl bg-amber-950/60 border border-amber-500/40 text-amber-200 text-xs font-bold">
                <?= htmlspecialchars($flashWarning, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($flashInfo): ?>
            <div class="p-4 rounded-xl bg-blue-950/60 border border-blue-500/40 text-blue-200 text-xs font-bold">
                <?= htmlspecialchars($flashInfo, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl">
            <form action="<?= \Zyrtex\Core\Response::url('/superadmin/login') ?>" method="POST" class="space-y-4">
                <?= $csrfField ?>
                
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        E-mail Master:
                    </label>
                    <input type="email" id="email" name="email" required
                           value="superadmin@zyrtex.com.br"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                            Chave Master / Senha:
                        </label>
                    </div>
                    <input type="password" id="password" name="password" required
                           value="admin123"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                        <span>🔑 Entrar no Painel Master</span>
                    </button>
                </div>
            </form>

            <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
                <span class="font-bold text-slate-300 block">💡 Credenciais Padrão Pré-carregadas:</span>
                <p>E-mail: <code class="text-indigo-400">superadmin@zyrtex.com.br</code></p>
                <p>Senha: <code class="text-indigo-400">admin123</code></p>
            </div>
        </div>

        <div class="text-center">
            <a href="<?= \Zyrtex\Core\Response::url('/') ?>" class="text-xs text-slate-500 hover:text-white transition">
                &larr; Voltar para a página inicial da plataforma
            </a>
        </div>

    </div>

</body>
</html>
