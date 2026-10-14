<?php

declare(strict_types=1);

namespace Zyrtex\Services;

/**
 * Motor de Impressão Multiformato (RF14 e RF16)
 * Gerencia padronizações de largura, regras @media print e configurações físicas para
 * Bobina 80mm, Bobina 58mm, Mini Adesivos QR e Folha A4 Oficial.
 */
class PrintEngine {

    public const FORMAT_80MM = '80mm';
    public const FORMAT_58MM = '58mm';
    public const FORMAT_LABEL = 'label';
    public const FORMAT_A4 = 'a4';

    /**
     * Retorna lista de todos os formatos físicos suportados com metadados
     */
    public static function getAvailableFormats(): array {
        return [
            self::FORMAT_80MM => [
                'name' => 'Térmica 80mm',
                'icon' => '🧾',
                'width_mm' => 80,
                'printable_mm' => 74,
                'paper_type' => 'Bobina Térmica Padrão Balcão',
                'description' => 'Impressoras Epson TM-T20, Elgin i9, Bematech MP-4200, Daruma. Ideal para cupom completo com laudo e QR Code.',
                'view' => 'print/thermal_80mm'
            ],
            self::FORMAT_58MM => [
                'name' => 'Térmica 58mm',
                'icon' => '🧾',
                'width_mm' => 58,
                'printable_mm' => 50,
                'paper_type' => 'Bobina Térmica Portátil / Mini POS',
                'description' => 'Mini impressoras bluetooth e maquininhas portáteis. Formato compacto e econômico.',
                'view' => 'print/thermal_58mm'
            ],
            self::FORMAT_LABEL => [
                'name' => 'Etiquetas Adesivas QR',
                'icon' => '🏷️',
                'width_mm' => 50,
                'height_mm' => 30,
                'paper_type' => 'Adesivos Térmicos (50x30mm / 40x25mm)',
                'description' => 'Etiquetas adesivas para fixar no chassi/peça com QR Code direto para o rastreio da OS.',
                'view' => 'print/sticker_labels'
            ],
            self::FORMAT_A4 => [
                'name' => 'Folha A4 Oficial',
                'icon' => '📄',
                'width_mm' => 210,
                'height_mm' => 297,
                'paper_type' => 'Folha Sulfite A4',
                'description' => 'Contrato formal e laudo técnico com termos legais completos (CDC e Abandono) para arquivamento.',
                'view' => 'print/a4_document'
            ]
        ];
    }

    /**
     * Resolve a view correta para o formato solicitado
     */
    public static function getViewForFormat(string $format): string {
        $formats = self::getAvailableFormats();
        return $formats[$format]['view'] ?? 'print/thermal_80mm';
    }

    /**
     * Gera dados fictícios para teste de alinhamento e calibração de impressora
     */
    public static function getSampleTestData(array $tenant, string $format): array {
        $now = date('Y-m-d H:i:s');
        $niches = require __DIR__ . '/../../config/niches.php';
        $nicheKey = $tenant['niche'] ?? 'cellphones';
        $nicheConfig = $niches[$nicheKey] ?? $niches['cellphones'];

        $sampleChecklist = [];
        if (!empty($nicheConfig['checklist'])) {
            $i = 0;
            foreach ($nicheConfig['checklist'] as $key => $title) {
                $sampleChecklist[$key] = ($i % 2 === 0) ? 'ok' : 'defeito';
                $i++;
            }
        }

        $sampleOs = [
            'id' => 9999,
            'os_number' => 'OS-TESTE-CALIBRA',
            'client_name' => 'Cliente Teste de Alinhamento',
            'client_phone' => '(11) 99999-8888',
            'client_document' => '123.456.789-00',
            'client_address' => 'Av. das Assistências, 1000 - Centro',
            'equipment_info' => 'Equipamento Modelo para Calibração de Margens',
            'identifier_code' => 'TESTE-SERIAL-998877',
            'defect_reported' => 'Teste de quebra de linha de defeito informado. Verificando largura máxima e legibilidade das fontes.',
            'technical_diagnosis' => 'Laudo técnico teste: Verificação e alinhamento do motor térmico da impressora.',
            'status' => 'ready',
            'warranty_days' => 90,
            'labor_value' => 120.00,
            'parts_value' => 80.00,
            'total_value' => 200.00,
            'created_at' => $now,
            'updated_at' => $now
        ];

        return [
            'sampleOs' => $sampleOs,
            'sampleChecklist' => $sampleChecklist,
            'nicheConfig' => $nicheConfig
        ];
    }
}
