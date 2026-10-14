<?php
/**
 * View: Edição de Cliente
 */
$csrf = \Zyrtex\Core\CSRF::token();
$client = $client ?? [];
?>

<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">
                Editar Cliente
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Atualize as informações de contato de <strong><?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?></strong>.
            </p>
        </div>

        <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes') ?>" 
           class="text-xs font-semibold text-slate-400 hover:text-white transition">
            &larr; Voltar
        </a>
    </div>

    <!-- Card do Formulário -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-sm">
        <form action="<?= \Zyrtex\Core\Response::url('/admin/clientes/editar') ?>" method="POST" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" value="<?= (int)$client['id'] ?>">

            <!-- Nome Completo -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Nome Completo <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Telefone e Documento -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        WhatsApp / Telefone <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" required
                           value="<?= htmlspecialchars($client['phone'], ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="document" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        CPF ou CNPJ
                    </label>
                    <input type="text" id="document" name="document"
                           value="<?= htmlspecialchars($client['document'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- E-mail -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    E-mail do Cliente
                </label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($client['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Endereço -->
            <div>
                <label for="address" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Endereço Completo
                </label>
                <input type="text" id="address" name="address"
                       value="<?= htmlspecialchars($client['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <!-- Observações Internas -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Observações Internas da Oficina
                </label>
                <textarea id="notes" name="notes" rows="2"
                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"><?= htmlspecialchars($client['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <!-- Botões de Ação -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes') ?>" 
                   class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm shadow-lg shadow-indigo-600/30 transition-all">
                    Salvar Alterações &rarr;
                </button>
            </div>

        </form>
    </div>

</div>
