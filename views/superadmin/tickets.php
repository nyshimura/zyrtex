<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>💬</span>
                <span>Central de Suporte & Chamados Técnicos</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Atendimento direto aos lojistas da rede Zyrtex com histórico de mensagens encadeadas.
            </p>
        </div>

        <!-- Filtros Rápidos -->
        <div class="flex items-center gap-2">
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tickets') ?>" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition <?= empty($statusFilter) ? 'bg-indigo-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                Todos
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tickets?status=open') ?>" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'open' ? 'bg-amber-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                Abertos
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tickets?status=answered') ?>" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'answered' ? 'bg-blue-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                Respondidos
            </a>
            <a href="<?= \Zyrtex\Core\Response::url('/superadmin/tickets?status=closed') ?>" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition <?= $statusFilter === 'closed' ? 'bg-emerald-600 text-white' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white' ?>">
                Fechados
            </a>
        </div>
    </div>

    <!-- Lista de Chamados -->
    <?php if (empty($tickets)): ?>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-12 text-center space-y-3">
            <span class="text-4xl">🎉</span>
            <h3 class="text-base font-bold text-white">Nenhum chamado pendente</h3>
            <p class="text-xs text-slate-400">Todas as dúvidas e solicitações dos lojistas foram atendidas com sucesso!</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($tickets as $tk): 
                $msgs = json_decode($tk['messages_json'] ?? '[]', true) ?: [];
                $lastMsg = end($msgs) ?: [];
                $isOpen = $tk['status'] === 'open';
                $isAnswered = $tk['status'] === 'answered';
            ?>
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-6 space-y-4 shadow-sm transition hover:border-slate-700">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-800 pb-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="font-extrabold text-white text-sm sm:text-base">
                                    #<?= (int)$tk['id'] ?> &bull; <?= htmlspecialchars($tk['subject'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <?php if ($isOpen): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        ⏳ Aberto / Aguardando Resposta
                                    </span>
                                <?php elseif ($isAnswered): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                        💬 Respondido
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        ✓ Fechado
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-slate-400">
                                Oficina: <strong class="text-white"><?= htmlspecialchars($tk['business_name'], ENT_QUOTES, 'UTF-8') ?></strong> 
                                (<code class="text-indigo-400 font-mono text-[11px]">/<?= htmlspecialchars($tk['tenant_slug'], ENT_QUOTES, 'UTF-8') ?></code>) &bull; 
                                Solicitante: <strong><?= htmlspecialchars($tk['user_name'], ENT_QUOTES, 'UTF-8') ?></strong> (<?= htmlspecialchars($tk['user_email'], ENT_QUOTES, 'UTF-8') ?>)
                            </p>
                        </div>

                        <span class="text-xs text-slate-500 whitespace-nowrap self-start sm:self-auto font-mono">
                            Última interação: <?= date('d/m/Y H:i', strtotime($tk['updated_at'])) ?>
                        </span>
                    </div>

                    <!-- Linha do Tempo das Mensagens -->
                    <div class="space-y-2.5 max-h-60 overflow-y-auto p-3 rounded-xl bg-slate-950 border border-slate-800">
                        <?php foreach ($msgs as $m): 
                            $isSuper = ($m['sender'] ?? '') === 'superadmin';
                        ?>
                            <div class="p-3 rounded-xl text-xs space-y-1 <?= $isSuper ? 'bg-indigo-950/40 border border-indigo-500/20 ml-6 text-indigo-100' : 'bg-slate-900 border border-slate-800 mr-6 text-slate-300' ?>">
                                <div class="flex items-center justify-between font-bold text-[11px]">
                                    <span class="<?= $isSuper ? 'text-indigo-300' : 'text-slate-400' ?>">
                                        <?= $isSuper ? '🛡️ Suporte Oficial Zyrtex' : '👤 ' . htmlspecialchars($m['sender_name'] ?? 'Lojista', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <span class="text-[10px] opacity-75 font-mono"><?= htmlspecialchars($m['created_at'] ?? '') ?></span>
                                </div>
                                <p class="leading-relaxed"><?= nl2br(htmlspecialchars($m['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Formulário de Resposta Rápida do SuperAdmin -->
                    <form action="<?= \Zyrtex\Core\Response::url('/superadmin/tickets/responder') ?>" method="POST" class="pt-2 flex flex-col sm:flex-row gap-2.5">
                        <?= $csrfField ?>
                        <input type="hidden" name="ticket_id" value="<?= (int)$tk['id'] ?>">

                        <input type="text" name="reply_text" required
                               placeholder="Digite sua resposta oficial para este lojista..."
                               class="flex-1 px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                        <div class="flex items-center gap-2">
                            <label class="text-[11px] text-slate-400 flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                                <input type="checkbox" name="close_ticket" value="1" class="rounded bg-slate-900 border-slate-700 text-indigo-600">
                                <span>Encerrar Chamado</span>
                            </label>

                            <button type="submit" 
                                    class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-md shrink-0 flex items-center gap-1.5">
                                <span>✉️ Responder</span>
                            </button>
                        </div>
                    </form>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
