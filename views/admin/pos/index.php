<?php
/**
 * View: PDV Totem Fast-Food Touch Screen (/admin/pdv - RF04)
 */
$csrf = \Zyrtex\Core\CSRF::token();
$products = $products ?? [];
$categories = $categories ?? [];
$selectedCategory = $selectedCategory ?? '';
$search = $search ?? '';
$cart = $cart ?? ['items' => []];
$cartSubtotal = $cartSubtotal ?? 0.0;
$cartCount = $cartCount ?? 0;
$lastSale = $lastSale ?? null;
?>

<div class="h-full flex flex-col md:flex-row overflow-hidden bg-slate-950">

    <!-- ===================================================================== -->
    <!-- COLUNA ESQUERDA: CATÁLOGO DE PRODUTOS & FILTROS TOUCH (65% width)   -->
    <!-- ===================================================================== -->
    <div class="flex-1 flex flex-col h-full overflow-hidden border-r border-slate-800">
        
        <!-- BARRA SUPERIOR DE FILTROS & BUSCA TOUCH -->
        <div class="p-4 bg-slate-900 border-b border-slate-800 space-y-3 shrink-0">
            
            <!-- Barra de Busca Rápida -->
            <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv') ?>" method="GET" class="flex gap-2">
                <?php if (!empty($selectedCategory)): ?>
                    <input type="hidden" name="categoria" value="<?= htmlspecialchars($selectedCategory, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                <div class="relative flex-1">
                    <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Buscar por nome da peça, modelo ou código SKU..."
                           autocomplete="off"
                           class="w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm font-medium">
                    <span class="absolute left-4 top-3.5 text-slate-500 text-base">🔍</span>
                </div>
                <button type="submit" 
                        class="px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-indigo-600/30">
                    Buscar
                </button>
                <?php if (!empty($search) || !empty($selectedCategory)): ?>
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/pdv') ?>" 
                       class="px-4 py-3 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs flex items-center transition border border-slate-700">
                        Limpar
                    </a>
                <?php endif; ?>
            </form>

            <!-- Pílulas de Categorias Touch (Estilo Fast-Food) -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                <a href="<?= \Zyrtex\Core\Response::url('/admin/pdv' . (!empty($search) ? '?q=' . urlencode($search) : '')) ?>" 
                   class="px-4 py-2 rounded-xl text-xs font-extrabold whitespace-nowrap transition <?= empty($selectedCategory) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' ?>">
                    🌟 Todos os Itens
                </a>

                <?php foreach ($categories as $cat): ?>
                    <?php $isActive = ($selectedCategory === $cat); ?>
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/pdv?categoria=' . urlencode($cat) . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>" 
                       class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition <?= $isActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700' ?>">
                        <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- Alerta de Venda Recente com Botões de Impressão de Cupom -->
        <?php if ($lastSale): ?>
            <div class="m-4 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shrink-0 shadow-lg animate-pulse-once">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xl font-bold">
                        ✓
                    </span>
                    <div>
                        <h4 class="text-sm font-extrabold text-white">Venda #<?= (int)$lastSale['id'] ?> Concluída!</h4>
                        <span class="text-xs text-emerald-400 font-semibold">
                            Total: R$ <?= number_format((float)$lastSale['total_amount'], 2, ',', '.') ?> &bull; Pagamento: <?= strtoupper(htmlspecialchars($lastSale['payment_method'], ENT_QUOTES, 'UTF-8')) ?>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/pdv/comprovante?id=' . (int)$lastSale['id'] . '&formato=80mm') ?>" target="_blank"
                       class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-md flex items-center gap-1.5">
                        <span>🖨️ Cupom 80mm</span>
                    </a>
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/pdv/comprovante?id=' . (int)$lastSale['id'] . '&formato=58mm') ?>" target="_blank"
                       class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition border border-slate-700">
                        <span>Mini 58mm</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- GRADE DE CARDS TOUCH DE PRODUTOS (Scroll Vertical) -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6">
            <?php if (empty($products)): ?>
                <div class="h-full flex flex-col items-center justify-center text-center p-8">
                    <span class="text-5xl block mb-3">🔍</span>
                    <h3 class="text-base font-bold text-white mb-1">Nenhum item encontrado</h3>
                    <p class="text-xs text-slate-400 max-w-sm mb-4">
                        Nenhum produto ou serviço cadastrado corresponde à sua busca ou categoria.
                    </p>
                    <a href="<?= \Zyrtex\Core\Response::url('/admin/estoque#novo-item') ?>" 
                       class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition">
                        + Cadastrar Peças no Estoque
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                    <?php foreach ($products as $prod): 
                        $isService = ((int)$prod['is_service'] === 1);
                        $stock = (int)$prod['stock'];
                        $isOutOfStock = (!$isService && $stock <= 0);
                    ?>
                        <div class="bg-slate-900 border <?= $isOutOfStock ? 'border-rose-900/40 opacity-60' : 'border-slate-800 hover:border-indigo-500/60' ?> rounded-2xl p-4 flex flex-col justify-between transition-all duration-200 hover:shadow-xl hover:shadow-indigo-950/40 relative group">
                            
                            <!-- Badges Superiores -->
                            <div>
                                <div class="flex items-center justify-between gap-1 mb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md <?= $isService ? 'bg-purple-500/20 text-purple-300 border border-purple-500/30' : 'bg-slate-800 text-slate-400' ?>">
                                        <?= htmlspecialchars($prod['category'] ?? ($isService ? 'Serviço' : 'Peça'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    
                                    <?php if ($isService): ?>
                                        <span class="text-[10px] font-bold text-purple-400">⚡ Mão de Obra</span>
                                    <?php elseif ($isOutOfStock): ?>
                                        <span class="text-[10px] font-bold text-rose-400">Esgotado</span>
                                    <?php else: ?>
                                        <span class="text-[10px] font-semibold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">
                                            <?= $stock ?> un.
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Imagem do Produto / Totem Visual -->
                                <?php if (!empty($prod['image_path'])): ?>
                                    <div class="w-full h-28 rounded-xl overflow-hidden bg-slate-950 mb-2.5 border border-slate-800/80 relative group-hover:border-indigo-500/40 transition-colors">
                                        <img src="<?= htmlspecialchars(asset($prod['image_path']), ENT_QUOTES, 'UTF-8') ?>" 
                                             alt="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-slate-700\'><svg class=\'w-7 h-7 opacity-40\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'/></svg></div>';">
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-20 rounded-xl bg-slate-950/50 border border-slate-800/50 mb-2.5 flex items-center justify-center text-slate-700">
                                        <svg class="w-6 h-6 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                <?php endif; ?>

                                <!-- Nome do Produto -->
                                <h3 class="text-sm font-bold text-white leading-snug line-clamp-2 mb-1" title="<?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($prod['name'], ENT_QUOTES, 'UTF-8') ?>
                                </h3>

                                <?php if (!empty($prod['sku'])): ?>
                                    <span class="text-[10px] font-mono text-slate-500 block mb-2">
                                        SKU: <?= htmlspecialchars($prod['sku'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Preço & Botão de Toque Adicionar -->
                            <div class="pt-3 border-t border-slate-800/80 mt-2 flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-[10px] text-slate-400 block font-medium">Preço</span>
                                    <span class="text-base sm:text-lg font-black text-emerald-400 font-mono leading-none">
                                        R$ <?= number_format((float)$prod['price'], 2, ',', '.') ?>
                                    </span>
                                </div>

                                <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv/adicionar') ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="product_id" value="<?= (int)$prod['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    
                                    <button type="submit" <?= $isOutOfStock ? 'disabled' : '' ?>
                                            class="w-10 h-10 rounded-xl flex items-center justify-center font-extrabold text-lg transition-all <?= $isOutOfStock ? 'bg-slate-800 text-slate-600 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30 active:scale-95' ?>"
                                            title="Toque para adicionar ao carrinho">
                                        +
                                    </button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- COLUNA DIREITA: O CARRINHO DO TOTEM & CHECKOUT EXPRESSO (35% width)  -->
    <!-- ===================================================================== -->
    <div class="w-full md:w-96 lg:w-[420px] bg-slate-900 flex flex-col h-full border-t md:border-t-0 md:border-l border-slate-800 shadow-2xl shrink-0">
        
        <!-- Topo do Carrinho -->
        <div class="p-4 border-b border-slate-800 flex items-center justify-between shrink-0 bg-slate-900/90">
            <div class="flex items-center gap-2">
                <span class="text-xl">🛒</span>
                <h2 class="text-sm font-extrabold text-white">Carrinho da Venda</h2>
                <span class="px-2 py-0.5 rounded-full text-[11px] font-black bg-indigo-600 text-white">
                    <?= $cartCount ?>
                </span>
            </div>

            <?php if (!empty($cart['items'])): ?>
                <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv/limpar') ?>" method="POST" onsubmit="return confirm('Deseja limpar todos os itens do carrinho?');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold hover:underline transition">
                        Limpar Carrinho
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Lista de Itens no Carrinho (Scroll Vertical) -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <?php if (empty($cart['items'])): ?>
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-500">
                    <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center text-2xl mb-3">
                        🛍️
                    </div>
                    <p class="text-sm font-bold text-slate-300 mb-1">Carrinho Vazio</p>
                    <p class="text-xs text-slate-500 max-w-xs">
                        Toque no botão <strong class="text-indigo-400">+</strong> dos produtos ao lado para incluir na venda de balcão.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($cart['items'] as $item): 
                    $subtotal = (float)$item['price'] * (int)$item['qty'];
                ?>
                    <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between gap-3 shadow-sm">
                        
                        <!-- Dados do Item -->
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-white truncate" title="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                            </h4>
                            <span class="text-[11px] text-slate-400 font-mono">
                                R$ <?= number_format((float)$item['price'], 2, ',', '.') ?> cada
                            </span>
                        </div>

                        <!-- Controles de Quantidade Touch (+ / -) -->
                        <div class="flex items-center gap-1.5 shrink-0">
                            <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv/quantidade') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                <input type="hidden" name="action" value="dec">
                                <button type="submit" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center justify-center transition active:scale-95">
                                    -
                                </button>
                            </form>

                            <span class="w-6 text-center text-xs font-black text-white font-mono">
                                <?= (int)$item['qty'] ?>
                            </span>

                            <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv/quantidade') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                <input type="hidden" name="action" value="inc">
                                <button type="submit" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs flex items-center justify-center transition active:scale-95">
                                    +
                                </button>
                            </form>
                        </div>

                        <!-- Subtotal & Remover -->
                        <div class="text-right shrink-0">
                            <span class="text-xs font-black text-emerald-400 font-mono block">
                                R$ <?= number_format($subtotal, 2, ',', '.') ?>
                            </span>
                            <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv/remover') ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                                <button type="submit" class="text-[10px] text-slate-500 hover:text-rose-400 transition" title="Remover item">
                                    remover
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- PAINEL DE PAGAMENTO & CHECKOUT EXPRESSO -->
        <div class="p-4 sm:p-5 bg-slate-950 border-t border-slate-800 space-y-4 shrink-0">
            
            <!-- Resumo Financeiro Grande -->
            <div class="flex items-baseline justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total a Pagar</span>
                <span class="text-3xl font-black text-emerald-400 font-mono tracking-tight">
                    R$ <?= number_format($cartSubtotal, 2, ',', '.') ?>
                </span>
            </div>

            <?php if (!empty($cart['items'])): ?>
                <!-- Formulário de Finalização -->
                <form action="<?= \Zyrtex\Core\Response::url('/admin/pdv/finalizar') ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Seletor de Forma de Pagamento (Cards Grandes Touch) -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                            Forma de Recebimento
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="p-2.5 rounded-xl border-2 border-indigo-600 bg-indigo-950/30 flex items-center gap-2 cursor-pointer transition hover:border-indigo-400">
                                <input type="radio" name="payment_method" value="pix" checked class="text-indigo-600">
                                <div>
                                    <span class="text-xs font-bold text-white block">⚡ Pix</span>
                                    <span class="text-[9px] text-indigo-300">Copia-e-Cola / QR</span>
                                </div>
                            </label>

                            <label class="p-2.5 rounded-xl border border-slate-800 bg-slate-900 flex items-center gap-2 cursor-pointer transition hover:border-slate-700">
                                <input type="radio" name="payment_method" value="dinheiro" class="text-indigo-600">
                                <div>
                                    <span class="text-xs font-bold text-white block">💵 Dinheiro</span>
                                    <span class="text-[9px] text-slate-400">Cédulas / Moedas</span>
                                </div>
                            </label>

                            <label class="p-2.5 rounded-xl border border-slate-800 bg-slate-900 flex items-center gap-2 cursor-pointer transition hover:border-slate-700">
                                <input type="radio" name="payment_method" value="cartao_debito" class="text-indigo-600">
                                <div>
                                    <span class="text-xs font-bold text-white block">💳 Débito</span>
                                    <span class="text-[9px] text-slate-400">Maquininha TEF</span>
                                </div>
                            </label>

                            <label class="p-2.5 rounded-xl border border-slate-800 bg-slate-900 flex items-center gap-2 cursor-pointer transition hover:border-slate-700">
                                <input type="radio" name="payment_method" value="cartao_credito" class="text-indigo-600">
                                <div>
                                    <span class="text-xs font-bold text-white block">💳 Crédito</span>
                                    <span class="text-[9px] text-slate-400">À vista ou parcelado</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Botão Touch Gigante de Confirmação -->
                    <button type="submit" 
                            class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-black text-base shadow-xl shadow-emerald-600/30 hover:shadow-emerald-600/50 transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <span>CONFIRMAR VENDA</span>
                        <span>&bull;</span>
                        <span class="font-mono">R$ <?= number_format($cartSubtotal, 2, ',', '.') ?></span>
                        <span>&rarr;</span>
                    </button>
                </form>
            <?php else: ?>
                <button type="button" disabled 
                        class="w-full py-4 rounded-2xl bg-slate-800 text-slate-600 font-bold text-sm cursor-not-allowed">
                    Aguardando itens no carrinho...
                </button>
            <?php endif; ?>

        </div>

    </div>

</div>
