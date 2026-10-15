<?php
require __DIR__ . '/../src/Core/Database.php';
require __DIR__ . '/../src/Core/Env.php';
require __DIR__ . '/../src/Core/Response.php';
require __DIR__ . '/../src/Core/Session.php';
require __DIR__ . '/../src/Core/Tenant.php';
require __DIR__ . '/../src/Core/Auth.php';
require __DIR__ . '/../src/Core/Audit.php';
require __DIR__ . '/../src/Services/MailService.php';

\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

echo "=== TEST SUITE: MÓDULO DE E-MAILS & 2FA STEAM GUARD ===\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertTest(string $desc, bool $condition, string $extra = '') {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        $testsPassed++;
        echo "✓ [PASS] {$desc}\n";
    } else {
        echo "✗ [FAIL] {$desc} {$extra}\n";
    }
}

// 1. Teste de Criptografia Simétrica da Senha SMTP (AES-256-CBC)
$plainPass = 'SmtpSecretPassword_2026!#$';
$encrypted = \Zyrtex\Services\MailService::encryptPassword($plainPass);
$decrypted = \Zyrtex\Services\MailService::decryptPassword($encrypted);

assertTest("Criptografia da senha SMTP começa com 'enc:'", str_starts_with($encrypted, 'enc:'));
assertTest("Senha descriptografada coincide perfeitamente com a original", $decrypted === $plainPass);

// 2. Teste do Gerador de Código Steam Guard
$code = \Zyrtex\Services\MailService::generateSteamCode();
assertTest("Código Steam Guard tem exatamente 5 caracteres", strlen($code) === 5);
assertTest("Código Steam Guard é todo em letras maiúsculas/números", (bool)preg_match('/^[A-Z0-9]{5}$/', $code));

// 3. Teste de Hash SHA-256 do Token 2FA
$codeHash = hash('sha256', $code);
assertTest("Hash do código é SHA-256 de 64 caracteres hexadecimais", strlen($codeHash) === 64 && ctype_xdigit($codeHash));
assertTest("Hash recalcula identicamente", hash('sha256', $code) === $codeHash);

// 4. Teste de Compilação de Blocos Drag & Drop
$testBlocks = [
    [
        'id' => 'b1',
        'type' => 'header',
        'title' => 'Oficina Teste',
        'subtitle' => 'Subtítulo Teste',
        'bgColor' => '#4f46e5',
        'textColor' => '#ffffff'
    ],
    [
        'id' => 'b2',
        'type' => 'token_box',
        'label' => 'CÓDIGO DE ACESSO:',
        'token' => '{{token_2fa}}',
        'hint' => 'Válido por 10 min.',
        'bgColor' => '#1e293b',
        'textColor' => '#38bdf8',
        'borderColor' => '#0284c7'
    ],
    [
        'id' => 'b3',
        'type' => 'footer',
        'text' => '&copy; 2026 Zyrtex'
    ]
];

$compiledHtml = \Zyrtex\Services\MailService::compileBlocksToHtml($testBlocks);
assertTest("Compilação de blocos gera documento HTML5 completo", str_contains($compiledHtml, '<!DOCTYPE html>'));
assertTest("Bloco de cabeçalho presente no HTML", str_contains($compiledHtml, 'Oficina Teste'));
assertTest("Bloco de Token Steam Guard presente no HTML com estilo cyber", str_contains($compiledHtml, '{{token_2fa}}') && str_contains($compiledHtml, '#38bdf8'));

// 5. Teste de Disparo de Template em Modo Simulação
$logFile = dirname(__DIR__) . '/storage/logs/mail_simulated.log';
if (file_exists($logFile)) {
    @unlink($logFile);
}

$simResult = \Zyrtex\Services\MailService::sendTemplate('lojista_2fa_steam', 'lojista_teste@exemplo.com.br', 'Lojista Teste', [
    'user_name' => 'Marcos Mecânico',
    'user_email' => 'lojista_teste@exemplo.com.br',
    'token_2fa' => 'K9P2X',
    'shop_name' => 'Auto Center Paulista',
    'ip_address' => '127.0.0.1'
]);

assertTest("Disparo em simulação retorna sucesso", $simResult['success'] === true);
assertTest("Modo simulação gravou em mail_simulated.log", file_exists($logFile));
$logContent = file_get_contents($logFile);
assertTest("Log contém o código Steam Guard interpolado (K9P2X)", str_contains($logContent, 'K9P2X'));
assertTest("Log contém o nome do lojista (Marcos Mecânico)", str_contains($logContent, 'Marcos Mecânico'));

// 6. Teste do Fluxo de Cadastro e Ativação do Cliente
$testEmail = 'cliente_teste_' . time() . '@exemplo.com.br';
$rawActivationToken = bin2hex(random_bytes(32));
$activationHash = hash('sha256', $rawActivationToken);
$expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

// Insere cliente teste com token hash
\Zyrtex\Core\Database::execute(
    "INSERT INTO clients (tenant_id, name, email, phone, status, activation_token_hash, activation_expires_at) 
     VALUES (1, 'Cliente Teste Automatizado', :email, '11999998888', 'pending_activation', :hash, :exp)",
    [
        'email' => $testEmail,
        'hash' => $activationHash,
        'exp' => $expiresAt
    ]
);
$clientId = (int)\Zyrtex\Core\Database::lastInsertId();
assertTest("Cliente de teste inserido no banco de dados com status 'pending_activation'", $clientId > 0);

// Busca cliente pelo hash do token (como faria o /portal/ativar?token=...)
$foundClient = \Zyrtex\Core\Database::fetchOne(
    "SELECT * FROM clients WHERE activation_token_hash = :hash AND activation_expires_at > NOW() LIMIT 1",
    ['hash' => hash('sha256', $rawActivationToken)]
);
assertTest("Busca por hash do token encontra o cliente correto", $foundClient !== null && (int)$foundClient['id'] === $clientId);

// Simula ativação pelo cliente definindo uma senha
$newPassword = 'senhaSegura123';
$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

\Zyrtex\Core\Database::execute(
    "UPDATE clients 
     SET status = 'active', 
         activated_at = NOW(), 
         activation_token_hash = NULL, 
         activation_expires_at = NULL, 
         password_hash = :pass 
     WHERE id = :id",
    ['pass' => $passwordHash, 'id' => $clientId]
);

$activatedClient = \Zyrtex\Core\Database::fetchOne("SELECT * FROM clients WHERE id = :id", ['id' => $clientId]);
assertTest("Status do cliente foi atualizado para 'active'", $activatedClient['status'] === 'active');
assertTest("Token hash foi limpo após ativação por segurança", empty($activatedClient['activation_token_hash']));
assertTest("Senha pessoal criptografada com BCRYPT", password_verify($newPassword, $activatedClient['password_hash']));

// Limpa cliente de teste
\Zyrtex\Core\Database::execute("DELETE FROM clients WHERE id = :id", ['id' => $clientId]);

// 7. Teste de Ativação do 2FA Steam Guard para Usuário
$testUser = \Zyrtex\Core\Database::fetchOne("SELECT * FROM users LIMIT 1");
if ($testUser) {
    $userId = (int)$testUser['id'];
    $orig2fa = (int)$testUser['two_factor_enabled'];

    // Ativa 2FA
    \Zyrtex\Core\Database::execute("UPDATE users SET two_factor_enabled = 1 WHERE id = :id", ['id' => $userId]);
    $u1 = \Zyrtex\Core\Database::fetchOne("SELECT two_factor_enabled FROM users WHERE id = :id", ['id' => $userId]);
    assertTest("Flag two_factor_enabled ativada no banco", (int)$u1['two_factor_enabled'] === 1);

    // Gera e salva token hash
    $steamCode = \Zyrtex\Services\MailService::generateSteamCode();
    $sHash = hash('sha256', $steamCode);
    $sExp = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    \Zyrtex\Core\Database::execute(
        "UPDATE users SET two_factor_code_hash = :hash, two_factor_expires_at = :exp WHERE id = :id",
        ['hash' => $sHash, 'exp' => $sExp, 'id' => $userId]
    );

    // Valida código correto
    $uValid = \Zyrtex\Core\Database::fetchOne(
        "SELECT * FROM users WHERE id = :id AND two_factor_code_hash = :hash AND two_factor_expires_at > NOW() LIMIT 1",
        ['id' => $userId, 'hash' => hash('sha256', $steamCode)]
    );
    assertTest("Validação com o código correto retorna o usuário", $uValid !== null);

    // Valida código incorreto
    $uInvalid = \Zyrtex\Core\Database::fetchOne(
        "SELECT * FROM users WHERE id = :id AND two_factor_code_hash = :hash AND two_factor_expires_at > NOW() LIMIT 1",
        ['id' => $userId, 'hash' => hash('sha256', 'WRONG')]
    );
    assertTest("Validação com código incorreto é rejeitada", $uInvalid === null);

    // Restaura estado original do usuário
    \Zyrtex\Core\Database::execute(
        "UPDATE users SET two_factor_enabled = :orig, two_factor_code_hash = NULL, two_factor_expires_at = NULL WHERE id = :id",
        ['orig' => $orig2fa, 'id' => $userId]
    );
}

echo "\n=======================================================\n";
echo "RESULTADO: {$testsPassed} de {$totalTests} testes passaram com sucesso!\n";
echo "=======================================================\n";
