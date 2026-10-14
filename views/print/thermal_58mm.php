<?php
/**
 * View: Cupom Térmico 58mm (Bobina Mini Portátil / Maquininha POS)
 * Otimizado para bobinas estreitas de 58mm (largura útil ~48-50mm)
 */
$os = $os ?? [];
$tenant = $tenant ?? [];
$printerSettings = $printerSettings ?? [];
$parts = $parts ?? [];
$nicheConfig = $nicheConfig ?? [];
$trackingUrl = $trackingUrl ?? '';

use Zyrtex\Services\QrCodeService;
$qrCodeImg = QrCodeService::pngUrl($trackingUrl, 130);

$businessName = htmlspecialchars(substr($tenant['business_name'] ?? 'OFICINA', 0, 20), ENT_QUOTES, 'UTF-8');
$osNumber = htmlspecialchars($os['os_number'] ?? 'OS-000', ENT_QUOTES, 'UTF-8');
$clientName = htmlspecialchars(substr($os['client_name'] ?? 'Consumidor', 0, 22), ENT_QUOTES, 'UTF-8');
$clientPhone = htmlspecialchars($os['client_phone'] ?? '', ENT_QUOTES, 'UTF-8');
$equipment = htmlspecialchars(substr($os['equipment_info'] ?? '', 0, 24), ENT_QUOTES, 'UTF-8');
$identifier = htmlspecialchars(substr($os['identifier_code'] ?? '', 0, 18), ENT_QUOTES, 'UTF-8');
$defect = htmlspecialchars(substr($os['defect_reported'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8');
$entryDate = !empty($os['created_at']) ? date('d/m/Y H:i', strtotime($os['created_at'])) : date('d/m/Y H:i');
$warrantyDays = (int)($os['warranty_days'] ?? 90);
$totalVal = (float)($os['total_value'] ?? 0);
$showTerms = !empty($printerSettings['show_terms']);
$showQr = !empty($printerSettings['show_qr']);
$showLogo = !empty($printerSettings['show_logo']);
$showPix = !empty($printerSettings['show_pix']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovante 58mm - <?= $osNumber ?></title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0mm !important;
        }
        @page :left { margin: 0mm !important; }
        @page :right { margin: 0mm !important; }
        @page :first { margin: 0mm !important; }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            background-color: #f1f5f9;
            color: #000;
            min-height: 100vh;
        }

        /* Barra de Controle de Pré-Visualização */
        .preview-bar {
            background: #0f172a;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
        }

        .preview-modes {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-modes a {
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 700;
            transition: all 0.2s;
        }

        .preview-modes a.active {
            background: #4f46e5;
            color: #fff;
        }

        .preview-modes a:not(.active) {
            background: #1e293b;
            color: #94a3b8;
        }

        .preview-modes a:not(.active):hover {
            color: #fff;
            background: #334155;
        }

        .btn-print {
            background: #10b981;
            color: #fff;
            border: 0;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-print:hover {
            background: #059669;
        }

        /* Container do Cupom Físico 58mm */
        .receipt-container {
            width: 56mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 2mm;
            font-size: 9.5px;
            line-height: 1.2;
            color: #000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 3px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
        }
        .qr-box {
            text-align: center;
            margin: 4px 0;
        }
        .qr-box img {
            width: 90px;
            height: 90px;
            display: inline-block;
        }
        .terms {
            font-size: 7.5px;
            text-align: justify;
            line-height: 1.1;
            margin-top: 3px;
        }

        @media print {
            .preview-bar {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
            }

            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .receipt-container {
                width: 54mm !important;
                max-width: 54mm !important;
                margin: 0 !important;
                padding: 1mm 2mm !important;
                box-shadow: none !important;
                border: none !important;
            }

            @page {
                size: 58mm auto !important;
                margin: 0mm !important;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR DE VISUALIZAÇÃO -->
    <div class="preview-bar">
        <div>
            <strong style="color: #6ee7b7; text-transform: uppercase; font-size: 11px; display: block;">Zyrtex &bull; Cupom Térmico Mini 58mm</strong>
            <span>OS: <code style="color: #a5b4fc; font-weight: bold;"><?= $osNumber ?></code></span>
        </div>

        <div class="preview-modes">
            <span style="font-size: 11px; color: #94a3b8;">Formato:</span>
            
            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=80mm">
                🧾 80mm
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=58mm" class="active">
                🧾 58mm
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=label">
                🏷️ Mini Etiqueta
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=a4">
                📄 Laudo A4
            </a>
        </div>

        <button onclick="window.print()" class="btn-print">
            <span>🖨️ Imprimir 58mm</span>
        </button>
    </div>

    <!-- CORPO DO CUPOM 58mm -->
    <div class="receipt-container">

        <div class="center">
            <?php if ($showLogo && !empty($tenant['logo_path'])): ?>
                <div style="margin-bottom: 4px;">
                    <img src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Logo <?= $businessName ?>" style="max-height: 38px; max-width: 110px; object-fit: contain; filter: grayscale(100%);">
                </div>
            <?php endif; ?>
            <h2 class="bold" style="font-size: 12.5px;"><?= $businessName ?></h2>
            <?php if (!empty($tenant['whatsapp_number'])): ?>
                <p style="font-size: 8.5px;">Zap: <?= htmlspecialchars($tenant['whatsapp_number']) ?></p>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <div class="center">
            <h1 class="bold" style="font-size: 15px;"><?= $osNumber ?></h1>
            <p style="font-size: 8.5px;"><?= $entryDate ?></p>
        </div>

        <div class="divider"></div>

        <p><span class="bold">CLI:</span> <?= $clientName ?></p>
        <p><span class="bold">TEL:</span> <?= $clientPhone ?></p>
        <p><span class="bold">EQUIP:</span> <?= $equipment ?></p>
        <p><span class="bold">ID:</span> <?= $identifier ?></p>
        <p><span class="bold">DEF:</span> <?= $defect ?></p>

        <?php if (!empty($parts)): ?>
            <div class="divider"></div>
            <p class="bold" style="font-size: 8.5px;">PEÇAS/INSUMOS:</p>
            <?php foreach ($parts as $p): ?>
                <div class="row" style="font-size: 8.5px;">
                    <span><?= (int)$p['quantity'] ?>x <?= htmlspecialchars(substr($p['part_name'], 0, 16)) ?></span>
                    <span class="bold">R$ <?= number_format((float)$p['unit_price'] * (int)$p['quantity'], 2, ',', '.') ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <div class="divider"></div>

        <div class="row" style="font-size: 11px; font-weight: bold;">
            <span>TOTAL:</span>
            <span>R$ <?= number_format($totalVal, 2, ',', '.') ?></span>
        </div>

        <?php if ($showPix && !empty($tenant['pix_key'])): 
            $pixKey = htmlspecialchars($tenant['pix_key'], ENT_QUOTES, 'UTF-8');
            $pixQrImg = QrCodeService::pngUrl($tenant['pix_key'], 100);
        ?>
            <div class="divider"></div>
            <div class="qr-box">
                <p class="bold" style="font-size: 8.5px;">PAGAR VIA PIX</p>
                <img src="<?= $pixQrImg ?>" alt="Pix" style="width: 75px; height: 75px; margin: 2px 0;">
                <p style="font-size: 7.5px;">Chave: <span class="bold"><?= $pixKey ?></span></p>
            </div>
        <?php endif; ?>

        <?php if ($showQr): ?>
            <div class="divider"></div>
            <div class="qr-box">
                <img src="<?= $qrCodeImg ?>" alt="QR Code">
                <p style="font-size: 7.5px;">Aponte para rastrear</p>
            </div>
        <?php endif; ?>

        <?php if ($showTerms): ?>
            <div class="divider"></div>
            <div class="terms">
                Garantia legal <?= $warrantyDays ?> dias (CDC Art. 26). Equipamentos não retirados em 90 dias serão considerados abandonados.
            </div>
        <?php endif; ?>

        <div class="center" style="margin-top: 12px;">
            <p>_______________________</p>
            <p style="font-size: 8px; margin-top: 1px;">Assinatura do Cliente</p>
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

        if (window.location.search.includes('autoprint=1')) {
            window.print();
        }
    </script>
</body>
</html>
