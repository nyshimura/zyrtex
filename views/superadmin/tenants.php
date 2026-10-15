<?php
$search = $search ?? '';
$statusFilter = $statusFilter ?? '';
$planFilter = $planFilter ?? '';
$tenants = $tenants ?? [];
$csrfField = $csrfField ?? \Zyrtex\Core\CSRF::field();
?>

<div class="space-y-6">

    <!-- Header & Filtros -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>🏢</span>
                <span>Gestão Global de Oficinas (Tenants)</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Controle de lojas cadastradas, suspensão de acesso em caso de inadimplência e personificação (Shadow Login).
            </p>
        </div>

        <span class="text-xs px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 font-bold self-start sm:self-auto">
            <?= count($tenants) ?> oficina(s) encontrada(s)
        </span>
    </div>

    <!-- Barra de Busca & Filtros Rápidos -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
        <form action="<?= \Zyrtex\Core\Response::url('/superadmin/tenants') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            
            <div class="sm:col-span-6">
                <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Buscar por nome da oficina ou endereço (/slug)..."
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs">
            </div>

            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                    <option value="">Status: Todos</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Somente Ativas</option>
                    <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>Somente Suspensas</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="plan" class="w-full px-3 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                    <option value="">Plano: Todos</option>
                    <option value="free" <?= $planFilter === 'free' ? 'selected' : '' ?>>Plano Free</option>
                    <option value="premium" <?= $planFilter === 'premium' ? 'selected' : '' ?>>Plano Premium</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" 
                        class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-md">
                    Filtrar
                </button>
                <?php if (!empty($search) || !empty($statusFilter) || !empty($planFilter)): ?>
                    <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tenants') ?>" 
                       class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                        Limpar
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- Tabela Master de Oficinas -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        
        <?php if (empty($tenants)): ?>
            <div class="p-12 text-center space-y-3">
                <span class="text-4xl">🏢</span>
                <h3 class="text-base font-bold text-white">Nenhuma oficina encontrada</h3>
                <p class="text-xs text-slate-400">Tente ajustar seus termos de busca ou filtros.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Oficina / Identificador</th>
                            <th class="py-3 px-4">Responsável & Contato</th>
                            <th class="py-3 px-4">Nicho</th>
                            <th class="py-3 px-4 text-center">Atividade (OS / Clientes)</th>
                            <th class="py-3 px-4">Plano</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Ações Globais</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 font-medium">
                        <?php foreach ($tenants as $t): 
                            $isActive = (int)$t['active'] === 1;
                            $isPrem = (int)$t['is_premium'] === 1;
                        ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white text-sm">
                                        <?= htmlspecialchars($t['business_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <a href="<?= \Zyrtex\Core\Response::url('/' . $t['slug']) ?>" target="_blank"
                                           class="font-mono text-xs text-indigo-400 hover:underline">
                                            /<?= htmlspecialchars($t['slug'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        <?php if ($isPrem): ?>
                                            <span class="text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-1.5 py-0.5 rounded font-bold">
                                                ★ Sem Anúncios
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-xs space-y-0.5">
                                    <span class="font-bold text-slate-200 block"><?= htmlspecialchars($t['owner_name'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="text-slate-400 block"><?= htmlspecialchars($t['owner_email'] ?? 'Sem email', ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (!empty($t['whatsapp_number'])): ?>
                                        <span class="text-slate-500 text-[11px] block">WhatsApp: <?= htmlspecialchars($t['whatsapp_number'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php endif; ?>
                                </td>

                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-800 text-slate-300 border border-slate-700">
                                        <?= htmlspecialchars($t['niche'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="font-mono font-bold text-slate-200">
                                        <?= (int)$t['total_os'] ?> <span class="text-slate-500 font-normal text-[11px]">OS(s)</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        <?= (int)$t['total_clients'] ?> clientes
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <form action="<?= \Zyrtex\Core\Response::url('/superadmin/tenants/premium') ?>" method="POST" class="inline">
                                        <?= $csrfField ?>
                                        <input type="hidden" name="tenant_id" value="<?= (int)$t['id'] ?>">
                                        <input type="hidden" name="is_premium" value="<?= $isPrem ? 0 : 1 ?>">
                                        
                                        <?php if ($isPrem): ?>
                                            <button type="submit" title="Clique para rebaixar para Free"
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 hover:bg-amber-500/30 transition flex items-center gap-1">
                                                <span>★</span> Premium
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" title="Clique para promover para Premium"
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700 hover:text-white transition">
                                                Plano Free
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>

                                <td class="py-3.5 px-4">
                                    <form id="tenant-status-form-<?= (int)$t['id'] ?>" action="<?= \Zyrtex\Core\Response::url('/superadmin/tenants/status') ?>" method="POST" class="inline">
                                        <?= $csrfField ?>
                                        <input type="hidden" name="tenant_id" value="<?= (int)$t['id'] ?>">
                                        <input type="hidden" name="status" value="<?= $isActive ? 0 : 1 ?>">

                                        <?php if ($isActive): ?>
                                            <button type="button" 
                                                    onclick="zyrtexConfirm({
                                                        title: 'Suspender Oficina',
                                                        badge: '<?= htmlspecialchars($t['business_name'] ?? $t['slug'] ?? 'Oficina', ENT_QUOTES, 'UTF-8') ?>',
                                                        icon: '⚠️',
                                                        messageHtml: '<p>Deseja realmente <strong>SUSPENDER</strong> o acesso desta oficina? Os operadores não conseguirão acessar até que seja reativada.</p>',
                                                        confirmText: 'Suspender Loja',
                                                        confirmClass: 'px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase transition shadow-lg shadow-rose-600/20 cursor-pointer',
                                                        onConfirm: () => document.getElementById('tenant-status-form-<?= (int)$t['id'] ?>').submit()
                                                    })"
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-rose-500/20 hover:text-rose-300 hover:border-rose-500/30 transition cursor-pointer"
                                                    title="Clique para suspender loja">
                                                ✓ Ativa
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    onclick="zyrtexConfirm({
                                                        title: 'Reativar Oficina',
                                                        badge: '<?= htmlspecialchars($t['business_name'] ?? $t['slug'] ?? 'Oficina', ENT_QUOTES, 'UTF-8') ?>',
                                                        icon: '✓',
                                                        messageHtml: '<p>Deseja realmente <strong>REATIVAR</strong> o acesso desta oficina à plataforma Zyrtex?</p>',
                                                        confirmText: 'Reativar Loja',
                                                        confirmClass: 'px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase transition shadow-lg shadow-emerald-500/20 cursor-pointer',
                                                        onConfirm: () => document.getElementById('tenant-status-form-<?= (int)$t['id'] ?>').submit()
                                                    })"
                                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 hover:bg-emerald-500/20 hover:text-emerald-300 transition cursor-pointer"
                                                    title="Clique para reativar loja">
                                                ✗ Suspensa
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Botão SHADOW LOGIN (Impersonation RF) -->
                                        <a href="<?= \Zyrtex\Core\Response::url('/superadmin/shadow-login?id=' . (int)$t['id']) ?>" 
                                           class="px-3 py-1.5 rounded-xl bg-indigo-600/90 hover:bg-indigo-600 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5"
                                           title="Entrar imediatamente como lojista">
                                            <span>👁️</span>
                                            <span>Shadow Login</span>
                                        </a>

                                        <!-- Link para Vitrine Pública -->
                                        <a href="<?= \Zyrtex\Core\Response::url('/' . $t['slug']) ?>" target="_blank"
                                           class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition"
                                           title="Ver Vitrine Pública">
                                            🌐
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
