<?php

declare(strict_types=1);

namespace Zyrtex\Controllers\Admin;

use Zyrtex\Core\Request;
use Zyrtex\Core\Response;
use Zyrtex\Core\Session;
use Zyrtex\Core\Database;
use Zyrtex\Core\Auth;
use Zyrtex\Core\View;
use Zyrtex\Core\Audit;

/**
 * Controlador de Chamados de Suporte do Lojista (/admin/suporte)
 * Permite ao dono da oficina abrir chamados diretamente com a equipe do SaaS Zyrtex
 */
class SupportController {

    /**
     * Tela Principal de Suporte (/admin/suporte)
     */
    public function index(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $tenant = Auth::currentTenant();
        $user = Auth::user();

        $tickets = Database::fetchAll(
            "SELECT * FROM tickets WHERE tenant_id = :tenant_id ORDER BY updated_at DESC",
            ['tenant_id' => $tenantId]
        );

        View::render('admin/support/index', [
            'tenant' => $tenant,
            'user' => $user,
            'tickets' => $tickets,
            'pageTitle' => 'Central de Ajuda & Suporte &bull; ' . ($tenant['business_name'] ?? 'Zyrtex')
        ], 'admin');
    }

    /**
     * Abertura de Novo Chamado (POST /admin/suporte/novo)
     */
    public function store(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();

        $subject = trim((string)$request->post('subject', ''));
        $message = trim((string)$request->post('message', ''));

        if (empty($subject) || empty($message)) {
            Session::setFlash('error', 'Por favor, informe o assunto e descreva sua dúvida ou solicitação.');
            Response::redirect('/admin/suporte');
            return;
        }

        $initialMessages = [
            [
                'sender' => 'client',
                'sender_name' => $user['name'] ?? 'Lojista',
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        Database::execute(
            "INSERT INTO tickets (tenant_id, user_id, subject, status, messages_json) 
             VALUES (:tenant_id, :user_id, :subject, 'open', :msgs)",
            [
                'tenant_id' => $tenantId,
                'user_id' => (int)$user['id'],
                'subject' => $subject,
                'msgs' => json_encode($initialMessages, JSON_UNESCAPED_UNICODE)
            ]
        );

        $ticketId = (int)Database::lastInsertId();

        Audit::log($tenantId, (int)$user['id'], 'support_ticket_created', [
            'ticket_id' => $ticketId,
            'subject' => $subject
        ]);

        Session::setFlash('success', 'Chamado aberto com sucesso! Nossa equipe técnica responderá em breve.');
        Response::redirect('/admin/suporte');
    }

    /**
     * Envia réplica em chamado aberto (POST /admin/suporte/responder)
     */
    public function reply(Request $request): void {
        Auth::requireAuth();
        $tenantId = Auth::tenantId();
        $user = Auth::user();

        $ticketId = (int)$request->post('ticket_id', 0);
        $messageText = trim((string)$request->post('message', ''));

        if ($ticketId <= 0 || empty($messageText)) {
            Session::setFlash('error', 'Mensagem não pode ficar em branco.');
            Response::redirect('/admin/suporte');
            return;
        }

        $ticket = Database::fetchOne(
            "SELECT * FROM tickets WHERE id = :id AND tenant_id = :tenant_id LIMIT 1",
            ['id' => $ticketId, 'tenant_id' => $tenantId]
        );

        if (!$ticket) {
            Session::setFlash('error', 'Chamado não encontrado.');
            Response::redirect('/admin/suporte');
            return;
        }

        $messages = json_decode($ticket['messages_json'] ?? '[]', true) ?: [];
        $messages[] = [
            'sender' => 'client',
            'sender_name' => $user['name'] ?? 'Lojista',
            'message' => $messageText,
            'created_at' => date('Y-m-d H:i:s')
        ];

        Database::execute(
            "UPDATE tickets 
             SET messages_json = :msgs, status = 'open', updated_at = NOW() 
             WHERE id = :id AND tenant_id = :tenant_id",
            [
                'msgs' => json_encode($messages, JSON_UNESCAPED_UNICODE),
                'id' => $ticketId,
                'tenant_id' => $tenantId
            ]
        );

        Session::setFlash('success', 'Mensagem enviada para o suporte!');
        Response::redirect('/admin/suporte');
    }
}
