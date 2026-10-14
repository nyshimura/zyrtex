<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>💬</span>
                <span>Central de Ajuda & Suporte Técnico</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Tire dúvidas sobre integrações, impressoras térmicas, chaves Pix ou solicite assistência diretamente aos especialistas da Zyrtex.
            </p>
        </div>

        <button type="button" onclick="document.getElementById('form_novo_chamado').scrollIntoView({behavior: 'smooth'})"
                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition flex items-center gap-1.5 self-start sm:self-auto">
            <span>+ Abrir Novo Chamado</span>
        </button>
    </div>

    <!-- Grade de Conteúdo: Histórico e Formulário -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Coluna Esquerda: Histórico de Chamados (8 colunas) -->
        <div class="lg:col-span-7 space-y-4">
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>📜</span>
                        <span>Seus Chamados Anteriores</span>
                    </h2>
                    <span class="text-xs text-slate-500"><?= count($tickets) ?> chamado(s)</span>
                </div>

                <?php if (empty($tickets)): ?>
                    <div class="p-8 text-center space-y-2">
                        <span class="text-3xl">💬</span>
                        <p class="text-xs text-slate-400">Você ainda não abriu nenhum chamado de suporte.</p>
                        <p class="text-[11px] text-slate-500">Precisa de ajuda com alguma configuração? Use o formulário ao lado!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($tickets as $tk): 
                            $msgs = json_decode($tk['messages_json'] ?? '[]', true) ?: [];
                            $isOpen = $tk['status'] === 'open';
                            $isAnswered = $tk['status'] === 'answered';
                        ?>
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                                
                                <div class="flex items-center justify-between border-b border-slate-800/80 pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-white text-xs">#<?= (int)$tk['id'] ?> &bull; <?= htmlspecialchars($tk['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>

                                    <?php if ($isAnswered): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 animate-pulse">
                                            ✓ Respondido pela Zyrtex
                                        </span>
                                    <?php elseif ($isOpen): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            ⏳ Em Análise
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400">
                                            Encerrado
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Mensagens -->
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    <?php foreach ($msgs as $m): 
                                        $isSuper = ($m['sender'] ?? '') === 'superadmin';
                                    ?>
                                        <div class="p-2.5 rounded-xl text-xs space-y-1 <?= $isSuper ? 'bg-indigo-950/60 border border-indigo-500/30 text-indigo-100 ml-4' : 'bg-slate-900 border border-slate-800 mr-4 text-slate-300' ?>">
                                            <div class="flex items-center justify-between font-bold text-[10px]">
                                                <span class="<?= $isSuper ? 'text-indigo-300' : 'text-slate-400' ?>">
                                                    <?= $isSuper ? '🛡️ Suporte Oficial Zyrtex' : '👤 Você' ?>
                                                </span>
                                                <span class="opacity-75 font-mono"><?= htmlspecialchars($m['created_at'] ?? '') ?></span>
                                            </div>
                                            <p class="leading-relaxed"><?= nl2br(htmlspecialchars($m['message'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($tk['status'] !== 'closed'): ?>
                                    <!-- Réplica -->
                                    <form action="<?= \Zyrtex\Core\Response::url('/admin/suporte/responder') ?>" method="POST" class="flex gap-2 pt-1 border-t border-slate-800/80">
                                        <?= $csrfField ?>
                                        <input type="hidden" name="ticket_id" value="<?= (int)$tk['id'] ?>">
                                        <input type="text" name="message" required
                                               placeholder="Escreva sua resposta ou dúvida adicional..."
                                               class="flex-1 px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <button type="submit" 
                                                class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition">
                                            Enviar
                                        </button>
                                    </form>
                                <?php endif; ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Coluna Direita: Formulário de Novo Chamado (5 colunas) -->
        <div class="lg:col-span-5 space-y-4" id="form_novo_chamado">
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4 shadow-sm sticky top-20">
                <div class="border-b border-slate-800 pb-3">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>✏️</span>
                        <span>Abrir Novo Chamado</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Descreva seu problema com detalhes para que nossa equipe te atenda com rapidez.
                    </p>
                </div>

                <form action="<?= \Zyrtex\Core\Response::url('/admin/suporte/novo') ?>" method="POST" class="space-y-4">
                    <?= $csrfField ?>
                    
                    <div>
                        <label for="subject" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">
                            Assunto Principal:
                        </label>
                        <input type="text" id="subject" name="subject" required
                               placeholder="Ex: Dúvida sobre impressão em 80mm ou chave Pix"
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label for="message" class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1">
                            Descrição Detalhada:
                        </label>
                        <textarea id="message" name="message" rows="5" required
                                  placeholder="Descreva o que está acontecendo, mensagens de erro que apareceram ou sua dúvida técnica..."
                                  class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 leading-relaxed"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
                            <span>🚀 Enviar Chamado para a Equipe</span>
                        </button>
                    </div>

                </form>

                <div class="p-3.5 rounded-xl bg-slate-950/80 border border-slate-800/80 text-[11px] text-slate-400 space-y-1">
                    <span class="font-bold text-slate-300 block">⚡ Tempo Médio de Resposta:</span>
                    <p>Nossa equipe técnica atende chamados de segunda a sexta, das 08h às 19h.</p>
                </div>

            </div>

        </div>

    </div>

</div>
