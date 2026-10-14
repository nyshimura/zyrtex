<?php
/**
 * View: Detalhes, Gestão Técnica e Financeira da Ordem de Serviço
 */
$csrf = \Zyrtex\Core\CSRF::token();
$os = $os ?? [];
$checklist = $checklist ?? [];
$fraudHistory = $fraudHistory ?? [];
$parts = $parts ?? [];
$availableProducts = $availableProducts ?? [];
$auditLogs = $auditLogs ?? [];
$mediaList = $mediaList ?? [];
$nicheConfig = $nicheConfig ?? [];
$cleanPhone = preg_replace('/\D/', '', $os['client_phone'] ?? '');

$statusMap = [
    'budget' => ['label' => 'Orçamento', 'class' => 'bg-amber-500/20 text-amber-300 border-amber-500/40'],
    'approved' => ['label' => 'Aprovado', 'class' => 'bg-blue-500/20 text-blue-300 border-blue-500/40'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/40'],
    'waiting_parts' => ['label' => 'Aguardando Peça', 'class' => 'bg-purple-500/20 text-purple-300 border-purple-500/40'],
    'ready' => ['label' => 'Pronto para Retirada', 'class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40'],
    'delivered' => ['label' => 'Entregue / Concluído', 'class' => 'bg-slate-700 text-slate-300 border-slate-600'],
    'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-500/20 text-rose-300 border-rose-500/40'],
];
$currentStatus = $statusMap[$os['status']] ?? ['label' => $os['status'], 'class' => 'bg-slate-800 text-slate-300 border-slate-700'];

// Mensagens pré-formatadas para WhatsApp (RF17)
$clientDisplayName = $os['client_name'] ?? 'Cliente';
$shopName = $tenant['business_name'] ?? 'Assistência Técnica';
$portalUrl = \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal?os=' . urlencode($os['os_number']));

$waMsgOrcamento = urlencode("Olá, {$clientDisplayName}! Seu orçamento para o reparo do {$os['equipment_info']} na {$shopName} está pronto! Valor total: R$ " . number_format((float)$os['total_value'], 2, ',', '.') . ".\n\nVocê pode revisar os itens e aprovar em 1 clique acessando seu Portal exclusivo:\n{$portalUrl}");
$waMsgPronto = urlencode("Olá, {$clientDisplayName}! Temos ótimas notícias: o reparo do seu {$os['equipment_info']} (OS {$os['os_number']}) foi concluído com sucesso pela {$shopName}! 🎉\n\nValor final: R$ " . number_format((float)$os['total_value'], 2, ',', '.') . ".\nVocê já pode retirá-lo em nossa oficina.\nVeja o laudo e fotos no seu Portal:\n{$portalUrl}");
$waMsgAdicional = urlencode("Olá, {$clientDisplayName}! Durante a análise em bancada na {$shopName}, nossos técnicos identificaram a necessidade de novos itens para a conclusão do seu {$os['equipment_info']} (OS {$os['os_number']}).\n\nPor favor, acesse seu Portal para revisar os novos valores e autorizar:\n{$portalUrl}");
$pixKeyText = !empty($tenant['pix_key']) ? "Chave Pix ({$tenant['pix_key_type']}): {$tenant['pix_key']} em nome de " . ($tenant['pix_beneficiary'] ?? $shopName) : "Link do Portal: {$portalUrl}";
$waMsgCobranca = urlencode("Olá, {$clientDisplayName}! Seguem os dados para pagamento do seu serviço na {$shopName} (OS {$os['os_number']}):\nValor total: R$ " . number_format((float)$os['total_value'], 2, ',', '.') . ".\n\n{$pixKeyText}\n\nVocê também pode pagar online acessando seu Portal:\n{$portalUrl}");
?>

<div class="space-y-6">

    <!-- Topbar da OS -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div class="flex items-center gap-4">
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" 
               class="p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
                &larr;
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight font-mono">
                        <?= htmlspecialchars($os['os_number'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold border <?= $currentStatus['class'] ?>">
                        <?= $currentStatus['label'] ?>
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Aberta em <?= date('d/m/Y \à\s H:i', strtotime($os['created_at'])) ?> &bull; Nicho: <?= htmlspecialchars($nicheConfig['name'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        </div>

        <!-- Ações Rápidas de Impressão e WhatsApp -->
        <div class="flex flex-wrap items-center gap-2">
            <?php if (strlen($cleanPhone) >= 10): ?>
                <a href="https://wa.me/55<?= $cleanPhone ?>?text=<?= $waMsgOrcamento ?>" target="_blank"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-md transition flex items-center gap-1.5">
                    <span>💬 Enviar Orçamento</span>
                </a>
                <a href="https://wa.me/55<?= $cleanPhone ?>?text=<?= $waMsgPronto ?>" target="_blank"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-600 shadow-md transition flex items-center gap-1.5">
                    <span>✅ Notificar Pronto</span>
                </a>
            <?php endif; ?>

            <!-- Botões de Impressão (RF16 & RF14) -->
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir?id=' . (int)$os['id'] . '&formato=80mm') ?>" target="_blank"
               class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition"
               title="Cupom Térmico 80mm">
                🖨️ 80mm
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir?id=' . (int)$os['id'] . '&formato=58mm') ?>" target="_blank"
               class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition"
               title="Cupom Térmico 58mm">
                🖨️ 58mm
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir?id=' . (int)$os['id'] . '&formato=label') ?>" target="_blank"
               class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-indigo-400 border border-slate-700 transition"
               title="Etiqueta Mini Adesiva QR Code (50x30 / 40x25mm)">
                🏷️ Mini QR
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os/imprimir?id=' . (int)$os['id'] . '&formato=a4') ?>" target="_blank"
               class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition"
               title="Termo e Laudo Oficial A4">
                📄 A4
            </a>
        </div>
    </div>

    <!-- ALERTA DO PRONTUÁRIO ANTIFRAUDE (RN01) -->
    <?php if (!empty($fraudHistory)): ?>
        <div class="p-4 rounded-2xl bg-amber-500/10 border-2 border-amber-500/30 text-amber-200 space-y-2">
            <div class="flex items-center gap-2 font-bold text-sm text-amber-300">
                <span>🛡️ Prontuário Antifraude Ativo (RN01):</span>
                <span>Este <?= htmlspecialchars($nicheConfig['identifier_name'], ENT_QUOTES, 'UTF-8') ?> já possui histórico nesta oficina!</span>
            </div>
            <p class="text-xs text-amber-200/90">
                Foram encontradas <strong><?= count($fraudHistory) ?> passagens anteriores</strong> para o identificador <code><?= htmlspecialchars($os['identifier_code'], ENT_QUOTES, 'UTF-8') ?></code>:
            </p>
            <ul class="text-xs space-y-1 list-disc list-inside text-amber-200/80">
                <?php foreach ($fraudHistory as $fh): ?>
                    <li>
                        <strong><?= htmlspecialchars($fh['os_number'], ENT_QUOTES, 'UTF-8') ?></strong> em <?= date('d/m/Y', strtotime($fh['created_at'])) ?> - Status: <?= $fh['status'] ?> (R$ <?= number_format((float)$fh['total_value'], 2, ',', '.') ?>) - "<?= htmlspecialchars($fh['defect_reported'], ENT_QUOTES, 'UTF-8') ?>"
                        <a href="<?= \Zyrtex\Core\Response::url('/admin/os/ver?id=' . (int)$fh['id']) ?>" class="underline font-bold text-amber-300 ml-1">Ver OS anterior &rarr;</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Grid Principal de Conteúdo -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Coluna Esquerda: Dados do Cliente e Aparelho (2 colunas) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card: Equipamento & Defeito -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Dados do Equipamento</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-2">
                    <div>
                        <span class="text-xs text-slate-500 block">Equipamento / Modelo:</span>
                        <span class="text-base font-bold text-white"><?= htmlspecialchars($os['equipment_info'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block"><?= htmlspecialchars($nicheConfig['identifier_name'], ENT_QUOTES, 'UTF-8') ?>:</span>
                        <code class="text-sm font-mono font-bold text-indigo-400 bg-slate-950 px-2 py-0.5 rounded border border-slate-800">
                            <?= htmlspecialchars($os['identifier_code'], ENT_QUOTES, 'UTF-8') ?>
                        </code>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-800">
                    <span class="text-xs text-slate-500 block mb-1">Defeito Informado pelo Cliente:</span>
                    <p class="text-sm text-slate-200 bg-slate-950/70 p-3.5 rounded-xl border border-slate-800/80 leading-relaxed">
                        <?= nl2br(htmlspecialchars($os['defect_reported'], ENT_QUOTES, 'UTF-8')) ?>
                    </p>
                </div>
            </div>

            <!-- Card: Checklist Técnico Registrado -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Checklist de Entrada</h3>
                    <span class="text-[11px] text-slate-500">Gravado na entrada</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <?php 
                    $checklistConfig = $nicheConfig['checklist'] ?? [];
                    foreach ($checklistConfig as $ckKey => $ckLabel): 
                        $val = $checklist[$ckKey] ?? 'nao_testado';
                        $chkClasses = [
                            'ok' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                            'defeito' => 'bg-rose-500/10 text-rose-400 border-rose-500/20 font-bold',
                            'nao_testado' => 'bg-slate-950 text-slate-400 border-slate-800'
                        ];
                        $badgeClass = $chkClasses[$val] ?? $chkClasses['nao_testado'];
                    ?>
                        <div class="p-2.5 rounded-xl border <?= $badgeClass ?> text-xs flex items-center justify-between">
                            <span class="truncate mr-2"><?= htmlspecialchars($ckLabel, ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="uppercase font-bold text-[10px]"><?= $val === 'ok' ? '✓ OK' : ($val === 'defeito' ? '✗ Def.' : 'N/T') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Card: Laudo Técnico e Valores -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Laudo Técnico & Orçamento</h3>

                <form action="<?= \Zyrtex\Core\Response::url('/admin/os/diagnostico') ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int)$os['id'] ?>">

                    <div>
                        <label for="technical_diagnosis" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Diagnóstico Técnico / Serviço a Realizar
                        </label>
                        <textarea id="technical_diagnosis" name="technical_diagnosis" rows="3"
                                  placeholder="Descreva detalhadamente o diagnóstico da bancada e as intervenções necessárias..."
                                  class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"><?= htmlspecialchars($os['technical_diagnosis'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="labor_value" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Mão de Obra (R$)
                            </label>
                            <input type="text" id="labor_value" name="labor_value"
                                   value="<?= number_format((float)$os['labor_value'], 2, ',', '.') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                        </div>

                        <div>
                            <label for="parts_value" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Peças (R$)
                            </label>
                            <input type="text" id="parts_value" name="parts_value"
                                   value="<?= number_format((float)$os['parts_value'], 2, ',', '.') ?>"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                        </div>

                        <div>
                            <label for="warranty_days" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                                Garantia Legal (Dias)
                            </label>
                            <input type="number" id="warranty_days" name="warranty_days"
                                   value="<?= (int)$os['warranty_days'] ?>" min="30" max="365"
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                            Atualizar Laudo & Valores &rarr;
                        </button>
                    </div>
                </form>

                <?php if (!empty($os['technical_diagnosis'])): ?>
                    <div class="pt-4 mt-2 border-t border-slate-800 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">✨</span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-purple-300">Inteligência Artificial (RF09)</h4>
                            </div>
                            <form action="<?= \Zyrtex\Core\Response::url('/admin/os/ia-simplificar') ?>" method="POST" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold text-xs shadow transition flex items-center gap-1.5">
                                    <span>✨</span> <?= !empty($os['simplified_diagnosis_ai']) ? 'Regerar Explicação com IA' : 'Traduzir Laudo Técnico com IA' ?>
                                </button>
                            </form>
                        </div>

                        <?php if (!empty($os['simplified_diagnosis_ai'])): ?>
                            <div class="p-3.5 rounded-xl bg-purple-950/40 border border-purple-500/30 text-xs text-purple-200 leading-relaxed">
                                <div class="flex items-center justify-between font-bold text-purple-300 mb-1 text-[11px]">
                                    <span>Exibição para o Cliente (Portal & Rastreio):</span>
                                    <span class="text-[10px] bg-purple-500/20 text-purple-300 px-2 py-0.5 rounded font-mono">Ativo no Portal</span>
                                </div>
                                <p class="text-slate-200 font-medium"><?= nl2br(htmlspecialchars($os['simplified_diagnosis_ai'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        <?php else: ?>
                            <p class="text-[11px] text-slate-500 italic">
                                Clique no botão acima para que o motor de IA traduza os jargões técnicos em uma explicação clara e acolhedora para o cliente final.
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Card: Peças & Insumos Utilizados (com Busca Pesquisável + Dropdown + Auditoria) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Peças & Insumos da Bancada</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Itens do orçamento inicial e aditivos adicionados durante o reparo com rastreabilidade de equipe.
                        </p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        📦 Estoque & Rastreio
                    </span>
                </div>

                <!-- Tabela de Peças Já Adicionadas com Auditoria de Funcionário e Status -->
                <?php if (empty($parts)): ?>
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 text-center text-xs text-slate-500">
                        Nenhuma peça vinculada a esta Ordem de Serviço ainda.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                                <tr>
                                    <th class="py-2.5 px-3">Peça / Insumo</th>
                                    <th class="py-2.5 px-3">Aprovação</th>
                                    <th class="py-2.5 px-3">Adicionado Por</th>
                                    <th class="py-2.5 px-3 text-center">Qtd</th>
                                    <th class="py-2.5 px-3">Valor Unit.</th>
                                    <th class="py-2.5 px-3">Subtotal</th>
                                    <th class="py-2.5 px-3 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-medium">
                                <?php foreach ($parts as $pt): 
                                    $sub = (float)$pt['unit_price'] * (int)$pt['quantity'];
                                    $appStatus = $pt['approval_status'] ?? 'approved';
                                ?>
                                    <tr class="hover:bg-slate-800/30">
                                        <td class="py-2.5 px-3 font-bold text-white">
                                            <?= htmlspecialchars($pt['part_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <?php if ($appStatus === 'approved'): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                    ✓ Aprovado
                                                </span>
                                            <?php elseif ($appStatus === 'pending_approval'): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30 animate-pulse">
                                                    ⚠️ Aditivo (Aguardando Cliente)
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                                    ❌ Recusado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2.5 px-3 text-[11px]">
                                            <span class="text-white font-medium block">
                                                <?= htmlspecialchars($pt['employee_name'] ?? 'Técnico', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span class="text-[10px] text-slate-500 font-mono">
                                                <?= date('d/m/Y H:i', strtotime($pt['created_at'])) ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-mono">
                                            <?= (int)$pt['quantity'] ?>
                                        </td>
                                        <td class="py-2.5 px-3 font-mono">
                                            R$ <?= number_format((float)$pt['unit_price'], 2, ',', '.') ?>
                                        </td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-emerald-400">
                                            R$ <?= number_format($sub, 2, ',', '.') ?>
                                        </td>
                                        <td class="py-2.5 px-3 text-right">
                                            <form action="<?= \Zyrtex\Core\Response::url('/admin/os/peca/remover') ?>" method="POST" class="inline"
                                                  onsubmit="return confirm('Tem certeza que deseja remover esta peça? O registro será mantido na auditoria da oficina.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="part_id" value="<?= (int)$pt['id'] ?>">
                                                <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                                                <button type="submit" class="text-rose-400 hover:text-rose-300 font-bold hover:underline" title="Remover e devolver ao estoque">
                                                    Remover
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Formulário de Adicionar Peça com Autocomplete Inteligente -->
                <form action="<?= \Zyrtex\Core\Response::url('/admin/os/peca') ?>" method="POST" class="pt-3 border-t border-slate-800 space-y-3" id="form_add_os_part">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                    <input type="hidden" name="product_id" id="show_product_id" value="0">

                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/80 space-y-2">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            
                            <!-- Campo com Autocomplete em Tempo Real -->
                            <div class="sm:col-span-6 relative">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="show_part_name" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                        🔍 Selecionar Peça do Estoque
                                    </label>
                                    <span id="show_stock_badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        Estoque: <span id="show_stock_qty">0</span> un
                                    </span>
                                </div>
                                <div class="relative">
                                    <input type="text" name="part_name" id="show_part_name" 
                                           placeholder="Comece a digitar o nome da peça ou insumo..."
                                           autocomplete="off" required
                                           class="w-full pl-3.5 pr-8 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                    <button type="button" id="show_clear_btn" class="hidden absolute right-2.5 top-2.5 text-slate-400 hover:text-white text-xs font-bold" title="Limpar seleção">
                                        ✕
                                    </button>
                                </div>

                                <!-- Dropdown Flutuante de Autocomplete -->
                                <div id="show_autocomplete_dropdown" 
                                     class="hidden absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl bg-slate-900 border border-slate-700 shadow-2xl divide-y divide-slate-800 text-xs">
                                </div>
                            </div>

                            <!-- Qtd -->
                            <div class="sm:col-span-2">
                                <label for="show_quantity" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Qtd
                                </label>
                                <input type="number" name="quantity" id="show_quantity" value="1" min="1" max="99" required
                                       class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono text-center focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <!-- Valor Unitário (R$) -->
                            <div class="sm:col-span-2">
                                <label for="show_unit_price" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                    Unitário (R$)
                                </label>
                                <input type="text" name="unit_price" id="show_unit_price" value="0,00" required
                                       class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            </div>

                            <!-- Botão Adicionar -->
                            <div class="sm:col-span-2">
                                <button type="submit" 
                                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5">
                                    <span>+ Incluir</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>

            <!-- Card: Linha do Tempo & Auditoria Completa da OS (Exclusivo da Oficina) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                            <span>📋 Linha do Tempo & Histórico de Auditoria da OS</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Histórico registrado com data, hora e qual funcionário acrescentou ou removeu cada item.
                        </p>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-amber-400 border border-amber-500/20">
                        🔒 Exclusivo da Oficina (Invisível para o Cliente)
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800/80 text-xs text-slate-400 leading-relaxed">
                    💡 <strong>Transparência para o Cliente:</strong> O cliente da loja apenas vê os itens aprovados e os novos itens pendentes de aprovação salvos no momento. Itens inseridos, corrigidos e removidos pela equipe ficam registrados exclusivamente nesta auditoria interna com o nome do funcionário e horário exato.
                </div>

                <?php if (empty($auditLogs)): ?>
                    <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 text-center text-xs text-slate-500">
                        Nenhum registro de auditoria gravado ainda para esta Ordem de Serviço.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                                <tr>
                                    <th class="py-2.5 px-3">Data & Hora</th>
                                    <th class="py-2.5 px-3">Funcionário</th>
                                    <th class="py-2.5 px-3">Ação Realizada</th>
                                    <th class="py-2.5 px-3">Detalhes do Evento</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-medium font-sans">
                                <?php foreach ($auditLogs as $log): 
                                    $details = json_decode($log['details_json'] ?? '{}', true) ?: [];
                                    $actionBadges = [
                                        'part_added' => ['label' => '+ Peça Adicionada', 'class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'],
                                        'part_removed' => ['label' => '- Peça Removida', 'class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20'],
                                        'status_changed' => ['label' => 'Status Alterado', 'class' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'],
                                        'os_created' => ['label' => 'Abertura da OS', 'class' => 'bg-blue-500/10 text-blue-400 border-blue-500/20'],
                                        'client_approved_additions' => ['label' => 'Aprovado pelo Cliente', 'class' => 'bg-teal-500/10 text-teal-300 border-teal-500/20'],
                                        'client_rejected_additions' => ['label' => 'Recusado pelo Cliente', 'class' => 'bg-amber-500/10 text-amber-300 border-amber-500/20']
                                    ];
                                    $badge = $actionBadges[$log['action']] ?? ['label' => $log['action'], 'class' => 'bg-slate-800 text-slate-400 border-slate-700'];
                                    $empName = $details['employee_name'] ?? ($log['user_name'] ?? 'Sistema / Cliente');
                                ?>
                                    <tr class="hover:bg-slate-800/20">
                                        <td class="py-2.5 px-3 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                                            <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                                        </td>
                                        <td class="py-2.5 px-3 font-bold text-white whitespace-nowrap">
                                            <?= htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') ?>
                                        </td>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $badge['class'] ?>">
                                                <?= $badge['label'] ?>
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-300 text-xs">
                                            <?php if ($log['action'] === 'part_added'): ?>
                                                Item: <strong><?= htmlspecialchars($details['part_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> 
                                                &bull; Qtd: <?= (int)($details['quantity'] ?? 1) ?>
                                                &bull; Unit.: R$ <?= number_format((float)($details['unit_price'] ?? 0), 2, ',', '.') ?>
                                                <?php if (!empty($details['approval_status']) && $details['approval_status'] === 'pending_approval'): ?>
                                                    <span class="text-amber-400 text-[10px] block font-semibold">(Aditivo pós-aprovação &bull; Aguardando cliente)</span>
                                                <?php endif; ?>
                                            <?php elseif ($log['action'] === 'part_removed'): ?>
                                                Peça removida: <strong><?= htmlspecialchars($details['part_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                                &bull; Qtd: <?= (int)($details['quantity'] ?? 1) ?>
                                                &bull; Subtotal estornado: R$ <?= number_format((float)($details['unit_price'] ?? 0) * (int)($details['quantity'] ?? 1), 2, ',', '.') ?>
                                            <?php elseif ($log['action'] === 'status_changed'): ?>
                                                Transição: <code><?= htmlspecialchars($details['old_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></code> &rarr; <strong><?= htmlspecialchars($details['new_status'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                            <?php elseif ($log['action'] === 'os_created'): ?>
                                                Ordem de serviço gerada para <strong><?= htmlspecialchars($details['equipment'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong> com <?= (int)($details['initial_parts_count'] ?? 0) ?> peça(s) inicial(is).
                                            <?php else: ?>
                                                <?= htmlspecialchars(json_encode($details, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ===================================================================== -->
            <!-- CARD: PRONTUÁRIO FOTOGRÁFICO & MÍDIAS DA OS (FASE 5)                 -->
            <!-- ===================================================================== -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-800 pb-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span>📸</span>
                            <span>Prontuário Fotográfico & Mídias da OS</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Fotos de check-in, bancada e laudo técnico com compressão automática. Visíveis ao cliente no rastreio online.
                        </p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 self-start sm:self-auto">
                        <?= count($mediaList) ?> foto(s) anexada(s)
                    </span>
                </div>

                <!-- Formulário de Anexar Foto (Dual: Upload Server-Side OU Link Direto com GD) -->
                <form action="<?= \Zyrtex\Core\Response::url('/admin/os/midia') ?>" method="POST" enctype="multipart/form-data" class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-4">
                            <label class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Etapa / Tipo de Foto:
                            </label>
                            <select name="media_type" required
                                    class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                                <option value="checkin">📸 Check-in (Entrada / Avarias)</option>
                                <option value="bancada">🔬 Bancada (Diagnóstico / Reparo)</option>
                                <option value="part">⚙️ Peça Trocada (Evidência)</option>
                                <option value="report">📋 Laudo Técnico / Testes Finais</option>
                            </select>
                        </div>

                        <div class="sm:col-span-8">
                            <label class="block text-[11px] font-semibold text-slate-300 uppercase tracking-wider mb-1">
                                Descrição Breve da Foto (Opcional):
                            </label>
                            <input type="text" name="title" placeholder="Ex: Detalhe do trincado no canto superior direito"
                                   class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs">
                        </div>
                    </div>

                    <!-- Dual Input: Arquivo OU Link com Live Preview -->
                    <div class="flex flex-col sm:flex-row items-center gap-4 pt-2 border-t border-slate-800/80">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shrink-0 flex items-center justify-center text-slate-500 text-[10px]" id="os_media_preview_box">
                            <img id="os_media_img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                            <span id="os_media_placeholder">Sem Imagem</span>
                        </div>

                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
                            <div>
                                <label class="block text-[10px] text-slate-400 font-medium mb-1">Upload do Arquivo:</label>
                                <input type="file" name="media_file" accept="image/*"
                                       onchange="previewOsImageInput(this)"
                                       class="block w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                            </div>

                            <div>
                                <label class="block text-[10px] text-slate-400 font-medium mb-1">Ou Link Direto da Foto:</label>
                                <input type="url" name="media_url" placeholder="https://exemplo.com/foto.jpg"
                                       oninput="previewOsUrlInput(this.value)"
                                       class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30 whitespace-nowrap self-stretch sm:self-end flex items-center justify-center gap-1.5">
                            <span>+ Anexar Foto</span>
                        </button>
                    </div>
                </form>

                <!-- Grid de Fotos Anexadas -->
                <?php if (empty($mediaList)): ?>
                    <div class="p-6 rounded-xl border border-dashed border-slate-800 text-center text-slate-500 text-xs">
                        Nenhuma foto anexada a esta OS. Anexe fotos da entrada para resguardo jurídico contra avarias pré-existentes.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach ($mediaList as $media): 
                            $typeBadges = [
                                'checkin' => ['label' => 'Check-in Entrada', 'class' => 'bg-amber-500/20 text-amber-300 border-amber-500/40'],
                                'bancada' => ['label' => 'Bancada Reparo', 'class' => 'bg-blue-500/20 text-blue-300 border-blue-500/40'],
                                'part'    => ['label' => 'Peça Trocada', 'class' => 'bg-purple-500/20 text-purple-300 border-purple-500/40'],
                                'report'  => ['label' => 'Laudo Final', 'class' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40']
                            ];
                            $tb = $typeBadges[$media['media_type']] ?? ['label' => $media['media_type'], 'class' => 'bg-slate-800 text-slate-300 border-slate-700'];
                            $imgSrc = !empty($media['file_path']) ? htmlspecialchars(asset($media['file_path']), ENT_QUOTES, 'UTF-8') : ($media['thumbnail_base64'] ?? '');
                        ?>
                            <div class="bg-slate-950 border border-slate-800 rounded-xl overflow-hidden flex flex-col justify-between group hover:border-indigo-500/40 transition">
                                <div class="relative h-32 bg-slate-900 overflow-hidden cursor-pointer" onclick="openLightbox('<?= $imgSrc ?>', '<?= htmlspecialchars($media['file_name'] ?? 'Foto da OS', ENT_QUOTES, 'UTF-8') ?>')">
                                    <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($media['file_name'] ?? 'Foto') ?>"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         loading="lazy">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1">
                                        <span>🔍 Ampliar</span>
                                    </div>
                                </div>

                                <div class="p-3 space-y-2">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded border <?= $tb['class'] ?>">
                                            <?= $tb['label'] ?>
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            <?php if (!empty($media['gdrive_file_id'])): ?>
                                                <a href="https://drive.google.com/file/d/<?= htmlspecialchars($media['gdrive_file_id'], ENT_QUOTES, 'UTF-8') ?>/view" target="_blank" 
                                                   title="Foto arquivada no seu Google Drive (BYOS)"
                                                   class="text-[9px] text-emerald-400 hover:text-emerald-300 font-bold flex items-center gap-0.5 bg-emerald-950/40 px-1.5 py-0.5 rounded border border-emerald-500/30">
                                                    <span>💾 Drive</span>
                                                </a>
                                            <?php endif; ?>
                                            <span class="text-[9px] text-slate-500 font-mono">
                                                <?= date('d/m H:i', strtotime($media['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <p class="text-[11px] font-medium text-white truncate" title="<?= htmlspecialchars($media['file_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($media['file_name'] ?? 'Sem título', ENT_QUOTES, 'UTF-8') ?>
                                    </p>

                                    <div class="pt-2 border-t border-slate-900 flex justify-end">
                                        <form action="<?= \Zyrtex\Core\Response::url('/admin/os/midia/remover') ?>" method="POST"
                                              onsubmit="return confirm('Deseja realmente remover esta foto do prontuário?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="os_id" value="<?= (int)$os['id'] ?>">
                                            <input type="hidden" name="media_id" value="<?= (int)$media['id'] ?>">
                                            <button type="submit" class="text-[10px] text-rose-400 hover:text-rose-300 font-bold transition">
                                                🗑️ Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('show_part_search');
                const selectDropdown = document.getElementById('show_product_select');
                const customNameInput = document.getElementById('show_custom_name');
                const priceInput = document.getElementById('show_unit_price');

                if (!searchInput || !selectDropdown) return;

                selectDropdown.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    if (opt && opt.value !== '0' && opt.value !== '') {
                        const name = opt.getAttribute('data-name');
                        const price = opt.getAttribute('data-price');
                        searchInput.value = name;
                        customNameInput.value = name;
                        priceInput.value = price;
                    }
                });

                searchInput.addEventListener('input', function() {
                    const val = this.value.trim().toLowerCase();
                    let matched = false;

                    for (let i = 0; i < selectDropdown.options.length; i++) {
                        const opt = selectDropdown.options[i];
                        const optName = (opt.getAttribute('data-name') || '').toLowerCase();
                        if (optName && optName === val) {
                            selectDropdown.selectedIndex = i;
                            customNameInput.value = opt.getAttribute('data-name');
                            priceInput.value = opt.getAttribute('data-price');
                            matched = true;
                            break;
                        }
                    }

                    if (!matched && val.length > 0) {
                        customNameInput.value = this.value;
                    }
                });
            });
            </script>

        </div>

        <!-- Coluna Direita: Fluxo de Status, Financeiro e Cliente (1 coluna) -->
        <div class="space-y-6">

            <!-- Card: Atualizar Status Operacional -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Fluxo da Bancada</h3>

                <form action="<?= \Zyrtex\Core\Response::url('/admin/os/status') ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int)$os['id'] ?>">

                    <div>
                        <label for="status" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Status do Reparo
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white font-semibold text-sm">
                            <option value="budget" <?= $os['status'] === 'budget' ? 'selected' : '' ?>>1. Orçamento</option>
                            <option value="approved" <?= $os['status'] === 'approved' ? 'selected' : '' ?>>2. Aprovado pelo Cliente</option>
                            <option value="in_progress" <?= $os['status'] === 'in_progress' ? 'selected' : '' ?>>3. Em Reparo na Bancada</option>
                            <option value="waiting_parts" <?= $os['status'] === 'waiting_parts' ? 'selected' : '' ?>>4. Aguardando Peça</option>
                            <option value="ready" <?= $os['status'] === 'ready' ? 'selected' : '' ?>>5. Pronto para Retirada</option>
                            <option value="delivered" <?= $os['status'] === 'delivered' ? 'selected' : '' ?>>6. Entregue / Concluído</option>
                            <option value="cancelled" <?= $os['status'] === 'cancelled' ? 'selected' : '' ?>>X. Cancelado</option>
                        </select>
                    </div>

                    <div>
                        <label for="payment_status" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Situação do Pagamento
                        </label>
                        <select id="payment_status" name="payment_status" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                            <option value="pending" <?= $os['payment_status'] === 'pending' ? 'selected' : '' ?>>Pendente (Não Pago)</option>
                            <option value="paid" <?= $os['payment_status'] === 'paid' ? 'selected' : '' ?>>Pago</option>
                            <option value="refunded" <?= $os['payment_status'] === 'refunded' ? 'selected' : '' ?>>Estornado</option>
                        </select>
                    </div>

                    <button type="submit" 
                            class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition">
                        Salvar Status &rarr;
                    </button>
                </form>
            </div>

            <!-- Card: Total Financeiro -->
            <div class="bg-gradient-to-br from-indigo-950/40 via-slate-900 to-slate-900 border border-indigo-500/30 rounded-2xl p-6 space-y-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block">Total da Ordem de Serviço</span>
                <div class="text-3xl font-black text-white">
                    R$ <?= number_format((float)$os['total_value'], 2, ',', '.') ?>
                </div>

                <div class="pt-3 border-t border-slate-800 text-xs space-y-1 text-slate-400">
                    <div class="flex justify-between">
                        <span>Mão de Obra:</span>
                        <span class="text-white">R$ <?= number_format((float)$os['labor_value'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Peças:</span>
                        <span class="text-white">R$ <?= number_format((float)$os['parts_value'], 2, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between pt-1 font-bold">
                        <span>Pagamento:</span>
                        <span class="<?= $os['payment_status'] === 'paid' ? 'text-emerald-400' : 'text-amber-400' ?>">
                            <?= $os['payment_status'] === 'paid' ? 'PAGO' : 'PENDENTE' ?>
                        </span>
                    </div>
                </div>

                <div class="pt-3">
                    <a href="<?= $portalUrl ?>" target="_blank"
                       class="w-full py-2.5 rounded-xl text-center text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 hover:bg-indigo-500/20 transition block">
                        🔗 Abrir Portal do Cliente &nearr;
                    </a>
                </div>
            </div>

            <!-- Card: Dados do Cliente -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Cliente</h3>
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/ver?id=' . (int)$os['client_id']) ?>" class="text-xs text-indigo-400 hover:underline">
                        Ver Ficha
                    </a>
                </div>

                <div class="space-y-1 text-xs text-slate-300">
                    <p class="font-bold text-white text-sm"><?= htmlspecialchars($os['client_name'] ?? 'Cliente', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>WhatsApp:</strong> <?= htmlspecialchars($os['client_phone'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Documento:</strong> <?= htmlspecialchars($os['client_document'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>E-mail:</strong> <?= htmlspecialchars($os['client_email'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <!-- Card: Disparos WhatsApp 1-Click (RF17) -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="text-emerald-400">💬</span> WhatsApp 1-Click (RF17)
                    </h3>
                    <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-full font-semibold">Ativo</span>
                </div>
                <p class="text-xs text-slate-400">
                    Notifique o cliente diretamente com links diretos para aprovação e pagamento no Portal.
                </p>

                <?php if (strlen($cleanPhone) >= 10): ?>
                    <div class="space-y-2 pt-1">
                        <a href="https://wa.me/55<?= $cleanPhone ?>?text=<?= $waMsgOrcamento ?>" target="_blank"
                           class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-emerald-600/90 hover:bg-emerald-600 border border-emerald-500/30 flex items-center justify-between transition">
                            <span class="flex items-center gap-2">📋 Orçamento Pronto</span>
                            <span class="text-[10px] text-emerald-200 opacity-80">Enviar &nearr;</span>
                        </a>

                        <a href="https://wa.me/55<?= $cleanPhone ?>?text=<?= $waMsgPronto ?>" target="_blank"
                           class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-emerald-700/90 hover:bg-emerald-700 border border-emerald-500/30 flex items-center justify-between transition">
                            <span class="flex items-center gap-2">✅ Aparelho Pronto</span>
                            <span class="text-[10px] text-emerald-200 opacity-80">Retirada &nearr;</span>
                        </a>

                        <a href="https://wa.me/55<?= $cleanPhone ?>?text=<?= $waMsgAdicional ?>" target="_blank"
                           class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-amber-600/90 hover:bg-amber-600 border border-amber-500/30 flex items-center justify-between transition">
                            <span class="flex items-center gap-2">⚠️ Aditivo de Bancada</span>
                            <span class="text-[10px] text-amber-200 opacity-80">Autorização &nearr;</span>
                        </a>

                        <a href="https://wa.me/55<?= $cleanPhone ?>?text=<?= $waMsgCobranca ?>" target="_blank"
                           class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-indigo-600/90 hover:bg-indigo-600 border border-indigo-500/30 flex items-center justify-between transition">
                            <span class="flex items-center gap-2">💳 Cobrança & Chave Pix</span>
                            <span class="text-[10px] text-indigo-200 opacity-80">Pagar &nearr;</span>
                        </a>

                        <a href="<?= \Zyrtex\Core\Response::url('/admin/os/ia-remarketing?os_id=' . (int)$os['id']) ?>" target="_blank"
                           class="w-full py-2 px-3 rounded-xl text-xs font-bold text-white bg-purple-600/90 hover:bg-purple-600 border border-purple-500/30 flex items-center justify-between transition">
                            <span class="flex items-center gap-2">🤖 Remarketing Preditivo (IA)</span>
                            <span class="text-[10px] text-purple-200 opacity-80">WhatsApp &nearr;</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs text-amber-400">
                        ⚠️ O cliente não possui um número de WhatsApp válido cadastrado.
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</div>

<!-- SCRIPT DE AUTOCOMPLETE PARA PEÇAS DO ESTOQUE -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stockItems = <?= json_encode(array_map(function($p) {
        return [
            'id' => (int)$p['id'],
            'name' => (string)$p['name'],
            'price' => number_format((float)$p['price'], 2, ',', '.'),
            'priceNum' => (float)$p['price'],
            'stock' => (int)$p['stock']
        ];
    }, $availableProducts), JSON_UNESCAPED_UNICODE) ?>;

    const inputName = document.getElementById('show_part_name');
    const inputProductId = document.getElementById('show_product_id');
    const inputPrice = document.getElementById('show_unit_price');
    const inputQty = document.getElementById('show_quantity');
    const dropdown = document.getElementById('show_autocomplete_dropdown');
    const badgeStock = document.getElementById('show_stock_badge');
    const badgeStockQty = document.getElementById('show_stock_qty');
    const btnClear = document.getElementById('show_clear_btn');

    if (!inputName || !dropdown) return;

    function renderDropdown(items, query) {
        dropdown.innerHTML = '';
        if (items.length === 0) {
            if (query.trim().length > 0) {
                const emptyItem = document.createElement('div');
                emptyItem.className = 'p-3 text-slate-400 text-center cursor-pointer hover:bg-slate-800/80';
                emptyItem.innerHTML = `<span class="text-indigo-400 font-semibold">+ Usar "${escapeHtml(query)}" como peça avulsa</span>`;
                emptyItem.addEventListener('click', function() {
                    inputProductId.value = '0';
                    badgeStock.classList.add('hidden');
                    closeDropdown();
                    inputPrice.focus();
                });
                dropdown.appendChild(emptyItem);
                dropdown.classList.remove('hidden');
            } else {
                dropdown.classList.add('hidden');
            }
            return;
        }

        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'p-2.5 px-3 hover:bg-indigo-600/20 hover:border-l-2 hover:border-indigo-500 cursor-pointer flex items-center justify-between transition gap-2';
            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <span class="font-bold text-white block truncate">${escapeHtml(item.name)}</span>
                    <span class="text-[10px] text-slate-400">Estoque disponível: <strong class="text-slate-200">${item.stock} un</strong></span>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-xs font-mono font-bold text-emerald-400">R$ ${item.price}</span>
                </div>
            `;
            row.addEventListener('click', function() {
                selectItem(item);
            });
            dropdown.appendChild(row);
        });

        dropdown.classList.remove('hidden');
    }

    function selectItem(item) {
        inputName.value = item.name;
        inputProductId.value = item.id;
        inputPrice.value = item.price;
        badgeStockQty.textContent = item.stock;
        badgeStock.classList.remove('hidden');
        if (btnClear) btnClear.classList.remove('hidden');
        closeDropdown();
        inputQty.focus();
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
    }

    inputName.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        if (btnClear) btnClear.classList.toggle('hidden', this.value.length === 0);

        const exactMatch = stockItems.find(i => i.name.toLowerCase() === q);
        if (exactMatch) {
            inputProductId.value = exactMatch.id;
            badgeStockQty.textContent = exactMatch.stock;
            badgeStock.classList.remove('hidden');
        } else {
            inputProductId.value = '0';
            badgeStock.classList.add('hidden');
        }

        const filtered = stockItems.filter(i => i.name.toLowerCase().includes(q));
        renderDropdown(filtered, this.value);
    });

    inputName.addEventListener('focus', function() {
        const q = this.value.trim().toLowerCase();
        const filtered = q ? stockItems.filter(i => i.name.toLowerCase().includes(q)) : stockItems.slice(0, 8);
        renderDropdown(filtered, this.value);
    });

    if (btnClear) {
        btnClear.addEventListener('click', function() {
            inputName.value = '';
            inputProductId.value = '0';
            inputPrice.value = '0,00';
            badgeStock.classList.add('hidden');
            btnClear.classList.add('hidden');
            closeDropdown();
            inputName.focus();
        });
    }

    document.addEventListener('click', function(e) {
        if (!inputName.contains(e.target) && !dropdown.contains(e.target)) {
            closeDropdown();
        }
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});

function previewOsImageInput(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('os_media_img');
            const placeholder = document.getElementById('os_media_placeholder');
            if (img) {
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewOsUrlInput(url) {
    const img = document.getElementById('os_media_img');
    const placeholder = document.getElementById('os_media_placeholder');
    if (url && url.trim().length > 5) {
        if (img) {
            img.src = url.trim();
            img.classList.remove('hidden');
        }
        if (placeholder) {
            placeholder.classList.add('hidden');
        }
    } else {
        if (img) {
            img.classList.add('hidden');
        }
        if (placeholder) {
            placeholder.classList.remove('hidden');
        }
    }
}

function openLightbox(src, title) {
    const modal = document.getElementById('modal-os-lightbox');
    const img = document.getElementById('lightbox-img');
    const titleEl = document.getElementById('lightbox-title');
    if (modal && img) {
        img.src = src;
        if (titleEl) titleEl.textContent = title;
        modal.classList.remove('hidden');
    }
}

function closeLightbox() {
    const modal = document.getElementById('modal-os-lightbox');
    if (modal) modal.classList.add('hidden');
}
</script>

<!-- MODAL LIGHTBOX DE FOTOS DA OS -->
<div id="modal-os-lightbox" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md hidden p-4" onclick="closeLightbox()">
    <div class="max-w-4xl max-h-[90vh] w-full flex flex-col items-center justify-center relative" onclick="event.stopPropagation()">
        <button type="button" onclick="closeLightbox()" class="absolute -top-10 right-0 text-white hover:text-rose-400 text-2xl font-black">
            ✕ Fechar
        </button>
        <img id="lightbox-img" src="" alt="Foto Ampliada" class="max-w-full max-h-[80vh] rounded-2xl shadow-2xl object-contain border border-slate-700">
        <p id="lightbox-title" class="text-xs text-slate-300 font-bold mt-3 text-center"></p>
    </div>
</div>
