<?php
/**
 * View: Portal Público de Rastreio da Ordem de Serviço & Prontuário Digital
 * Exibe a situação da OS para o cliente final da loja.
 * O cliente vê apenas os itens aprovados e os novos itens pendentes de aprovação salvos no momento.
 */
$csrf = \Zyrtex\Core\CSRF::token();
$os = $os ?? null;
$tenant = $tenant ?? [];
$approvedParts = $approvedParts ?? [];
$pendingParts = $pendingParts ?? [];
$rejectedParts = $rejectedParts ?? [];
$mediaList = $mediaList ?? [];
$nicheConfig = $nicheConfig ?? [];
$searchedOs = $searchedOs ?? '';

$statusMap = [
    'budget' => ['label' => 'Orçamento (Aguardando Aprovação)', 'class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border-amber-300'],
    'approved' => ['label' => 'Aprovado pelo Cliente', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border-blue-300'],
    'in_progress' => ['label' => 'Em Reparo na Bancada', 'class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-300'],
    'waiting_parts' => ['label' => 'Aguardando Peça', 'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300 border-purple-300'],
    'ready' => ['label' => 'Pronto para Retirada!', 'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300'],
    'delivered' => ['label' => 'Entregue / Concluído', 'class' => 'bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300 border-slate-300'],
    'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border-rose-300'],
];

$cleanShopPhone = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? ($tenant['phone'] ?? ''));
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-8">

    <!-- Barra Superior de Busca de OS -->
    <div class="bg-white dark:bg-slate-800 p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-200/80 dark:border-slate-700">
        <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio') ?>" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" name="os" value="<?= htmlspecialchars($searchedOs, ENT_QUOTES, 'UTF-8') ?>" 
                       placeholder="Digite o número da sua OS (Ex: OS-2026-001) ou IMEI/Serial..." required
                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
            </div>
            <button type="submit" 
                    class="px-6 py-3 rounded-xl font-bold text-sm text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                <span>Rastrear OS</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
    </div>

    <?php if (!$os): ?>
        <!-- Estado: Nenhuma OS Encontrada -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-8 sm:p-12 text-center border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-400 mx-auto flex items-center justify-center text-2xl">
                🔍
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">
                <?= !empty($searchedOs) ? 'Nenhuma Ordem de Serviço encontrada' : 'Consulte sua Ordem de Serviço' ?>
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                <?= !empty($searchedOs) 
                    ? 'Verifique se digitou o código corretamente como impresso no seu comprovante (ex: OS-2026-001) ou tente pelo número de série/IMEI.' 
                    : 'Digite o número da OS ou número de série do seu equipamento na caixa de busca acima para acompanhar o status em tempo real.' ?>
            </p>
        </div>
    <?php else: 
        $st = $statusMap[$os['status']] ?? ['label' => $os['status'], 'class' => 'bg-slate-100 text-slate-700 border-slate-300'];
    ?>
        <!-- Ficha Principal da OS -->
        <div class="space-y-6">

            <!-- Cabeçalho do Rastreio -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Ordem de Serviço</span>
                        <div class="flex items-center gap-3 mt-1">
                            <h1 class="text-3xl font-black text-slate-900 dark:text-white font-mono tracking-tight">
                                <?= htmlspecialchars($os['os_number'], ENT_QUOTES, 'UTF-8') ?>
                            </h1>
                            <span class="px-3 py-1 rounded-full text-xs font-bold border <?= $st['class'] ?>">
                                <?= $st['label'] ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Aberto em <?= date('d/m/Y \à\s H:i', strtotime($os['created_at'])) ?> &bull; Oficina: <?= htmlspecialchars($tenant['business_name'] ?? 'Assistência Técnica', ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <?php if (!empty($cleanShopPhone)): 
                        $waMsg = urlencode("Olá! Gostaria de informações sobre minha OS {$os['os_number']} ({$os['equipment_info']}).");
                    ?>
                        <a href="https://wa.me/55<?= $cleanShopPhone ?>?text=<?= $waMsg ?>" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition">
                            <span>Falar no WhatsApp da Oficina</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Dados do Aparelho & Cliente -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block font-semibold">Equipamento / Modelo:</span>
                        <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($os['equipment_info'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block font-semibold"><?= htmlspecialchars($nicheConfig['identifier_name'] ?? 'IMEI / Serial', ENT_QUOTES, 'UTF-8') ?>:</span>
                        <code class="font-mono text-indigo-600 dark:text-indigo-400 font-bold bg-slate-100 dark:bg-slate-900 px-2 py-0.5 rounded text-xs"><?= htmlspecialchars($os['identifier_code'], ENT_QUOTES, 'UTF-8') ?></code>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block font-semibold">Cliente:</span>
                        <span class="font-medium text-slate-900 dark:text-white"><?= htmlspecialchars($os['client_name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>

                <!-- Defeito e Diagnóstico -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Defeito Informado na Entrada:</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed"><?= nl2br(htmlspecialchars($os['defect_reported'], ENT_QUOTES, 'UTF-8')) ?></p>
                    </div>

                    <div class="p-4 rounded-xl bg-indigo-50/50 dark:bg-slate-900/60 border border-indigo-100 dark:border-slate-700/60">
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider block mb-1">Laudo Técnico da Oficina:</span>
                        <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                            <?= !empty($os['technical_diagnosis']) ? nl2br(htmlspecialchars($os['technical_diagnosis'], ENT_QUOTES, 'UTF-8')) : 'Em análise minuciosa na bancada técnica.' ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($os['simplified_diagnosis_ai'])): ?>
                    <div class="mt-4 p-4 rounded-xl bg-gradient-to-r from-purple-500/10 via-indigo-500/10 to-transparent border border-purple-500/30">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-base">💡</span>
                            <span class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider">Entenda o Reparo em Linguagem Clara:</span>
                        </div>
                        <p class="text-xs text-slate-700 dark:text-slate-200 leading-relaxed font-medium">
                            <?= nl2br(htmlspecialchars($os['simplified_diagnosis_ai'], ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SEÇÃO DE PEÇAS & APROVAÇÃO (RF - Central de Aprovação do Cliente) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-6">

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            Itens, Peças & Orçamento do Reparo
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Detalhamento transparente de todos os itens acordados e serviços em execução.
                        </p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300">
                        🛡️ Garantia: <?= (int)$os['warranty_days'] ?> dias
                    </span>
                </div>

                <!-- ALERTA DE ADITIVO PÓS-APROVAÇÃO: Se existirem novos itens aguardando aprovação -->
                <?php if (!empty($pendingParts)): ?>
                    <div class="p-5 rounded-2xl bg-amber-500/10 border-2 border-amber-500/40 space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h3 class="text-sm font-extrabold text-amber-800 dark:text-amber-300">
                                    Novos Itens Adicionais Adicionados pela Assistência (Aguardando Sua Aprovação)
                                </h3>
                                <p class="text-xs text-amber-900/90 dark:text-amber-200/90 mt-1 leading-relaxed">
                                    Durante o procedimento em bancada, nossos técnicos identificaram a necessidade dos novos itens listados abaixo para a conclusão com qualidade do reparo. <strong>Por favor, revise os novos valores e confirme sua aprovação ou recusa.</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Tabela dos Novos Itens Pendentes -->
                        <div class="overflow-x-auto rounded-xl border border-amber-300 dark:border-amber-700/60 bg-white/70 dark:bg-slate-900/70">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-amber-100/60 dark:bg-slate-800 text-amber-900 dark:text-amber-300 uppercase font-bold text-[10px] border-b border-amber-200 dark:border-amber-700/40">
                                    <tr>
                                        <th class="py-2.5 px-3">Novo Item Sugerido</th>
                                        <th class="py-2.5 px-3 text-center">Qtd</th>
                                        <th class="py-2.5 px-3">Valor Unit.</th>
                                        <th class="py-2.5 px-3">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-amber-100 dark:divide-slate-800 font-medium">
                                    <?php 
                                    $pendingSum = 0.0;
                                    foreach ($pendingParts as $pt): 
                                        $sub = (float)$pt['unit_price'] * (int)$pt['quantity'];
                                        $pendingSum += $sub;
                                    ?>
                                        <tr>
                                            <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white">
                                                <?= htmlspecialchars($pt['part_name'], ENT_QUOTES, 'UTF-8') ?>
                                                <span class="block text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Novo Aditivo de Bancada</span>
                                            </td>
                                            <td class="py-2.5 px-3 text-center font-mono text-slate-700 dark:text-slate-300">
                                                <?= (int)$pt['quantity'] ?>
                                            </td>
                                            <td class="py-2.5 px-3 font-mono text-slate-700 dark:text-slate-300">
                                                R$ <?= number_format((float)$pt['unit_price'], 2, ',', '.') ?>
                                            </td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-amber-600 dark:text-amber-400">
                                                R$ <?= number_format($sub, 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Botões de Ação para o Cliente (Aprovar ou Recusar os Novos Itens) -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
                            <span class="text-xs font-bold text-amber-900 dark:text-amber-200">
                                Acréscimo nos novos itens: R$ <?= number_format($pendingSum, 2, ',', '.') ?>
                            </span>

                            <div class="flex items-center gap-2 w-full sm:w-auto">
                                <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio/rejeitar-adicional') ?>" method="POST" class="w-1/2 sm:w-auto"
                                      onsubmit="return confirm('Tem certeza que não deseja incluir estes novos itens?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                                    <button type="submit" 
                                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-100 hover:bg-rose-200 dark:bg-rose-950 dark:text-rose-300 transition">
                                        ✕ Recusar Novos Itens
                                    </button>
                                </form>

                                <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio/aprovar-adicional') ?>" method="POST" class="w-1/2 sm:w-auto"
                                      onsubmit="return confirm('Confirma a aprovação destes novos itens adicionais?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                                    <button type="submit" 
                                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md shadow-emerald-600/20 transition transform hover:-translate-y-0.5">
                                        ✓ Aprovar Novos Itens Adicionais
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- GRUPO 1: ITENS E PEÇAS APROVADOS ANTERIORMENTE -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span>Itens & Peças Aceitos Anteriormente</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                ✓ Aprovado
                            </span>
                        </h3>
                    </div>

                    <?php if (empty($approvedParts)): ?>
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 text-center text-xs text-slate-500">
                            Nenhuma peça física aplicada até o momento (apenas serviço / mão de obra orçada).
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                                <thead class="bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="py-2.5 px-3">Peça / Insumo</th>
                                        <th class="py-2.5 px-3 text-center">Qtd</th>
                                        <th class="py-2.5 px-3">Valor Unit.</th>
                                        <th class="py-2.5 px-3">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                                    <?php foreach ($approvedParts as $pt): 
                                        $sub = (float)$pt['unit_price'] * (int)$pt['quantity'];
                                    ?>
                                        <tr>
                                            <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white">
                                                <?= htmlspecialchars($pt['part_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </td>
                                            <td class="py-2.5 px-3 text-center font-mono">
                                                <?= (int)$pt['quantity'] ?>
                                            </td>
                                            <td class="py-2.5 px-3 font-mono">
                                                R$ <?= number_format((float)$pt['unit_price'], 2, ',', '.') ?>
                                            </td>
                                            <td class="py-2.5 px-3 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                R$ <?= number_format($sub, 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- GRUPO: PRONTUÁRIO FOTOGRÁFICO DO SEU APARELHO (FASE 5) -->
                <?php if (!empty($mediaList)): ?>
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <span>📸</span>
                                <span>Prontuário Fotográfico & Evidências da Bancada</span>
                            </h3>
                            <span class="text-[10px] text-slate-400 font-medium">Toque para ampliar</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            <?php foreach ($mediaList as $med): 
                                $typeLabels = [
                                    'checkin' => 'Entrada / Avarias',
                                    'bancada' => 'Bancada / Reparo',
                                    'part'    => 'Peça Substituída',
                                    'report'  => 'Laudo Final'
                                ];
                                $tLabel = $typeLabels[$med['media_type']] ?? 'Foto';
                                $imgSrc = !empty($med['file_path']) ? htmlspecialchars(asset($med['file_path']), ENT_QUOTES, 'UTF-8') : ($med['thumbnail_base64'] ?? '');
                            ?>
                                <div class="bg-slate-50 dark:bg-slate-900 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 cursor-pointer group"
                                     onclick="window.open('<?= $imgSrc ?>', '_blank')">
                                    <div class="h-28 overflow-hidden relative">
                                        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($med['file_name'] ?? 'Foto') ?>"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             loading="lazy">
                                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-[10px] font-bold">
                                            🔍 Ver Foto
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <span class="text-[9px] font-bold block text-brand-600 dark:text-brand-400 uppercase tracking-wider">
                                            <?= $tLabel ?>
                                        </span>
                                        <span class="text-[10px] font-medium text-slate-700 dark:text-slate-300 block truncate" title="<?= htmlspecialchars($med['file_name'] ?? '') ?>">
                                            <?= htmlspecialchars($med['file_name'] ?? '') ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- RESUMO FINANCEIRO TOTAL DA ORDEM DE SERVIÇO -->
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700 space-y-3">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Mão de Obra Especializada:</span>
                        <span class="font-bold text-slate-900 dark:text-white">R$ <?= number_format((float)$os['labor_value'], 2, ',', '.') ?></span>
                    </div>

                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Total em Peças & Insumos Aprovados:</span>
                        <span class="font-bold text-slate-900 dark:text-white">R$ <?= number_format((float)$os['parts_value'], 2, ',', '.') ?></span>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Total Geral da Ordem de Serviço</span>
                            <span class="text-[11px] <?= $os['payment_status'] === 'paid' ? 'text-emerald-600' : 'text-amber-500' ?> font-semibold">
                                Pagamento: <?= $os['payment_status'] === 'paid' ? 'PAGO' : 'Pendente na retirada' ?>
                            </span>
                        </div>
                        <div class="text-3xl font-black text-slate-900 dark:text-white">
                            R$ <?= number_format((float)$os['total_value'], 2, ',', '.') ?>
                        </div>
                    </div>

                    <!-- Botão de Aprovação de Orçamento Inicial (caso status = budget) -->
                    <?php if ($os['status'] === 'budget'): ?>
                        <div class="pt-4 mt-2 border-t border-slate-200 dark:border-slate-800">
                            <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio/aprovar-orcamento') ?>" method="POST"
                                  onsubmit="return confirm('Confirma a aprovação deste orçamento para início imediato do reparo?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                                <button type="submit" 
                                        class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                                    <span>✓ Aprovar Orçamento e Autorizar Reparo</span>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    <?php endif; ?>

</div>
