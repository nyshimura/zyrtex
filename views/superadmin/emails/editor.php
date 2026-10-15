<?php
/**
 * View: Construtor Visual Drag & Drop de Templates de E-mail
 */
$saSlug = \Zyrtex\Controllers\SuperAdmin\SuperAdminController::slug();
$csrfField = \Zyrtex\Core\CSRF::field();
$template = $template ?? [];
$smtpConfig = $smtpConfig ?? [];

$blocksJson = $template['blocks_json'] ?? '[]';
$availablePlaceholders = json_decode($template['available_placeholders_json'] ?? '[]', true) ?: [];
$templateId = (int)$template['id'];
$templateSlug = $template['slug'];
?>

<div class="space-y-4">

    <!-- Topbar do Construtor -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 shadow-lg sticky top-16 z-30">
        <div class="flex items-center gap-3">
            <a href="<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/emails') ?>" 
               class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition border border-slate-700 text-sm font-bold">
                &larr;
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-sm sm:text-base font-extrabold text-white flex items-center gap-2">
                        <span>✏️</span>
                        <span><?= htmlspecialchars($template['name']) ?></span>
                    </h1>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                        <?= htmlspecialchars($templateSlug) ?>
                    </span>
                </div>
                <span class="text-[11px] text-slate-400">Arraste os blocos, personalize cores e insira variáveis para montar o layout.</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Alternador de Viewport (Desktop / Mobile) -->
            <div class="bg-slate-950 p-1 rounded-xl border border-slate-800 flex items-center gap-1">
                <button type="button" onclick="setPreviewDevice('desktop')" id="btnDeviceDesktop"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-indigo-600 text-white shadow">
                    <span>🖥️</span>
                    <span class="hidden sm:inline">Desktop</span>
                </button>
                <button type="button" onclick="setPreviewDevice('mobile')" id="btnDeviceMobile"
                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-400 hover:text-white">
                    <span>📱</span>
                    <span class="hidden sm:inline">Mobile</span>
                </button>
            </div>

            <!-- Botão Testar -->
            <button type="button" onclick="openTestModal()"
                    class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition flex items-center gap-1.5">
                <span>🧪</span>
                <span>Testar Disparo</span>
            </button>

            <!-- Botão Salvar -->
            <button type="button" onclick="saveTemplate()" id="btnSaveTemplate"
                    class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                <span>💾</span>
                <span>Salvar Template</span>
            </button>
        </div>
    </div>

    <!-- Barra de Assunto e Placeholders Rápidos -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-3">
        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                Assunto do E-mail (Suporta variáveis dinâmicas):
            </label>
            <div class="relative">
                <input type="text" id="templateSubject" value="<?= htmlspecialchars($template['subject']) ?>" 
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-white focus:outline-none focus:border-indigo-500">
            </div>
        </div>

        <div class="space-y-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
                Clique para inserir placeholder no campo ativo:
            </span>
            <div class="flex flex-wrap gap-1.5">
                <?php foreach ($availablePlaceholders as $tag => $label): ?>
                    <button type="button" onclick="insertPlaceholder('{{<?= htmlspecialchars($tag) ?>}}')"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold bg-slate-950 hover:bg-indigo-950/60 text-indigo-300 hover:text-indigo-200 border border-slate-800 hover:border-indigo-500/40 transition flex items-center gap-1 shadow-sm"
                            title="<?= htmlspecialchars($label) ?>">
                        <span>+</span>
                        <span>{{<?= htmlspecialchars($tag) ?>}}</span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Layout Dividido: Construtor (Esquerda) vs Pré-Visualização (Direita) -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">

        <!-- Coluna Esquerda: Paleta de Blocos & Área de Montagem (7 Cols) -->
        <div class="xl:col-span-7 space-y-4">

            <!-- Paleta de Blocos Disponíveis -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                        <span>🧩</span>
                        <span>Adicionar Novo Bloco ao Layout</span>
                    </span>
                    <span class="text-[10px] text-slate-500">Clique para adicionar ao final</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <button type="button" onclick="addBlock('header')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">🏷️</span>
                        <span>Cabeçalho</span>
                    </button>
                    <button type="button" onclick="addBlock('text')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">📝</span>
                        <span>Texto / Artigo</span>
                    </button>
                    <button type="button" onclick="addBlock('token_box')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">🛡️</span>
                        <span>Token Guard</span>
                    </button>
                    <button type="button" onclick="addBlock('button')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">🔘</span>
                        <span>Botão (CTA)</span>
                    </button>
                    <button type="button" onclick="addBlock('details_box')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">📋</span>
                        <span>Lista Detalhes</span>
                    </button>
                    <button type="button" onclick="addBlock('divider')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">➖</span>
                        <span>Divisor</span>
                    </button>
                    <button type="button" onclick="addBlock('footer')"
                            class="p-2.5 rounded-xl bg-slate-950 hover:bg-slate-800 border border-slate-800 hover:border-indigo-500/40 text-slate-300 hover:text-white transition flex items-center gap-2 font-bold text-left">
                        <span class="text-base">📄</span>
                        <span>Rodapé</span>
                    </button>
                </div>
            </div>

            <!-- Canvas de Blocos (Drag & Drop Lista) -->
            <div class="space-y-3" id="blocksCanvasContainer">
                <!-- Blocos serão renderizados dinamicamente via JS -->
            </div>
        </div>

        <!-- Coluna Direita: Live Preview Real-time (5 Cols) -->
        <div class="xl:col-span-5 sticky top-36">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 space-y-3 shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-white">Visualização em Tempo Real</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    </div>
                    <span id="previewDeviceLabel" class="text-[10px] font-mono text-slate-400">Desktop (600px)</span>
                </div>

                <!-- Envelope do Preview -->
                <div class="bg-slate-950 rounded-xl p-3 border border-slate-800/80 flex justify-center overflow-x-auto min-h-[520px]">
                    <div id="previewFrameContainer" class="w-full max-w-[600px] transition-all duration-300 bg-white rounded-xl shadow-2xl overflow-hidden text-slate-900">
                        <iframe id="emailPreviewIframe" class="w-full h-[650px] border-0" sandbox="allow-same-origin"></iframe>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-1">
                    <span>💡 Valores de exemplo são injetados automaticamente no preview.</span>
                    <button type="button" onclick="updateLivePreview()" class="hover:text-indigo-400 transition font-bold">↻ Atualizar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Teste de Disparo Real -->
<div id="editorTestModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">🧪</span>
                <h3 class="text-sm font-bold text-white">Disparo com o Layout Atual</h3>
            </div>
            <button type="button" onclick="closeTestModal()" class="text-slate-500 hover:text-white text-lg font-bold">&times;</button>
        </div>

        <p class="text-xs text-slate-400">
            Você receberá este layout no seu e-mail exatamente como editado, mesmo antes de salvar!
        </p>

        <div>
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                E-mail para Receber o Teste:
            </label>
            <input type="email" id="editorTestEmailInput" value="<?= htmlspecialchars(\Zyrtex\Core\Session::get('superadmin_email') ?? 'superadmin@zyrtex.com.br') ?>"
                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500">
        </div>

        <div id="editorTestResult" class="hidden text-xs p-3 rounded-xl font-bold"></div>

        <div class="flex items-center justify-end gap-2 pt-2">
            <button type="button" onclick="closeTestModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700">
                Cancelar
            </button>
            <button type="button" id="btnExecuteEditorTest" onclick="executeEditorTest()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow">
                Disparar Agora
            </button>
        </div>
    </div>
</div>

<script>
// Estado Global do Editor
let blocks = <?= $blocksJson ?>;
let activeInputElement = null;
let dragStartIndex = null;

// Rastreia campo de texto focado para inserção de placeholders
document.addEventListener('focusin', (e) => {
    if (e.target.matches('input[type="text"], textarea')) {
        activeInputElement = e.target;
    }
});

function insertPlaceholder(tag) {
    if (!activeInputElement) {
        activeInputElement = document.getElementById('templateSubject');
    }
    if (activeInputElement) {
        const start = activeInputElement.selectionStart || activeInputElement.value.length;
        const end = activeInputElement.selectionEnd || activeInputElement.value.length;
        const val = activeInputElement.value;
        activeInputElement.value = val.substring(0, start) + tag + val.substring(end);
        activeInputElement.focus();
        activeInputElement.setSelectionRange(start + tag.length, start + tag.length);

        // Se alterou um campo de bloco, atualiza objeto
        const blockId = activeInputElement.getAttribute('data-block-id');
        const field = activeInputElement.getAttribute('data-field');
        if (blockId && field) {
            updateBlockField(blockId, field, activeInputElement.value);
        } else {
            updateLivePreview();
        }
    }
}

// Renderiza a lista de blocos interativos
function renderBlocksCanvas() {
    const container = document.getElementById('blocksCanvasContainer');
    if (!blocks || blocks.length === 0) {
        container.innerHTML = `
            <div class="p-8 text-center bg-slate-900 border border-dashed border-slate-800 rounded-2xl text-slate-500 text-xs">
                Nenhum bloco no layout. Clique em um dos botões acima para adicionar.
            </div>`;
        updateLivePreview();
        return;
    }

    container.innerHTML = blocks.map((blk, idx) => {
        return createBlockHtml(blk, idx);
    }).join('');

    attachDragEvents();
    updateLivePreview();
}

// Gera HTML de cada bloco na lista
function createBlockHtml(blk, idx) {
    const blockTypesInfo = {
        'header': { icon: '🏷️', name: 'Cabeçalho / Banner', color: 'border-indigo-500/40' },
        'text': { icon: '📝', name: 'Texto / Artigo', color: 'border-slate-700' },
        'token_box': { icon: '🛡️', name: 'Token Zyrtex Guard (Steam)', color: 'border-cyan-500/50' },
        'button': { icon: '🔘', name: 'Botão de Ação (CTA)', color: 'border-emerald-500/40' },
        'details_box': { icon: '📋', name: 'Lista de Informações', color: 'border-purple-500/40' },
        'divider': { icon: '➖', name: 'Linha Divisória', color: 'border-slate-800' },
        'footer': { icon: '📄', name: 'Rodapé & Suporte', color: 'border-slate-700' }
    };

    const info = blockTypesInfo[blk.type] || { icon: '📦', name: 'Bloco', color: 'border-slate-700' };

    let fieldsHtml = '';

    if (blk.type === 'header') {
        fieldsHtml = `
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Título:</label>
                    <input type="text" data-block-id="${blk.id}" data-field="title" value="${escapeAttr(blk.title || '')}" oninput="handleFieldChange('${blk.id}', 'title', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Subtítulo:</label>
                    <input type="text" data-block-id="${blk.id}" data-field="subtitle" value="${escapeAttr(blk.subtitle || '')}" oninput="handleFieldChange('${blk.id}', 'subtitle', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cor de Fundo:</label>
                    <input type="color" value="${blk.bgColor || '#4f46e5'}" onchange="handleFieldChange('${blk.id}', 'bgColor', this.value)" class="w-full h-8 rounded-lg bg-slate-950 border border-slate-800 cursor-pointer">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cor do Texto:</label>
                    <input type="color" value="${blk.textColor || '#ffffff'}" onchange="handleFieldChange('${blk.id}', 'textColor', this.value)" class="w-full h-8 rounded-lg bg-slate-950 border border-slate-800 cursor-pointer">
                </div>
            </div>`;
    } else if (blk.type === 'text') {
        fieldsHtml = `
            <div class="space-y-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Título do Parágrafo (Opcional):</label>
                    <input type="text" data-block-id="${blk.id}" data-field="heading" value="${escapeAttr(blk.heading || '')}" oninput="handleFieldChange('${blk.id}', 'heading', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Conteúdo (HTML / Texto):</label>
                    <textarea data-block-id="${blk.id}" data-field="content" rows="3" oninput="handleFieldChange('${blk.id}', 'content', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white font-sans">${escapeAttr(blk.content || '')}</textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Alinhamento:</label>
                    <select onchange="handleFieldChange('${blk.id}', 'align', this.value)" class="px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                        <option value="left" ${blk.align === 'left' ? 'selected' : ''}>Esquerda</option>
                        <option value="center" ${blk.align === 'center' ? 'selected' : ''}>Centralizado</option>
                        <option value="right" ${blk.align === 'right' ? 'selected' : ''}>Direita</option>
                    </select>
                </div>
            </div>`;
    } else if (blk.type === 'token_box') {
        fieldsHtml = `
            <div class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Rótulo Superior:</label>
                        <input type="text" data-block-id="${blk.id}" data-field="label" value="${escapeAttr(blk.label || '')}" oninput="handleFieldChange('${blk.id}', 'label', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Placeholder do Código:</label>
                        <input type="text" data-block-id="${blk.id}" data-field="token" value="${escapeAttr(blk.token || '{{token_2fa}}')}" oninput="handleFieldChange('${blk.id}', 'token', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs font-mono text-cyan-300">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Aviso / Validade:</label>
                    <input type="text" data-block-id="${blk.id}" data-field="hint" value="${escapeAttr(blk.hint || '')}" oninput="handleFieldChange('${blk.id}', 'hint', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Fundo:</label>
                        <input type="color" value="${blk.bgColor || '#1e293b'}" onchange="handleFieldChange('${blk.id}', 'bgColor', this.value)" class="w-full h-7 rounded bg-slate-950 border border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Cor do Código:</label>
                        <input type="color" value="${blk.textColor || '#38bdf8'}" onchange="handleFieldChange('${blk.id}', 'textColor', this.value)" class="w-full h-7 rounded bg-slate-950 border border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Borda:</label>
                        <input type="color" value="${blk.borderColor || '#0284c7'}" onchange="handleFieldChange('${blk.id}', 'borderColor', this.value)" class="w-full h-7 rounded bg-slate-950 border border-slate-800">
                    </div>
                </div>
            </div>`;
    } else if (blk.type === 'button') {
        fieldsHtml = `
            <div class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Texto do Botão:</label>
                        <input type="text" data-block-id="${blk.id}" data-field="text" value="${escapeAttr(blk.text || '')}" oninput="handleFieldChange('${blk.id}', 'text', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">URL / Link:</label>
                        <input type="text" data-block-id="${blk.id}" data-field="url" value="${escapeAttr(blk.url || '')}" oninput="handleFieldChange('${blk.id}', 'url', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Cor do Botão:</label>
                        <input type="color" value="${blk.bgColor || '#10b981'}" onchange="handleFieldChange('${blk.id}', 'bgColor', this.value)" class="w-full h-7 rounded bg-slate-950 border border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Cor do Texto:</label>
                        <input type="color" value="${blk.textColor || '#ffffff'}" onchange="handleFieldChange('${blk.id}', 'textColor', this.value)" class="w-full h-7 rounded bg-slate-950 border border-slate-800">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Alinhamento:</label>
                        <select onchange="handleFieldChange('${blk.id}', 'align', this.value)" class="w-full h-7 rounded bg-slate-950 border border-slate-800 text-[11px] text-white">
                            <option value="center" ${blk.align === 'center' ? 'selected' : ''}>Centro</option>
                            <option value="left" ${blk.align === 'left' ? 'selected' : ''}>Esquerda</option>
                        </select>
                    </div>
                </div>
            </div>`;
    } else if (blk.type === 'details_box') {
        const itemsText = Array.isArray(blk.items) ? blk.items.join("\n") : (blk.items || '');
        fieldsHtml = `
            <div class="space-y-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Título da Caixa:</label>
                    <input type="text" data-block-id="${blk.id}" data-field="title" value="${escapeAttr(blk.title || '')}" oninput="handleFieldChange('${blk.id}', 'title', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Itens da Lista (um por linha):</label>
                    <textarea rows="3" data-block-id="${blk.id}" oninput="handleItemsChange('${blk.id}', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white font-mono">${escapeAttr(itemsText)}</textarea>
                </div>
            </div>`;
    } else if (blk.type === 'divider') {
        fieldsHtml = `<div class="text-[11px] text-slate-500 italic py-1">Divisor horizontal para separar seções do e-mail.</div>`;
    } else if (blk.type === 'footer') {
        fieldsHtml = `
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Texto de Rodapé (HTML / Placeholders):</label>
                <textarea data-block-id="${blk.id}" data-field="text" rows="3" oninput="handleFieldChange('${blk.id}', 'text', this.value)" class="w-full px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-xs text-white">${escapeAttr(blk.text || '')}</textarea>
            </div>`;
    }

    return `
        <div class="block-card rounded-2xl bg-slate-900 border ${info.color} p-4 space-y-3 transition-all shadow-md"
             draggable="true" data-index="${idx}" id="card-${blk.id}">
            <div class="flex items-center justify-between border-b border-slate-800/80 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="cursor-grab text-slate-500 hover:text-slate-300 text-sm select-none" title="Arraste para reordenar">☰</span>
                    <span class="text-base">${info.icon}</span>
                    <span class="text-xs font-bold text-white">${info.name}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="moveBlock(${idx}, -1)" ${idx === 0 ? 'disabled class="opacity-30 cursor-not-allowed"' : 'class="hover:text-white"'} title="Mover para cima" class="p-1 text-slate-400 text-xs">▲</button>
                    <button type="button" onclick="moveBlock(${idx}, 1)" ${idx === blocks.length - 1 ? 'disabled class="opacity-30 cursor-not-allowed"' : 'class="hover:text-white"'} title="Mover para baixo" class="p-1 text-slate-400 text-xs">▼</button>
                    <button type="button" onclick="deleteBlock(${idx})" class="p-1 text-rose-400 hover:text-rose-300 text-xs ml-1" title="Excluir bloco">🗑️</button>
                </div>
            </div>
            <div>${fieldsHtml}</div>
        </div>`;
}

// Manipulação de Dados de Blocos
function handleFieldChange(id, field, val) {
    const blk = blocks.find(b => b.id === id);
    if (blk) {
        blk[field] = val;
        updateLivePreview();
    }
}

function handleItemsChange(id, text) {
    const blk = blocks.find(b => b.id === id);
    if (blk) {
        blk.items = text.split("\n").filter(l => l.trim().length > 0);
        updateLivePreview();
    }
}

function addBlock(type) {
    const newId = 'blk_' + type + '_' + Date.now();
    let newBlock = { id: newId, type: type };

    if (type === 'header') {
        newBlock.title = 'Título do Cabeçalho';
        newBlock.subtitle = 'Subtítulo da Mensagem';
        newBlock.bgColor = '#4f46e5';
        newBlock.textColor = '#ffffff';
    } else if (type === 'text') {
        newBlock.heading = 'Novo Parágrafo';
        newBlock.content = 'Digite o texto do seu e-mail aqui. Você pode usar formatações e placeholders.';
        newBlock.align = 'left';
    } else if (type === 'token_box') {
        newBlock.label = 'SEU CÓDIGO DE SEGURANÇA:';
        newBlock.token = '{{token_2fa}}';
        newBlock.hint = 'Válido por 10 minutos. Nunca informe a terceiros.';
        newBlock.bgColor = '#1e293b';
        newBlock.textColor = '#38bdf8';
        newBlock.borderColor = '#0284c7';
    } else if (type === 'button') {
        newBlock.text = 'Clique Aqui &rarr;';
        newBlock.url = '{{app_url}}';
        newBlock.bgColor = '#10b981';
        newBlock.textColor = '#ffffff';
        newBlock.align = 'center';
    } else if (type === 'details_box') {
        newBlock.title = 'Informações Importantes:';
        newBlock.items = ['Item informativo 1', 'Item informativo 2'];
    } else if (type === 'divider') {
        // divider tem apenas o type
    } else if (type === 'footer') {
        newBlock.text = '&copy; {{current_year}} Zyrtex SaaS &bull; Todos os direitos reservados.';
    }

    blocks.push(newBlock);
    renderBlocksCanvas();
}

function deleteBlock(index) {
    if (confirm('Deseja remover este bloco do layout?')) {
        blocks.splice(index, 1);
        renderBlocksCanvas();
    }
}

function moveBlock(index, direction) {
    const target = index + direction;
    if (target < 0 || target >= blocks.length) return;
    const item = blocks.splice(index, 1)[0];
    blocks.splice(target, 0, item);
    renderBlocksCanvas();
}

// Drag and drop nativo
function attachDragEvents() {
    const cards = document.querySelectorAll('.block-card');
    cards.forEach(card => {
        card.addEventListener('dragstart', (e) => {
            dragStartIndex = parseInt(card.getAttribute('data-index'));
            e.dataTransfer.effectAllowed = 'move';
            card.classList.add('opacity-50');
        });

        card.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            card.classList.add('border-indigo-500');
        });

        card.addEventListener('dragleave', () => {
            card.classList.remove('border-indigo-500');
        });

        card.addEventListener('drop', (e) => {
            e.preventDefault();
            card.classList.remove('border-indigo-500');
            const dropIndex = parseInt(card.getAttribute('data-index'));
            if (dragStartIndex !== null && dragStartIndex !== dropIndex) {
                const moved = blocks.splice(dragStartIndex, 1)[0];
                blocks.splice(dropIndex, 0, moved);
                renderBlocksCanvas();
            }
        });

        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-50');
            dragStartIndex = null;
        });
    });
}

// Compila os blocos em HTML client-side para o Preview imediato
function compileClientBlocks(blocksArray) {
    let innerHtml = '';

    for (const blk of blocksArray) {
        if (blk.type === 'header') {
            innerHtml += `
                <tr>
                    <td style="background-color: ${blk.bgColor || '#4f46e5'}; padding: 36px 30px; text-align: center; border-radius: 16px 16px 0 0;">
                        <h1 style="margin: 0; color: ${blk.textColor || '#ffffff'}; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">${escapeHtml(blk.title || '')}</h1>
                        ${blk.subtitle ? `<p style="margin: 6px 0 0; color: ${blk.textColor || '#ffffff'}; font-size: 13px; opacity: 0.9;">${escapeHtml(blk.subtitle)}</p>` : ''}
                    </td>
                </tr>`;
        } else if (blk.type === 'text') {
            innerHtml += `
                <tr>
                    <td style="padding: 24px 30px; text-align: ${blk.align || 'left'}; font-size: 14px; line-height: 1.6; color: #334155;">
                        ${blk.heading ? `<h2 style="margin: 0 0 10px; font-size: 17px; font-weight: 700; color: #0f172a;">${escapeHtml(blk.heading)}</h2>` : ''}
                        <div style="margin: 0;">${blk.content || ''}</div>
                    </td>
                </tr>`;
        } else if (blk.type === 'token_box') {
            const tokenVal = blk.token || 'STEAM-4X9K2';
            innerHtml += `
                <tr>
                    <td style="padding: 10px 30px 24px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: ${blk.bgColor || '#1e293b'}; border-radius: 12px; border: 2px dashed ${blk.borderColor || '#0284c7'}; text-align: center;">
                            <tr>
                                <td style="padding: 20px;">
                                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 1px; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">${escapeHtml(blk.label || '')}</div>
                                    <div style="font-family: 'Courier New', monospace; font-size: 32px; font-weight: 900; letter-spacing: 6px; color: ${blk.textColor || '#38bdf8'};">${escapeHtml(tokenVal)}</div>
                                    ${blk.hint ? `<div style="font-size: 11px; color: #64748b; margin-top: 8px;">${escapeHtml(blk.hint)}</div>` : ''}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>`;
        } else if (blk.type === 'button') {
            innerHtml += `
                <tr>
                    <td style="padding: 16px 30px 28px 30px; text-align: ${blk.align || 'center'};">
                        <table align="${blk.align === 'center' ? 'center' : 'left'}" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="border-radius: 10px; background-color: ${blk.bgColor || '#10b981'};">
                                    <a href="${escapeAttr(blk.url || '#')}" target="_blank" style="display: inline-block; padding: 14px 32px; color: ${blk.textColor || '#ffffff'}; text-decoration: none; font-size: 14px; font-weight: 700; border-radius: 10px;">${escapeHtml(blk.text || '')}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>`;
        } else if (blk.type === 'details_box') {
            const items = Array.isArray(blk.items) ? blk.items : [];
            let itemsHtml = items.map(it => `<li style="margin-bottom: 6px;">${escapeHtml(it)}</li>`).join('');
            innerHtml += `
                <tr>
                    <td style="padding: 10px 30px 20px 30px;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                            <tr>
                                <td>
                                    ${blk.title ? `<div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">${escapeHtml(blk.title)}</div>` : ''}
                                    <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #475569; line-height: 1.6;">${itemsHtml}</ul>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>`;
        } else if (blk.type === 'divider') {
            innerHtml += `
                <tr>
                    <td style="padding: 8px 30px;">
                        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 0;">
                    </td>
                </tr>`;
        } else if (blk.type === 'footer') {
            innerHtml += `
                <tr>
                    <td style="padding: 24px 30px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; line-height: 1.6; color: #94a3b8; border-radius: 0 0 16px 16px;">
                        ${blk.text || ''}
                    </td>
                </tr>`;
        }
    }

    let fullHtml = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 20px 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f1f5f9; color: #334155; }
        * { box-sizing: border-box; }
    </style>
</head>
<body>
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
        ${innerHtml}
    </table>
</body>
</html>`;

    // Injeta mock data realista para o Preview
    const samplePlaceholders = {
        'client_name': 'Dr. Carlos Alberto Ferreira',
        'client_email': 'carlos.alberto@email.com',
        'activation_url': '<?= \Zyrtex\Core\Response::url('/portal/ativar?token=demo_token_123') ?>',
        'shop_name': 'Oficina Master Tech',
        'shop_phone': '(11) 98765-4321',
        'user_name': 'Administrador da Oficina',
        'token_2fa': 'H8K4N',
        'ip_address': '189.40.12.55',
        'date_time': '<?= date('d/m/Y \à\s H:i:s') ?>',
        'current_year': '<?= date('Y') ?>',
        'app_url': '<?= \Zyrtex\Core\Response::url('/') ?>'
    };

    for (const [k, v] of Object.entries(samplePlaceholders)) {
        const regex = new RegExp('\\{\\{' + k + '\\}\\}', 'g');
        fullHtml = fullHtml.replace(regex, v);
    }

    return fullHtml;
}

// Atualiza o iframe em tempo real
function updateLivePreview() {
    const iframe = document.getElementById('emailPreviewIframe');
    const compiled = compileClientBlocks(blocks);
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    doc.open();
    doc.write(compiled);
    doc.close();
}

// Dispositivo de Preview (Desktop vs Mobile)
function setPreviewDevice(device) {
    const container = document.getElementById('previewFrameContainer');
    const btnD = document.getElementById('btnDeviceDesktop');
    const btnM = document.getElementById('btnDeviceMobile');
    const lbl = document.getElementById('previewDeviceLabel');

    if (device === 'mobile') {
        container.style.maxWidth = '375px';
        btnM.className = 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-indigo-600 text-white shadow';
        btnD.className = 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-400 hover:text-white';
        lbl.textContent = 'Mobile (375px)';
    } else {
        container.style.maxWidth = '600px';
        btnD.className = 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 bg-indigo-600 text-white shadow';
        btnM.className = 'px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 text-slate-400 hover:text-white';
        lbl.textContent = 'Desktop (600px)';
    }
}

// Salva o template no banco de dados via AJAX
async function saveTemplate() {
    const btn = document.getElementById('btnSaveTemplate');
    const subject = document.getElementById('templateSubject').value.trim();

    if (!subject) {
        alert('Por favor, informe o assunto do e-mail.');
        document.getElementById('templateSubject').focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin">⏳</span> Salvando...';

    try {
        const res = await fetch('<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/emails/salvar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: <?= $templateId ?>,
                subject: subject,
                name: '<?= htmlspecialchars(addslashes($template['name'])) ?>',
                description: '<?= htmlspecialchars(addslashes($template['description'])) ?>',
                blocks_json: blocks
            })
        });

        const data = await res.json();
        if (data.success) {
            btn.className = 'px-5 py-2 rounded-xl bg-emerald-500 text-white text-xs font-extrabold shadow-lg transition flex items-center gap-2';
            btn.innerHTML = '<span>✓</span><span>Salvo com Sucesso!</span>';
            setTimeout(() => {
                btn.className = 'px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/30 transition flex items-center gap-2';
                btn.innerHTML = '<span>💾</span><span>Salvar Template</span>';
                btn.disabled = false;
            }, 2000);
        } else {
            alert('Erro ao salvar template: ' + (data.error || 'Falha desconhecida.'));
            btn.disabled = false;
            btn.innerHTML = '<span>💾</span><span>Salvar Template</span>';
        }
    } catch (err) {
        alert('Erro ao conectar ao servidor: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<span>💾</span><span>Salvar Template</span>';
    }
}

// Modal de Teste
function openTestModal() {
    document.getElementById('editorTestResult').className = 'hidden';
    document.getElementById('editorTestModal').classList.remove('hidden');
}

function closeTestModal() {
    document.getElementById('editorTestModal').classList.add('hidden');
}

async function executeEditorTest() {
    const email = document.getElementById('editorTestEmailInput').value.trim();
    const btn = document.getElementById('btnExecuteEditorTest');
    const resBox = document.getElementById('editorTestResult');
    const subject = document.getElementById('templateSubject').value.trim();

    if (!email) {
        alert('Informe seu e-mail para o teste.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin">⏳</span> Disparando...';
    resBox.className = 'hidden';

    try {
        const res = await fetch('<?= \Zyrtex\Core\Response::url('/' . $saSlug . '/emails/testar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: <?= $templateId ?>,
                test_email: email,
                subject: subject,
                blocks_json: blocks
            })
        });

        const data = await res.json();
        resBox.classList.remove('hidden');
        if (data.success) {
            resBox.className = 'text-xs p-3 rounded-xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-200 font-bold';
            resBox.textContent = '✓ ' + (data.message || 'E-mail de teste disparado com sucesso!');
        } else {
            resBox.className = 'text-xs p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 font-bold';
            resBox.textContent = '✕ ' + (data.error || 'Falha no disparo do teste.');
        }
    } catch (err) {
        resBox.classList.remove('hidden');
        resBox.className = 'text-xs p-3 rounded-xl bg-rose-950/60 border border-rose-500/40 text-rose-200 font-bold';
        resBox.textContent = 'Erro de conexão: ' + err.message;
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Disparar Agora';
    }
}

// Atalho Ctrl+S / Cmd+S para salvar
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        saveTemplate();
    }
});

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function escapeAttr(str) {
    if (!str) return '';
    return String(str).replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

// Inicialização
document.addEventListener('DOMContentLoaded', () => {
    renderBlocksCanvas();
});
</script>
