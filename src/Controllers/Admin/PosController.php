<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;

class PosController {
    /**
     * Interface Principal do PDV Totem Fast-Food (/admin/pdv)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $selectedCategory = trim((string)$request->query('categoria', ''));
        $search = trim((string)$request->query('q', ''));
        $saleSuccessId = (int)$request->query('venda_sucesso', 0);

        // Categorias distintas para os filtros rápidos em botões largos estilo totem
        $categoriesRaw = Database::fetchAll(
            "SELECT DISTINCT category FROM products_pos WHERE tenant_id = :tenant_id AND category IS NOT NULL AND category != '' ORDER BY category ASC",
            ['tenant_id' => $tenantId]
        );
        $categories = array_map(fn($c) => $c['category'], $categoriesRaw);

        // Query de produtos/serviços
        $sql = "SELECT * FROM products_pos WHERE tenant_id = :tenant_id";
        $params = ['tenant_id' => $tenantId];

        if (!empty($selectedCategory)) {
            $sql .= " AND category = :cat";
            $params['cat'] = $selectedCategory;
        }

        if (!empty($search)) {
            $sql .= " AND (name LIKE :q1 OR sku LIKE :q2)";
            $params['q1'] = "%{$search}%";
            $params['q2'] = "%{$search}%";
        }

        $sql .= " ORDER BY is_service DESC, name ASC";
        $products = Database::fetchAll($sql, $params);

        // Recupera carrinho da sessão
        $cart = $_SESSION['pos_cart'][$tenantId] ?? ['items' => []];

        // Cálculo de totais do carrinho
        $cartSubtotal = 0.0;
        $cartCount = 0;
        foreach ($cart['items'] as $item) {
            $cartSubtotal += ((float)$item['price'] * (int)$item['qty']);
            $cartCount += (int)$item['qty'];
        }

        // Venda recente para atalho de impressão de comprovante
        $lastSale = null;
        if ($saleSuccessId > 0) {
            $lastSale = Database::fetchOne(
                "SELECT * FROM pos_sales WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
                ['id' => $saleSuccessId, 'tenant_id' => $tenantId]
            );
        }

        View::render('admin/pos/index', [
            'tenant' => $tenant,
            'user' => $user,
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'cart' => $cart,
            'cartSubtotal' => $cartSubtotal,
            'cartCount' => $cartCount,
            'lastSale' => $lastSale
        ], 'pos');
    }

    /**
     * Adiciona Item ao Carrinho (POST /admin/pdv/adicionar)
     */
    public function addItem(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $productId = (int)$request->post('product_id', 0);
        $qtyToAdd = max(1, (int)$request->post('quantity', 1));

        if ($productId <= 0) {
            Session::setFlash('error', 'Produto inválido.');
            Response::redirect('/admin/pdv');
            return;
        }

        $product = Database::fetchOne(
            "SELECT id, name, price, stock, is_service, sku, category FROM products_pos WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $productId, 'tenant_id' => $tenantId]
        );

        if (!$product) {
            Session::setFlash('error', 'Item não encontrado no catálogo.');
            Response::redirect('/admin/pdv');
            return;
        }

        if (!isset($_SESSION['pos_cart'][$tenantId])) {
            $_SESSION['pos_cart'][$tenantId] = ['items' => []];
        }

        $currentQty = $_SESSION['pos_cart'][$tenantId]['items'][$productId]['qty'] ?? 0;
        $newQty = $currentQty + $qtyToAdd;

        // Se não for serviço, valida estoque disponível
        if ((int)$product['is_service'] === 0 && (int)$product['stock'] < $newQty) {
            Session::setFlash('warning', "Estoque insuficiente para '{$product['name']}'. Disponível: {$product['stock']} un.");
            Response::redirect('/admin/pdv');
            return;
        }

        $_SESSION['pos_cart'][$tenantId]['items'][$productId] = [
            'id' => (int)$product['id'],
            'name' => (string)$product['name'],
            'sku' => (string)($product['sku'] ?? ''),
            'price' => (float)$product['price'],
            'is_service' => (int)$product['is_service'],
            'qty' => $newQty
        ];

        Session::setFlash('success', "Adicionado: {$product['name']} (x{$qtyToAdd})");
        Response::redirect('/admin/pdv');
    }

    /**
     * Atualiza Quantidade de um Item (POST /admin/pdv/quantidade)
     */
    public function updateQty(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $productId = (int)$request->post('product_id', 0);
        $action = (string)$request->post('action', ''); // 'inc' ou 'dec'

        if (!isset($_SESSION['pos_cart'][$tenantId]['items'][$productId])) {
            Response::redirect('/admin/pdv');
            return;
        }

        $item = $_SESSION['pos_cart'][$tenantId]['items'][$productId];

        if ($action === 'inc') {
            // Se não for serviço, valida estoque
            if ((int)$item['is_service'] === 0) {
                $stock = (int)Database::fetchColumn(
                    "SELECT stock FROM products_pos WHERE id = :id AND tenant_id = :tenant_id",
                    ['id' => $productId, 'tenant_id' => $tenantId]
                );
                if ($stock <= $item['qty']) {
                    Session::setFlash('warning', "Estoque máximo atingido para '{$item['name']}' ({$stock} un.)");
                    Response::redirect('/admin/pdv');
                    return;
                }
            }
            $_SESSION['pos_cart'][$tenantId]['items'][$productId]['qty']++;
        } elseif ($action === 'dec') {
            if ($item['qty'] > 1) {
                $_SESSION['pos_cart'][$tenantId]['items'][$productId]['qty']--;
            } else {
                unset($_SESSION['pos_cart'][$tenantId]['items'][$productId]);
            }
        }

        Response::redirect('/admin/pdv');
    }

    /**
     * Remove Item do Carrinho (POST /admin/pdv/remover)
     */
    public function removeItem(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $productId = (int)$request->post('product_id', 0);
        if (isset($_SESSION['pos_cart'][$tenantId]['items'][$productId])) {
            unset($_SESSION['pos_cart'][$tenantId]['items'][$productId]);
            Session::setFlash('info', 'Item removido do carrinho.');
        }

        Response::redirect('/admin/pdv');
    }

    /**
     * Limpa Todo o Carrinho (POST /admin/pdv/limpar)
     */
    public function clearCart(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $_SESSION['pos_cart'][$tenantId] = ['items' => []];
        Session::setFlash('info', 'Carrinho esvaziado.');
        Response::redirect('/admin/pdv');
    }

    /**
     * Finaliza a Venda no Balcão e Abate Estoque (POST /admin/pdv/finalizar)
     */
    public function checkout(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $cart = $_SESSION['pos_cart'][$tenantId]['items'] ?? [];
        if (empty($cart)) {
            Session::setFlash('error', 'O carrinho está vazio.');
            Response::redirect('/admin/pdv');
            return;
        }

        $paymentMethod = trim((string)$request->post('payment_method', 'dinheiro'));
        $validMethods = ['dinheiro', 'pix', 'cartao_debito', 'cartao_credito'];
        if (!in_array($paymentMethod, $validMethods, true)) {
            $paymentMethod = 'dinheiro';
        }

        // Calcula total e valida estoque antes de confirmar
        $totalAmount = 0.0;
        foreach ($cart as $item) {
            $totalAmount += ((float)$item['price'] * (int)$item['qty']);

            if ((int)$item['is_service'] === 0) {
                $stock = (int)Database::fetchColumn(
                    "SELECT stock FROM products_pos WHERE id = :id AND tenant_id = :tenant_id",
                    ['id' => (int)$item['id'], 'tenant_id' => $tenantId]
                );
                if ($stock < (int)$item['qty']) {
                    Session::setFlash('error', "Estoque insuficiente para {$item['name']}. Venda não concluída.");
                    Response::redirect('/admin/pdv');
                    return;
                }
            }
        }

        try {
            Database::beginTransaction();

            // 1. Registra a venda no banco
            Database::execute(
                "INSERT INTO pos_sales (tenant_id, total_amount, payment_method, items_json)
                 VALUES (:tenant_id, :total_amount, :payment_method, :items_json)",
                [
                    'tenant_id' => $tenantId,
                    'total_amount' => $totalAmount,
                    'payment_method' => $paymentMethod,
                    'items_json' => json_encode(array_values($cart), JSON_UNESCAPED_UNICODE)
                ]
            );

            $saleId = (int)Database::lastInsertId();

            // 2. Abate do estoque (RN05 / Venda Balcão)
            foreach ($cart as $item) {
                if ((int)$item['is_service'] === 0) {
                    Database::execute(
                        "UPDATE products_pos 
                         SET stock = stock - :qty 
                         WHERE id = :id AND tenant_id = :tenant_id",
                        [
                            'qty' => (int)$item['qty'],
                            'id' => (int)$item['id'],
                            'tenant_id' => $tenantId
                        ]
                    );
                }
            }

            Database::commit();

            // Limpa o carrinho
            $_SESSION['pos_cart'][$tenantId] = ['items' => []];

            Session::setFlash('success', "Venda #{$saleId} concluída com sucesso! Total: R$ " . number_format($totalAmount, 2, ',', '.'));
            Response::redirect('/admin/pdv?venda_sucesso=' . $saleId);
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::setFlash('error', 'Erro ao finalizar venda: ' . $e->getMessage());
            Response::redirect('/admin/pdv');
        }
    }

    /**
     * Impressão do Comprovante Térmico da Venda (/admin/pdv/comprovante?id=X)
     */
    public function receipt(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();

        $saleId = (int)$request->query('id', 0);
        $format = (string)$request->query('formato', '80mm');

        $sale = Database::fetchOne(
            "SELECT * FROM pos_sales WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $saleId, 'tenant_id' => $tenantId]
        );

        if (!$sale) {
            Response::notFound('Comprovante de venda não encontrado.');
            return;
        }

        $items = json_decode($sale['items_json'] ?? '[]', true) ?: [];

        View::render('admin/pos/receipt', [
            'tenant' => $tenant,
            'sale' => $sale,
            'items' => $items,
            'format' => $format
        ], null);
    }
}
