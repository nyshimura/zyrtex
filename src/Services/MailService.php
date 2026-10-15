<?php

declare(strict_types=1);

namespace Zyrtex\Services;

use Zyrtex\Core\Database;
use Zyrtex\Core\Env;

/**
 * Serviço de E-mails Transacionais do Zyrtex SaaS
 * Cliente SMTP Socket Nativo (RFC 5321) com suporte a STARTTLS, SSL,
 * templates dinâmicos por blocos e simulação offline segura.
 */
class MailService {

    private const CIPHER = 'AES-256-CBC';

    /**
     * Retorna as configurações SMTP salvas no SaaS
     */
    public static function getConfig(): array {
        $rows = Database::fetchAll("SELECT config_key, config_value FROM saas_settings WHERE config_key LIKE 'smtp_%'");
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['config_key']] = $r['config_value'];
        }

        $host = $settings['smtp_host'] ?? Env::get('SMTP_HOST', '');
        $port = (int)($settings['smtp_port'] ?? Env::get('SMTP_PORT', 587));
        $encryption = $settings['smtp_encryption'] ?? Env::get('SMTP_ENCRYPTION', 'tls'); // 'tls', 'ssl', 'none'
        $username = $settings['smtp_username'] ?? Env::get('SMTP_USERNAME', '');
        $rawPass = $settings['smtp_password'] ?? Env::get('SMTP_PASSWORD', '');
        $fromEmail = $settings['smtp_from_email'] ?? Env::get('SMTP_FROM_EMAIL', 'notificacoes@zyrtex.com.br');
        $fromName = $settings['smtp_from_name'] ?? Env::get('SMTP_FROM_NAME', 'Zyrtex SaaS');
        $active = (int)($settings['smtp_active'] ?? Env::get('SMTP_ACTIVE', 0)) === 1;

        // Descriptografa senha se estiver cifrada
        $password = self::decryptPassword($rawPass);

        return [
            'host' => trim((string)$host),
            'port' => $port > 0 ? $port : 587,
            'encryption' => in_array($encryption, ['tls', 'ssl', 'none'], true) ? $encryption : 'tls',
            'username' => trim((string)$username),
            'password' => $password,
            'from_email' => trim((string)$fromEmail) ?: 'no-reply@zyrtex.com.br',
            'from_name' => trim((string)$fromName) ?: 'Zyrtex SaaS',
            'active' => $active
        ];
    }

    /**
     * Criptografa senha do SMTP para armazenamento seguro
     */
    public static function encryptPassword(string $plain): string {
        if (empty($plain)) {
            return '';
        }
        $key = hash('sha256', Env::get('APP_KEY', 'zyrtex_saas_secret_salt_2026'), true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER));
        $encrypted = openssl_encrypt($plain, self::CIPHER, $key, 0, $iv);
        return 'enc:' . base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Descriptografa senha do SMTP
     */
    public static function decryptPassword(string $encrypted): string {
        if (!str_starts_with($encrypted, 'enc:')) {
            return $encrypted; // Texto plano legado ou não cifrado
        }
        try {
            $raw = base64_decode(substr($encrypted, 4));
            $parts = explode('::', $raw, 2);
            if (count($parts) !== 2) {
                return $encrypted;
            }
            $key = hash('sha256', Env::get('APP_KEY', 'zyrtex_saas_secret_salt_2026'), true);
            $decrypted = openssl_decrypt($parts[1], self::CIPHER, $key, 0, $parts[0]);
            return $decrypted !== false ? $decrypted : '';
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Envia e-mail baseado em um template do banco de dados
     */
    public static function sendTemplate(string $templateSlug, string $toEmail, string $toName, array $placeholders, ?int $tenantId = null): array {
        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'E-mail do destinatário inválido: ' . $toEmail];
        }

        $tpl = Database::fetchOne("SELECT * FROM email_templates WHERE slug = :s LIMIT 1", ['s' => $templateSlug]);
        if (!$tpl) {
            return ['success' => false, 'error' => "Template de e-mail '{$templateSlug}' não encontrado."];
        }

        // Adiciona placeholders padrão se não informados
        $placeholders['current_year'] = $placeholders['current_year'] ?? date('Y');
        $placeholders['date_time'] = $placeholders['date_time'] ?? date('d/m/Y \à\s H:i:s');
        $placeholders['app_url'] = $placeholders['app_url'] ?? (\Zyrtex\Core\Response::url('/'));

        // Se o body_html compilado estiver vazio, compila agora a partir de blocks_json
        $bodyHtml = $tpl['body_html'];
        if (empty(trim((string)$bodyHtml))) {
            $blocks = json_decode($tpl['blocks_json'] ?? '[]', true) ?: [];
            $bodyHtml = self::compileBlocksToHtml($blocks);
        }

        // Substituição de placeholders no assunto e no corpo
        $subject = $tpl['subject'];
        foreach ($placeholders as $k => $v) {
            $tag = '{{' . $k . '}}';
            $subject = str_replace($tag, (string)$v, $subject);
            $bodyHtml = str_replace($tag, (string)$v, $bodyHtml);
        }

        return self::send($toEmail, $toName, $subject, $bodyHtml);
    }

    /**
     * Envio de E-mail (SMTP real ou simulação segura)
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): array {
        $config = self::getConfig();

        // Se o SMTP não estiver ativo ou configurado, simula e grava log
        if (!$config['active'] || empty($config['host'])) {
            self::logSimulatedEmail($toEmail, $toName, $subject, $htmlBody);
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'E-mail simulado com sucesso (SMTP inativo nas configurações do SaaS).'
            ];
        }

        return self::sendViaSmtpSocket($config, $toEmail, $toName, $subject, $htmlBody);
    }

    /**
     * Envia e-mail direto fornecendo HTML customizado
     */
    public static function sendRaw(string $toEmail, string $toName, string $subject, string $htmlBody): array {
        return self::send($toEmail, $toName, $subject, $htmlBody);
    }

    /**
     * Gera código aleatório estilo Steam Guard de 5 caracteres alfanuméricos
     */
    public static function generateSteamCode(int $length = 5): string {
        // Exclui caracteres ambíguos como 0, O, 1, I
        $charset = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code = '';
        $max = strlen($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $charset[random_int(0, $max)];
        }
        return $code;
    }

    /**
     * Testa a conexão SMTP e envia mensagem de verificação
     */
    public static function testConnection(array|string $configOrRecipient, ?string $testRecipient = null): array {
        if (is_string($configOrRecipient)) {
            $testRecipient = $configOrRecipient;
            $config = self::getConfig();
        } else {
            $config = $configOrRecipient;
        }

        $testRecipient = trim((string)$testRecipient);
        if (empty($testRecipient)) {
            return ['success' => false, 'error' => 'Destinatário de teste não informado.'];
        }

        $log = [];
        $log[] = "Iniciando diagnóstico SMTP em " . date('d/m/Y H:i:s');
        $log[] = "Host: {$config['host']}:{$config['port']} (Criptografia: {$config['encryption']})";
        $log[] = "Usuário: {$config['username']}";

        // Se host estiver vazio, simula teste
        if (empty($config['host'])) {
            $log[] = "⚠️ Servidor SMTP não configurado. Simulação executada com sucesso.";
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Servidor SMTP não configurado. Teste simulado com sucesso em modo offline.',
                'log' => $log,
                'debug_log' => $log
            ];
        }

        $testSubject = "🧪 Teste de Conexão SMTP - Zyrtex SaaS";
        $testBody = self::generateTestEmailHtml($config);

        $result = self::sendViaSmtpSocket($config, $testRecipient, 'SuperAdmin Zyrtex', $testSubject, $testBody, $log);
        $result['log'] = $log;
        $result['debug_log'] = $log;
        return $result;
    }

    /**
     * Transmissão SMTP nativa via Sockets RFC 5321 (Zero dependências)
     */
    private static function sendViaSmtpSocket(array $config, string $toEmail, string $toName, string $subject, string $htmlBody, array &$debugLog = []): array {
        $host = $config['host'];
        $port = (int)$config['port'];
        $encryption = $config['encryption'];
        $timeout = 15;

        $targetHost = ($encryption === 'ssl') ? 'ssl://' . $host : 'tcp://' . $host;
        $debugLog[] = "Conectando ao socket {$targetHost}:{$port}...";

        $socket = @stream_socket_client(
            "{$targetHost}:{$port}",
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ])
        );

        if (!$socket) {
            $debugLog[] = "✗ Falha ao abrir socket: [{$errno}] {$errstr}";
            return ['success' => false, 'error' => "Falha ao conectar no servidor SMTP: {$errstr} ({$errno})"];
        }

        stream_set_timeout($socket, $timeout);

        // Função interna para ler resposta do servidor
        $readResponse = function() use ($socket, &$debugLog): string {
            $response = '';
            while ($line = fgets($socket, 515)) {
                $response .= $line;
                if (substr($line, 3, 1) === ' ') {
                    break;
                }
            }
            $debugLog[] = "< " . trim($response);
            return $response;
        };

        // Função interna para enviar comando
        $sendCommand = function(string $cmd, ?string $mask = null) use ($socket, &$debugLog): void {
            $display = $mask ?? $cmd;
            $debugLog[] = "> " . $display;
            fputs($socket, $cmd . "\r\n");
        };

        // 1. Banner inicial
        $res = $readResponse();
        if (!str_starts_with($res, '220')) {
            fclose($socket);
            return ['success' => false, 'error' => "Resposta inesperada do servidor: {$res}"];
        }

        // 2. EHLO
        $clientHost = gethostname() ?: 'localhost';
        $sendCommand("EHLO {$clientHost}");
        $res = $readResponse();

        // 3. STARTTLS (se configurado como TLS ou na porta 587)
        if ($encryption === 'tls') {
            $debugLog[] = "Negociando STARTTLS...";
            $sendCommand("STARTTLS");
            $res = $readResponse();

            if (!str_starts_with($res, '220')) {
                fclose($socket);
                return ['success' => false, 'error' => "Servidor rejeitou STARTTLS: {$res}"];
            }

            // Ativa criptografia no socket existente
            $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            }
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }

            $cryptoOk = @stream_socket_enable_crypto($socket, true, $cryptoMethod);
            if (!$cryptoOk) {
                fclose($socket);
                $debugLog[] = "✗ Falha no handshake TLS.";
                return ['success' => false, 'error' => "Falha ao negociar TLS com o servidor SMTP."];
            }

            $debugLog[] = "✓ TLS ativado com sucesso. Reenviando EHLO...";
            $sendCommand("EHLO {$clientHost}");
            $res = $readResponse();
        }

        // 4. Autenticação (AUTH LOGIN)
        if (!empty($config['username']) && !empty($config['password'])) {
            $sendCommand("AUTH LOGIN");
            $res = $readResponse();

            if (!str_starts_with($res, '334')) {
                fclose($socket);
                return ['success' => false, 'error' => "Servidor não suporta AUTH LOGIN: {$res}"];
            }

            $sendCommand(base64_encode($config['username']), "[Username Base64]");
            $res = $readResponse();

            if (!str_starts_with($res, '334')) {
                fclose($socket);
                return ['success' => false, 'error' => "Usuário SMTP rejeitado: {$res}"];
            }

            $sendCommand(base64_encode($config['password']), "[Password Hidden]");
            $res = $readResponse();

            if (!str_starts_with($res, '235')) {
                fclose($socket);
                return ['success' => false, 'error' => "Falha na autenticação SMTP (Senha ou Usuário incorretos): {$res}"];
            }

            $debugLog[] = "✓ Autenticado com sucesso no servidor SMTP!";
        }

        // 5. MAIL FROM
        $fromEmail = $config['from_email'];
        $sendCommand("MAIL FROM:<{$fromEmail}>");
        $res = $readResponse();
        if (!str_starts_with($res, '250')) {
            fclose($socket);
            return ['success' => false, 'error' => "Remetente rejeitado ({$fromEmail}): {$res}"];
        }

        // 6. RCPT TO
        $sendCommand("RCPT TO:<{$toEmail}>");
        $res = $readResponse();
        if (!str_starts_with($res, '250') && !str_starts_with($res, '251')) {
            fclose($socket);
            return ['success' => false, 'error' => "Destinatário rejeitado ({$toEmail}): {$res}"];
        }

        // 7. DATA
        $sendCommand("DATA");
        $res = $readResponse();
        if (!str_starts_with($res, '354')) {
            fclose($socket);
            return ['success' => false, 'error' => "Comando DATA rejeitado: {$res}"];
        }

        // 8. Cabeçalhos e Corpo MIME
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromNameHeader = '=?UTF-8?B?' . base64_encode($config['from_name']) . '?=';
        $toNameHeader = !empty($toName) ? '=?UTF-8?B?' . base64_encode($toName) . '?=' : '';

        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: {$fromNameHeader} <{$fromEmail}>";
        $headers[] = !empty($toNameHeader) ? "To: {$toNameHeader} <{$toEmail}>" : "To: <{$toEmail}>";
        $headers[] = "Subject: {$encodedSubject}";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Content-Transfer-Encoding: base64";
        $headers[] = "X-Mailer: Zyrtex-SaaS-Mailer/1.2";

        $mimeData = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";

        $debugLog[] = "Enviando payload do e-mail (" . strlen($htmlBody) . " bytes)...";
        fputs($socket, $mimeData . "\r\n");
        $res = $readResponse();

        if (!str_starts_with($res, '250')) {
            fclose($socket);
            return ['success' => false, 'error' => "Erro ao finalizar transmissão de dados: {$res}"];
        }

        // 9. QUIT
        $sendCommand("QUIT");
        $readResponse();
        fclose($socket);

        $debugLog[] = "✓ E-mail entregue com sucesso à fila do servidor SMTP!";
        return [
            'success' => true,
            'message' => 'E-mail enviado com sucesso via SMTP!'
        ];
    }

    /**
     * Compilador de Blocos para HTML Responsivo e Elegante
     */
    public static function compileBlocksToHtml(array $blocks): string {
        $renderedBlocks = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? 'text';

            switch ($type) {
                case 'header':
                    $title = htmlspecialchars($block['title'] ?? 'Zyrtex SaaS', ENT_QUOTES, 'UTF-8');
                    $sub = htmlspecialchars($block['subtitle'] ?? '', ENT_QUOTES, 'UTF-8');
                    $bg = htmlspecialchars($block['bgColor'] ?? '#4f46e5', ENT_QUOTES, 'UTF-8');
                    $txt = htmlspecialchars($block['textColor'] ?? '#ffffff', ENT_QUOTES, 'UTF-8');

                    $renderedBlocks[] = "
                    <!-- HEADER BLOCK -->
                    <tr>
                        <td align=\"center\" style=\"background-color: {$bg}; padding: 32px 24px; border-radius: 16px 16px 0 0;\">
                            <h1 style=\"margin: 0; color: {$txt}; font-size: 24px; font-weight: 800; letter-spacing: -0.5px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;\">
                                {$title}
                            </h1>
                            " . (!empty($sub) ? "<p style=\"margin: 6px 0 0 0; color: {$txt}; opacity: 0.85; font-size: 13px; font-weight: 500;\">{$sub}</p>" : "") . "
                        </td>
                    </tr>";
                    break;

                case 'text':
                    $heading = $block['heading'] ?? '';
                    $content = $block['content'] ?? '';
                    $align = $block['align'] ?? 'left';

                    $renderedBlocks[] = "
                    <!-- TEXT BLOCK -->
                    <tr>
                        <td align=\"{$align}\" style=\"padding: 24px 28px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;\">
                            " . (!empty($heading) ? "<h2 style=\"margin: 0 0 12px 0; color: #0f172a; font-size: 18px; font-weight: 700;\">{$heading}</h2>" : "") . "
                            <div style=\"color: #334155; font-size: 14px; line-height: 1.6;\">
                                {$content}
                            </div>
                        </td>
                    </tr>";
                    break;

                case 'token_box':
                    // Estilo Steam Guard em destaque!
                    $label = htmlspecialchars($block['label'] ?? 'SEU CÓDIGO DE SEGURANÇA:', ENT_QUOTES, 'UTF-8');
                    $token = htmlspecialchars($block['token'] ?? '{{token_2fa}}', ENT_QUOTES, 'UTF-8');
                    $hint = htmlspecialchars($block['hint'] ?? 'Válido por 10 minutos.', ENT_QUOTES, 'UTF-8');
                    $bg = htmlspecialchars($block['bgColor'] ?? '#0f172a', ENT_QUOTES, 'UTF-8');
                    $color = htmlspecialchars($block['textColor'] ?? '#38bdf8', ENT_QUOTES, 'UTF-8');
                    $border = htmlspecialchars($block['borderColor'] ?? '#0284c7', ENT_QUOTES, 'UTF-8');

                    $renderedBlocks[] = "
                    <!-- TOKEN BOX (STEAM GUARD STYLE) -->
                    <tr>
                        <td align=\"center\" style=\"padding: 12px 28px 24px 28px;\">
                            <div style=\"background-color: {$bg}; border: 2px solid {$border}; border-radius: 14px; padding: 22px 18px; max-width: 440px; margin: 0 auto; text-align: center; box-shadow: 0 4px 14px rgba(0,0,0,0.2);\">
                                <span style=\"display: block; font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;\">
                                    {$label}
                                </span>
                                <div style=\"font-size: 32px; font-weight: 900; letter-spacing: 6px; color: {$color}; font-family: 'Courier New', Courier, monospace; background: rgba(255,255,255,0.06); padding: 10px 16px; border-radius: 8px; display: inline-block; margin: 4px 0;\">
                                    {$token}
                                </div>
                                <p style=\"margin: 8px 0 0 0; font-size: 11px; color: #64748b; font-family: -apple-system, BlinkMacSystemFont, sans-serif;\">
                                    {$hint}
                                </p>
                            </div>
                        </td>
                    </tr>";
                    break;

                case 'button':
                    $text = htmlspecialchars($block['text'] ?? 'Clique Aqui', ENT_QUOTES, 'UTF-8');
                    $url = htmlspecialchars($block['url'] ?? '#', ENT_QUOTES, 'UTF-8');
                    $bg = htmlspecialchars($block['bgColor'] ?? '#4f46e5', ENT_QUOTES, 'UTF-8');
                    $txt = htmlspecialchars($block['textColor'] ?? '#ffffff', ENT_QUOTES, 'UTF-8');
                    $align = $block['align'] ?? 'center';

                    $renderedBlocks[] = "
                    <!-- BUTTON BLOCK -->
                    <tr>
                        <td align=\"{$align}\" style=\"padding: 16px 28px 24px 28px;\">
                            <a href=\"{$url}\" target=\"_blank\" style=\"display: inline-block; background-color: {$bg}; color: {$txt}; text-decoration: none; font-weight: 700; font-size: 14px; padding: 14px 28px; border-radius: 12px; font-family: -apple-system, BlinkMacSystemFont, sans-serif; box-shadow: 0 4px 10px rgba(0,0,0,0.12);\">
                                {$text}
                            </a>
                        </td>
                    </tr>";
                    break;

                case 'details_box':
                    $title = htmlspecialchars($block['title'] ?? 'Informações:', ENT_QUOTES, 'UTF-8');
                    $items = (array)($block['items'] ?? []);

                    $itemsHtml = '';
                    foreach ($items as $item) {
                        $itemsHtml .= "<li style=\"margin-bottom: 6px;\">" . htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8') . "</li>";
                    }

                    $renderedBlocks[] = "
                    <!-- DETAILS BOX BLOCK -->
                    <tr>
                        <td style=\"padding: 12px 28px;\">
                            <div style=\"background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px;\">
                                <h4 style=\"margin: 0 0 8px 0; color: #1e293b; font-size: 13px; font-weight: 700;\">{$title}</h4>
                                <ul style=\"margin: 0; padding-left: 20px; color: #475569; font-size: 13px; line-height: 1.5;\">
                                    {$itemsHtml}
                                </ul>
                            </div>
                        </td>
                    </tr>";
                    break;

                case 'divider':
                    $renderedBlocks[] = "
                    <!-- DIVIDER BLOCK -->
                    <tr>
                        <td style=\"padding: 12px 28px;\">
                            <hr style=\"border: none; border-top: 1px solid #e2e8f0; margin: 0;\">
                        </td>
                    </tr>";
                    break;

                case 'footer':
                    $text = $block['text'] ?? '';
                    $renderedBlocks[] = "
                    <!-- FOOTER BLOCK -->
                    <tr>
                        <td align=\"center\" style=\"background-color: #f1f5f9; padding: 24px 28px; border-radius: 0 0 16px 16px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;\">
                            <p style=\"margin: 0; color: #64748b; font-size: 11px; line-height: 1.6;\">
                                {$text}
                            </p>
                        </td>
                    </tr>";
                    break;
            }
        }

        $innerHtml = implode("\n", $renderedBlocks);

        return "
<!DOCTYPE html>
<html lang=\"pt-BR\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Notificação Zyrtex</title>
</head>
<body style=\"margin: 0; padding: 24px 12px; background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;\">
    <table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" style=\"max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.04);\">
        {$innerHtml}
    </table>
</body>
</html>";
    }

    /**
     * Gera e-mail de diagnóstico do teste de conexão SMTP
     */
    private static function generateTestEmailHtml(array $config): string {
        $now = date('d/m/Y \à\s H:i:s');
        $blocks = [
            [
                'type' => 'header',
                'title' => '⚡ Diagnóstico SMTP Concluído',
                'subtitle' => 'Zyrtex SaaS - Mensagem de Teste em Tempo Real',
                'bgColor' => '#059669',
                'textColor' => '#ffffff'
            ],
            [
                'type' => 'text',
                'heading' => 'Conexão Estabelecida com Sucesso!',
                'content' => "Se você está lendo este e-mail, significa que o servidor SMTP configurado no seu painel SuperAdmin do SaaS está 100% operacional e pronto para disparar ativações de clientes e tokens de segurança 2FA.<br><br><strong>Detalhes Técnicos do Servidor:</strong>",
                'align' => 'left'
            ],
            [
                'type' => 'details_box',
                'title' => 'Parâmetros Verificados:',
                'items' => [
                    "Host: {$config['host']}",
                    "Porta: {$config['port']} (Criptografia: {$config['encryption']})",
                    "Usuário Autenticado: {$config['username']}",
                    "Remetente Oficial: {$config['from_name']} <{$config['from_email']}>",
                    "Momento do Teste: {$now}"
                ]
            ],
            [
                'type' => 'divider'
            ],
            [
                'type' => 'footer',
                'text' => "Mensagem de teste gerada automaticamente pelo painel do SuperAdmin.<br>&copy; " . date('Y') . " Zyrtex SaaS Management."
            ]
        ];

        return self::compileBlocksToHtml($blocks);
    }

    /**
     * Registra log de e-mail simulado quando SMTP está inativo
     */
    private static function logSimulatedEmail(string $toEmail, string $toName, string $subject, string $htmlBody): void {
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/mail_simulated.log';
        $entry = "=================================================================\n";
        $entry .= "DATA: " . date('d/m/Y H:i:s') . "\n";
        $entry .= "PARA: {$toName} <{$toEmail}>\n";
        $entry .= "ASSUNTO: {$subject}\n";
        $entry .= "CORPO (HTML):\n" . $htmlBody . "\n\n";

        @file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
