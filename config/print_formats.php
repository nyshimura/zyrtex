<?php

return [
    'formats' => [
        '80mm' => [
            'name' => 'Bobina Térmica 80mm (Padrão Balcão)',
            'width_css' => '72mm',
            'paper_width' => '80mm',
            'type' => 'thermal',
            'recommended_for' => 'Impressoras Bematech, Elgin i9, Epson TM-T20'
        ],
        '58mm' => [
            'name' => 'Bobina Térmica 58mm (Mini Impressora / Portátil)',
            'width_css' => '48mm',
            'paper_width' => '58mm',
            'type' => 'thermal',
            'recommended_for' => 'Mini impressoras Bluetooth, Maquininhas POS'
        ],
        'a4' => [
            'name' => 'Folha A4 Completa (Documento e Laudo Oficial)',
            'width_css' => '210mm',
            'paper_width' => 'A4',
            'type' => 'sheet',
            'recommended_for' => 'Impressoras Jato de Tinta ou Laser'
        ],
        'label_50x30' => [
            'name' => 'Etiqueta Adesiva QR Code 50x30mm (Lacre de Garantia)',
            'width_css' => '50mm',
            'height_css' => '30mm',
            'type' => 'label',
            'recommended_for' => 'Impressoras Térmicas Zebra, Argox, Elgin L42'
        ],
        'label_40x25' => [
            'name' => 'Mini Etiqueta Adesiva QR Code 40x25mm (Peças Pequenas)',
            'width_css' => '40mm',
            'height_css' => '25mm',
            'type' => 'label',
            'recommended_for' => 'Lacre de carcaça de celulares e placas'
        ]
    ]
];
