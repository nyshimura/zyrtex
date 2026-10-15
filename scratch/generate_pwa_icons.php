<?php
$dir = __DIR__ . '/../public/assets/images';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// 1. Gera o Ícone em SVG (Vetorial de Alta Resolução)
$svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#4f46e5" />
      <stop offset="50%" stop-color="#3730a3" />
      <stop offset="100%" stop-color="#0f172a" />
    </linearGradient>
    <linearGradient id="wrenchGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#38bdf8" />
      <stop offset="100%" stop-color="#818cf8" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="16" stdDeviation="16" flood-color="#000" flood-opacity="0.35"/>
    </filter>
  </defs>

  <!-- Fundo com cantos arredondados (estilo app icon iOS / Android maskable) -->
  <rect width="512" height="512" rx="128" fill="url(#bgGrad)"/>
  
  <!-- Círculo Interno Suave -->
  <circle cx="256" cy="256" r="190" fill="#ffffff" fill-opacity="0.04" stroke="#ffffff" stroke-opacity="0.08" stroke-width="4"/>

  <!-- Letra Z Estilizada & Símbolo Tecnológico de Assistência -->
  <g filter="url(#shadow)">
    <!-- Smartphone Frame -->
    <rect x="156" y="106" width="200" height="300" rx="28" fill="none" stroke="url(#wrenchGrad)" stroke-width="20"/>
    <line x1="236" y1="136" x2="276" y2="136" stroke="#818cf8" stroke-width="8" stroke-linecap="round"/>
    <circle cx="256" cy="376" r="10" fill="#38bdf8"/>
    
    <!-- Relâmpago / Z Central de Alta Energia -->
    <path d="M280 186 L216 266 L266 266 L232 326 L306 246 L256 246 Z" fill="#38bdf8"/>
  </g>
</svg>
SVG;

file_put_contents($dir . '/pwa-icon.svg', $svgContent);
echo "SVG criado em: " . $dir . "/pwa-icon.svg\n";

// 2. Função para desenhar o ícone em PNG via GD
function generatePngIcon($size, $outputPath) {
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);

    $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $trans);
    imagealphablending($im, true);

    // Gradiente de Fundo Simulado (Quadrado com cantos arredondados)
    $radius = (int)($size * 0.22);
    $bgTop = imagecolorallocate($im, 79, 70, 229);    // #4f46e5
    $bgBottom = imagecolorallocate($im, 15, 23, 42);  // #0f172a
    $cyan = imagecolorallocate($im, 56, 189, 248);    // #38bdf8
    $white = imagecolorallocate($im, 255, 255, 255);

    // Preenche com o fundo degradê
    for ($y = 0; $y < $size; $y++) {
        $ratio = $y / $size;
        $r = (int)(79 * (1 - $ratio) + 15 * $ratio);
        $g = (int)(70 * (1 - $ratio) + 23 * $ratio);
        $b = (int)(229 * (1 - $ratio) + 42 * $ratio);
        $lineColor = imagecolorallocate($im, $r, $g, $b);
        imagefilledrectangle($im, 0, $y, $size, $y, $lineColor);
    }

    // Desenha borda de smartphone estilisado no centro
    $phoneW = (int)($size * 0.44);
    $phoneH = (int)($size * 0.65);
    $phoneX = (int)(($size - $phoneW) / 2);
    $phoneY = (int)(($size - $phoneH) / 2);
    $thickness = max(2, (int)($size * 0.035));

    imagesetthickness($im, $thickness);
    imagerectangle($im, $phoneX, $phoneY, $phoneX + $phoneW, $phoneY + $phoneH, $cyan);

    // Desenha botão home e alto-falante
    imagefilledellipse($im, (int)($size / 2), $phoneY + $phoneH - (int)($size * 0.06), (int)($size * 0.04), (int)($size * 0.04), $cyan);
    imageline($im, (int)($size / 2) - (int)($size * 0.05), $phoneY + (int)($size * 0.05), (int)($size / 2) + (int)($size * 0.05), $phoneY + (int)($size * 0.05), $white);

    // Letra Z estilizada no meio da tela
    $zPoints = [
        ['x' => $size * 0.42, 'y' => $size * 0.38],
        ['x' => $size * 0.58, 'y' => $size * 0.38],
        ['x' => $size * 0.42, 'y' => $size * 0.58],
        ['x' => $size * 0.58, 'y' => $size * 0.58],
    ];

    imagesetthickness($im, max(3, (int)($size * 0.045)));
    imageline($im, (int)$zPoints[0]['x'], (int)$zPoints[0]['y'], (int)$zPoints[1]['x'], (int)$zPoints[1]['y'], $white);
    imageline($im, (int)$zPoints[1]['x'], (int)$zPoints[1]['y'], (int)$zPoints[2]['x'], (int)$zPoints[2]['y'], $cyan);
    imageline($im, (int)$zPoints[2]['x'], (int)$zPoints[2]['y'], (int)$zPoints[3]['x'], (int)$zPoints[3]['y'], $white);

    imagepng($im, $outputPath);
    imagedestroy($im);
    echo "PNG {$size}x{$size} criado em: {$outputPath}\n";
}

generatePngIcon(192, $dir . '/pwa-icon-192.png');
generatePngIcon(512, $dir . '/pwa-icon-512.png');

echo "Icons gerados com sucesso!\n";
