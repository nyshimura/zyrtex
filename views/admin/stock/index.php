<?php
/**
 * View: Controle de Peças & Estoque
 */
$csrf = \Zyrtex\Core\CSRF::token();
$products = $products ?? [];
$search = $search ?? '';

$totalItems = count($products);

// Considera apenas peças físicas com estoque real em prateleira (exclui serviços e mão de obra)
$physicalProducts = array_filter($products, fn($p) => empty($p['is_service']));
$servicesCount = $totalItems - count($physicalProducts);

$totalStock = array_reduce($physicalProducts, fn($carry, $p) => $carry + (int)$p['stock'], 0);
$totalValue = array_reduce($physicalProducts, fn($carry, $p) => $carry + ((int)$p['stock'] * (float)$p['price']), 0);
$totalCostValue = array_reduce($physicalProducts, fn($carry, $p) => $carry + ((int)$p['stock'] * (float)$p['cost_price']), 0);
?>

<div class="space-y-6">

    <!-- Header e Botão Novo Item -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                Peças & Estoque de Balcão
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Catálogo de peças de reposição e produtos para venda no balcão e baixa automática na OS.
            </p>
        </div>

        <a href="#novo-item" 
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/30 transition-all transform hover:-translate-y-0.5">
            <span class="text-lg">+</span>
            <span>Novo Item / Peça</span>
        </a>
    </div>

    <!-- 3 Cards de Resumo de Estoque Físico -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Itens no Catálogo</span>
            <span class="text-2xl font-black text-white mt-1 block"><?= $totalItems ?> cadastrados</span>
            <span class="text-[11px] text-slate-500 mt-1 block">
                <?= count($physicalProducts) ?> peças físicas <?= $servicesCount > 0 ? "• {$servicesCount} serviços" : '' ?>
            </span>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Peças em Prateleira</span>
            <span class="text-2xl font-black text-indigo-400 mt-1 block"><?= $totalStock ?> un.</span>
            <span class="text-[11px] text-slate-500 mt-1 block">Estoque físico disponível</span>
        </div>
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 font-semibold uppercase tracking-wider block">Valor Total em Estoque</span>
            <span class="text-2xl font-black text-emerald-400 mt-1 block">R$ <?= number_format($totalValue, 2, ',', '.') ?></span>
            <span class="text-[11px] text-slate-500 mt-1 block">
                Custo de aquisição: R$ <?= number_format($totalCostValue, 2, ',', '.') ?>
            </span>
        </div>
    </div>

    <!-- Barra de Busca -->
    <form method="GET" action="<?= \Zyrtex\Core\Response::url('/admin/estoque') ?>" class="flex gap-3">
        <div class="relative flex-1">
            <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" 
                   placeholder="Buscar por nome da peça, código SKU ou categoria..."
                   class="w-full pl-11 pr-4 py-3 rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            <span class="absolute left-4 top-3.5 text-slate-500">🔍</span>
        </div>
        <button type="submit" class="px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-bold text-white transition">
            Buscar
        </button>
        <?php if (!empty($search)): ?>
            <a href="<?= \Zyrtex\Core\Response::url('/admin/estoque') ?>" class="px-4 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-sm text-slate-400 hover:text-white border border-slate-800 transition">
                Limpar
            </a>
        <?php endif; ?>
    </form>

    <!-- Tabela de Produtos -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <?php if (empty($products)): ?>
            <div class="text-center py-16 px-4">
                <span class="text-4xl block mb-3">📦</span>
                <h3 class="text-base font-bold text-white mb-1">Nenhum item em estoque</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-5">
                    <?= !empty($search) ? 'Nenhum item corresponde à pesquisa.' : 'Cadastre suas peças para dar baixa automática quando aplicar na Ordem de Serviço.' ?>
                </p>
                <a href="#novo-item" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500">
                    + Cadastrar Primeira Peça
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="text-xs uppercase bg-slate-950/60 text-slate-400 border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4 w-14 text-center">Foto</th>
                            <th class="py-3 px-4">Item / Peça</th>
                            <th class="py-3 px-4">SKU / Código</th>
                            <th class="py-3 px-4">Categoria</th>
                            <th class="py-3 px-4">Preço Custo</th>
                            <th class="py-3 px-4">Preço Venda</th>
                            <th class="py-3 px-4 text-center">Estoque</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 font-medium">
                        <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3 px-4 text-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-center overflow-hidden mx-auto shadow-sm">
                                        <?php if (!empty($p['image_path'])): ?>
                                            <img src="<?= htmlspecialchars(asset($p['image_path']), ENT_QUOTES, 'UTF-8') ?>" 
                                                 alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="text-sm"><?= !empty($p['is_service']) ? '⚙️' : '📦' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-white">
                                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($p['is_service'])): ?>
                                        <span class="text-[10px] ml-1.5 px-1.5 py-0.2 rounded bg-purple-500/20 text-purple-300 border border-purple-500/30">Serviço</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-400">
                                    <?= htmlspecialchars($p['sku'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-300">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700">
                                        <?= htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-400">
                                    R$ <?= number_format((float)$p['cost_price'], 2, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-emerald-400">
                                    R$ <?= number_format((float)$p['price'], 2, ',', '.') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if (!empty($p['is_service'])): ?>
                                        <span class="text-xs text-slate-500 font-semibold">Ilimitado</span>
                                    <?php else: 
                                        $stockQty = (int)$p['stock'];
                                        $stockClass = $stockQty <= 2 ? 'bg-rose-500/20 text-rose-300 border-rose-500/30 font-bold' : ($stockQty <= 5 ? 'bg-amber-500/20 text-amber-300 border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20');
                                    ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs border <?= $stockClass ?>">
                                            <?= $stockQty ?> un.
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center justify-end gap-2">
                                        <button type="button" 
                                                onclick="openEditStockModal(this)"
                                                data-id="<?= (int)$p['id'] ?>"
                                                data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-sku="<?= htmlspecialchars($p['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-category="<?= htmlspecialchars($p['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                data-cost="<?= number_format((float)$p['cost_price'], 2, ',', '') ?>"
                                                data-price="<?= number_format((float)$p['price'], 2, ',', '') ?>"
                                                data-stock="<?= (int)$p['stock'] ?>"
                                                data-service="<?= (int)($p['is_service'] ?? 0) ?>"
                                                data-image="<?= htmlspecialchars(asset($p['image_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 transition flex items-center gap-1.5 cursor-pointer shadow-sm">
                                            <span>✏️</span>
                                            <span>Editar</span>
                                        </button>

                                        <form method="POST" action="<?= \Zyrtex\Core\Response::url('/admin/estoque/excluir') ?>" 
                                              onsubmit="return confirm('Tem certeza que deseja remover este item do estoque?');" class="inline m-0">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <button type="submit" 
                                                    title="Excluir do Estoque"
                                                    class="p-1.5 rounded-xl text-xs font-semibold bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 transition cursor-pointer">
                                                🗑️
                                            </button>
                                        </form>
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

<!-- MODAL CSS PURO (:target) para Cadastro de Item -->
<div id="novo-item" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none target:opacity-100 target:pointer-events-auto transition-opacity duration-200">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-5">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span>📦</span>
                <span>Cadastrar Item no Estoque</span>
            </h3>
            <a href="#" class="text-slate-400 hover:text-white text-xl font-bold">&times;</a>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/estoque') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Imagem do Produto / Peça -->
            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Foto da Peça / Produto (Opcional):
                    </label>
                    <span class="text-[10px] text-slate-500">Exibida no PDV Totem e Estoque</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl border border-slate-700 bg-slate-900 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
                        <img id="preview_novo_item_img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <span id="placeholder_novo_item_img" class="text-2xl text-slate-600">📦</span>
                    </div>

                    <div class="flex-1 space-y-2">
                        <input type="file" name="image_file" accept="image/*"
                               onchange="previewImageInput(this, 'preview_novo_item_img', 'placeholder_novo_item_img')"
                               class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer bg-slate-900 border border-slate-700 rounded-xl p-1">
                        
                        <input type="url" name="image_url" placeholder="Ou cole a URL da imagem (https://...)"
                               oninput="previewUrlInput(this.value, 'preview_novo_item_img', 'placeholder_novo_item_img')"
                               class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Nome da Peça ou Produto <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="name" name="name" required
                       placeholder="Ex: Tela Display iPhone 11 Original Nacional"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="sku" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Código / SKU
                    </label>
                    <input type="text" id="sku" name="sku" placeholder="Ex: TELA-IPH11"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-mono">
                </div>

                <div>
                    <label for="category" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Categoria
                    </label>
                    <input type="text" id="category" name="category" placeholder="Ex: Displays, Baterias"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center gap-3">
                <input type="checkbox" id="novo_is_service" name="is_service" value="1" onchange="toggleStockInput('novo')"
                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700 cursor-pointer">
                <label for="novo_is_service" class="text-xs text-slate-300 cursor-pointer select-none">
                    <strong>Mão de Obra / Serviço</strong> (sem controle de estoque físico)
                </label>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="cost_price" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Custo (R$)
                    </label>
                    <input type="text" id="cost_price" name="cost_price" placeholder="0,00"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                </div>

                <div>
                    <label for="price" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Venda (R$) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="price" name="price" required placeholder="0,00"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-bold text-emerald-400">
                </div>

                <div id="novo_stock_container">
                    <label for="stock" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Qtd. Estoque
                    </label>
                    <input type="number" id="stock" name="stock" value="1" min="0"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-bold">
                </div>
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-800">
                <a href="#" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition">
                    Salvar no Estoque &rarr;
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DINÂMICO PARA EDIÇÃO DE PRODUTO / PEÇA -->
<div id="modal-editar-item" class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 hidden items-center justify-center p-4 transition-opacity duration-200">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-5">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <span>✏️</span>
                <span>Editar Peça / Produto</span>
            </h3>
            <button type="button" onclick="closeEditStockModal()" class="text-slate-400 hover:text-white text-xl font-bold p-1 cursor-pointer">&times;</button>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/estoque/editar') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" id="edit_id" name="id" value="">

            <!-- Imagem do Produto / Peça na Edição -->
            <div class="p-3.5 rounded-2xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Foto da Peça / Produto:
                    </label>
                    <label id="container_remove_image" class="hidden items-center gap-1.5 text-[11px] text-rose-400 hover:text-rose-300 cursor-pointer">
                        <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-700 text-rose-600 focus:ring-rose-500">
                        <span>Remover Foto</span>
                    </label>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl border border-slate-700 bg-slate-900 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
                        <img id="preview_edit_item_img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <span id="placeholder_edit_item_img" class="text-2xl text-slate-600">📦</span>
                    </div>

                    <div class="flex-1 space-y-2">
                        <input type="file" name="image_file" accept="image/*"
                               onchange="previewImageInput(this, 'preview_edit_item_img', 'placeholder_edit_item_img')"
                               class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer bg-slate-900 border border-slate-700 rounded-xl p-1">
                        
                        <input type="url" id="edit_image_url" name="image_url" placeholder="Ou cole a URL da imagem (https://...)"
                               oninput="previewUrlInput(this.value, 'preview_edit_item_img', 'placeholder_edit_item_img')"
                               class="w-full px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            <div>
                <label for="edit_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                    Nome da Peça ou Produto <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="edit_name" name="name" required
                       placeholder="Ex: Tela Display iPhone 11 Original Nacional"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_sku" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Código / SKU
                    </label>
                    <input type="text" id="edit_sku" name="sku" placeholder="Ex: TELA-IPH11"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-mono">
                </div>

                <div>
                    <label for="edit_category" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Categoria
                    </label>
                    <input type="text" id="edit_category" name="category" placeholder="Ex: Displays, Baterias"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                </div>
            </div>

            <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 flex items-center gap-3">
                <input type="checkbox" id="edit_is_service" name="is_service" value="1" onchange="toggleStockInput('edit')"
                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700 cursor-pointer">
                <label for="edit_is_service" class="text-xs text-slate-300 cursor-pointer select-none">
                    <strong>Mão de Obra / Serviço</strong> (sem controle de estoque físico)
                </label>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="edit_cost_price" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Custo (R$)
                    </label>
                    <input type="text" id="edit_cost_price" name="cost_price" placeholder="0,00"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                </div>

                <div>
                    <label for="edit_price" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Venda (R$) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="edit_price" name="price" required placeholder="0,00"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-bold text-emerald-400">
                </div>

                <div id="edit_stock_container">
                    <label for="edit_stock" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Qtd. Estoque
                    </label>
                    <input type="number" id="edit_stock" name="stock" value="1" min="0"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm font-bold">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-800">
                <button type="button" onclick="closeEditStockModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white transition cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5 cursor-pointer">
                    <span>💾</span>
                    <span>Salvar Alterações</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditStockModal(btn) {
    const d = btn.dataset;
    document.getElementById('edit_id').value = d.id;
    document.getElementById('edit_name').value = d.name;
    document.getElementById('edit_sku').value = d.sku;
    document.getElementById('edit_category').value = d.category;
    document.getElementById('edit_cost_price').value = d.cost;
    document.getElementById('edit_price').value = d.price;
    document.getElementById('edit_stock').value = d.stock;
    
    // Imagem
    const editImg = document.getElementById('preview_edit_item_img');
    const editPlaceholder = document.getElementById('placeholder_edit_item_img');
    const editUrlInput = document.getElementById('edit_image_url');
    const removeContainer = document.getElementById('container_remove_image');

    if (d.image && d.image.trim() !== '') {
        editImg.src = d.image;
        editImg.classList.remove('hidden');
        editPlaceholder.classList.add('hidden');
        editUrlInput.value = d.image;
        if (removeContainer) removeContainer.classList.remove('hidden'), removeContainer.classList.add('inline-flex');
    } else {
        editImg.src = '';
        editImg.classList.add('hidden');
        editPlaceholder.classList.remove('hidden');
        editUrlInput.value = '';
        if (removeContainer) removeContainer.classList.add('hidden'), removeContainer.classList.remove('inline-flex');
    }

    const isService = d.service === '1';
    const chk = document.getElementById('edit_is_service');
    if (chk) {
        chk.checked = isService;
        toggleStockInput('edit');
    }

    const modal = document.getElementById('modal-editar-item');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.getElementById('edit_name').focus();
}

function closeEditStockModal() {
    const modal = document.getElementById('modal-editar-item');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function previewImageInput(input, imgId, placeholderId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgId);
            const placeholder = document.getElementById(placeholderId);
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

function previewUrlInput(url, imgId, placeholderId) {
    const img = document.getElementById(imgId);
    const placeholder = document.getElementById(placeholderId);
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

function toggleStockInput(type) {
    if (type === 'edit') {
        const chk = document.getElementById('edit_is_service');
        const container = document.getElementById('edit_stock_container');
        const stockInput = document.getElementById('edit_stock');
        if (chk && chk.checked) {
            if (container) container.classList.add('opacity-40', 'pointer-events-none');
            if (stockInput) stockInput.value = '0';
        } else {
            if (container) container.classList.remove('opacity-40', 'pointer-events-none');
        }
    } else if (type === 'novo') {
        const chk = document.getElementById('novo_is_service');
        const container = document.getElementById('novo_stock_container');
        const stockInput = document.getElementById('stock');
        if (chk && chk.checked) {
            if (container) container.classList.add('opacity-40', 'pointer-events-none');
            if (stockInput) stockInput.value = '0';
        } else {
            if (container) container.classList.remove('opacity-40', 'pointer-events-none');
        }
    }
}

// Fechar ao pressionar ESC ou clicar fora do conteúdo
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditStockModal();
    }
});
document.getElementById('modal-editar-item')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditStockModal();
    }
});
</script>
