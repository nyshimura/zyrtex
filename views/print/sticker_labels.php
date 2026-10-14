<?php
/**
 * View: Gerador de Etiquetas Adesivas & Selo QR Code
 * Suporte a:
 * 1. Etiqueta Individual 50x30mm (Impressoras Térmicas de Rolo - Zebra, Elgin, Argox, Xprinter)
 * 2. Etiqueta Individual 40x25mm (Mini Lacre)
 * 3. Folha A4 Completa com Grade 3x10 (Pimaco / Folha de Etiquetas de Escritório)
 */
$os = $os ?? [];
$tenant = $tenant ?? [];
$trackingUrl = $trackingUrl ?? '';
$mode = $_GET['modo'] ?? '50x30'; // '50x30', '40x25', 'grade_a4'

use Zyrtex\Services\QrCodeService;
$qrCodeImg = QrCodeService::pngUrl($trackingUrl, 180);
$businessName = htmlspecialchars(substr($tenant['business_name'] ?? 'OFICINA', 0, 18), ENT_QUOTES, 'UTF-8');
$osNumber = htmlspecialchars($os['os_number'] ?? 'OS-000', ENT_QUOTES, 'UTF-8');
$identifier = htmlspecialchars(substr($os['identifier_code'] ?? '', 0, 16), ENT_QUOTES, 'UTF-8');
$equipment = htmlspecialchars(substr($os['equipment_info'] ?? '', 0, 20), ENT_QUOTES, 'UTF-8');
$entryDate = !empty($os['created_at']) ? date('d/m/y', strtotime($os['created_at'])) : date('d/m/y');
$warrantyDays = (int)($os['warranty_days'] ?? 90);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta QR Code - <?= $osNumber ?></title>
    <style>
        <?php if ($mode === '40x25'): ?>
        @page {
            size: 40mm 25mm;
            margin: 0mm !important;
        }
        <?php elseif ($mode === 'grade_a4'): ?>
        @page {
            size: A4 portrait;
            margin: 6mm 5mm !important;
        }
        <?php else: ?>
        @page {
            size: 50mm 30mm;
            margin: 0mm !important;
        }
        <?php endif; ?>
        @page :left { margin: 0mm !important; }
        @page :right { margin: 0mm !important; }
        @page :first { margin: 0mm !important; }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
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

        /* ------------------------------------------------------------------ */
        /* FORMATO 1: INDIVIDUAL 50mm x 30mm                                   */
        /* ------------------------------------------------------------------ */
        .label-container-single {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 10px;
        }

        .label-50x30 {
            width: 50mm;
            height: 30mm;
            max-width: 50mm;
            max-height: 30mm;
            background: #ffffff;
            border: 1px dashed #94a3b8;
            padding: 1.8mm 2mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .label-50x30 .qr-col {
            width: 24mm;
            height: 26mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-50x30 .qr-col img {
            width: 24mm;
            height: 24mm;
            display: block;
            image-rendering: pixelated;
        }

        .label-50x30 .info-col {
            width: 22mm;
            height: 26mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-left: 1.5mm;
            line-height: 1.1;
        }

        .tag-brand {
            font-size: 6.5px;
            font-weight: 800;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #000;
        }

        .tag-os {
            font-size: 10.5px;
            font-weight: 900;
            font-family: monospace;
            line-height: 1;
            letter-spacing: -0.3px;
            color: #000;
        }

        .tag-id {
            font-size: 6.5px;
            font-family: monospace;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tag-meta {
            font-size: 6.5px;
            color: #334155;
            display: flex;
            justify-content: space-between;
        }

        .tag-seal {
            font-size: 6px;
            font-weight: 800;
            background: #000;
            color: #fff;
            padding: 1px 2px;
            border-radius: 2px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        /* ------------------------------------------------------------------ */
        /* FORMATO 2: INDIVIDUAL 40mm x 25mm                                   */
        /* ------------------------------------------------------------------ */
        .label-40x25 {
            width: 40mm;
            height: 25mm;
            max-width: 40mm;
            max-height: 25mm;
            background: #ffffff;
            border: 1px dashed #94a3b8;
            padding: 1.2mm 1.5mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            overflow: hidden;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .label-40x25 .qr-col {
            width: 20mm;
            height: 22mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .label-40x25 .qr-col img {
            width: 20mm;
            height: 20mm;
            display: block;
        }

        .label-40x25 .info-col {
            width: 17mm;
            height: 22mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-left: 1mm;
            line-height: 1.05;
        }

        .label-40x25 .tag-brand { font-size: 5.5px; }
        .label-40x25 .tag-os { font-size: 9px; }
        .label-40x25 .tag-id { font-size: 5.5px; }
        .label-40x25 .tag-meta { font-size: 5.5px; }
        .label-40x25 .tag-seal { font-size: 5px; }

        /* ------------------------------------------------------------------ */
        /* FORMATO 3: GRADE DE ETIQUETAS EM FOLHA A4 (3x10 = 30 ETIQUETAS)     */
        /* ------------------------------------------------------------------ */
        .a4-sheet {
            width: 210mm;
            height: 297mm;
            background: #fff;
            margin: 20px auto;
            padding: 10mm 7mm;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(10, 1fr);
            gap: 2mm 3mm;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            box-sizing: border-box;
        }

        .a4-sheet .grid-label {
            width: 100%;
            height: 26mm;
            border: 1px dotted #cbd5e1;
            padding: 1.5mm 2mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* ------------------------------------------------------------------ */
        /* REGRAS RÍGIDAS DE IMPRESSÃO (@media print)                          */
        /* Garante estritamente 1 PÁGINA ÚNICA na bobina ou folha!             */
        /* ------------------------------------------------------------------ */
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
                min-height: auto !important;
            }

            .label-container-single {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            <?php if ($mode === '40x25'): ?>
                @page {
                    size: 40mm 25mm !important;
                    margin: 0mm !important;
                }
                html, body {
                    width: 40mm !important;
                    height: 25mm !important;
                    max-width: 40mm !important;
                    max-height: 25mm !important;
                    overflow: hidden !important;
                }
                .label-40x25 {
                    border: none !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    width: 40mm !important;
                    height: 25mm !important;
                    max-width: 40mm !important;
                    max-height: 25mm !important;
                    page-break-inside: avoid !important;
                    page-break-after: avoid !important;
                    page-break-before: avoid !important;
                    break-inside: avoid !important;
                    break-after: avoid !important;
                }
            <?php elseif ($mode === 'grade_a4'): ?>
                @page {
                    size: A4 portrait !important;
                    margin: 8mm 6mm !important;
                }
                .a4-sheet {
                    margin: 0 !important;
                    padding: 0 !important;
                    box-shadow: none !important;
                    width: 100% !important;
                    height: 100% !important;
                    page-break-inside: avoid !important;
                    page-break-after: avoid !important;
                }
            <?php else: ?>
                @page {
                    size: 50mm 30mm !important;
                    margin: 0mm !important;
                }
                html, body {
                    width: 50mm !important;
                    height: 30mm !important;
                    max-width: 50mm !important;
                    max-height: 30mm !important;
                    overflow: hidden !important;
                }
                .label-50x30 {
                    border: none !important;
                    box-shadow: none !important;
                    margin: 0 !important;
                    width: 50mm !important;
                    height: 30mm !important;
                    max-width: 50mm !important;
                    max-height: 30mm !important;
                    page-break-inside: avoid !important;
                    page-break-after: avoid !important;
                    page-break-before: avoid !important;
                    break-inside: avoid !important;
                    break-after: avoid !important;
                }
            <?php endif; ?>
        }
    </style>
</head>
<body>

    <!-- BARRA DE CONTROLE E SELEÇÃO DE MODELO FÍSICO -->
    <div class="preview-bar">
        <div>
            <strong style="color: #6ee7b7; text-transform: uppercase; font-size: 11px; display: block;">Zyrtex &bull; Lacre Adesivo QR Code</strong>
            <span>OS: <code style="color: #a5b4fc; font-weight: bold;"><?= $osNumber ?></code> &bull; <?= $equipment ?></span>
        </div>

        <div class="preview-modes">
            <span style="font-size: 11px; color: #94a3b8;">Formato Físico:</span>
            
            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=label&modo=50x30" 
               class="<?= $mode === '50x30' ? 'active' : '' ?>">
                🏷️ Rolo 50x30mm (Padrão)
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=label&modo=40x25" 
               class="<?= $mode === '40x25' ? 'active' : '' ?>">
                🏷️ Rolo 40x25mm (Mini)
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=label&modo=grade_a4" 
               class="<?= $mode === 'grade_a4' ? 'active' : '' ?>">
                📄 Folha A4 (Pimaco / 30un)
            </a>
        </div>

        <button onclick="window.print()" class="btn-print">
            <span>🖨️ Imprimir Agora</span>
        </button>
    </div>

    <?php if ($mode === '40x25'): ?>
        <!-- ============================================================= -->
        <!-- MODO 2: ETIQUETA INDIVIDUAL 40x25mm                           -->
        <!-- ============================================================= -->
        <div class="label-container-single">
            <div class="label-40x25">
                <div class="qr-col">
                    <img src="<?= $qrCodeImg ?>" alt="QR">
                </div>
                <div class="info-col">
                    <span class="tag-brand"><?= $businessName ?></span>
                    <span class="tag-os"><?= $osNumber ?></span>
                    <span class="tag-id"><?= $identifier ?></span>
                    <div class="tag-meta">
                        <span><?= $entryDate ?></span>
                        <span><?= $warrantyDays ?>D</span>
                    </div>
                    <span class="tag-seal">Lacre Garantia</span>
                </div>
            </div>
        </div>

    <?php elseif ($mode === 'grade_a4'): ?>
        <!-- ============================================================= -->
        <!-- MODO 3: GRADE DE 30 ETIQUETAS EM FOLHA A4                     -->
        <!-- ============================================================= -->
        <div class="a4-sheet">
            <?php for ($i = 1; $i <= 30; $i++): ?>
                <div class="grid-label">
                    <div style="width: 22mm; height: 22mm; display: flex; align-items: center; justify-content: center;">
                        <img src="<?= $qrCodeImg ?>" style="width: 21mm; height: 21mm;" alt="QR">
                    </div>
                    <div style="width: 38mm; display: flex; flex-direction: column; justify-content: space-between; height: 22mm; line-height: 1.15; padding-left: 2mm;">
                        <span class="tag-brand" style="font-size: 7.5px;"><?= $businessName ?></span>
                        <span class="tag-os" style="font-size: 11px;"><?= $osNumber ?></span>
                        <span class="tag-id" style="font-size: 7.5px;"><?= $identifier ?></span>
                        <div class="tag-meta" style="font-size: 7px;">
                            <span>Entrada: <?= $entryDate ?></span>
                            <span>Garantia: <?= $warrantyDays ?>D</span>
                        </div>
                        <span class="tag-seal" style="font-size: 6px;">Lacre de Garantia &bull; Rompimento anula CDC</span>
                    </div>
                </div>
            <?php endfor; ?>
        </div>

    <?php else: ?>
        <!-- ============================================================= -->
        <!-- MODO 1: ETIQUETA INDIVIDUAL PADRÃO 50x30mm                    -->
        <!-- ============================================================= -->
        <div class="label-container-single">
            <div class="label-50x30">
                <div class="qr-col">
                    <img src="<?= $qrCodeImg ?>" alt="QR">
                </div>
                <div class="info-col">
                    <span class="tag-brand"><?= $businessName ?></span>
                    <span class="tag-os"><?= $osNumber ?></span>
                    <span class="tag-id"><?= $identifier ?></span>
                    <div class="tag-meta">
                        <span>Entr: <?= $entryDate ?></span>
                        <span>Gar: <?= $warrantyDays ?>D</span>
                    </div>
                    <span class="tag-seal">Lacre de Segurança</span>
                </div>
            </div>
        </div>

    <?php endif; ?>

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
