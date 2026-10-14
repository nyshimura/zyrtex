<?php
/**
 * View: Dashboard Administrativo da Oficina
 */
$user = $user ?? [];
$tenant = $tenant ?? [];
$kpis = $kpis ?? ['open_os' => 0, 'ready_os' => 0, 'clients' => 0, 'revenue' => 0.0];
$recentOrders = $recentOrders ?? [];
?>

<div class="space-y-8">
    
    <!-- Header de Boas-Vindas e Ação Rápida -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Painel da Oficina
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Visão operacional em tempo real de <strong><?= htmlspecialchars($tenant['business_name'] ?? 'Sua Oficina', ENT_QUOTES, 'UTF-8') ?></strong>.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os/nova') ?>" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all transform hover:-translate-y-0.5">
                <span class="text-lg">+</span>
                <span>Nova Ordem de Serviço</span>
            </a>
        </div>
    </div>

    <!-- BANNER DE ACESSO & COMPARTILHAMENTO DA PÁGINA PÚBLICA / ÁREA DO CLIENTE -->
    <?php 
    $publicStoreUrl = \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? 'demo-assistencia');
    $whatsText = "Olá! Acompanhe o status do seu aparelho e consulte orçamentos em nossa área do cliente: " . $publicStoreUrl;
    $whatsLink = "https://wa.me/?text=" . urlencode($whatsText);
    ?>
    <div class="p-5 rounded-2xl bg-gradient-to-r from-indigo-950/60 via-slate-900 to-slate-900 border border-indigo-500/30 shadow-xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center text-2xl shrink-0 border border-indigo-500/30">
                🌐
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-extrabold text-white">Sua Página Pública & Área do Cliente</h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">ONLINE</span>
                </div>
                <p class="text-xs text-slate-400 mt-0.5">
                    Envie este link para os clientes acompanharem orçamentos, diagnósticos e fotos da bancada:
                </p>
                <code class="text-xs font-mono text-indigo-300 font-bold block mt-1 select-all"><?= $publicStoreUrl ?></code>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button type="button" onclick="navigator.clipboard.writeText('<?= $publicStoreUrl ?>'); this.innerText='✓ Copiado!'; setTimeout(() => this.innerText='📋 Copiar Link', 2000);"
                    class="px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                📋 Copiar Link
            </button>

            <a href="<?= $whatsLink ?>" target="_blank"
               class="px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition flex items-center gap-1.5">
                <span>💬 Enviar no WhatsApp</span>
            </a>

            <a href="<?= $publicStoreUrl ?>" target="_blank"
               class="px-3 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-xs border border-slate-700 transition">
                <span>Ver Vitrine &nearr;</span>
            </a>
        </div>
    </div>

    <!-- 4 CARDS DE KPIS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: OS Abertas -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-indigo-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">OS em Andamento</span>
                <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg">⏳</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white"><?= (int)$kpis['open_os'] ?></span>
                <span class="text-xs text-slate-400">na bancada</span>
            </div>
        </div>

        <!-- Card 2: Prontas para Retirada -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-emerald-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Prontas p/ Retirada</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">✅</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-emerald-400"><?= (int)$kpis['ready_os'] ?></span>
                <span class="text-xs text-slate-400">notificar cliente</span>
            </div>
        </div>

        <!-- Card 3: Clientes Cadastrados -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-indigo-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Clientes Atendidos</span>
                <span class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg">👥</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white"><?= (int)$kpis['clients'] ?></span>
                <span class="text-xs text-slate-400">cadastrados</span>
            </div>
        </div>

        <!-- Card 4: Faturamento Realizado -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm relative overflow-hidden group hover:border-indigo-500/50 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Faturamento Pago</span>
                <span class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">💰</span>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-2xl font-black text-white">R$ <?= number_format((float)$kpis['revenue'], 2, ',', '.') ?></span>
            </div>
        </div>

    </div>

    <!-- TABELA: ORDENS DE SERVIÇO RECENTES -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-white">Ordens de Serviço Recentes</h2>
                <p class="text-xs text-slate-400">Últimos atendimentos gerados na sua bancada</p>
            </div>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" class="text-xs font-bold text-indigo-400 hover:text-indigo-300">
                Ver todas &rarr;
            </a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <div class="text-center py-12 border-2 border-dashed border-slate-800 rounded-xl">
                <span class="text-4xl block mb-3">🛠️</span>
                <h3 class="text-base font-bold text-white mb-1">Nenhuma Ordem de Serviço cadastrada ainda</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">
                    Cadastre o primeiro equipamento ou veículo trazido pelo seu cliente para gerar o prontuário digital e imprimir a etiqueta.
                </p>
                <a href="<?= \Zyrtex\Core\Response::url('/admin/os/nova') ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">
                    + Abrir Primeira OS
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
                            <th class="py-3 px-4">Valor Total</th>
                            <th class="py-3 px-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        <?php foreach ($recentOrders as $os): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-indigo-400">
                                    <?= htmlspecialchars($os['os_number'], ENT_QUOTES, 'UTF-8') ?>
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
                                <td class="py-3.5 px-4 font-bold text-white">
                                    R$ <?= number_format((float)$os['total_value'], 2, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="<?= \Zyrtex\Core\Response::url('/admin/os/ver?id=' . (int)$os['id']) ?>" 
                                       class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-white transition">
                                        Gerenciar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- MINI-TUTORIAIS / TIPS (Conforme solicitado pelo usuário com links diretos) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        
        <?php 
        \Zyrtex\Core\View::partial('tips_card', [
            'title' => 'Configuração de Pagamento (Pix Manual ou Mercado Pago)',
            'description' => 'Escolha como deseja receber dos seus clientes. Você pode ativar chave Pix manual ou gerar QR Code dinâmico via Mercado Pago com baixa automática.',
            'link' => \Zyrtex\Core\Response::url('/admin/configuracoes#pagamentos'),
            'linkText' => 'Acessar Configurações de Pagamento &rarr;'
        ]);

        \Zyrtex\Core\View::partial('tips_card', [
            'title' => 'Formatos de Impressão (Térmicas 80mm, 58mm e Mini QR Codes)',
            'description' => 'Imprima comprovantes com termos legais de garantia ou gere adesivos mini com QR Code para colar no aparelho do cliente.',
            'link' => \Zyrtex\Core\Response::url('/admin/configuracoes#impressao'),
            'linkText' => 'Configurar Modelos de Impressão &rarr;'
        ]);
        ?>

    </div>

</div>
