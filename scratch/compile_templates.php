<?php
require __DIR__ . '/../src/Core/Database.php';
require __DIR__ . '/../src/Core/Env.php';
require __DIR__ . '/../src/Services/MailService.php';

\Zyrtex\Core\Env::load(__DIR__ . '/../.env');

$templates = \Zyrtex\Core\Database::fetchAll('SELECT id, slug, blocks_json FROM email_templates');
foreach ($templates as $t) {
    $blocks = json_decode($t['blocks_json'] ?? '[]', true) ?: [];
    $html = \Zyrtex\Services\MailService::compileBlocksToHtml($blocks);
    \Zyrtex\Core\Database::execute(
        'UPDATE email_templates SET body_html = :h WHERE id = :id',
        ['h' => $html, 'id' => $t['id']]
    );
    echo "Template '{$t['slug']}' compiled (" . strlen($html) . " bytes)\n";
}
echo "All templates compiled!\n";
