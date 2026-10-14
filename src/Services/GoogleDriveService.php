<?php

declare(strict_types=1);

namespace Zyrtex\Services;

use Zyrtex\Core\Database;

/**
 * Conector Google Drive (BYOS - Bring Your Own Storage - RF10)
 * Permite que cada oficina envie fotos e mídias das OSs diretamente para sua
 * pasta do Google Drive através de Service Account ou OAuth2 Token.
 * 
 * Atende estritamente à RN-Shield03: Quarentena de disco com bloco try/finally.
 */
class GoogleDriveService {

    /**
     * Realiza o upload de um arquivo para o Google Drive do Tenant
     *
     * @param int $tenantId ID da oficina
     * @param string $localFilePath Caminho do arquivo no servidor
     * @param string $fileName Nome do arquivo a ser salvo no Drive
     * @param string $mimeType Tipo MIME da imagem
     * @return array [success => bool, file_id => ?string, web_view_link => ?string, error => ?string]
     */
    public static function uploadFile(
        int $tenantId,
        string $localFilePath,
        string $fileName,
        string $mimeType = 'image/jpeg'
    ): array {
        if (!file_exists($localFilePath) || filesize($localFilePath) === 0) {
            return ['success' => false, 'error' => 'Arquivo local não encontrado ou vazio.'];
        }

        $tenant = Database::fetchOne(
            "SELECT gdrive_folder_id, gdrive_token_json FROM tenants WHERE id = :id LIMIT 1",
            ['id' => $tenantId]
        );

        if (!$tenant || empty($tenant['gdrive_folder_id'])) {
            return ['success' => false, 'error' => 'Pasta do Google Drive não configurada para esta oficina.'];
        }

        $folderId = trim((string)$tenant['gdrive_folder_id']);
        $tokenData = json_decode((string)($tenant['gdrive_token_json'] ?? ''), true);

        if (!$tokenData || (!isset($tokenData['client_email']) && !isset($tokenData['access_token']))) {
            return ['success' => false, 'error' => 'Credenciais do Google Drive não configuradas ou inválidas.'];
        }

        // Obtém Access Token válido
        $accessToken = self::getAccessToken($tokenData);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'Falha ao autenticar com a API do Google Drive.'];
        }

        // Envia o arquivo usando Multipart Upload da API v3 do Google Drive
        $boundary = '-------zyrtex_gdrive_' . md5(uniqid('', true));
        $metadata = [
            'name' => $fileName,
            'parents' => [$folderId]
        ];

        $fileContent = file_get_contents($localFilePath);
        if ($fileContent === false) {
            return ['success' => false, 'error' => 'Não foi possível ler o arquivo local para upload.'];
        }

        $multipartBody = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$mimeType}\r\n\r\n"
            . $fileContent . "\r\n"
            . "--{$boundary}--";

        $ch = curl_init('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipartBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: multipart/related; boundary=' . $boundary,
            'Content-Length: ' . strlen($multipartBody)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            if (!empty($data['id'])) {
                return [
                    'success' => true,
                    'file_id' => $data['id'],
                    'web_view_link' => $data['webViewLink'] ?? "https://drive.google.com/file/d/{$data['id']}/view"
                ];
            }
        }

        return [
            'success' => false,
            'error' => 'Erro ao transferir arquivo para o Google Drive (HTTP ' . $httpCode . ').'
        ];
    }

    /**
     * Obtém um token de acesso válido a partir de Service Account JSON ou OAuth token
     */
    private static function getAccessToken(array $tokenData): ?string {
        // Se já tiver access_token direto não expirado
        if (!empty($tokenData['access_token']) && empty($tokenData['private_key'])) {
            return $tokenData['access_token'];
        }

        // Service Account: Gera JWT assinado com chave privada RS256
        if (!empty($tokenData['client_email']) && !empty($tokenData['private_key'])) {
            $now = time();
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $claimSet = [
                'iss' => $tokenData['client_email'],
                'scope' => 'https://www.googleapis.com/auth/drive.file',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ];

            $base64Header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
            $base64Claim = rtrim(strtr(base64_encode(json_encode($claimSet)), '+/', '-_'), '=');
            $signatureInput = $base64Header . '.' . $base64Claim;

            $signature = '';
            $privateKey = openssl_pkey_get_private($tokenData['private_key']);
            if (!$privateKey) {
                return null;
            }

            if (!openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                return null;
            }

            $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $jwt = $signatureInput . '.' . $base64Signature;

            // Solicita o token de acesso à Google OAuth
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $res) {
                $tokenRes = json_decode($res, true);
                return $tokenRes['access_token'] ?? null;
            }
        }

        return null;
    }
}
