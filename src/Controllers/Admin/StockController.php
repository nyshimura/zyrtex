<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;

class StockController {
    /**
     * Listagem do Estoque e Peças (/admin/estoque)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $search = trim((string)$request->query('q', ''));

        if (!empty($search)) {
            $products = Database::fetchAll(
                "SELECT * FROM products_pos 
                 WHERE tenant_id = :tenant_id 
                   AND (name LIKE :q1 OR sku LIKE :q2 OR category LIKE :q3)
                 ORDER BY name ASC",
                [
                    'tenant_id' => $tenantId,
                    'q1' => "%{$search}%",
                    'q2' => "%{$search}%",
                    'q3' => "%{$search}%"
                ]
            );
        } else {
            $products = Database::fetchAll(
                "SELECT * FROM products_pos WHERE tenant_id = :tenant_id ORDER BY id DESC",
                ['tenant_id' => $tenantId]
            );
        }

        View::render('admin/stock/index', [
            'tenant' => $tenant,
            'user' => $user,
            'products' => $products,
            'search' => $search
        ], 'admin');
    }

    /**
     * Cadastro de Novo Item no Estoque (POST /admin/estoque)
     */
    public function store(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $name = trim((string)$request->post('name', ''));
        $sku = trim((string)$request->post('sku', ''));
        $category = trim((string)$request->post('category', 'Geral'));
        $price = (float)str_replace(',', '.', (string)$request->post('price', '0'));
        $costPrice = (float)str_replace(',', '.', (string)$request->post('cost_price', '0'));
        $stock = (int)$request->post('stock', 0);
        $isService = (int)$request->post('is_service', 0);
        $imageUrl = trim((string)$request->post('image_url', ''));

        $imagePath = null;
        if (!empty($_FILES['image_file']['tmp_name'])) {
            $upload = \Zyrtex\Services\ImageCompressor::processUpload($_FILES['image_file'], 'products', 1000, 1000);
            if ($upload['success']) {
                $imagePath = $upload['url'];
            }
        } elseif (!empty($imageUrl)) {
            $imagePath = $imageUrl;
        }

        if (empty($name)) {
            Session::setFlash('error', 'Nome do produto ou peça é obrigatório.');
            Response::redirect('/admin/estoque');
            return;
        }

        try {
            Database::execute(
                "INSERT INTO products_pos (tenant_id, name, sku, category, price, cost_price, stock, is_service, image_path)
                 VALUES (:tenant_id, :name, :sku, :category, :price, :cost_price, :stock, :is_service, :image_path)",
                [
                    'tenant_id' => $tenantId,
                    'name' => $name,
                    'sku' => $sku ?: null,
                    'category' => $category ?: 'Geral',
                    'price' => $price,
                    'cost_price' => $costPrice,
                    'stock' => $isService ? 0 : $stock,
                    'is_service' => $isService,
                    'image_path' => $imagePath ?: null
                ]
            );

            Session::setFlash('success', "Item '{$name}' cadastrado no estoque!");
            Response::redirect('/admin/estoque');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao salvar produto: ' . $e->getMessage());
            Response::redirect('/admin/estoque');
        }
    }

    /**
     * Atualização de Item no Estoque (POST /admin/estoque/editar)
     */
    public function update(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $id = (int)$request->post('id', 0);
        $name = trim((string)$request->post('name', ''));
        $sku = trim((string)$request->post('sku', ''));
        $category = trim((string)$request->post('category', 'Geral'));
        $price = $this->parseMoney((string)$request->post('price', '0'));
        $costPrice = $this->parseMoney((string)$request->post('cost_price', '0'));
        $stock = (int)$request->post('stock', 0);
        $isService = (int)$request->post('is_service', 0);
        $imageUrl = trim((string)$request->post('image_url', ''));
        $removeImage = !empty($request->post('remove_image'));

        if ($id <= 0 || empty($name)) {
            Session::setFlash('error', 'Nome do produto e identificador são obrigatórios.');
            Response::redirect('/admin/estoque');
            return;
        }

        $current = Database::fetchOne("SELECT image_path FROM products_pos WHERE id = :id AND tenant_id = :tenant_id", [
            'id' => $id,
            'tenant_id' => $tenantId
        ]);
        $imagePath = $current['image_path'] ?? null;

        if ($removeImage) {
            $imagePath = null;
        } elseif (!empty($_FILES['image_file']['tmp_name'])) {
            $upload = \Zyrtex\Services\ImageCompressor::processUpload($_FILES['image_file'], 'products', 1000, 1000);
            if ($upload['success']) {
                $imagePath = $upload['url'];
            }
        } elseif (!empty($imageUrl)) {
            $imagePath = $imageUrl;
        }

        try {
            Database::execute(
                "UPDATE products_pos 
                 SET name = :name, 
                     sku = :sku, 
                     category = :category, 
                     price = :price, 
                     cost_price = :cost_price, 
                     stock = :stock,
                     is_service = :is_service,
                     image_path = :image_path
                 WHERE id = :id AND tenant_id = :tenant_id",
                [
                    'name' => $name,
                    'sku' => $sku ?: null,
                    'category' => $category ?: 'Geral',
                    'price' => $price,
                    'cost_price' => $costPrice,
                    'stock' => $isService ? 0 : $stock,
                    'is_service' => $isService,
                    'image_path' => $imagePath ?: null,
                    'id' => $id,
                    'tenant_id' => $tenantId
                ]
            );

            Session::setFlash('success', "Item '{$name}' atualizado com sucesso!");
            Response::redirect('/admin/estoque');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao atualizar: ' . $e->getMessage());
            Response::redirect('/admin/estoque');
        }
    }

    /**
     * Exclusão de Item no Estoque (POST /admin/estoque/excluir)
     */
    public function delete(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();

        $id = (int)$request->post('id', 0);

        if ($id <= 0) {
            Session::setFlash('error', 'Identificador inválido para exclusão.');
            Response::redirect('/admin/estoque');
            return;
        }

        try {
            // Verificar se o item existe e pertence ao tenant
            $item = Database::fetchOne(
                "SELECT name FROM products_pos WHERE id = :id AND tenant_id = :tenant_id",
                ['id' => $id, 'tenant_id' => $tenantId]
            );

            if (!$item) {
                Session::setFlash('error', 'Item não encontrado ou acesso não autorizado.');
                Response::redirect('/admin/estoque');
                return;
            }

            Database::execute(
                "DELETE FROM products_pos WHERE id = :id AND tenant_id = :tenant_id",
                ['id' => $id, 'tenant_id' => $tenantId]
            );

            Session::setFlash('success', "Item '{$item['name']}' removido do estoque.");
            Response::redirect('/admin/estoque');
        } catch (\Throwable $e) {
            Session::setFlash('error', 'Erro ao remover item: ' . $e->getMessage());
            Response::redirect('/admin/estoque');
        }
    }

    /**
     * Auxiliar de conversão monetária BR/US
     */
    private function parseMoney(string $value): float {
        $clean = trim($value);
        if (str_contains($clean, ',') && str_contains($clean, '.')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '.', $clean);
        }
        return (float)$clean;
    }
}
