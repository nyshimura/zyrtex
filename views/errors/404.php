<div class="min-h-[60vh] flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md">
        <span class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-extrabold text-2xl mb-4">
            404
        </span>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">
            Página Não Encontrada
        </h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">
            O endereço que você tentou acessar não existe ou foi alterado.
        </p>
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
            <span>Voltar ao Início</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
    </div>
</div>
