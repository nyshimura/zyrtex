<?php
/**
 * View: Portal do Cliente (RF15)
 * Área do cliente final para consulta de histórico de aparelhos,
 * aprovação digital de orçamentos em 1 clique e extrato financeiro com Pix/Mercado Pago.
 */
$csrf = \Zyrtex\Core\CSRF::token();
$tenant = $tenant ?? [];
$client = $client ?? null;
$orders = $orders ?? [];
$selectedOs = $selectedOs ?? null;
$searchQuery = $searchQuery ?? '';
$totalPending = $totalPending ?? 0.0;
$totalPaid = $totalPaid ?? 0.0;
$activeCount = $activeCount ?? 0;
$pixQrCodeSvg = $pixQrCodeSvg ?? null;
$nicheConfig = $nicheConfig ?? [];

$statusMap = [
    'budget' => ['label' => 'Orçamento Gerado', 'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30'],
    'approved' => ['label' => 'Aprovado / Na Fila', 'class' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30'],
    'in_progress' => ['label' => 'Em Manutenção na Bancada', 'class' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/30'],
    'waiting_parts' => ['label' => 'Aguardando Peça', 'class' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30'],
    'ready' => ['label' => 'Pronto para Retirada', 'class' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'],
    'delivered' => ['label' => 'Entregue / Concluído', 'class' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30'],
    'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30'],
];

$cleanShopPhone = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? '');
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Topbar / Boas-vindas do Portal -->
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 mb-2">
                <span>🛡️ Portal Exclusivo do Cliente</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                <?= $client ? 'Olá, ' . htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') . '!' : 'Portal do Cliente' ?>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                <?= $client 
                    ? 'Acompanhe todos os seus aparelhos, aprove orçamentos em 1 clique e consulte seus pagamentos na ' . htmlspecialchars($tenant['business_name'] ?? 'Oficina', ENT_QUOTES, 'UTF-8') . '.'
                    : 'Acesse seu histórico completo de serviços e faturas informando seu CPF, CNPJ ou código de Ordem de Serviço.' ?>
            </p>
        </div>

        <!-- Formulário de Consulta / Troca de Documento -->
        <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal') ?>" method="GET" class="w-full md:w-auto shrink-0 flex items-center gap-2">
            <div class="relative flex-1 md:w-64">
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" 
                       placeholder="Seu CPF, CNPJ ou OS..." required
                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <span class="absolute left-3 top-3 text-slate-400 text-xs">🔍</span>
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition shrink-0">
                Acessar &rarr;
            </button>
        </form>
    </div>

    <?php if (!$client): ?>
        <!-- Tela de Boas-vindas quando nenhum cliente foi identificado -->
        <div class="bg-gradient-to-br from-indigo-950/20 via-slate-900 to-slate-950 rounded-3xl p-8 sm:p-14 border border-indigo-500/20 text-center space-y-6">
            <div class="w-20 h-20 mx-auto rounded-3xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-4xl shadow-inner">
                📱
            </div>
            <div class="max-w-lg mx-auto space-y-2">
                <h2 class="text-xl sm:text-2xl font-black text-white">Consulte seus Aparelhos e Faturas</h2>
                <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                    Aqui você acompanha o laudo em tempo real de cada celular, computador ou equipamento deixado em nossa assistência, com total transparência e aprovação digital rápida.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-2xl mx-auto pt-4 text-left">
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1.5">
                    <span class="text-xl">⚡</span>
                    <h4 class="text-xs font-bold text-white">Aprovação em 1 Clique</h4>
                    <p class="text-[11px] text-slate-400 leading-tight">Autorize o reparo imediatamente do seu celular sem precisar vir até a loja.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1.5">
                    <span class="text-xl">📸</span>
                    <h4 class="text-xs font-bold text-white">Prontuário com Fotos</h4>
                    <p class="text-[11px] text-slate-400 leading-tight">Veja fotos da bancada, peças substituídas e laudo de garantia.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1.5">
                    <span class="text-xl">💳</span>
                    <h4 class="text-xs font-bold text-white">Pagamento Pix & MP</h4>
                    <p class="text-[11px] text-slate-400 leading-tight">Pague com Pix Copia e Cola instantâneo para agilizar a retirada.</p>
                </div>
            </div>
        </div>

    <?php else: ?>

        <!-- Painel do Cliente Identificado -->

        <!-- Cards de Resumo Rápido (KPIs do Cliente) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Meus Aparelhos</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-0.5 block">
                        <?= count($orders) ?>
                    </span>
                    <span class="text-[10px] text-slate-500">Ordens de serviço registradas</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                    📱
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Em Andamento</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono mt-0.5 block">
                        <?= $activeCount ?>
                    </span>
                    <span class="text-[10px] text-slate-500">Serviços ativos na oficina</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
                    ⚙️
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Faturas em Aberto</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block">
                        R$ <?= number_format($totalPending, 2, ',', '.') ?>
                    </span>
                    <span class="text-[10px] text-slate-500">Pendente de pagamento</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                    💳
                </div>
            </div>

        </div>

        <!-- Navegação em Abas do Portal (Aparelhos / Extrato Financeiro) -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <button type="button" onclick="switchPortalTab('aparelhos')" id="portal-tab-btn-aparelhos"
                    class="portal-tab-btn px-4 py-2.5 rounded-xl font-bold text-xs transition flex items-center gap-2 bg-indigo-600 text-white shadow-sm">
                <span>📱 Meus Aparelhos & Histórico</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-mono"><?= count($orders) ?></span>
            </button>

            <button type="button" onclick="switchPortalTab('financeiro')" id="portal-tab-btn-financeiro"
                    class="portal-tab-btn px-4 py-2.5 rounded-xl font-bold text-xs transition flex items-center gap-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <span>💳 Extrato & Pagamentos</span>
                <?php if ($totalPending > 0): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-500 font-mono font-bold">Pendente</span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ================================================================= -->
        <!-- ABA 1: MEUS APARELHOS & HISTÓRICO DE ORDENS DE SERVIÇO             -->
        <!-- ================================================================= -->
        <div id="portal-tab-aparelhos" class="portal-tab-pane space-y-4">
            
            <?php if (empty($orders)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-10 text-center text-slate-400 space-y-2 border border-slate-200 dark:border-slate-700">
                    <p class="text-base font-bold text-slate-900 dark:text-white">Nenhum aparelho encontrado para este documento.</p>
                    <p class="text-xs text-slate-500">Se você acabou de dar entrada no balcão, aguarde alguns instantes ou confira com a equipe.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $ord): 
                        $st = $statusMap[$ord['status']] ?? ['label' => $ord['status'], 'class' => 'bg-slate-100 text-slate-600 border-slate-300'];
                        $isBudget = ($ord['status'] === 'budget');
                        $hasPendingParts = ((int)$ord['pending_parts_count'] > 0);
                        $needsClientAction = ($isBudget || $hasPendingParts);
                    ?>
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border <?= $needsClientAction ? 'border-amber-400 dark:border-amber-500/60 shadow-md ring-2 ring-amber-400/20' : 'border-slate-200/80 dark:border-slate-700' ?> overflow-hidden transition hover:border-indigo-500/40">
                            
                            <!-- Cabeçalho do Card da OS -->
                            <div class="p-5 sm:p-6 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-lg shrink-0">
                                            <?= $needsClientAction ? '⚠️' : '🔧' ?>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-black text-lg text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($ord['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold border <?= $st['class'] ?>">
                                                    <?= $st['label'] ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Entrada em <?= date('d/m/Y \à\s H:i', strtotime($ord['created_at'])) ?> &bull; Garantia de <?= (int)$ord['warranty_days'] ?> dias
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right flex sm:flex-col justify-between items-center sm:items-end">
                                        <span class="text-[11px] uppercase tracking-wider text-slate-400 block font-semibold">Valor do Reparo:</span>
                                        <span class="text-xl font-black text-slate-900 dark:text-white font-mono">
                                            R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Detalhes do Aparelho & Defeito -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900 text-xs">
                                    <div>
                                        <span class="text-slate-400 block font-semibold">Equipamento / Modelo:</span>
                                        <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($ord['equipment_info'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block font-semibold"><?= htmlspecialchars($nicheConfig['identifier_name'] ?? 'IMEI / Serial', ENT_QUOTES, 'UTF-8') ?>:</span>
                                        <code class="font-mono text-indigo-600 dark:text-indigo-400 font-bold"><?= htmlspecialchars($ord['identifier_code'], ENT_QUOTES, 'UTF-8') ?></code>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block font-semibold">Situação Financeira:</span>
                                        <span class="<?= $ord['payment_status'] === 'paid' ? 'text-emerald-500 font-bold' : 'text-amber-500 font-semibold' ?>">
                                            <?= $ord['payment_status'] === 'paid' ? '✓ Pago / Quitado' : '⏳ Pendente na Retirada' ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Alerta & Botão de Aprovação em 1 Clique (RF15) -->
                                <?php if ($isBudget): ?>
                                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-3">
                                        <div class="text-xs text-amber-800 dark:text-amber-200">
                                            <p class="font-extrabold text-sm text-amber-900 dark:text-amber-300">Orçamento Aguardando Sua Aprovação</p>
                                            <p class="mt-0.5">Nossos técnicos finalizaram o diagnóstico. Autorize para iniciarmos o reparo imediatamente.</p>
                                        </div>
                                        <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal/aprovar') ?>" method="POST"
                                              onsubmit="return confirm('Confirma a aprovação deste orçamento no valor de R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>?');"
                                              class="shrink-0 w-full sm:w-auto">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="os_id" value="<?= (int)$ord['id'] ?>">
                                            <input type="hidden" name="client_doc" value="<?= htmlspecialchars($client['document'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <button type="submit" 
                                                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                                <span>✓ Aprovar Orçamento (1 Clique)</span>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <!-- Botões de Ação da OS -->
                                <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-700/60">
                                    <span class="text-[11px] text-slate-400">
                                        <?= (int)$ord['media_count'] ?> foto(s) anexada(s) no prontuário
                                    </span>

                                    <div class="flex items-center gap-2">
                                        <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio?os=' . urlencode($ord['os_number'])) ?>" 
                                           class="px-4 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 transition flex items-center gap-1.5">
                                            <span>🔍 Ver Prontuário & Fotos &rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- ================================================================= -->
        <!-- ABA 2: EXTRATO FINANCEIRO & PAGAMENTO PIX / MERCADO PAGO          -->
        <!-- ================================================================= -->
        <div id="portal-tab-financeiro" class="portal-tab-pane hidden space-y-6">

            <!-- Card de Pagamento Imediato via Pix (se houver pendência) -->
            <?php if ($totalPending > 0 && !empty($tenant['pix_key'])): ?>
                <div class="p-6 rounded-2xl bg-gradient-to-br from-emerald-950/30 via-slate-900 to-slate-950 border border-emerald-500/30 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block">Pagamento Facilitado via Pix</span>
                            <h3 class="text-lg font-bold text-white mt-0.5">Pague seu serviço e agilize a liberação na bancada</h3>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block font-semibold">Total a Pagar:</span>
                            <span class="text-2xl font-black text-emerald-400 font-mono">
                                R$ <?= number_format($totalPending, 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center pt-2 border-t border-slate-800">
                        
                        <!-- QR Code Pix -->
                        <?php if (!empty($pixQrCodeSvg)): ?>
                            <div class="md:col-span-4 flex flex-col items-center text-center space-y-2">
                                <div class="p-3 bg-white rounded-2xl shadow-md inline-block">
                                    <img src="<?= htmlspecialchars($pixQrCodeSvg, ENT_QUOTES, 'UTF-8') ?>" alt="QR Code Pix" class="w-40 h-40">
                                </div>
                                <span class="text-[11px] text-slate-400">Escaneie com o app do seu banco</span>
                            </div>
                        <?php endif; ?>

                        <!-- Chave Copia e Cola & Dados do Favorecido -->
                        <div class="md:col-span-8 space-y-3">
                            <div class="space-y-1">
                                <label class="block text-[11px] font-bold text-slate-300 uppercase">
                                    Chave Pix da Oficina (<?= htmlspecialchars($tenant['pix_key_type'] ?? 'Chave', ENT_QUOTES, 'UTF-8') ?>):
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="text" id="pix_key_display" readonly value="<?= htmlspecialchars($tenant['pix_key'], ENT_QUOTES, 'UTF-8') ?>"
                                           class="flex-1 px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-emerald-300 font-mono text-xs select-all">
                                    <button type="button" onclick="copyPixPortalKey()"
                                            id="btn_copy_portal_pix"
                                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition shrink-0">
                                        Copiar Chave
                                    </button>
                                </div>
                            </div>

                            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 text-xs text-slate-400 space-y-1">
                                <p><strong class="text-slate-300">Favorecido:</strong> <?= htmlspecialchars($tenant['pix_beneficiary'] ?? $tenant['business_name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <p><strong class="text-slate-300">Instruções:</strong> Após realizar o Pix, envie o comprovante no WhatsApp da oficina para baixa imediata.</p>
                            </div>

                            <?php if (!empty($cleanShopPhone)): ?>
                                <a href="https://wa.me/55<?= $cleanShopPhone ?>?text=<?= urlencode("Olá! Acabei de realizar o pagamento Pix no valor de R$ " . number_format($totalPending, 2, ',', '.') . " referente às minhas ordens de serviço. Segue o comprovante.") ?>" 
                                   target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                                    <span>💬 Enviar Comprovante no WhatsApp</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabela de Extrato de Ordens de Serviço -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200/80 dark:border-slate-700 overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Extrato de Faturas e Serviços</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Histórico detalhado de valores pagos e pendentes</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">
                        Total Geral: R$ <?= number_format($totalPaid + $totalPending, 2, ',', '.') ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-900 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-3 px-4">Ordem de Serviço</th>
                                <th class="py-3 px-4">Equipamento</th>
                                <th class="py-3 px-4">Data</th>
                                <th class="py-3 px-4">Situação</th>
                                <th class="py-3 px-4 text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($ord['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?= htmlspecialchars($ord['equipment_info'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500">
                                        <?= date('d/m/Y', strtotime($ord['created_at'])) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($ord['payment_status'] === 'paid'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                ✓ Pago
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                ⏳ Pendente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<script>
function switchPortalTab(tabId) {
    document.querySelectorAll('.portal-tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.portal-tab-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-sm');
        btn.classList.add('text-slate-500', 'dark:text-slate-400');
    });

    const activePane = document.getElementById('portal-tab-' + tabId);
    if (activePane) activePane.classList.remove('hidden');

    const activeBtn = document.getElementById('portal-tab-btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
    }
}

function copyPixPortalKey() {
    const input = document.getElementById('pix_key_display');
    if (!input) return;
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('btn_copy_portal_pix');
        const orig = btn.innerText;
        btn.innerText = '✓ Copiado!';
        setTimeout(() => { btn.innerText = orig; }, 2000);
    });
}
</script>
