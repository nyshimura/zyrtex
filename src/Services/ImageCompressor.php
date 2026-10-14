<?php

declare(strict_types=1);

namespace Zyrtex\Services;

/**
 * ImageCompressor & Media Uploader Service (Fase 5 - RNF02 & RN-Shield03)
 * Processamento Server-Side de Imagens via PHP GD com redimensionamento,
 * compressão inteligente, geração de thumbnail Base64 e quarentena de disco.
 * 
 * Totalmente tolerante a falhas: se a biblioteca GD não estiver ativa no PHP,
 * realiza a validação de segurança via finfo e persiste o arquivo diretamente
 * sem gerar erros fatais ou quebrar a aplicação.
 */
class ImageCompressor {

    public const MAX_FILE_SIZE = 15728640; // 15MB em bytes (RNF02)
    public const ALLOWED_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    /**
     * Processa um arquivo enviado via $_FILES
     *
     * @param array $fileInput Array $_FILES['campo']
     * @param string $subDir Subdiretório em public/uploads/ (ex: 'logos', 'products', 'os', 'promotions')
     * @param int $maxWidth Largura máxima em pixels
     * @param int $maxHeight Altura máxima em pixels
     * @return array [success => bool, url => string, path => string, thumbnail => string, error => ?string]
     */
    public static function processUpload(
        array $fileInput,
        string $subDir = 'general',
        int $maxWidth = 1600,
        int $maxHeight = 1600
    ): array {
        if (empty($fileInput['tmp_name']) || !is_uploaded_file($fileInput['tmp_name'])) {
            return ['success' => false, 'error' => 'Nenhum arquivo enviado ou upload inválido.'];
        }

        if (($fileInput['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Falha no upload do arquivo (código: ' . ($fileInput['error'] ?? 'desconhecido') . ').'];
        }

        if (($fileInput['size'] ?? 0) > self::MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'O arquivo excede o limite máximo permitido de 15MB.'];
        }

        $tmpPath = $fileInput['tmp_name'];

        return self::processFileInternal($tmpPath, $fileInput['name'] ?? 'image', $subDir, $maxWidth, $maxHeight);
    }

    /**
     * Processa uma imagem a partir de uma URL informada pelo usuário (download seguro)
     *
     * @param string $imageUrl URL da imagem
     * @param string $subDir Subdiretório em public/uploads/
     * @return array [success => bool, url => string, path => string, thumbnail => string, error => ?string]
     */
    public static function processUrl(string $imageUrl, string $subDir = 'general'): array {
        $imageUrl = trim($imageUrl);
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'error' => 'URL de imagem inválida.'];
        }

        // Se já for uma URL local do próprio sistema, retorna ela mesma
        if (str_contains($imageUrl, '/uploads/')) {
            return [
                'success' => true,
                'url' => $imageUrl,
                'path' => $imageUrl,
                'thumbnail' => ''
            ];
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'zyrtex_img_');
        if (!$tempFile) {
            return ['success' => false, 'error' => 'Erro ao criar arquivo temporário no servidor.'];
        }

        try {
            // Download com timeout e validação de tamanho máximo
            $ch = curl_init($imageUrl);
            $fp = fopen($tempFile, 'wb');
            curl_setopt_array($ch, [
                CURLOPT_FILE => $fp,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXFILESIZE => self::MAX_FILE_SIZE,
                CURLOPT_USERAGENT => 'Zyrtex-SaaS/1.0',
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $exec = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if (!$exec || $httpCode >= 400 || filesize($tempFile) === 0) {
                // Se falhou o download, podemos aceitar a URL direta como fallback externo
                return [
                    'success' => true,
                    'url' => $imageUrl,
                    'path' => $imageUrl,
                    'thumbnail' => ''
                ];
            }

            return self::processFileInternal($tempFile, basename(parse_url($imageUrl, PHP_URL_PATH) ?? 'image.jpg'), $subDir);
        } finally {
            // RN-Shield03: Remoção incondicional do arquivo temporário
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * Processamento interno com compressão GD e geração de thumbnail Base64.
     * Possui fallback gracioso com cópia direta e validação MIME se GD não estiver disponível.
     */
    private static function processFileInternal(
        string $filePath,
        string $originalName,
        string $subDir,
        int $maxWidth = 1600,
        int $maxHeight = 1600
    ): array {
        // Valida MIME Type real via finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_MIMES[$mime])) {
            return ['success' => false, 'error' => 'Formato não suportado. Envie imagens JPG, PNG ou WebP.'];
        }

        $extension = self::ALLOWED_MIMES[$mime];

        // Diretório de destino
        $baseUploadDir = __DIR__ . '/../../public/uploads/' . trim($subDir, '/');
        if (!is_dir($baseUploadDir)) {
            @mkdir($baseUploadDir, 0755, true);
        }

        $uniqueFileName = bin2hex(random_bytes(16)) . '.' . ($extension === 'png' ? 'png' : ($extension === 'webp' ? 'webp' : 'jpg'));
        $destinationPath = $baseUploadDir . '/' . $uniqueFileName;
        $appConfig = require __DIR__ . '/../../config/app.php';
        $basePath = rtrim(parse_url($appConfig['url'] ?? 'http://localhost/zyrtex', PHP_URL_PATH) ?? '', '/');
        $publicRelativeUrl = ($basePath !== '' ? $basePath : '') . '/uploads/' . trim($subDir, '/') . '/' . $uniqueFileName;

        // Verifica se a extensão GD do PHP está ativa com suporte às funções necessárias
        $hasGd = extension_loaded('gd') && function_exists('imagecreatetruecolor');

        if (!$hasGd) {
            // Fallback seguro: copia o arquivo validado diretamente para o disco
            if (@copy($filePath, $destinationPath)) {
                return [
                    'success' => true,
                    'url' => $publicRelativeUrl,
                    'path' => $destinationPath,
                    'file_name' => $originalName,
                    'thumbnail' => ''
                ];
            }
            return ['success' => false, 'error' => 'Não foi possível gravar o arquivo no servidor.'];
        }

        // Tenta carregar a imagem com funções nativas do GD com checagem de existência prévia
        $srcImage = null;
        try {
            if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
                $srcImage = @\imagecreatefromjpeg($filePath);
            } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
                $srcImage = @\imagecreatefrompng($filePath);
            } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
                $srcImage = @\imagecreatefromwebp($filePath);
            } elseif ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
                $srcImage = @\imagecreatefromgif($filePath);
            }
        } catch (\Throwable $e) {
            $srcImage = null;
        }

        if (!$srcImage) {
            // Se GD não conseguiu decodificar, faz fallback para cópia direta
            if (@copy($filePath, $destinationPath)) {
                return [
                    'success' => true,
                    'url' => $publicRelativeUrl,
                    'path' => $destinationPath,
                    'file_name' => $originalName,
                    'thumbnail' => ''
                ];
            }
            return ['success' => false, 'error' => 'Não foi possível processar a imagem enviada.'];
        }

        try {
            $origWidth = \imagesx($srcImage);
            $origHeight = \imagesy($srcImage);

            // 1. Redimensiona para limites máximos preservando proporção
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight, 1.0);
            $newWidth = max(1, (int)round($origWidth * $ratio));
            $newHeight = max(1, (int)round($origHeight * $ratio));

            $dstImage = \imagecreatetruecolor($newWidth, $newHeight);

            // Preserva transparência para PNG e WebP
            if ($extension === 'png') {
                \imagealphablending($dstImage, false);
                \imagesavealpha($dstImage, true);
                $transparent = \imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                \imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
                \imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                \imagepng($dstImage, $destinationPath, 8);
            } elseif ($extension === 'webp' && function_exists('imagewebp')) {
                \imagealphablending($dstImage, false);
                \imagesavealpha($dstImage, true);
                \imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                \imagewebp($dstImage, $destinationPath, 82);
            } else {
                // Converte para JPG 82% qualidade
                $whiteBg = \imagecolorallocate($dstImage, 255, 255, 255);
                \imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $whiteBg);
                \imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
                \imagejpeg($dstImage, $destinationPath, 82);
            }
            \imagedestroy($dstImage);

            // 2. Gera Thumbnail leve Base64 (~180px) para cache instantâneo e offline
            $thumbnailBase64 = '';
            $thumbRatio = min(180 / $origWidth, 180 / $origHeight, 1.0);
            $thumbW = max(1, (int)round($origWidth * $thumbRatio));
            $thumbH = max(1, (int)round($origHeight * $thumbRatio));

            $thumbImage = \imagecreatetruecolor($thumbW, $thumbH);
            \imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $thumbW, $thumbH, $origWidth, $origHeight);

            ob_start();
            \imagejpeg($thumbImage, null, 70);
            $thumbData = ob_get_clean();
            \imagedestroy($thumbImage);

            if (!empty($thumbData)) {
                $thumbnailBase64 = 'data:image/jpeg;base64,' . base64_encode($thumbData);
            }

            return [
                'success' => true,
                'url' => $publicRelativeUrl,
                'path' => $destinationPath,
                'file_name' => $originalName,
                'thumbnail' => $thumbnailBase64
            ];
        } catch (\Throwable $e) {
            // Em caso de qualquer falha na manipulação GD, garante cópia do arquivo original
            if (!file_exists($destinationPath)) {
                @copy($filePath, $destinationPath);
            }
            return [
                'success' => true,
                'url' => $publicRelativeUrl,
                'path' => $destinationPath,
                'file_name' => $originalName,
                'thumbnail' => ''
            ];
        } finally {
            if ($srcImage) {
                @\imagedestroy($srcImage);
            }
        }
    }
}
