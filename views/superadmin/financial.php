<?php
$saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
$currentUrl = \Zyrtex\Core\Response::url('/' . $saSlug . '/financeiro');
?>

<div class="space-y-8">

    <!-- Header & Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="w-8 h-8 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white font-black text-sm shadow-md shadow-emerald-500/20">
                    💰
                </span>
                <h1 class="text-2xl font-black text-white tracking-tight">
                    Painel Financeiro & Gestão de Receita (MRR)
                </h1>
                <?php if (!empty($versionData['version'])): ?>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700 font-mono">
                        v<?= htmlspecialchars($versionData['version'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-xs text-slate-400">
                Acompanhamento em tempo real de assinaturas, transações do Mercado Pago, projeções de MRR/ARR e taxas de retenção da plataforma.
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/financeiro/exportar') ?>" 
               class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 text-xs font-bold transition flex items-center gap-2 shadow-sm">
                <span>📥</span>
                <span>Exportar CSV</span>
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/parametros') ?>" 
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white text-xs font-bold transition flex items-center gap-2 shadow-lg shadow-indigo-600/30">
                <span>⚙️</span>
                <span>Tabela de Preços</span>
            </a>
        </div>
    </div>

    <!-- Cards de KPIs Executivos Principais -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Card 1: MRR (Receita Recorrente Mensal) -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">MRR Recorrente</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs">📈 Projeção</span>
            </div>
            <div class="text-3xl font-black text-white tracking-tight">
                R$ <?= number_format($mrr, 2, ',', '.') ?>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                <span>ARR Anualizado:</span>
                <span class="font-mono font-bold text-emerald-400">R$ <?= number_format($arr, 2, ',', '.') ?></span>
            </div>
            <div class="absolute -right-2 -bottom-2 w-16 h-16 bg-emerald-500/5 rounded-full blur-xl group-hover:scale-150 transition-all pointer-events-none"></div>
        </div>

        <!-- Card 2: Faturamento do Mês Atual -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Faturamento (Este Mês)</span>
                <span class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-xs">🗓️ <?= date('m/Y') ?></span>
            </div>
            <div class="text-3xl font-black text-white tracking-tight">
                R$ <?= number_format($currentMonthRevenue, 2, ',', '.') ?>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-400">vs Mês Anterior:</span>
                <?php if ($growthRate >= 0): ?>
                    <span class="font-bold text-emerald-400 flex items-center gap-0.5">
                        &uarr; +<?= number_format($growthRate, 1, ',', '.') ?>%
                    </span>
                <?php else: ?>
                    <span class="font-bold text-rose-400 flex items-center gap-0.5">
                        &darr; <?= number_format($growthRate, 1, ',', '.') ?>%
                    </span>
                <?php endif; ?>
            </div>
            <div class="absolute -right-2 -bottom-2 w-16 h-16 bg-indigo-500/5 rounded-full blur-xl group-hover:scale-150 transition-all pointer-events-none"></div>
        </div>

        <!-- Card 3: Faturamento Total Líquido -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Faturamento Acumulado</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 text-xs">💎 Total</span>
            </div>
            <div class="text-3xl font-black text-white tracking-tight">
                R$ <?= number_format($totalRevenue, 2, ',', '.') ?>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                <span>Ticket Médio:</span>
                <span class="font-mono font-bold text-purple-300">R$ <?= number_format($averageTicket, 2, ',', '.') ?></span>
            </div>
            <div class="absolute -right-2 -bottom-2 w-16 h-16 bg-purple-500/5 rounded-full blur-xl group-hover:scale-150 transition-all pointer-events-none"></div>
        </div>

        <!-- Card 4: Assinantes & Churn/Estornos -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-700 transition">
            <div class="flex items-center justify-between text-slate-400 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Lojas Pagantes</span>
                <span class="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 text-xs">🏢 Ativas</span>
            </div>
            <div class="text-3xl font-black text-white tracking-tight">
                <?= $totalSubscribers ?> <span class="text-sm font-normal text-slate-400">oficinas</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-400">Taxa de Estorno:</span>
                <span class="font-mono font-bold <?= $refundRate > 5 ? 'text-amber-400' : 'text-slate-300' ?>">
                    <?= number_format($refundRate, 1, ',', '.') ?>% (R$ <?= number_format($refundAmount, 2, ',', '.') ?>)
                </span>
            </div>
            <div class="absolute -right-2 -bottom-2 w-16 h-16 bg-cyan-500/5 rounded-full blur-xl group-hover:scale-150 transition-all pointer-events-none"></div>
        </div>

    </div>

    <!-- Seção Visual: Gráfico de Evolução (6 Meses) & Breakdown de Planos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Coluna 1 & 2: Gráfico de Barras de Faturamento dos Últimos 6 Meses -->
        <div class="lg:col-span-2 p-5 sm:p-7 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-6 overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>📊</span>
                        <span>Evolução de Faturamento Mensal (Últimos 6 Meses)</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Volume financeiro líquido aprovado mês a mês através do Mercado Pago.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Tempo Real
                </span>
            </div>

            <!-- Gráfico Visual Responsivo com Grid 6 Colunas (Zero Overflow) -->
            <div class="space-y-4 pt-2">
                <div class="grid grid-cols-6 gap-2 sm:gap-3 w-full h-52 items-end pt-4 overflow-hidden">
                    <?php 
                    $safeMax = max($maxMonthRev, 100.0);
                    foreach ($monthsHistory as $mh): 
                        $heightPercent = $mh['revenue'] > 0 ? max(round(($mh['revenue'] / $safeMax) * 100), 12) : 6;
                        $isCurrentMonth = ($mh['label'] === ($monthsHistory[count($monthsHistory)-1]['label'] ?? ''));
                    ?>
                        <div class="flex flex-col items-center gap-2 h-full justify-end min-w-0">
                            <!-- Valor monetário acima da barra -->
                            <div class="text-[9px] sm:text-[11px] font-mono font-bold text-center truncate w-full <?= $mh['revenue'] > 0 ? ($isCurrentMonth ? 'text-emerald-400' : 'text-indigo-300') : 'text-slate-600' ?>">
                                <?= $mh['revenue'] > 0 ? 'R$' . number_format($mh['revenue'], 0, ',', '.') : 'R$ 0' ?>
                            </div>

                            <!-- Barra com trilha de fundo -->
                            <div class="w-full h-36 bg-slate-950/90 rounded-xl border border-slate-800/80 flex items-end p-1 overflow-hidden relative group">
                                <div class="w-full rounded-md transition-all duration-300 <?= $isCurrentMonth ? 'bg-gradient-to-t from-emerald-600 to-teal-400 shadow-lg shadow-emerald-500/20' : ($mh['revenue'] > 0 ? 'bg-gradient-to-t from-indigo-700 to-indigo-500' : 'bg-slate-800/80 border-t border-slate-700/60') ?>"
                                     style="height: <?= $heightPercent ?>%;">
                                </div>
                                <!-- Tooltip flutuante no hover -->
                                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-slate-950/95 text-white text-[10px] p-1 text-center font-mono rounded-lg pointer-events-none">
                                    <?= $mh['count'] ?> <?= $mh['count'] === 1 ? 'transação' : 'transações' ?>
                                </div>
                            </div>

                            <!-- Label do Mês -->
                            <span class="text-[10px] sm:text-xs font-bold truncate text-center w-full <?= $isCurrentMonth ? 'text-emerald-400' : 'text-slate-400' ?>">
                                <?= $mh['label'] ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-800/80">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded bg-indigo-600 inline-block"></span> Meses Anteriores
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded bg-emerald-500 inline-block"></span> Mês Atual
                    </span>
                </div>
            </div>
        </div>

        <!-- Coluna 3: Breakdown de Receita por Categoria de Serviço -->
        <div class="p-6 sm:p-8 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-6 flex flex-col justify-between">
            <div>
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>🥧</span>
                        <span>Origem das Receitas</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Distribuição por modalidade de monetização.
                    </p>
                </div>

                <div class="space-y-4 mt-5">
                    <?php 
                    $safeTotalRev = max($totalRevenue, 1.0);
                    foreach ($breakdown as $key => $info): 
                        $pct = round(($info['total'] / $safeTotalRev) * 100, 1);
                    ?>
                        <div class="space-y-1.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-white flex items-center gap-1.5">
                                    <span><?= $info['icon'] ?></span>
                                    <span><?= $info['name'] ?></span>
                                </span>
                                <span class="font-mono text-emerald-400 font-bold">R$ <?= number_format($info['total'], 2, ',', '.') ?></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span><?= $info['count'] ?> pagamentos</span>
                                <span><?= $pct ?>% do faturamento</span>
                            </div>
                            <!-- Barra de progresso -->
                            <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400 rounded-full" style="width: <?= max($pct, 2) ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Box Informativo de Doações & AdSense -->
            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/80 text-xs text-slate-400 space-y-1">
                <span class="font-bold text-slate-300 block flex items-center gap-1.5">
                    <span>💡</span>
                    <span>Outras Fontes de Receita:</span>
                </span>
                <p>Anúncios do Google AdSense geram receita de tráfego direto na sua conta oficial da Google.</p>
                <p>Doações espontâneas via Pix (Buy Me a Coffee) caem diretamente na chave configurada.</p>
            </div>
        </div>

    </div>

    <!-- Tabela e Extrato de Transações com Filtros -->
    <div class="p-6 sm:p-8 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm space-y-6">
        
        <!-- Header da Tabela com Filtros Rápidos -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-800 pb-5">
            <div>
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span>📑</span>
                    <span>Extrato Detalhado de Assinaturas & Cobranças</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Histórico auditado de transações geradas via Pix e Cartão pelo Mercado Pago.
                </p>
            </div>

            <!-- Filtros de Status & Busca -->
            <form action="<?= $currentUrl ?>" method="GET" class="flex flex-wrap items-center gap-2.5 text-xs">
                
                <!-- Filtro de Status -->
                <select name="status" onchange="this.form.submit()" 
                        class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-slate-300 font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value="" <?= empty($filterStatus) ? 'selected' : '' ?>>Todos os Status</option>
                    <option value="approved" <?= $filterStatus === 'approved' ? 'selected' : '' ?>>Apenas Aprovados</option>
                    <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Apenas Pendentes</option>
                    <option value="refunded" <?= $filterStatus === 'refunded' ? 'selected' : '' ?>>Apenas Estornados</option>
                    <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Cancelados</option>
                </select>

                <!-- Filtro de Tipo de Plano -->
                <select name="plan" onchange="this.form.submit()" 
                        class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-slate-300 font-semibold focus:ring-2 focus:ring-indigo-500">
                    <option value="" <?= empty($filterPlan) ? 'selected' : '' ?>>Todos os Serviços</option>
                    <option value="annual_domain" <?= $filterPlan === 'annual_domain' ? 'selected' : '' ?>>Domínio Anual</option>
                    <option value="slug_change" <?= $filterPlan === 'slug_change' ? 'selected' : '' ?>>Troca de Slug</option>
                    <option value="niche_change" <?= $filterPlan === 'niche_change' ? 'selected' : '' ?>>Troca de Nicho</option>
                </select>

                <!-- Campo de Busca -->
                <div class="relative">
                    <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" 
                           placeholder="Buscar loja ou ID..."
                           class="pl-8 pr-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500">
                    <span class="absolute left-2.5 top-2.5 text-slate-500">🔍</span>
                </div>

                <button type="submit" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold transition">
                    Filtrar
                </button>

                <?php if (!empty($filterStatus) || !empty($filterPlan) || !empty($search)): ?>
                    <a href="<?= $currentUrl ?>" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold transition">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabela -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Oficina / Lojista</th>
                        <th class="py-3 px-4">Serviço / Plano</th>
                        <th class="py-3 px-4">Valor</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">ID Mercado Pago</th>
                        <th class="py-3 px-4">Data Pagamento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-medium">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                Nenhuma transação encontrada com os filtros selecionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 font-mono text-slate-500">
                                    #<?= $tx['id'] ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-white"><?= htmlspecialchars($tx['business_name'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="text-[11px] text-slate-500 font-mono">/<?= htmlspecialchars($tx['tenant_slug'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <?php 
                                    $planName = match($tx['plan_type']) {
                                        'annual_domain' => 'Domínio Próprio Anual',
                                        'slug_change' => 'Troca de Rota / Slug',
                                        'niche_change' => 'Readequação de Nicho',
                                        default => $tx['plan_type']
                                    };
                                    ?>
                                    <span class="font-semibold text-slate-300"><?= $planName ?></span>
                                    <?php if (!empty($tx['target_value'])): ?>
                                        <div class="text-[11px] text-indigo-400 font-mono truncate max-w-xs">
                                            <?= htmlspecialchars($tx['target_value'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-white text-sm">
                                    R$ <?= number_format((float)$tx['amount'], 2, ',', '.') ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($tx['is_refunded']): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span>↩️</span> Estornado
                                        </span>
                                    <?php elseif ($tx['status'] === 'approved'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span>✓</span> Aprovado
                                        </span>
                                    <?php elseif ($tx['status'] === 'pending'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <span>⏳</span> Pendente
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                            <?= htmlspecialchars($tx['status'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-400">
                                    <?= htmlspecialchars($tx['mp_payment_id'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3 px-4 text-slate-400 text-[11px]">
                                    <?= !empty($tx['purchased_at']) ? date('d/m/Y H:i', strtotime($tx['purchased_at'])) : date('d/m/Y H:i', strtotime($tx['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>
