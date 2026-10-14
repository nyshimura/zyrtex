<?php
/**
 * View: Ficha do Cliente & Prontuário de Atendimentos
 */
$client = $client ?? [];
$orders = $orders ?? [];
$cleanPhone = preg_replace('/\D/', '', $client['phone'] ?? '');
?>

<div class="space-y-6">

    <!-- Header com Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div class="flex items-center gap-4">
            <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes') ?>" 
               class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                &larr;
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span><?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        Cliente #<?= (int)$client['id'] ?>
                    </span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    Cadastrado em <?= date('d/m/Y \à\s H:i', strtotime($client['created_at'])) ?>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <?php if (strlen($cleanPhone) >= 10): ?>
                <a href="https://wa.me/55<?= $cleanPhone ?>" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md transition">
                    <span>💬 WhatsApp</span>
                </a>
            <?php endif; ?>

            <a href="<?= \Zyrtex\Core\Response::url('/admin/os/nova?client_id=' . (int)$client['id']) ?>" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-md transition">
                <span>+ Abrir Nova OS</span>
            </a>

            <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/editar?id=' . (int)$client['id']) ?>" 
               class="p-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition"
               title="Editar Dados">
                ✏️
            </a>
        </div>
    </div>

    <!-- Cards de Dados e Resumo -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Contato e Documento -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Contatos & Documentos</h3>
            <div class="space-y-1.5 text-xs text-slate-300">
                <p><strong>WhatsApp:</strong> <?= htmlspecialchars($client['phone'], ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>CPF/CNPJ:</strong> <?= htmlspecialchars($client['document'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></p>
                <p><strong>E-mail:</strong> <?= htmlspecialchars($client['email'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>

        <!-- Endereço -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Endereço de Atendimento</h3>
            <p class="text-xs text-slate-300 leading-relaxed">
                <?= nl2br(htmlspecialchars($client['address'] ?? 'Nenhum endereço cadastrado.', ENT_QUOTES, 'UTF-8')) ?>
            </p>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Resumo Operacional</h3>
            <div class="grid grid-cols-2 gap-2 text-center pt-1">
                <div class="p-2 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-xl font-black text-indigo-400"><?= count($orders) ?></span>
                    <span class="text-[10px] text-slate-400 block">Total de OS</span>
                </div>
                <?php 
                $totalSpent = array_reduce($orders, fn($carry, $o) => $carry + ($o['payment_status'] === 'paid' ? (float)$o['total_value'] : 0), 0);
                ?>
                <div class="p-2 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-sm font-black text-emerald-400">R$ <?= number_format($totalSpent, 2, ',', '.') ?></span>
                    <span class="text-[10px] text-slate-400 block">Total Pago</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Tabela de Histórico de Ordens de Serviço do Cliente -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
        <h2 class="text-base font-bold text-white mb-4">
            Histórico de Ordens de Serviço do Cliente
        </h2>

        <?php if (empty($orders)): ?>
            <div class="text-center py-10 border border-dashed border-slate-800 rounded-xl">
                <p class="text-xs text-slate-400">Nenhum atendimento ou orçamento registrado para este cliente ainda.</p>
                <a href="<?= \Zyrtex\Core\Response::url('/admin/os/nova?client_id=' . (int)$client['id']) ?>" 
                   class="inline-block mt-3 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">
                    + Abrir Primeira OS deste Cliente
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-950/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Nº OS</th>
                            <th class="py-3 px-4">Equipamento / Veículo</th>
                            <th class="py-3 px-4">Identificador</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Data</th>
                            <th class="py-3 px-4">Valor Total</th>
                            <th class="py-3 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        <?php foreach ($orders as $os): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-400">
                                    <?= htmlspecialchars($os['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-4 text-white">
                                    <?= htmlspecialchars($os['equipment_info'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-400">
                                    <?= htmlspecialchars($os['identifier_code'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                        <?= htmlspecialchars($os['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-400">
                                    <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-white">
                                    R$ <?= number_format((float)$os['total_value'], 2, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?= \Zyrtex\Core\Response::url('/admin/os/ver?id=' . (int)$os['id']) ?>" 
                                       class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition">
                                        Abrir OS
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
