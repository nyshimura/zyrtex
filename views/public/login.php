<?php
/**
 * View: Tela de Login (Lojista / Operador)
 */
$csrf = \Zyrtex\Core\CSRF::token();
$tenant = $tenant ?? null;
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-slate-900/90 border border-slate-800 p-8 sm:p-10 rounded-2xl shadow-2xl backdrop-blur-xl">
        
        <!-- Header do Card -->
        <div class="text-center">
            <?php if ($tenant): ?>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-3">
                    🏪 Oficina: <?= htmlspecialchars($tenant['business_name'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php else: ?>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 mb-3">
                    ⚡ Acesso Central Zyrtex SaaS
                </div>
            <?php endif; ?>

            <h2 class="text-3xl font-extrabold text-white tracking-tight">
                Entrar no Sistema
            </h2>
            <p class="mt-2 text-sm text-slate-400">
                Acesse o painel administrativo da sua oficina ou ponto de atendimento.
            </p>
        </div>

        <!-- Formulário de Login (POST com CSRF e sem JS obrigatório) -->
        <form class="mt-8 space-y-6" action="<?= \Zyrtex\Core\Response::url('/login') ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <div class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1">
                        E-mail de Acesso
                    </label>
                    <div class="relative">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               placeholder="seuemail@oficina.com.br"
                               class="appearance-none block w-full px-4 py-3 border border-slate-700 rounded-xl bg-slate-950 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all sm:text-sm">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-slate-300">
                            Senha
                        </label>
                        <a href="#recuperar-senha" class="text-xs font-medium text-indigo-400 hover:text-indigo-300">
                            Esqueceu a senha?
                        </a>
                    </div>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                               placeholder="••••••••"
                               class="appearance-none block w-full px-4 py-3 border border-slate-700 rounded-xl bg-slate-950 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all sm:text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-slate-400 text-xs">Lembrar este dispositivo</span>
                </label>
            </div>

            <div>
                <button type="submit" 
                        class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5">
                    Entrar no Painel &rarr;
                </button>
            </div>
        </form>

        <!-- Informação de Ajuda / Demonstração -->
        <div class="p-3 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-slate-400 space-y-1">
            <p class="font-semibold text-slate-300">💡 Dados da Oficina Demo para Testes:</p>
            <p><strong>E-mail:</strong> <code class="text-indigo-400">admin@oficina.com.br</code></p>
            <p><strong>Senha:</strong> <code class="text-indigo-400">admin123</code></p>
        </div>

        <!-- Rodapé do Card -->
        <div class="text-center pt-2 border-t border-slate-800/80">
            <p class="text-sm text-slate-400">
                Ainda não tem o Zyrtex na sua oficina?
                <a href="<?= \Zyrtex\Core\Response::url('/cadastro') ?>" class="font-bold text-indigo-400 hover:text-indigo-300 ml-1 underline decoration-indigo-500/50 underline-offset-4">
                    Cadastre-se Grátis
                </a>
            </p>
        </div>

    </div>
</div>
