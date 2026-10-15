<div class="space-y-6">

    <!-- Topo da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white flex items-center gap-2">
                <span>🎁</span>
                <span>Indique & Ganhe Zyrtex Pro</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Convide colegas de bancada e donos de assistência técnica. A cada oficina que ativar o sistema, você ganha <strong><?= (int)$rewardDaysPerReferral ?> dias de Zyrtex Pro grátis</strong> sem limites de acumulação!
            </p>
        </div>

        <a href="<?= htmlspecialchars($whatsappShareUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition shrink-0">
            <span>💬</span>
            <span>Convidar no WhatsApp</span>
        </a>
    </div>

    <!-- Cards de Métricas e Desempenho -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total de Convites -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total de Convites</span>
                <span class="text-2xl font-black text-white block"><?= (int)$totalInvited ?></span>
                <span class="text-[11px] text-slate-500 block">Oficinas cadastradas pelo seu link</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-xl">
                👥
            </div>
        </div>

        <!-- Em Validação -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Em Validação</span>
                <span class="text-2xl font-black text-amber-300 block"><?= (int)$pendingValidation ?></span>
                <span class="text-[11px] text-slate-500 block">Aguardando 1ª transação bancária</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl">
                ⏳
            </div>
        </div>

        <!-- Validadas com Sucesso -->
        <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider block">Bônus Liberados</span>
                <span class="text-2xl font-black text-emerald-300 block"><?= (int)$rewardedCount ?></span>
                <span class="text-[11px] text-slate-500 block">Oficinas reais confirmadas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xl">
                ✓
            </div>
        </div>

        <!-- Total de Dias Pro Acumulados -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950/40 border border-indigo-500/30 shadow-sm flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-bold text-indigo-300 uppercase tracking-wider block">Dias Pro Ganhos</span>
                <span class="text-2xl font-black text-cyan-300 block">+<?= (int)$totalDaysEarned ?> Dias</span>
                <span class="text-[11px] text-slate-400 block">Adicionados à sua assinatura</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center text-xl">
                ⭐
            </div>
        </div>

    </div>

    <!-- Card de Compartilhamento do Link -->
    <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-sm space-y-6">
        <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-bold text-white flex items-center gap-2">
                    <span>🔗</span>
                    <span>Seu Link de Parceiro Oficial</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    Quem se cadastrar por este link ganha automaticamente <strong><?= (int)$welcomeDays ?> dias de teste Pro</strong> de boas-vindas!
                </p>
            </div>
            <div class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shrink-0">
                Código: <?= htmlspecialchars($referralCode, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>

        <!-- Link Input com Copiar e Botão WhatsApp -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2 flex items-center gap-2">
                <input type="text" readonly id="referralUrlInput" value="<?= htmlspecialchars($referralUrl, ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-xs text-slate-200 font-mono focus:outline-none select-all">
                <button type="button" onclick="copyReferralLink()" id="btnCopyReferral"
                        class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shrink-0 shadow-lg shadow-indigo-600/30 transition flex items-center gap-1.5">
                    <span>📋</span>
                    <span>Copiar Link</span>
                </button>
            </div>

            <div>
                <a href="<?= htmlspecialchars($whatsappShareUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank"
                   class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/25 transition">
                    <span>💬</span>
                    <span>Compartilhar no WhatsApp</span>
                </a>
            </div>
        </div>

        <!-- Bloco Informativo das Regras Transparentes -->
        <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-slate-300 space-y-2">
            <div class="flex items-center gap-2 text-indigo-400 font-bold">
                <span>🛡️</span>
                <span>Como Funciona a Validação por Pagamento Real (Regra Anti-Fraude):</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-slate-400 text-[11px] leading-relaxed">
                <li>Assim que a oficina convidada se cadastra, ela ganha <strong><?= (int)$welcomeDays ?> dias de teste Pro</strong> de boas-vindas.</li>
                <li>Para evitar cadastros falsos e garantir a integridade da plataforma, o seu bônus de <strong><?= (int)$rewardDaysPerReferral ?> dias Pro grátis</strong> é liberado automaticamente no momento em que a nova oficina <strong>concluir seu primeiro pagamento</strong> (seja recebendo um Pix de cliente por uma OS ou contratando um plano do Zyrtex).</li>
                <li>Conforme as diretrizes de privacidade da LGPD, os valores financeiros e clientes da oficina indicada são mantidos em sigilo estrito — você apenas acompanha o status de validação.</li>
            </ul>
        </div>
    </div>

    <!-- Tabela de Indicações Realizadas -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <span>📋</span>
                    <span>Histórico de Oficinas Indicadas</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Acompanhe as ativações em tempo real.</p>
            </div>
            <span class="text-xs text-slate-500 font-bold"><?= count($referralsList) ?> registros</span>
        </div>

        <?php if (empty($referralsList)): ?>
            <div class="text-center py-12 space-y-3">
                <div class="text-4xl">🚀</div>
                <h4 class="text-sm font-bold text-slate-300">Nenhum colega convidado ainda</h4>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Copie seu link acima e envie para seus amigos técnicos em grupos de WhatsApp. A cada oficina ativa, você acumula meses grátis de Zyrtex Pro!
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3">Oficina Convidada</th>
                            <th class="py-3 px-3">Especialidade / Nicho</th>
                            <th class="py-3 px-3">Data de Entrada</th>
                            <th class="py-3 px-3">Status da Validação</th>
                            <th class="py-3 px-3 text-right">Seu Bônus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($referralsList as $r): ?>
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="py-3.5 px-3 font-bold text-white flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-slate-800 text-slate-300 flex items-center justify-center text-xs">
                                        🏢
                                    </span>
                                    <span><?= htmlspecialchars($r['business_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-400 capitalize">
                                    <?= htmlspecialchars($r['niche'] ?? 'Geral', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="py-3.5 px-3 text-slate-400 font-mono text-[11px]">
                                    <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-3">
                                    <?php if ($r['status'] === 'rewarded' || $r['status'] === 'qualified'): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span>Validada por Transação Real</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            <span>Aguardando 1º Pagamento</span>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3 text-right font-bold">
                                    <?php if ($r['status'] === 'rewarded' || $r['status'] === 'qualified'): ?>
                                        <span class="text-emerald-400 font-mono">+<?= (int)$r['reward_value'] ?> Dias Pro</span>
                                    <?php else: ?>
                                        <span class="text-slate-500 font-mono">Em Espera</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script>
function copyReferralLink() {
    const input = document.getElementById('referralUrlInput');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('btnCopyReferral');
        const orig = btn.innerHTML;
        btn.innerHTML = '<span>✓</span> <span>Copiado!</span>';
        btn.classList.add('bg-emerald-600');
        setTimeout(() => {
            btn.innerHTML = orig;
            btn.classList.remove('bg-emerald-600');
        }, 2000);
    });
}
</script>
