<?php
/**
 * View: Listagem de Ordens de Serviço
 */
$orders = $orders ?? [];
$statusFilter = $statusFilter ?? '';
$search = $search ?? '';
$countsMap = $countsMap ?? [];

$statusLabels = [
    '' => 'Todas',
    'budget' => 'Orçamentos',
    'approved' => 'Aprovadas',
    'in_progress' => 'Em Reparo',
    'waiting_parts' => 'Aguard. Peça',
    'ready' => 'Prontas',
    'delivered' => 'Entregues',
    'cancelled' => 'Canceladas'
];
?>

<div class="space-y-6">

    <!-- Header e Botão Nova OS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Ordens de Serviço
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Controle operacional de bancada, diagnósticos e entregas da oficina.
            </p>
        </div>

        <a href="<?= \Zyrtex\Core\Response::url('/admin/os/nova') ?>" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all transform hover:-translate-y-0.5">
            <span class="text-lg">+</span>
            <span>Nova Ordem de Serviço</span>
        </a>
    </div>

    <!-- Abas de Filtro Rápido por Status -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <?php foreach ($statusLabels as $sKey => $sLabel): 
            $isActive = ($statusFilter === $sKey);
            $count = empty($sKey) ? array_sum($countsMap) : ($countsMap[$sKey] ?? 0);
        ?>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os' . ($sKey !== '' ? '?status=' . $sKey : '')) ?>" 
               class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition flex items-center gap-2 border <?= $isActive ? 'bg-indigo-600 text-white border-indigo-500 shadow-sm' : 'bg-slate-900 text-slate-400 hover:text-white border-slate-800 hover:bg-slate-800' ?>">
                <span><?= $sLabel ?></span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] <?= $isActive ? 'bg-indigo-900 text-indigo-200' : 'bg-slate-800 text-slate-400' ?>">
                    <?= $count ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Barra de Busca -->
    <form method="GET" action="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" class="flex gap-3">
        <?php if (!empty($statusFilter)): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter, ENT_QUOTES, 'UTF-8') ?>">
        <?php endif; ?>

        <div class="relative flex-1">
            <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="Buscar por Nº OS, IMEI/Serial/Placa, aparelho ou nome do cliente..."
                   class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <span class="absolute left-4 top-3.5 text-slate-500">🔍</span>
        </div>
        <button type="submit" 
                class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-bold text-white transition">
            Buscar
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os' . (!empty($statusFilter) ? '?status=' . $statusFilter : '')) ?>" 
               class="px-4 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-sm text-slate-400 hover:text-white border border-slate-800 transition">
                Limpar
            </a>
        <?php endif; ?>
    </form>

    <!-- Tabela de OS -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <?php if (empty($orders)): ?>
            <div class="text-center py-16 px-4">
                <span class="text-4xl block mb-3">📋</span>
                <h3 class="text-base font-bold text-white mb-1">Nenhuma Ordem de Serviço encontrada</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-5">
                    <?= !empty($search) || !empty($statusFilter) ? 'Nenhuma OS corresponde aos filtros selecionados.' : 'Cadastre sua primeira OS para imprimir comprovantes e registrar o checklist técnico.' ?>
                </p>
                <a href="<?= \Zyrtex\Core\Response::url('/admin/os/nova') ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">
                    + Abrir Nova OS
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-950/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Nº OS</th>
                            <th class="py-3 px-4">Cliente</th>
                            <th class="py-3 px-4">Equipamento / Identificador</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Data Entrada</th>
                            <th class="py-3 px-4">Valor Total</th>
                            <th class="py-3 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        <?php foreach ($orders as $os): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-400">
                                    <a href="<?= \Zyrtex\Core\Response::url('/admin/os/ver?id=' . (int)$os['id']) ?>" class="hover:underline">
                                        <?= htmlspecialchars($os['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-white">
                                    <div><?= htmlspecialchars($os['client_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-[11px] text-slate-500"><?= htmlspecialchars($os['client_phone'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-white"><?= htmlspecialchars($os['equipment_info'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-[11px] font-mono text-slate-400"><?= htmlspecialchars($os['identifier_code'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php
                                    $statusMap = [
                                        'budget' => ['label' => 'Orçamento', 'class' => 'bg-amber-500/10 text-amber-400 border-amber-500/20'],
                                        'approved' => ['label' => 'Aprovado', 'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
                                        'in_progress' => ['label' => 'Em Reparo', 'class' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'],
                                        'waiting_parts' => ['label' => 'Aguard. Peça', 'class' => 'bg-purple-500/10 text-purple-400 border-purple-500/20'],
                                        'ready' => ['label' => 'Pronto', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                                        'delivered' => ['label' => 'Entregue', 'class' => 'bg-slate-700/50 text-slate-300 border-slate-600'],
                                        'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'],
                                    ];
                                    $s = $statusMap[$os['status']] ?? ['label' => $os['status'], 'class' => 'bg-slate-800 text-slate-300 border-slate-700'];
                                    ?>
                                    <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border <?= $s['class'] ?>">
                                        <?= $s['label'] ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-400">
                                    <?= date('d/m/Y', strtotime($os['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-white">
                                    R$ <?= number_format((float)$os['total_value'], 2, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir?id=' . (int)$os['id']) ?>" target="_blank"
                                           title="Imprimir Cupom da OS"
                                           class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs border border-slate-700 transition">
                                            🖨️
                                        </a>
                                        <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir?id=' . (int)$os['id'] . '&formato=label') ?>" target="_blank"
                                           title="Imprimir Mini Etiqueta QR"
                                           class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-400 hover:text-white text-xs border border-slate-700 transition">
                                            🏷️
                                        </a>
                                        <a href="<?= \Zyrtex\Core\Response::url('/admin/os/ver?id=' . (int)$os['id']) ?>" 
                                           class="px-3 py-1.5 rounded-lg bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 text-xs font-semibold transition">
                                            Gerenciar &rarr;
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
