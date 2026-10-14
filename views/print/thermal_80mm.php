<?php
/**
 * View: Cupom Térmico 80mm (Bobina Padrão Balcão)
 * Otimizado para impressoras térmicas (Epson, Elgin, Bematech, Daruma, etc.)
 */
$os = $os ?? [];
$tenant = $tenant ?? [];
$printerSettings = $printerSettings ?? [];
$checklist = $checklist ?? [];
$parts = $parts ?? [];
$nicheConfig = $nicheConfig ?? [];
$trackingUrl = $trackingUrl ?? '';

use Zyrtex\Services\QrCodeService;
$qrCodeImg = QrCodeService::pngUrl($trackingUrl, 160);

$businessName = htmlspecialchars($tenant['business_name'] ?? 'OFICINA MODELO', ENT_QUOTES, 'UTF-8');
$osNumber = htmlspecialchars($os['os_number'] ?? 'OS-000', ENT_QUOTES, 'UTF-8');
$clientName = htmlspecialchars($os['client_name'] ?? 'Consumidor', ENT_QUOTES, 'UTF-8');
$clientPhone = htmlspecialchars($os['client_phone'] ?? '', ENT_QUOTES, 'UTF-8');
$clientDoc = htmlspecialchars($os['client_document'] ?? '', ENT_QUOTES, 'UTF-8');
$equipment = htmlspecialchars($os['equipment_info'] ?? '', ENT_QUOTES, 'UTF-8');
$identifierName = htmlspecialchars($nicheConfig['identifier_name'] ?? 'IDENTIFICADOR', ENT_QUOTES, 'UTF-8');
$identifier = htmlspecialchars($os['identifier_code'] ?? '', ENT_QUOTES, 'UTF-8');
$defect = htmlspecialchars($os['defect_reported'] ?? 'Não especificado', ENT_QUOTES, 'UTF-8');
$diagnosis = htmlspecialchars($os['technical_diagnosis'] ?? '', ENT_QUOTES, 'UTF-8');
$entryDate = !empty($os['created_at']) ? date('d/m/Y H:i', strtotime($os['created_at'])) : date('d/m/Y H:i');
$warrantyDays = (int)($os['warranty_days'] ?? 90);
$laborVal = (float)($os['labor_value'] ?? 0);
$partsVal = (float)($os['parts_value'] ?? 0);
$totalVal = (float)($os['total_value'] ?? ($laborVal + $partsVal));
$showTerms = !empty($printerSettings['show_terms']);
$showQr = !empty($printerSettings['show_qr']);
$showLogo = !empty($printerSettings['show_logo']);
$showPix = !empty($printerSettings['show_pix']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Comprovante 80mm - <?= $osNumber ?></title>
    <style>
        @page {
            size: 80mm auto;
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

        /* Barra de Controle de Pré-Visualização (Desaparece na impressão) */
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

        /* Container do Cupom Físico 80mm */
        .receipt-container {
            width: 78mm;
            margin: 25px auto;
            background: #ffffff;
            padding: 3mm 4mm;
            font-size: 11px;
            line-height: 1.25;
            color: #000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .divider {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        .double-divider {
            border-top: 2px dashed #000;
            margin: 5px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .qr-box {
            text-align: center;
            margin: 5px 0;
        }

        .qr-box img {
            width: 120px;
            height: 120px;
            display: inline-block;
        }

        .terms {
            font-size: 8.5px;
            text-align: justify;
            line-height: 1.15;
            margin-top: 4px;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin: 3px 0;
        }

        table.items-table th, table.items-table td {
            padding: 1.5px 0;
        }

        /* REGRAS RÍGIDAS DE IMPRESSÃO TÉRMICA 80mm */
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
                width: 78mm !important;
                max-width: 78mm !important;
                margin: 0 !important;
                padding: 2mm 3mm !important;
                box-shadow: none !important;
                border: none !important;
            }

            @page {
                size: 80mm auto !important;
                margin: 0mm !important;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR DE VISUALIZAÇÃO E SELEÇÃO DE FORMATO -->
    <div class="preview-bar">
        <div>
            <strong style="color: #6ee7b7; text-transform: uppercase; font-size: 11px; display: block;">Zyrtex &bull; Cupom Balcão Térmico 80mm</strong>
            <span>OS: <code style="color: #a5b4fc; font-weight: bold;"><?= $osNumber ?></code> &bull; <?= $clientName ?></span>
        </div>

        <div class="preview-modes">
            <span style="font-size: 11px; color: #94a3b8;">Trocar Formato:</span>
            
            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=80mm" class="active">
                🧾 80mm Balcão
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=58mm">
                🧾 58mm Portátil
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=label">
                🏷️ Mini Etiqueta QR
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=a4">
                📄 Laudo A4
            </a>
        </div>

        <button onclick="window.print()" class="btn-print">
            <span>🖨️ Imprimir 80mm</span>
        </button>
    </div>

    <!-- CORPO DO CUPOM TÉRMICO 80mm -->
    <div class="receipt-container">

        <!-- CABEÇALHO DA EMPRESA -->
        <div class="center">
            <?php if ($showLogo && !empty($tenant['logo_path'])): ?>
                <div style="margin-bottom: 6px;">
                    <img src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Logo <?= $businessName ?>" style="max-height: 48px; max-width: 140px; object-fit: contain; filter: grayscale(100%);">
                </div>
            <?php endif; ?>
            <h2 class="bold uppercase" style="font-size: 14.5px;"><?= $businessName ?></h2>
            <?php if (!empty($tenant['slogan'])): ?>
                <p style="font-size: 9.5px;"><?= htmlspecialchars($tenant['slogan']) ?></p>
            <?php endif; ?>
            <?php if (!empty($tenant['whatsapp_number'])): ?>
                <p style="font-size: 10px;">WhatsApp: <?= htmlspecialchars($tenant['whatsapp_number']) ?></p>
            <?php endif; ?>
        </div>

        <div class="double-divider"></div>

        <!-- IDENTIFICAÇÃO DA OS -->
        <div class="center">
            <h1 class="bold" style="font-size: 18px; letter-spacing: 0.5px;"><?= $osNumber ?></h1>
            <p style="font-size: 9.5px;">Entrada: <?= $entryDate ?></p>
        </div>

        <div class="divider"></div>

        <!-- DADOS DO CLIENTE -->
        <div>
            <p><span class="bold">CLIENTE:</span> <?= $clientName ?></p>
            <p><span class="bold">TEL:</span> <?= $clientPhone ?></p>
            <?php if (!empty($clientDoc)): ?>
                <p><span class="bold">DOC:</span> <?= $clientDoc ?></p>
            <?php endif; ?>
        </div>

        <div class="divider"></div>

        <!-- DADOS DO EQUIPAMENTO -->
        <div>
            <p><span class="bold">EQUIP:</span> <?= $equipment ?></p>
            <p><span class="bold"><?= $identifierName ?>:</span> <?= $identifier ?></p>
            <p><span class="bold">DEFEITO:</span> <?= $defect ?></p>
            <?php if (!empty($diagnosis)): ?>
                <p><span class="bold">LAUDO:</span> <?= $diagnosis ?></p>
            <?php endif; ?>
        </div>

        <!-- CHECKLIST DE ENTRADA -->
        <?php if (!empty($checklist) && $showTerms): ?>
            <div class="divider"></div>
            <p class="bold" style="font-size: 9.5px;">CHECKLIST DE ENTRADA:</p>
            <div style="font-size: 8.5px; margin-top: 2px;">
                <?php 
                $chkList = [];
                foreach ($checklist as $k => $v) {
                    if ($v === 'ok') $chkList[] = "{$k}: OK";
                    elseif ($v === 'defeito') $chkList[] = "{$k}: [DEF]";
                }
                echo implode(' | ', $chkList);
                ?>
            </div>
        <?php endif; ?>

        <!-- DISCRIMINAÇÃO DE PEÇAS / INSUMOS -->
        <?php if (!empty($parts)): ?>
            <div class="divider"></div>
            <p class="bold" style="font-size: 9.5px;">PEÇAS E INSUMOS:</p>
            <table class="items-table">
                <tbody>
                    <?php foreach ($parts as $p): ?>
                        <tr>
                            <td><?= (int)$p['quantity'] ?>x <?= htmlspecialchars($p['part_name']) ?></td>
                            <td class="right bold">R$ <?= number_format((float)$p['unit_price'] * (int)$p['quantity'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="divider"></div>

        <!-- TOTAIS FINANCEIROS -->
        <div class="row">
            <span>MÃO DE OBRA:</span>
            <span class="bold">R$ <?= number_format($laborVal, 2, ',', '.') ?></span>
        </div>
        <div class="row">
            <span>PEÇAS E INSUMOS:</span>
            <span class="bold">R$ <?= number_format($partsVal, 2, ',', '.') ?></span>
        </div>
        <div class="row" style="font-size: 13.5px; margin-top: 3px; border-top: 1px solid #000; padding-top: 3px;">
            <span class="bold">TOTAL GERAL:</span>
            <span class="bold">R$ <?= number_format($totalVal, 2, ',', '.') ?></span>
        </div>

        <!-- QR CODE DO PIX PARA PAGAMENTO -->
        <?php if ($showPix && !empty($tenant['pix_key'])): 
            $pixKey = htmlspecialchars($tenant['pix_key'], ENT_QUOTES, 'UTF-8');
            $pixBeneficiary = htmlspecialchars($tenant['pix_beneficiary'] ?? $tenant['business_name'] ?? '', ENT_QUOTES, 'UTF-8');
            $pixQrImg = QrCodeService::pngUrl($tenant['pix_key'], 130);
        ?>
            <div class="divider"></div>
            <div class="qr-box">
                <p class="bold" style="font-size: 9.5px; text-transform: uppercase;">Pagar via Pix</p>
                <img src="<?= $pixQrImg ?>" alt="QR Code Pix" style="width: 105px; height: 105px; margin: 3px 0;">
                <p style="font-size: 8.5px;">Chave Pix: <span class="bold"><?= $pixKey ?></span></p>
                <?php if (!empty($pixBeneficiary)): ?>
                    <p style="font-size: 8px; color: #333;">Beneficiário: <?= $pixBeneficiary ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- QR CODE DE RASTREIO DA ORDEM DE SERVIÇO -->
        <?php if ($showQr): ?>
            <div class="divider"></div>
            <div class="qr-box">
                <img src="<?= $qrCodeImg ?>" alt="QR Code Rastreio">
                <p style="font-size: 8.5px; margin-top: 2px;">Aponte a câmera do celular para rastrear a OS</p>
            </div>
        <?php endif; ?>

        <!-- TERMOS LEGAIS E GARANTIA -->
        <?php if ($showTerms): ?>
            <div class="divider"></div>
            <div class="terms">
                <strong>TERMO LEGAL & GARANTIA (CDC Art. 26):</strong> Garantia legal de <?= $warrantyDays ?> dias restrita exclusivamente aos serviços executados e peças substituídas discriminadas acima. Equipamentos não retirados em até 90 dias após a notificação de conclusão serão considerados expressamente abandonados (Art. 1.275 Código Civil), autorizando a oficina a cobrar taxa de guarda ou destinar o bem para cobrir despesas.
            </div>
        <?php endif; ?>

        <div class="divider" style="margin-top: 18px;"></div>
        <div class="center" style="margin-top: 18px;">
            <p>_____________________________________</p>
            <p style="font-size: 9px; margin-top: 2px;">Assinatura do Cliente</p>
        </div>

        <div class="center" style="margin-top: 8px; font-size: 7.5px; color: #444;">
            Zyrtex SaaS &bull; Tecnologia para Assistências
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
