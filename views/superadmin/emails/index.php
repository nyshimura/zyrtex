<?php
/**
 * View: Gestão Central de Templates e Disparos de E-mail do SuperAdmin
 */
$saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
$csrfField = \Zyrtex\Core\CSRF::field();
$smtpConfig = $smtpConfig ?? [];
$templates = $templates ?? [];

$isSmtpActive = !empty($smtpConfig['active']);
?>

<div class="space-y-6">

    <!-- Header Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-cyan-500 flex items-center justify-center text-lg shadow-lg shadow-indigo-600/30">
                    ✉️
                </span>
                <span>Templates de E-mail & Disparos</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Personalize os e-mails transacionais do SaaS com o construtor visual Drag & Drop, variáveis dinâmicas e tokens de segurança.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/parametros#tab-smtp') ?>" 
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition flex items-center gap-2">
                <span>⚙️</span>
                <span>Configurar SMTP Master</span>
            </a>
            <button type="button" onclick="openSmtpTestModal()"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                <span>⚡</span>
                <span>Testar Conexão</span>
            </button>
        </div>
    </div>

    <!-- Status do Servidor SMTP -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl <?= $isSmtpActive ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' ?> flex items-center justify-center text-xl shrink-0">
                <?= $isSmtpActive ? '🟢' : '🟡' ?>
            </div>
            <div>
                <span class="text-[10px] uppercase font-black tracking-wider text-slate-400 block">Modo Operacional</span>
                <span class="text-xs font-bold text-white flex items-center gap-1.5">
                    <?= $isSmtpActive ? 'SMTP Ativo (Produção)' : 'Simulação Offline (Log Local)' ?>
                </span>
                <span class="text-[10px] text-slate-500 block truncate">
                    <?= !empty($smtpConfig['host']) ? htmlspecialchars($smtpConfig['host']) . ':' . $smtpConfig['port'] : 'storage/logs/mail_simulated.log' ?>
                </span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center text-lg shrink-0">
                👤
            </div>
            <div class="min-w-0">
                <span class="text-[10px] uppercase font-black tracking-wider text-slate-400 block">Remetente Oficial</span>
                <span class="text-xs font-bold text-white truncate block">
                    <?= htmlspecialchars($smtpConfig['from_name'] ?? 'Zyrtex SaaS') ?>
                </span>
                <span class="text-[10px] text-slate-500 truncate block">
                    &lt;<?= htmlspecialchars($smtpConfig['from_email'] ?? 'notificacoes@zyrtex.com.br') ?>&gt;
                </span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20 flex items-center justify-center text-lg shrink-0">
                📑
            </div>
            <div>
                <span class="text-[10px] uppercase font-black tracking-wider text-slate-400 block">Modelos Disponíveis</span>
                <span class="text-xs font-bold text-white">
                    <?= count($templates) ?> Templates Ativos
                </span>
                <span class="text-[10px] text-slate-500 block">
                    Suporte Drag & Drop e Placeholders
                </span>
            </div>
        </div>
    </div>

    <!-- Lista de Templates em Cards Premium -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <span>🎨</span>
                <span>Templates Transacionais Registrados</span>
            </h2>
            <span class="text-xs text-slate-500">
                Selecione um template para abrir o construtor interativo
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <?php foreach ($templates as $tpl): ?>
                <?php 
                $blocks = json_decode($tpl['blocks_json'] ?? '[]', true) ?: [];
                $placeholders = json_decode($tpl['available_placeholders_json'] ?? '[]', true) ?: [];
                $isSteam = ($tpl['slug'] === 'lojista_2fa_steam');
                ?>
                <div class="rounded-2xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 transition-all p-6 flex flex-col justify-between space-y-5 shadow-sm group">
                    <div class="space-y-4">
                        <!-- Topo do Card -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl <?= $isSteam ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/30' ?> flex items-center justify-center text-2xl group-hover:scale-105 transition-transform">
                                    <?= $isSteam ? '🛡️' : '🔑' ?>
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold text-white group-hover:text-indigo-300 transition">
                                        <?= htmlspecialchars($tpl['name']) ?>
                                    </h3>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                        slug: <?= htmlspecialchars($tpl['slug']) ?>
                                    </span>
                                </div>
                            </div>

                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shrink-0">
                                ✓ Sistema Nativo
                            </span>
                        </div>

                        <!-- Descrição -->
                        <p class="text-xs text-slate-400 leading-relaxed">
                            <?= htmlspecialchars($tpl['description']) ?>
                        </p>

                        <!-- Assunto com Placeholders em Destaque -->
                        <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Assunto Padrão:</span>
                            <div class="text-xs text-slate-200 font-mono font-medium truncate">
                                <?= htmlspecialchars($tpl['subject']) ?>
                            </div>
                        </div>

                        <!-- Tags de Placeholders Disponíveis -->
                        <div class="space-y-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Variáveis Disponíveis:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($placeholders as $k => $desc): ?>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-slate-800/80 text-indigo-300 border border-slate-700" title="<?= htmlspecialchars($desc) ?>">
                                        {{<?= htmlspecialchars($k) ?>}}
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Estatísticas de Blocos -->
                        <div class="flex items-center gap-4 pt-2 text-[11px] text-slate-500 border-t border-slate-800/60">
                            <span>📦 <strong><?= count($blocks) ?></strong> blocos estruturais</span>
                            <span>⚡ Drag & Drop Ativo</span>
                            <span>📅 Atualizado: <?= !empty($tpl['updated_at']) ? date('d/m/Y H:i', strtotime($tpl['updated_at'])) : 'Inicial' ?></span>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between gap-3">
                        <button type="button" onclick="openTestModal(<?= (int)$tpl['id'] ?>, '<?= htmlspecialchars(addslashes($tpl['name'])) ?>')"
                                class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                            <span>🧪</span>
                            <span>Disparar Teste</span>
                        </button>

                        <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/emails/editar?id=' . $tpl['id']) ?>" 
                           class="flex-1 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition text-center shadow-md shadow-indigo-600/30 flex items-center justify-center gap-2 group-hover:bg-indigo-500">
                            <span>✏️</span>
                            <span>Editar no Construtor Visual</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal de Teste de Template Individual -->
<div id="testTemplateModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">🧪</span>
                <h3 class="text-sm font-bold text-white">Disparar E-mail de Teste</h3>
            </div>
            <button type="button" onclick="closeTestModal()" class="text-slate-500 hover:text-white text-lg font-bold">&times;</button>
        </div>

        <div class="space-y-3">
            <p class="text-xs text-slate-400 leading-relaxed">
                Você está disparando um teste para o template <strong id="modalTemplateName" class="text-indigo-400"></strong>.
                Os placeholders serão substituídos por valores de demonstração realistas.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                    Seu E-mail para Recebimento:
                </label>
                <input type="email" id="testEmailInput" placeholder="seu-email@dominio.com.br"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div id="testStatusMsg" class="hidden text-xs p-3 rounded-xl"></div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" onclick="closeTestModal()"
                    class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 transition">
                Cancelar
            </button>
            <button type="button" id="btnConfirmTest" onclick="sendTemplateTest()"
                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/30 transition flex items-center gap-2">
                <span>⚡</span>
                <span>Enviar Agora</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Diagnóstico Completo SMTP -->
<div id="smtpTestModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex items-center justify-between border-b border-slate-800 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">⚡</span>
                <h3 class="text-sm font-bold text-white">Diagnóstico de Conexão SMTP em Tempo Real</h3>
            </div>
            <button type="button" onclick="closeSmtpTestModal()" class="text-slate-500 hover:text-white text-lg font-bold">&times;</button>
        </div>

        <div class="space-y-3">
            <p class="text-xs text-slate-400 leading-relaxed">
                O Zyrtex SaaS abrirá uma conexão RFC 5321 nativa via socket, testará o handshake TLS/SSL, autenticará o usuário e disparará uma mensagem técnica de confirmação.
            </p>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                    E-mail do Administrador Destinatário:
                </label>
                <input type="email" id="smtpRecipientInput" value="<?= htmlspecialchars(\Zyrtex\Core\Session::get('superadmin_email') ?? 'superadmin@zyrtex.com.br') ?>"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500">
            </div>

            <div id="smtpResultContainer" class="hidden space-y-2">
                <div id="smtpStatusBadge" class="text-xs font-bold p-3 rounded-xl"></div>
                <div class="bg-slate-950 rounded-xl p-3 border border-slate-800 max-h-48 overflow-y-auto font-mono text-[11px] text-slate-300" id="smtpLogConsole"></div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" onclick="closeSmtpTestModal()"
                    class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 transition">
                Fechar
            </button>
            <button type="button" id="btnRunSmtpTest" onclick="runSmtpTest()"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/30 transition flex items-center gap-2">
                <span>🚀</span>
                <span>Executar Teste Socket</span>
            </button>
        </div>
    </div>
</div>

<script>
let currentTemplateId = null;

function openTestModal(id, name) {
    currentTemplateId = id;
    document.getElementById('modalTemplateName').textContent = name;
    document.getElementById('testStatusMsg').className = 'hidden';
    document.getElementById('testTemplateModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('testTemplateModal').classList.add('hidden');
}

async function sendTemplateTest() {
    const email = document.getElementById('testEmailInput').value.trim();
    const btn = document.getElementById('btnConfirmTest');
    const msg = document.getElementById('testStatusMsg');

    if (!email) {
        alert('Por favor, informe seu e-mail.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin">⏳</span> Disparando...';
    msg.className = 'hidden';

    try {
        const res = await fetch('<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/emails/testar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: currentTemplateId,
                test_email: email
            })
        });
        const data = await res.json();

        msg.classList.remove('hidden');
        if (data.success) {
            msg.className = 'text-xs p-3 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 font-bold';
            msg.textContent = '✓ ' + (data.message || 'E-mail enviado com sucesso!');
        } else {
            msg.className = 'text-xs p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 font-bold';
            msg.textContent = '✕ ' + (data.error || 'Falha no envio.');
        }
    } catch (err) {
        msg.classList.remove('hidden');
        msg.className = 'text-xs p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 font-bold';
        msg.textContent = 'Erro ao processar requisição: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>⚡</span><span>Enviar Agora</span>';
    }
}

function openSmtpTestModal() {
    document.getElementById('smtpResultContainer').classList.add('hidden');
    document.getElementById('smtpTestModal').classList.remove('hidden');
}

function closeSmtpTestModal() {
    document.getElementById('smtpTestModal').classList.add('hidden');
}

async function runSmtpTest() {
    const email = document.getElementById('smtpRecipientInput').value.trim();
    const btn = document.getElementById('btnRunSmtpTest');
    const resBox = document.getElementById('smtpResultContainer');
    const badge = document.getElementById('smtpStatusBadge');
    const logBox = document.getElementById('smtpLogConsole');

    if (!email) {
        alert('Informe um e-mail válido para o teste.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin">⏳</span> Testando Socket...';

    const formData = new FormData();
    formData.append('test_email', email);

    try {
        const res = await fetch('<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/parametros/smtp-teste') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        resBox.classList.remove('hidden');
        if (data.success) {
            badge.className = 'text-xs font-bold p-3 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300';
            badge.textContent = '✓ ' + data.message;
        } else {
            badge.className = 'text-xs font-bold p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300';
            badge.textContent = '✕ ' + (data.error || 'Falha no teste SMTP.');
        }

        if (Array.isArray(data.log)) {
            logBox.innerHTML = data.log.map(l => `<div class="py-0.5">${escapeHtml(l)}</div>`).join('');
        } else {
            logBox.textContent = 'Nenhum log retornado.';
        }
    } catch (err) {
        resBox.classList.remove('hidden');
        badge.className = 'text-xs font-bold p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-300';
        badge.textContent = 'Erro ao contactar servidor: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span>🚀</span><span>Executar Teste Socket</span>';
    }
}

function escapeHtml(str) {
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}
</script>
