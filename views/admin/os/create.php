<?php
/**
 * View: Abertura de Nova Ordem de Serviço com Checklist Dinâmico
 */
$csrf = \Zyrtex\Core\CSRF::token();
$nicheConfig = $nicheConfig ?? [];
$clients = $clients ?? [];
$selectedClientId = $selectedClientId ?? 0;
$availableProducts = $availableProducts ?? [];
$old = $old ?? [];
?>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>Nova Ordem de Serviço</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                    <?= htmlspecialchars($nicheConfig['name'] ?? 'Assistência', ENT_QUOTES, 'UTF-8') ?>
                </span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Entrada de equipamento/veículo com checklist de conformidade e prontuário digital.
            </p>
        </div>

        <a href="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" 
           class="text-xs font-semibold text-slate-400 hover:text-white transition">
            &larr; Voltar
        </a>
    </div>

    <!-- Formulário Principal -->
    <form action="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" method="POST" class="space-y-6">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

        <!-- BLOCO 1: Cliente -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                    1. Dados do Cliente
                </h2>
                <a href="<?= \Zyrtex\Core\Response::url('/admin/clientes/novo?return_to=os_nova') ?>" 
                   class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
                    + Cadastrar Novo Cliente
                </a>
            </div>

            <div>
                <label for="client_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Selecione o Cliente <span class="text-rose-500">*</span>
                </label>
                <select id="client_id" name="client_id" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">-- Escolha um cliente cadastrado --</option>
                    <?php foreach ($clients as $c): 
                        $isSelected = ($selectedClientId === (int)$c['id'] || ($old['client_id'] ?? 0) == $c['id']);
                    ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?> - Tel: <?= htmlspecialchars($c['phone'], ENT_QUOTES, 'UTF-8') ?> <?= !empty($c['document']) ? '(' . htmlspecialchars($c['document'], ENT_QUOTES, 'UTF-8') . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- BLOCO 2: Aparelho e Identificador Antifraude -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                2. Equipamento / Veículo e Identificador Único
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="equipment_info" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Descrição do Aparelho / Modelo <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="equipment_info" name="equipment_info" required
                           value="<?= htmlspecialchars($old['equipment_info'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: iPhone 13 Pro Max 128GB Azul Sierra"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="identifier_code" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                            <?= htmlspecialchars($nicheConfig['identifier_name'] ?? 'IMEI ou Serial', ENT_QUOTES, 'UTF-8') ?> <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-[10px] text-amber-400 font-bold">🛡️ Prontuário Antifraude</span>
                    </div>
                    <input type="text" id="identifier_code" name="identifier_code" required
                           value="<?= htmlspecialchars($old['identifier_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="<?= htmlspecialchars($nicheConfig['identifier_placeholder'] ?? 'Ex: 356987123456789', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-mono">
                    <span class="text-[11px] text-slate-500 mt-1 block">O sistema detecta automaticamente se este item já esteve na oficina</span>
                </div>
            </div>

            <div>
                <label for="defect_reported" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Defeito Relatado pelo Cliente <span class="text-rose-500">*</span>
                </label>
                <textarea id="defect_reported" name="defect_reported" rows="2" required
                          placeholder="Ex: Não liga após queda; tela sem imagem mas vibra..."
                          class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"><?= htmlspecialchars($old['defect_reported'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
        </div>

        <!-- BLOCO 3: Checklist Dinâmico de Entrada -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                    3. Checklist Técnico de Entrada (<?= htmlspecialchars($nicheConfig['name'], ENT_QUOTES, 'UTF-8') ?>)
                </h2>
                <span class="text-xs text-slate-400">Proteção mútua de garantia</span>
            </div>

            <p class="text-xs text-slate-400">
                Marque o estado de cada item para constar no comprovante impresso e prevenir contestações indevidas.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                <?php 
                $checklistItems = $nicheConfig['checklist'] ?? [];
                foreach ($checklistItems as $chkKey => $chkLabel): 
                ?>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950 border border-slate-800">
                        <span class="text-xs font-medium text-slate-200"><?= htmlspecialchars($chkLabel, ENT_QUOTES, 'UTF-8') ?></span>
                        <div class="flex items-center gap-3 text-xs">
                            <label class="flex items-center gap-1 cursor-pointer text-emerald-400">
                                <input type="radio" name="checklist[<?= $chkKey ?>]" value="ok" checked
                                       class="text-emerald-600 focus:ring-emerald-500 bg-slate-900 border-slate-700">
                                <span>OK</span>
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer text-rose-400">
                                <input type="radio" name="checklist[<?= $chkKey ?>]" value="defeito"
                                       class="text-rose-600 focus:ring-rose-500 bg-slate-900 border-slate-700">
                                <span>Defeito</span>
                            </label>
                            <label class="flex items-center gap-1 cursor-pointer text-slate-400">
                                <input type="radio" name="checklist[<?= $chkKey ?>]" value="nao_testado"
                                       class="text-slate-600 focus:ring-slate-500 bg-slate-900 border-slate-700">
                                <span>N/T</span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
        </div>

        <!-- BLOCO 4: Peças & Insumos Iniciais da Bancada (Pesquisável + Dropdown) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>4. Peças & Insumos Iniciais da Bancada</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Pesquisável no Estoque
                        </span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Adicione peças que já serão orçadas e vinculadas desde a abertura da OS.
                    </p>
                </div>
            </div>

            <!-- Barra de Seleção e Busca de Peças com Autocomplete Inteligente -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800/80 space-y-2">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    
                    <!-- Campo com Autocomplete em Tempo Real -->
                    <div class="sm:col-span-6 relative">
                        <div class="flex items-center justify-between mb-1">
                            <label for="create_part_input" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                🔍 Selecionar Peça do Estoque
                            </label>
                            <span id="create_stock_badge" class="hidden text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                Estoque: <span id="create_stock_qty">0</span> un
                            </span>
                        </div>
                        <div class="relative">
                            <input type="text" id="create_part_input" 
                                   placeholder="Comece a digitar o nome da peça ou insumo..."
                                   autocomplete="off"
                                   class="w-full pl-3.5 pr-8 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white placeholder-slate-500 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <input type="hidden" id="create_product_id" value="0">
                            <button type="button" id="create_clear_btn" class="hidden absolute right-2.5 top-2.5 text-slate-400 hover:text-white text-xs font-bold" title="Limpar seleção">
                                ✕
                            </button>
                        </div>

                        <!-- Dropdown Flutuante de Autocomplete -->
                        <div id="create_autocomplete_dropdown" 
                             class="hidden absolute z-50 left-0 right-0 mt-1 max-h-56 overflow-y-auto rounded-xl bg-slate-900 border border-slate-700 shadow-2xl divide-y divide-slate-800 text-xs">
                        </div>
                    </div>

                    <!-- Qtd -->
                    <div class="sm:col-span-2">
                        <label for="create_part_qty" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Qtd
                        </label>
                        <input type="number" id="create_part_qty" value="1" min="1" max="99" 
                               class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono text-center focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Unitário (R$) -->
                    <div class="sm:col-span-2">
                        <label for="create_part_price" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                            Unitário (R$)
                        </label>
                        <input type="text" id="create_part_price" value="0,00" 
                               class="w-full px-3 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Botão Adicionar -->
                    <div class="sm:col-span-2">
                        <button type="button" id="btn_add_initial_part" 
                                class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5">
                            <span>+ Adicionar</span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- Tabela Dinâmica de Peças Iniciais Adicionadas -->
            <div id="initial_parts_container" class="space-y-2">
                <div id="initial_parts_empty" class="p-4 rounded-xl bg-slate-950/50 border border-dashed border-slate-800 text-center text-xs text-slate-500">
                    Nenhuma peça vinculada inicialmente. (Você também poderá adicionar ou alterar itens depois na tela de gestão da OS).
                </div>

                <div id="initial_parts_table_wrapper" class="hidden overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Peça / Insumo</th>
                                <th class="py-2.5 px-3 text-center">Qtd</th>
                                <th class="py-2.5 px-3">Valor Unit.</th>
                                <th class="py-2.5 px-3">Subtotal</th>
                                <th class="py-2.5 px-3 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="initial_parts_tbody" class="divide-y divide-slate-800/60 font-medium">
                            <!-- Inserido dinamicamente via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BLOCO 5: Valores e Status Inicial -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">
                5. Orçamento Estimado & Termos
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="labor_value" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Mão de Obra (R$)
                    </label>
                    <input type="text" id="labor_value" name="labor_value"
                           value="<?= htmlspecialchars($old['labor_value'] ?? '0,00', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="parts_value" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Peças Estimadas (R$)
                    </label>
                    <input type="text" id="parts_value" name="parts_value"
                           value="<?= htmlspecialchars($old['parts_value'] ?? '0,00', ENT_QUOTES, 'UTF-8') ?>"
                           class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Status Inicial
                    </label>
                    <select id="status" name="status"
                            class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        <option value="budget">Orçamento (Aguardando Aprovação)</option>
                        <option value="approved">Aprovado pelo Cliente</option>
                        <option value="in_progress">Em Andamento na Bancada</option>
                    </select>
                </div>
            </div>

            <!-- Blindagem Jurídica RN04 -->
            <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-xs text-slate-400 leading-relaxed">
                ⚖️ <strong>Termo Legal de Garantia (RN04):</strong> Esta OS inclui automaticamente a garantia legal de 90 dias (CDC) e a cláusula de abandono caso o item não seja retirado em até 90 dias após a notificação oficial.
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" 
               class="px-5 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                Cancelar
            </a>
            <button type="submit" 
                    class="px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-sm shadow-xl shadow-indigo-600/30 transition-all transform hover:-translate-y-0.5">
                Emitir Ordem de Serviço &rarr;
            </button>
        </div>

    </form>

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

        const inputPart = document.getElementById('create_part_input');
        const inputProductId = document.getElementById('create_product_id');
        const inputQty = document.getElementById('create_part_qty');
        const inputPrice = document.getElementById('create_part_price');
        const btnAdd = document.getElementById('btn_add_initial_part');
        const dropdown = document.getElementById('create_autocomplete_dropdown');
        const badgeStock = document.getElementById('create_stock_badge');
        const badgeStockQty = document.getElementById('create_stock_qty');
        const btnClear = document.getElementById('create_clear_btn');

        const tbody = document.getElementById('initial_parts_tbody');
        const emptyMsg = document.getElementById('initial_parts_empty');
        const tableWrapper = document.getElementById('initial_parts_table_wrapper');
        const partsValueField = document.getElementById('parts_value');

        let initialParts = [];

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
            inputPart.value = item.name;
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

        inputPart.addEventListener('input', function() {
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

        inputPart.addEventListener('focus', function() {
            const q = this.value.trim().toLowerCase();
            const filtered = q ? stockItems.filter(i => i.name.toLowerCase().includes(q)) : stockItems.slice(0, 8);
            renderDropdown(filtered, this.value);
        });

        if (btnClear) {
            btnClear.addEventListener('click', function() {
                inputPart.value = '';
                inputProductId.value = '0';
                inputPrice.value = '0,00';
                badgeStock.classList.add('hidden');
                btnClear.classList.add('hidden');
                closeDropdown();
                inputPart.focus();
            });
        }

        document.addEventListener('click', function(e) {
            if (!inputPart.contains(e.target) && !dropdown.contains(e.target)) {
                closeDropdown();
            }
        });

        // Adicionar peça à tabela
        btnAdd.addEventListener('click', function() {
            const name = inputPart.value.trim();
            if (!name) {
                alert('Informe ou selecione o nome da peça/insumo.');
                inputPart.focus();
                return;
            }

            const productId = parseInt(inputProductId.value) || 0;
            const qty = Math.max(1, parseInt(inputQty.value) || 1);
            let rawPrice = (inputPrice.value || '0').replace('.', '').replace(',', '.');
            const unitPrice = parseFloat(rawPrice) || 0.0;

            initialParts.push({
                productId: productId,
                name: name,
                quantity: qty,
                unitPrice: unitPrice
            });

            renderTable();

            // Limpa campos
            inputPart.value = '';
            inputProductId.value = '0';
            inputQty.value = '1';
            inputPrice.value = '0,00';
            badgeStock.classList.add('hidden');
            if (btnClear) btnClear.classList.add('hidden');
        });

        function renderTable() {
            tbody.innerHTML = '';
            let total = 0.0;

            if (initialParts.length === 0) {
                emptyMsg.classList.remove('hidden');
                tableWrapper.classList.add('hidden');
            } else {
                emptyMsg.classList.add('hidden');
                tableWrapper.classList.remove('hidden');

                initialParts.forEach((item, index) => {
                    const subtotal = item.quantity * item.unitPrice;
                    total += subtotal;

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-800/30';
                    tr.innerHTML = `
                        <td class="py-2.5 px-3 font-bold text-white">
                            ${escapeHtml(item.name)}
                            <input type="hidden" name="initial_parts[${index}][product_id]" value="${item.productId}">
                            <input type="hidden" name="initial_parts[${index}][part_name]" value="${escapeHtml(item.name)}">
                            <input type="hidden" name="initial_parts[${index}][quantity]" value="${item.quantity}">
                            <input type="hidden" name="initial_parts[${index}][unit_price]" value="${item.unitPrice.toFixed(2)}">
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono">${item.quantity}</td>
                        <td class="py-2.5 px-3 font-mono">R$ ${item.unitPrice.toFixed(2).replace('.', ',')}</td>
                        <td class="py-2.5 px-3 font-mono font-bold text-emerald-400">R$ ${subtotal.toFixed(2).replace('.', ',')}</td>
                        <td class="py-2.5 px-3 text-right">
                            <button type="button" class="text-rose-400 hover:text-rose-300 font-bold hover:underline" data-index="${index}">
                                Remover
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });

                // Botões de remover
                tbody.querySelectorAll('button[data-index]').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-index'));
                        initialParts.splice(idx, 1);
                        renderTable();
                    });
                });
            }

            if (partsValueField) {
                partsValueField.value = total.toFixed(2).replace('.', ',');
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
    </script>

</div>
