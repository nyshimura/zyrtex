<?php

return [
    'cellphones' => [
        'name' => 'Smartphones & Tablets',
        'code' => 'cellphones',
        'icon' => 'device-mobile',
        'identifier_name' => 'IMEI ou Serial',
        'identifier_placeholder' => 'Ex: 356987123456789 ou F17X49...',
        'fields' => [
            'brand' => 'Marca / Fabricante (Ex: Apple, Samsung, Xiaomi)',
            'model' => 'Modelo Exato (Ex: iPhone 13 Pro 128GB)',
            'passcode' => 'Senha / Padrão de Desbloqueio (Se autorizada)',
            'color' => 'Cor do Aparelho',
            'battery_health' => 'Saúde da Bateria (%)',
        ],
        'checklist' => [
            'powers_on' => 'Aparelho Liga?',
            'screen_touch' => 'Touch e Display Funcionam?',
            'charging' => 'Carrega Normal?',
            'cameras' => 'Câmeras Frontal e Traseira OK?',
            'audio' => 'Microfone e Alto-Falante OK?',
            'wifi_bluetooth' => 'Wi-Fi e Bluetooth Conectam?',
            'biometrics' => 'Biometria / Face ID Funciona?',
            'housing_condition' => 'Trincos ou Riscos na Carcaça?',
        ]
    ],
    'computers' => [
        'name' => 'Computadores & Notebooks',
        'code' => 'computers',
        'icon' => 'desktop-computer',
        'identifier_name' => 'Número de Série / Service Tag',
        'identifier_placeholder' => 'Ex: BRG92482X1 ou S/N...',
        'fields' => [
            'brand' => 'Marca (Ex: Dell, Lenovo, Acer, Custom PC)',
            'processor' => 'Processador (Ex: Core i5 11ª Ger, Ryzen 5)',
            'ram' => 'Memória RAM (Ex: 16GB DDR4)',
            'storage' => 'Armazenamento (Ex: SSD 512GB NVMe)',
            'has_charger' => 'Carregador / Fonte Entregue junto?',
            'os_pass' => 'Senha do Sistema Operacional',
        ],
        'checklist' => [
            'powers_on' => 'Dá Vídeo / Liga?',
            'keyboard_touchpad' => 'Teclado e Touchpad OK?',
            'screen_hinges' => 'Dobradiças e Tela Íntegras?',
            'battery_holding' => 'Bateria Segura Carga?',
            'ports_usb' => 'Entradas USB e HDMI Funcionando?',
            'cooling_fan' => 'Cooler gira sem ruídos anormais?',
            'storage_health' => 'SSD/HD sem setores defeituosos?',
        ]
    ],
    'automotive' => [
        'name' => 'Oficina Mecânica & Auto Elétrica',
        'code' => 'automotive',
        'icon' => 'truck',
        'identifier_name' => 'Placa do Veículo / Chassi',
        'identifier_placeholder' => 'Ex: BRA2E19 ou 9BWZZZ...',
        'fields' => [
            'brand_model' => 'Veículo (Ex: VW Gol 1.6 MSI, Fiat Toro)',
            'year' => 'Ano de Fabricação / Modelo',
            'mileage' => 'Quilometragem Atual (KM)',
            'fuel_type' => 'Combustível (Flex, Gasolina, Diesel)',
            'fuel_level' => 'Nível de Combustível no Tanque',
        ],
        'checklist' => [
            'starts_engine' => 'Motor Dá Partida?',
            'dashboard_alerts' => 'Luz de Injeção / Freio Acesa?',
            'brakes' => 'Pastilhas e Freios em Ordem?',
            'suspension' => 'Barulhos em Suspensão / Direção?',
            'tires' => 'Pneus e Estepe em Bom Estado?',
            'fluids_level' => 'Óleo e Arrefecimento Verificados?',
            'bodywork' => 'Arranhões ou Amassados na Lataria?',
        ]
    ],
    'appliances' => [
        'name' => 'Eletrodomésticos & Linha Branca',
        'code' => 'appliances',
        'icon' => 'sparkles',
        'identifier_name' => 'Nº de Série ou Modelo',
        'identifier_placeholder' => 'Ex: S/N 827391...',
        'fields' => [
            'appliance_type' => 'Aparelho (Geladeira, Lava e Seca, Microondas)',
            'voltage' => 'Tensão (110V, 220V, Bivolt)',
            'brand' => 'Marca (Ex: Brastemp, Electrolux, LG)',
        ],
        'checklist' => [
            'powers_on' => 'Liga na Tomada?',
            'main_function' => 'Refrigera / Aquece / Gira?',
            'panel_buttons' => 'Painel de Botões Responde?',
            'water_leaks' => 'Há Vazamento de Água/Gás?',
        ]
    ]
];
