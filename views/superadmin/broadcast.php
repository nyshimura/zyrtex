<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <span>📢</span>
                <span>Avisos Globais da Plataforma (Broadcast)</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Dispare comunicados urgentes, novidades ou avisos de manutenção programada no topo do painel de todos os lojistas.
            </p>
        </div>
    </div>

    <!-- Formulário do Broadcast -->
    <form action="<?= \Zyrtex\Core\Response::url('/superadmin/broadcast') ?>" method="POST" class="space-y-6">
        <?= $csrfField ?>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-6 shadow-sm">
            
            <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">Configuração da Notificação em Massa</h2>
                    <p class="text-xs text-slate-400 mt-0.5">O aviso será fixado no cabeçalho administrativo de todas as oficinas ativas.</p>
                </div>

                <div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="broadcast_active" value="1" id="broadcast_active"
                               <?= !empty($broadcast['broadcast_active']) && $broadcast['broadcast_active'] === '1' ? 'checked' : '' ?> 
                               class="sr-only peer" onchange="updatePreview()">
                        <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ml-2.5 text-xs font-bold text-slate-300">Exibir Aviso para os Lojistas</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="broadcast_type" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Estilo / Nível de Urgência:
                    </label>
                    <select id="broadcast_type" name="broadcast_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs font-bold" onchange="updatePreview()">
                        <option value="info" <?= ($broadcast['broadcast_type'] ?? 'info') === 'info' ? 'selected' : '' ?>>🔵 Informativo (Azul / Dicas e Novidades)</option>
                        <option value="warning" <?= ($broadcast['broadcast_type'] ?? '') === 'warning' ? 'selected' : '' ?>>🟡 Alerta (Amarelo / Atenção)</option>
                        <option value="danger" <?= ($broadcast['broadcast_type'] ?? '') === 'danger' ? 'selected' : '' ?>>🔴 Urgente / Crítico (Vermelho / Manutenção)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="broadcast_message" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Mensagem do Comunicado:
                    </label>
                    <input type="text" id="broadcast_message" name="broadcast_message"
                           value="<?= htmlspecialchars($broadcast['broadcast_message'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Ex: Manutenção preventiva agendada para domingo às 02h da manhã. O sistema permanecerá online."
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           oninput="updatePreview()">
                </div>
            </div>

            <!-- Preview em Tempo Real -->
            <div class="space-y-2 pt-2 border-t border-slate-800">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                    Pré-visualização ao Vivo (Como os lojistas verão):
                </span>

                <div id="preview_box" class="p-3.5 rounded-xl border flex items-center justify-between text-xs font-bold transition-all shadow-md">
                    <div class="flex items-center gap-2">
                        <span id="preview_icon" class="text-base">📢</span>
                        <span id="preview_text">Digite sua mensagem acima para visualizar...</span>
                    </div>
                    <span class="text-[10px] uppercase font-bold opacity-75">Aviso Global Zyrtex</span>
                </div>
            </div>

        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="px-8 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <span>✓ Salvar e Publicar Aviso Global</span>
            </button>
        </div>

    </form>

</div>

<script>
function updatePreview() {
    const msg = document.getElementById('broadcast_message').value.trim() || 'Digite sua mensagem acima para visualizar...';
    const type = document.getElementById('broadcast_type').value;
    const active = document.getElementById('broadcast_active').checked;
    const box = document.getElementById('preview_box');
    const icon = document.getElementById('preview_icon');
    const text = document.getElementById('preview_text');

    text.innerText = msg;

    if (!active) {
        box.className = 'p-3.5 rounded-xl border border-slate-800 bg-slate-950 text-slate-500 flex items-center justify-between text-xs font-bold opacity-50';
        icon.innerText = '⏸️';
        return;
    }

    if (type === 'warning') {
        box.className = 'p-3.5 rounded-xl border border-amber-500/40 bg-amber-950/40 text-amber-200 flex items-center justify-between text-xs font-bold shadow-amber-950/30';
        icon.innerText = '⚠️';
    } else if (type === 'danger') {
        box.className = 'p-3.5 rounded-xl border border-rose-500/40 bg-rose-950/40 text-rose-200 flex items-center justify-between text-xs font-bold shadow-rose-950/30';
        icon.innerText = '🚨';
    } else {
        box.className = 'p-3.5 rounded-xl border border-blue-500/40 bg-blue-950/40 text-blue-200 flex items-center justify-between text-xs font-bold shadow-blue-950/30';
        icon.innerText = '📢';
    }
}

document.addEventListener('DOMContentLoaded', updatePreview);
</script>
