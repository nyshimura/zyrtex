<?php
/**
 * View: Comprovante Térmico de Venda Balcão PDV (80mm / 58mm)
 */
$sale = $sale ?? [];
$items = $items ?? [];
$tenant = $tenant ?? [];
$format = $format ?? '80mm';

$address = json_decode($tenant['address_json'] ?? '{}', true) ?: [];
$addressLine = trim(($address['street'] ?? '') . ' - ' . ($address['city'] ?? ''));
$isMini = ($format === '58mm');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovante de Venda #<?= (int)$sale['id'] ?> - <?= htmlspecialchars($tenant['business_name'] ?? 'Zyrtex', ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        @page {
            size: <?= $isMini ? '58mm auto' : '80mm auto' ?>;
            margin: 0mm !important;
        }
        @page :left { margin: 0mm !important; }
        @page :right { margin: 0mm !important; }
        @page :first { margin: 0mm !important; }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .receipt-container {
            width: <?= $isMini ? '58mm' : '80mm' ?>;
            background: #fff;
            padding: <?= $isMini ? '3mm' : '5mm' ?>;
            font-size: <?= $isMini ? '10px' : '12px' ?>;
            line-height: 1.25;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .double-divider {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: inherit;
        }

        table th, table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .no-print {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
        }

        .btn {
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 13px;
            font-weight: bold;
            text-decoration: none;
        }

        .btn-secondary {
            background: #64748b;
        }

        @media print {
            html, body {
                width: 100%;
                background: none;
                padding: 0;
                margin: 0;
            }
            .receipt-container {
                width: 100%;
                box-shadow: none;
                padding: 2mm;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
        <button onclick="window.close()" class="btn btn-secondary">Fechar</button>
    </div>

    <div class="receipt-container">
        
        <!-- CABEÇALHO DA EMPRESA -->
        <div class="text-center">
            <h2 class="font-bold uppercase" style="font-size: <?= $isMini ? '12px' : '15px' ?>;">
                <?= htmlspecialchars($tenant['business_name'] ?? 'OFICINA MODELO', ENT_QUOTES, 'UTF-8') ?>
            </h2>
            <?php if (!empty($addressLine)): ?>
                <p><?= htmlspecialchars($addressLine, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <?php if (!empty($tenant['whatsapp_number'])): ?>
                <p>WhatsApp: <?= htmlspecialchars($tenant['whatsapp_number'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <div class="double-divider"></div>

        <!-- DADOS DA VENDA -->
        <div class="text-center font-bold">
            COMPROVANTE DE VENDA A VISTA
        </div>
        <div>
            <span>Venda Nº: #<?= str_pad((string)$sale['id'], 5, '0', STR_PAD_LEFT) ?></span><br>
            <span>Data: <?= date('d/m/Y H:i:s', strtotime($sale['created_at'])) ?></span><br>
            <span>Pagamento: <?= strtoupper(htmlspecialchars($sale['payment_method'], ENT_QUOTES, 'UTF-8')) ?></span>
        </div>

        <div class="divider"></div>

        <!-- ITENS DA VENDA -->
        <table>
            <thead>
                <tr class="font-bold">
                    <th style="text-align: left;">QTD ITEM</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): 
                    $subtotal = (float)$it['price'] * (int)$it['qty'];
                ?>
                    <tr>
                        <td style="text-align: left;">
                            <?= (int)$it['qty'] ?>x <?= htmlspecialchars($it['name'], ENT_QUOTES, 'UTF-8') ?>
                            <br>
                            <span style="font-size: 9px; color: #555;">(R$ <?= number_format((float)$it['price'], 2, ',', '.') ?> un)</span>
                        </td>
                        <td class="text-right font-bold">
                            R$ <?= number_format($subtotal, 2, ',', '.') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- TOTAL GERAL -->
        <div style="font-size: <?= $isMini ? '13px' : '15px' ?>;" class="font-bold text-right">
            TOTAL PAGO: R$ <?= number_format((float)$sale['total_amount'], 2, ',', '.') ?>
        </div>

        <div class="double-divider"></div>

        <!-- RODAPÉ & AGRADECIMENTO -->
        <div class="text-center" style="font-size: 10px;">
            <p>Obrigado pela preferência!</p>
            <p>Volte sempre.</p>
            <p style="margin-top: 4px; font-size: 8px;">Sistema Zyrtex SaaS</p>
        </div>

    </div>

    <script>
        // Suprime o título e data no cabeçalho em navegadores Chromium/Firefox
        window.addEventListener('beforeprint', function() {
            window._savedDocTitle = document.title;
            document.title = "";
        });
        window.addEventListener('afterprint', function() {
            if (window._savedDocTitle) {
                document.title = window._savedDocTitle;
            }
        });

        window.addEventListener('load', () => {
            // Se abrir em popup de impressão, dispara diálogo
            setTimeout(() => { window.print(); }, 400);
        });
    </script>
</body>
</html>
