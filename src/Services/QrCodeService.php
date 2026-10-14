<?php

declare(strict_types=1);

namespace Zyrtex\Services;

/**
 * Serviço de Geração de QR Code (RF14 e RF16)
 * Suporta URL otimizada com margem zero e dados em SVG para máxima nitidez em impressoras térmicas
 */
class QrCodeService {

    /**
     * Retorna a URL otimizada de alta resolução para impressão térmica e adesivos
     */
    public static function url(string $data, int $size = 140): string {
        $encoded = urlencode(trim($data));
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&margin=1&format=svg&data={$encoded}";
    }

    /**
     * Retorna URL em formato PNG caso a impressora não processe SVG nativo
     */
    public static function pngUrl(string $data, int $size = 180): string {
        $encoded = urlencode(trim($data));
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&margin=1&format=png&data={$encoded}";
    }

    /**
     * Alias para url (retorna URL para renderização SVG nítida)
     */
    public static function generateSvg(string $data, int $size = 160): string {
        return self::url($data, $size);
    }
}
