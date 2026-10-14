<?php
/**
 * View: Listagem de Clientes
 */
$clients = $clients ?? [];
$search = $search ?? '';
?>

<div class="space-y-6">

    <!-- Header e Botão Novo Cliente -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Gestão de Clientes
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Controle de cadastros, contatos e histórico de ordens de serviço.
            </p>
        </div>

        <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/novo') ?>" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all transform hover:-translate-y-0.5">
            <span class="text-lg">+</span>
            <span>Novo Cliente</span>
        </a>
    </div>

    <!-- Barra de Busca -->
    <form method="GET" action="<?= \Zyrtex\Core\Response::url('/admin/clientes') ?>" class="flex gap-3">
        <div class="relative flex-1">
            <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="Buscar por nome, telefone, CPF/CNPJ ou e-mail..."
                   class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <span class="absolute left-4 top-3.5 text-slate-500">🔍</span>
        </div>
        <button type="submit" 
                class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-bold text-white transition">
            Buscar
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes') ?>" 
               class="px-4 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-sm text-slate-400 hover:text-white border border-slate-800 transition">
                Limpar
            </a>
        <?php endif; ?>
    </form>

    <!-- Tabela de Clientes -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <?php if (empty($clients)): ?>
            <div class="text-center py-16 px-4">
                <span class="text-4xl block mb-3">👥</span>
                <h3 class="text-base font-bold text-white mb-1">Nenhum cliente encontrado</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-5">
                    <?= !empty($search) ? 'Nenhum resultado corresponde à sua pesquisa.' : 'Cadastre seu primeiro cliente para agilizar a abertura de Ordens de Serviço.' ?>
                </p>
                <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/novo') ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">
                    + Cadastrar Primeiro Cliente
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-950/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Nome</th>
                            <th class="py-3 px-4">WhatsApp / Telefone</th>
                            <th class="py-3 px-4">Documento</th>
                            <th class="py-3 px-4">E-mail</th>
                            <th class="py-3 px-4 text-center">Total OS</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        <?php foreach ($clients as $c): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-bold text-white">
                                    <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/ver?id=' . (int)$c['id']) ?>" class="hover:text-indigo-400 transition">
                                        <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span><?= htmlspecialchars($c['phone'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php 
                                        $cleanPhone = preg_replace('/\D/', '', $c['phone']);
                                        if (strlen($cleanPhone) >= 10): 
                                        ?>
                                            <a href="https://wa.me/55<?= $cleanPhone ?>" target="_blank" title="Abrir conversa no WhatsApp"
                                               class="text-emerald-400 hover:text-emerald-300 text-xs">
                                                💬
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">
                                    <?= htmlspecialchars($c['document'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 text-xs">
                                    <?= htmlspecialchars($c['email'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        <?= (int)($c['total_os'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/ver?id=' . (int)$c['id']) ?>" 
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 hover:text-white transition"
                                       title="Ver Ficha e Histórico">
                                        Ficha
                                    </a>
                                    <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/editar?id=' . (int)$c['id']) ?>" 
                                       class="px-2.5 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 text-xs font-semibold transition"
                                       title="Editar Dados">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
