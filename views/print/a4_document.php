<?php
/**
 * View: Documento Oficial A4 (Termo de Garantia, Laudo Técnico e Contrato)
 * Formato formal em folha inteira A4 para arquivamento ou entrega solene ao cliente
 */
$os = $os ?? [];
$tenant = $tenant ?? [];
$printerSettings = $printerSettings ?? [];
$parts = $parts ?? [];
$checklist = $checklist ?? [];
$nicheConfig = $nicheConfig ?? [];
$trackingUrl = $trackingUrl ?? '';

use Zyrtex\Services\QrCodeService;
$qrCodeImg = QrCodeService::pngUrl($trackingUrl, 150);

$businessName = htmlspecialchars($tenant['business_name'] ?? 'OFICINA MODELO', ENT_QUOTES, 'UTF-8');
$osNumber = htmlspecialchars($os['os_number'] ?? 'OS-000', ENT_QUOTES, 'UTF-8');
$clientName = htmlspecialchars($os['client_name'] ?? 'Consumidor', ENT_QUOTES, 'UTF-8');
$clientPhone = htmlspecialchars($os['client_phone'] ?? '', ENT_QUOTES, 'UTF-8');
$clientDoc = htmlspecialchars($os['client_document'] ?? 'Não informado', ENT_QUOTES, 'UTF-8');
$clientAddress = htmlspecialchars($os['client_address'] ?? 'Não informado', ENT_QUOTES, 'UTF-8');
$equipment = htmlspecialchars($os['equipment_info'] ?? '', ENT_QUOTES, 'UTF-8');
$identifierName = htmlspecialchars($nicheConfig['identifier_name'] ?? 'Identificador', ENT_QUOTES, 'UTF-8');
$identifier = htmlspecialchars($os['identifier_code'] ?? '', ENT_QUOTES, 'UTF-8');
$defect = htmlspecialchars($os['defect_reported'] ?? 'Não informado', ENT_QUOTES, 'UTF-8');
$diagnosis = htmlspecialchars($os['technical_diagnosis'] ?? 'Diagnóstico técnico registrado na bancada.', ENT_QUOTES, 'UTF-8');
$entryDate = !empty($os['created_at']) ? date('d/m/Y \à\s H:i', strtotime($os['created_at'])) : date('d/m/Y H:i');
$warrantyDays = (int)($os['warranty_days'] ?? 90);
$laborVal = (float)($os['labor_value'] ?? 0);
$partsVal = (float)($os['parts_value'] ?? 0);
$totalVal = (float)($os['total_value'] ?? ($laborVal + $partsVal));
$status = strtoupper($os['status'] ?? 'ABERTO');
$showTerms = !empty($printerSettings['show_terms'] ?? 1);
$showQr = !empty($printerSettings['show_qr'] ?? 1);
$showLogo = !empty($printerSettings['show_logo'] ?? 1);
$showPix = !empty($printerSettings['show_pix'] ?? 1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Laudo Oficial A4 - <?= $osNumber ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0mm !important;
        }
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
            color: #0f172a;
            min-height: 100vh;
            font-size: 12px;
            line-height: 1.4;
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

        /* Página Oficial A4 */
        .a4-page {
            max-width: 210mm;
            min-height: 297mm;
            margin: 25px auto;
            background: #ffffff;
            padding: 15mm 18mm;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .title {
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #0f172a;
        }

        .section {
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 6px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 11.5px;
        }

        table.table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 11.5px;
        }

        table.table th, table.table td {
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            text-align: left;
        }

        table.table th {
            background: #f1f5f9;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .terms-box {
            font-size: 9.5px;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            border-radius: 6px;
            line-height: 1.35;
            text-align: justify;
        }

        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 25px;
            text-align: center;
            font-size: 11px;
        }

        .signatures div {
            border-top: 1px solid #0f172a;
            padding-top: 5px;
        }

        /* REGRAS RÍGIDAS DE IMPRESSÃO A4 */
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

            .a4-page {
                box-shadow: none !important;
                border: none !important;
                padding: 10mm 12mm !important;
                margin: 0 !important;
                max-width: 100% !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR DE VISUALIZAÇÃO -->
    <div class="preview-bar">
        <div>
            <strong style="color: #6ee7b7; text-transform: uppercase; font-size: 11px; display: block;">Zyrtex &bull; Laudo Técnico e Contrato A4</strong>
            <span>OS: <code style="color: #a5b4fc; font-weight: bold;"><?= $osNumber ?></code> &bull; <?= $clientName ?></span>
        </div>

        <div class="preview-modes">
            <span style="font-size: 11px; color: #94a3b8;">Outros Formatos:</span>
            
            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=80mm">
                🧾 80mm Balcão
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=58mm">
                🧾 58mm Portátil
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=label">
                🏷️ Mini Etiqueta QR
            </a>

            <a href="?id=<?= (int)($os['id'] ?? 0) ?>&formato=a4" class="active">
                📄 Laudo A4
            </a>
        </div>

        <button onclick="window.print()" class="btn-print">
            <span>🖨️ Imprimir Laudo A4</span>
        </button>
    </div>

    <!-- FOLHA OFICIAL A4 -->
    <div class="a4-page">
        <div>
            <!-- Cabeçalho -->
            <div class="header">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <?php if ($showLogo && !empty($tenant['logo_path'])): ?>
                        <img src="<?= htmlspecialchars(asset($tenant['logo_path']), ENT_QUOTES, 'UTF-8') ?>" alt="Logo <?= $businessName ?>" style="max-height: 55px; max-width: 140px; object-fit: contain;">
                    <?php endif; ?>
                    <div>
                        <h1 class="title"><?= $businessName ?></h1>
                        <?php if (!empty($tenant['slogan'])): ?>
                            <p style="color: #64748b; font-size: 11px;"><?= htmlspecialchars($tenant['slogan']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($tenant['whatsapp_number'])): ?>
                            <p style="font-size: 11px; margin-top: 3px;">WhatsApp de Atendimento: <strong><?= htmlspecialchars($tenant['whatsapp_number']) ?></strong></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align: right;">
                    <h2 style="font-size: 20px; font-family: monospace; font-weight: 900; color: #0f172a;"><?= $osNumber ?></h2>
                    <p style="font-size: 11px; color: #64748b;">Entrada: <?= $entryDate ?></p>
                    <p style="font-size: 11px; font-weight: bold; margin-top: 2px;">Status: <?= $status ?></p>
                </div>
            </div>

            <!-- Dados do Cliente e Aparelho -->
            <div class="grid-2 section">
                <div class="box">
                    <div class="section-title">Dados do Cliente</div>
                    <p><strong>Nome:</strong> <?= $clientName ?></p>
                    <p><strong>Telefone:</strong> <?= $clientPhone ?></p>
                    <p><strong>Documento:</strong> <?= $clientDoc ?></p>
                    <p><strong>Endereço:</strong> <?= $clientAddress ?></p>
                </div>

                <div class="box">
                    <div class="section-title">Dados do Equipamento (<?= htmlspecialchars($nicheConfig['name'] ?? 'Equipamento') ?>)</div>
                    <p><strong>Equipamento:</strong> <?= $equipment ?></p>
                    <p><strong><?= $identifierName ?>:</strong> <code style="font-weight: bold;"><?= $identifier ?></code></p>
                    <p><strong>Garantia Estipulada:</strong> <?= $warrantyDays ?> dias legais</p>
                </div>
            </div>

            <!-- Defeito e Laudo Técnico -->
            <div class="section">
                <div class="section-title">Defeito Informado pelo Cliente</div>
                <div class="box" style="margin-bottom: 8px;">
                    <?= nl2br($defect) ?>
                </div>

                <div class="section-title">Laudo Técnico da Bancada</div>
                <div class="box">
                    <?= nl2br($diagnosis) ?>
                </div>
            </div>

            <!-- Checklist de Entrada -->
            <?php if (!empty($checklist)): ?>
                <div class="section">
                    <div class="section-title">Checklist de Conformidade de Entrada</div>
                    <div class="grid-3" style="font-size: 10.5px;">
                        <?php 
                        $checkMap = $nicheConfig['checklist'] ?? [];
                        foreach ($checkMap as $cK => $cL): 
                            $val = $checklist[$cK] ?? 'nao_testado';
                        ?>
                            <div class="box" style="padding: 5px 8px; display: flex; justify-content: space-between;">
                                <span><?= htmlspecialchars($cL) ?></span>
                                <strong style="color: <?= $val === 'ok' ? '#16a34a' : ($val === 'defeito' ? '#dc2626' : '#64748b') ?>;">
                                    <?= $val === 'ok' ? '✓ OK' : ($val === 'defeito' ? '✗ DEF' : 'N/T') ?>
                                </strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Detalhamento de Valores e Peças -->
            <div class="section">
                <div class="section-title">Detalhamento Financeiro dos Serviços e Peças</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item / Serviço / Insumo</th>
                            <th style="width: 80px; text-align: center;">Qtd</th>
                            <th style="width: 120px; text-align: right;">Unitário</th>
                            <th style="width: 130px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mão de Obra Técnica Especializada</td>
                            <td style="text-align: center;">1</td>
                            <td style="text-align: right;">R$ <?= number_format($laborVal, 2, ',', '.') ?></td>
                            <td style="text-align: right;">R$ <?= number_format($laborVal, 2, ',', '.') ?></td>
                        </tr>
                        <?php if (!empty($parts)): ?>
                            <?php foreach ($parts as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['part_name']) ?></td>
                                    <td style="text-align: center;"><?= (int)$p['quantity'] ?></td>
                                    <td style="text-align: right;">R$ <?= number_format((float)$p['unit_price'], 2, ',', '.') ?></td>
                                    <td style="text-align: right;">R$ <?= number_format((float)$p['unit_price'] * (int)$p['quantity'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr style="font-size: 13px; font-weight: 800; background: #f8fafc;">
                            <td colspan="3" style="text-align: right; text-transform: uppercase;">Valor Total do Atendimento:</td>
                            <td style="text-align: right; color: #0f172a;">R$ <?= number_format($totalVal, 2, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagamento via Pix no Laudo A4 -->
            <?php if ($showPix && !empty($tenant['pix_key'])): 
                $pixKey = htmlspecialchars($tenant['pix_key'], ENT_QUOTES, 'UTF-8');
                $pixBeneficiary = htmlspecialchars($tenant['pix_beneficiary'] ?? $tenant['business_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $pixQrImg = QrCodeService::pngUrl($tenant['pix_key'], 120);
            ?>
                <div class="box section" style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border-color: #bbf7d0;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <img src="<?= $pixQrImg ?>" alt="QR Code Pix" style="width: 60px; height: 60px; border-radius: 4px;">
                        <div>
                            <strong style="color: #166534; font-size: 11px; text-transform: uppercase;">Pagar Imediatamente via Pix</strong>
                            <p style="font-size: 10.5px; color: #15803d; margin-top: 2px;">Chave Pix: <strong style="font-family: monospace;"><?= $pixKey ?></strong></p>
                            <?php if (!empty($pixBeneficiary)): ?>
                                <p style="font-size: 9.5px; color: #166534;">Beneficiário: <?= $pixBeneficiary ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 9.5px; color: #15803d; text-transform: uppercase; font-weight: bold;">Total da OS</span>
                        <div style="font-size: 16px; font-weight: 900; color: #14532d;">R$ <?= number_format($totalVal, 2, ',', '.') ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Termos e Condições Legais (CDC Art. 26 e Abandono) -->
            <?php if ($showTerms): ?>
                <div class="terms-box section">
                    <strong>TERMOS E CONDIÇÕES LEGAIS (Código de Defesa do Consumidor Art. 26 e Código Civil Art. 1.275):</strong><br>
                    1. A garantia cobre exclusivamente os serviços e peças discriminados por <strong><?= $warrantyDays ?> dias corridos</strong> contra defeitos de fabricação, sendo cancelada em caso de violação de lacre, oxidação por umidade, quedas, trincas ou intervenção não autorizada.<br>
                    2. <strong>Cláusula de Abandono:</strong> O cliente declara ciência de que equipamentos não retirados em até <strong>90 (noventa) dias corridos</strong> após a notificação formal de conclusão ou reprovação de orçamento serão considerados expressamente abandonados, autorizando a assistência técnica a cobrar taxa de custódia e destinar o bem para amortização de prejuízos com peças e armazenamento.
                </div>
            <?php endif; ?>
        </div>

        <!-- Rodapé com Assinaturas e QR Code de Rastreio -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15px;">
                <?php if ($showQr): ?>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <img src="<?= $qrCodeImg ?>" alt="QR Rastreio" style="width: 60px; height: 60px;">
                        <div style="font-size: 10px; color: #64748b; line-height: 1.2;">
                            <strong>Acompanhe online:</strong><br>
                            Escaneie o QR Code para ver fotos da bancada,<br>
                            laudo completo e status em tempo real.
                        </div>
                    </div>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>

                <div class="signatures" style="flex: 1; margin-top: 0; margin-left: 30px;">
                    <div>
                        <strong><?= $businessName ?></strong><br>
                        <span style="font-size: 10px; color: #64748b;">Responsável Técnico</span>
                    </div>
                    <div>
                        <strong><?= $clientName ?></strong><br>
                        <span style="font-size: 10px; color: #64748b;">Assinatura do Cliente</span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; font-size: 9px; color: #94a3b8; margin-top: 15px; border-top: 1px solid #f1f5f9; padding-top: 5px;">
                Emitido via Zyrtex SaaS &bull; Tecnologia para Assistências Técnicas &bull; <?= date('d/m/Y H:i:s') ?>
            </div>
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
