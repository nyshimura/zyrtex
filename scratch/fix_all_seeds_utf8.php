<?php
require_once __DIR__ . '/../src/autoload.php';
\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

// 1. Corrige tenant 1
$promotions = [
    [
        'title' => 'Troca de Tela com Película 3D Grátis',
        'description' => 'Display original com aplicação de película protetora e garantia legal de 90 dias.',
        'price' => '199,90'
    ],
    [
        'title' => 'Banho Químico Ultrassônico',
        'description' => 'Desoxidação profunda de placa para celulares molhados ou com umidade.',
        'price' => '130,00'
    ],
    [
        'title' => 'Troca de Bateria com Saúde 100%',
        'description' => 'Bateria homologada com selo de procedência e calibração de ciclo.',
        'price' => '149,90'
    ]
];

$address = [
    'street' => 'Av. Paulista, 1000 - Loja 4',
    'city' => 'São Paulo - SP',
    'maps_link' => 'https://maps.google.com'
];

$hours = [
    'weekdays' => 'Segunda a Sexta: 08:30 às 18:30',
    'saturday' => 'Sábado: 09:00 às 13:00'
];

\Zyrtex\Core\Database::execute(
    "UPDATE tenants 
     SET business_name = 'Zyrtex Smart Fix Assistência',
         slogan = 'Especialistas em reparos rápidos com peças originais e prontuário digital',
         address_json = :addr,
         opening_hours_json = :hours,
         promotions_json = :promos
     WHERE id = 1",
    [
        'addr' => json_encode($address, JSON_UNESCAPED_UNICODE),
        'hours' => json_encode($hours, JSON_UNESCAPED_UNICODE),
        'promos' => json_encode($promotions, JSON_UNESCAPED_UNICODE)
    ]
);

// 2. Corrige produtos
\Zyrtex\Core\Database::execute("UPDATE products_pos SET category = 'Serviços' WHERE category LIKE 'Servi%'");

// 3. Corrige service_orders #1
\Zyrtex\Core\Database::execute(
    "UPDATE service_orders 
     SET defect_reported = 'Aparelho caiu no chão. Tela trincou e metade do touch parou de responder.',
         technical_diagnosis = 'Constatado rompimento das trilhas do flex do digitalizador. Necessária substituição completa do módulo frontal.',
         simplified_diagnosis_ai = 'Olá Mariana! Identificamos que a queda danificou a peça responsável pelo toque na tela. Faremos a troca da tela completa por uma nova com garantia de 90 dias.'
     WHERE id = 1"
);

echo "Todos os textos em UTF-8 corrigidos com perfeição!\n";
