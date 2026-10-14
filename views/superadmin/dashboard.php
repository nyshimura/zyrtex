<div class="space-y-6">

    <!-- Header do Dashboard -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>📊</span>
                <span>Dashboard Global &bull; Zyrtex SaaS</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Monitoramento consolidado de todas as oficinas, receita bruta, ordens de serviço e chamados.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tenants') ?>" 
               class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5">
                <span>🏢</span>
                <span>Ver Todas as Oficinas</span>
            </a>
        </div>
    </div>

    <!-- Grade de Métricas Principais (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card: Total de Oficinas -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Oficinas / Tenants</span>
                <span class="text-lg">🏢</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-white font-mono"><?= $totalTenants ?></span>
                <span class="text-xs text-emerald-400 font-bold"><?= $activeTenants ?> ativas</span>
            </div>
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
                <span>Suspensas: <strong class="text-rose-400"><?= $suspendedTenants ?></strong></span>
                <span>Plano Premium: <strong class="text-amber-400"><?= $premiumTenants ?></strong></span>
            </div>
        </div>

        <!-- Card: Faturamento do SaaS -->
        <a href="<?= \Zyrtex\Core\Response::url('/' . \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug() . '/financeiro') ?>" 
           class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2 shadow-sm hover:border-emerald-500/50 transition group block">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-emerald-300 transition">Receita Bruta do SaaS</span>
                <span class="text-lg">💰</span>
            </div>
            <div class="text-3xl font-black text-emerald-400 font-mono">
                R$ <?= number_format($totalRevenue, 2, ',', '.') ?>
            </div>
            <div class="text-[11px] text-slate-400 pt-2 border-t border-slate-800/80 flex items-center justify-between">
                <span>Assinaturas &amp; Taxas</span>
                <span class="text-indigo-400 group-hover:underline font-bold">Painel MRR &rarr;</span>
            </div>
        </a>

        <!-- Card: Total de OSs -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ordens de Serviço</span>
                <span class="text-lg">🔧</span>
            </div>
            <div class="text-3xl font-black text-cyan-400 font-mono">
                <?= $totalOrders ?>
            </div>
            <p class="text-[11px] text-slate-400 pt-2 border-t border-slate-800/80">
                Aparelhos reparados em toda a rede Zyrtex
            </p>
        </div>

        <!-- Card: Tickets de Suporte -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-2 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Chamados Abertos</span>
                <span class="text-lg">💬</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black text-amber-400 font-mono"><?= $openTickets ?></span>
                <span class="text-xs text-slate-400">pendentes</span>
            </div>
            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tickets') ?>" class="text-indigo-400 hover:underline font-bold">
                    Acessar Caixa de Entrada &rarr;
                </a>
            </div>
        </div>

    </div>

    <!-- Grid Duplo: Oficinas Recentes & Transações Recentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Tabela: Oficinas Recentes -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span>🏢</span>
                    <span>Últimas Oficinas Cadastradas</span>
                </h2>
                <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tenants') ?>" class="text-xs text-indigo-400 hover:underline">
                    Ver todas &rarr;
                </a>
            </div>

            <?php if (empty($recentTenants)): ?>
                <div class="p-4 rounded-xl bg-slate-950 text-center text-xs text-slate-500">
                    Nenhuma oficina cadastrada até o momento.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Oficina</th>
                                <th class="py-2.5 px-3">Nicho</th>
                                <th class="py-2.5 px-3 text-center">OSs</th>
                                <th class="py-2.5 px-3">Plano</th>
                                <th class="py-2.5 px-3 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            <?php foreach ($recentTenants as $rt): ?>
                                <tr class="hover:bg-slate-800/20">
                                    <td class="py-2.5 px-3 font-bold text-white">
                                        <div class="flex items-center gap-2">
                                            <span><?= htmlspecialchars($rt['business_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                            <?php if ($rt['active'] == 0): ?>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] bg-rose-500/20 text-rose-300">Suspensa</span>
                                            <?php endif; ?>
                                        </div>
                                        <code class="text-[10px] text-slate-500 font-mono">/<?= htmlspecialchars($rt['slug'], ENT_QUOTES, 'UTF-8') ?></code>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-400 uppercase text-[10px] font-bold">
                                        <?= htmlspecialchars($rt['niche'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-400">
                                        <?= (int)$rt['os_count'] ?>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <?php if ($rt['is_premium']): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/20">
                                                ★ Premium
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400">
                                                Free
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <a href="<?= \Zyrtex\Core\Response::url('/superadmin/shadow-login?id=' . (int)$rt['id']) ?>"
                                           class="px-2.5 py-1 rounded-lg bg-indigo-600/80 hover:bg-indigo-600 text-white text-[10px] font-bold transition">
                                            👁️ Shadow
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabela: Transações Recentes -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span>💳</span>
                    <span>Últimas Transações do SaaS</span>
                </h2>
                <span class="text-xs text-slate-400">Mercado Pago</span>
            </div>

            <?php if (empty($recentTransactions)): ?>
                <div class="p-4 rounded-xl bg-slate-950 text-center text-xs text-slate-500">
                    Nenhuma transação registrada no SaaS ainda.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Oficina</th>
                                <th class="py-2.5 px-3">Plano / Taxa</th>
                                <th class="py-2.5 px-3">Valor</th>
                                <th class="py-2.5 px-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            <?php foreach ($recentTransactions as $tx): ?>
                                <tr class="hover:bg-slate-800/20">
                                    <td class="py-2.5 px-3 font-bold text-white">
                                        <?= htmlspecialchars($tx['business_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-400 text-xs">
                                        <?= htmlspecialchars($tx['plan_type'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-2.5 px-3 font-mono font-bold text-white">
                                        R$ <?= number_format((float)$tx['amount'], 2, ',', '.') ?>
                                    </td>
                                    <td class="py-2.5 px-3 text-right">
                                        <?php if ($tx['status'] === 'approved'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                ✓ Pago
                                            </span>
                                        <?php elseif ($tx['status'] === 'refunded' || (int)$tx['is_refunded'] === 1): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                                                ↩ Estorno
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                                ⏳ Pendente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>
