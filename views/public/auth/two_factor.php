<?php
/**
 * View: Autenticação de Dois Fatores (Zyrtex Guard - Estilo Steam Guard)
 */
$csrfField = \Zyrtex\Core\CSRF::field();
$emailHint = $emailHint ?? '';
$name = $name ?? 'Lojista';
$shopName = $shopName ?? 'Oficina';
?>

<div class="min-h-[85vh] flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full space-y-6">

        <!-- Card Estilo Steam Guard -->
        <div class="rounded-3xl bg-slate-900/90 border border-cyan-500/30 p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden backdrop-blur-xl">
            <!-- Glows Cibernéticos Decorativos -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Cabeçalho Steam Guard -->
            <div class="text-center space-y-2 relative">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-slate-950 via-slate-900 to-cyan-950 border border-cyan-400/40 text-cyan-400 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-cyan-500/20">
                    🛡️
                </div>
                <div class="pt-1">
                    <span class="text-[10px] uppercase font-black tracking-widest text-cyan-400 font-mono block">Segurança de Dois Fatores</span>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center justify-center gap-1.5">
                        <span>ZYRTEX</span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-400">GUARD</span>
                    </h1>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed max-w-xs mx-auto">
                    Olá, <strong><?= htmlspecialchars($name) ?></strong>! Digite o código de 5 caracteres enviado para:
                </p>
                <div class="inline-block px-3 py-1 rounded-xl bg-slate-950 border border-cyan-500/30 font-mono text-xs text-cyan-300 font-bold">
                    <?= htmlspecialchars($emailHint) ?>
                </div>
            </div>

            <!-- Formulário de Digitação dos 5 Caracteres -->
            <form action="<?= \Zyrtex\Core\Response::url('/login/2fa') ?>" method="POST" id="form2fa" class="space-y-6">
                <?= $csrfField ?>
                <input type="hidden" name="code" id="fullCodeInput" value="">

                <!-- 5 Caixas de Dígitos estilo Steam -->
                <div class="space-y-2">
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-center text-slate-400 font-mono">
                        Código de Acesso (5 Caracteres):
                    </label>
                    <div class="flex items-center justify-center gap-2 sm:gap-3" id="digitsContainer">
                        <input type="text" maxlength="1" class="steam-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl sm:text-3xl font-black font-mono uppercase rounded-2xl bg-slate-950 border-2 border-slate-700 text-cyan-300 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none transition-all shadow-inner" autofocus data-index="0">
                        <input type="text" maxlength="1" class="steam-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl sm:text-3xl font-black font-mono uppercase rounded-2xl bg-slate-950 border-2 border-slate-700 text-cyan-300 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none transition-all shadow-inner" data-index="1">
                        <input type="text" maxlength="1" class="steam-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl sm:text-3xl font-black font-mono uppercase rounded-2xl bg-slate-950 border-2 border-slate-700 text-cyan-300 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none transition-all shadow-inner" data-index="2">
                        <input type="text" maxlength="1" class="steam-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl sm:text-3xl font-black font-mono uppercase rounded-2xl bg-slate-950 border-2 border-slate-700 text-cyan-300 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none transition-all shadow-inner" data-index="3">
                        <input type="text" maxlength="1" class="steam-digit w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl sm:text-3xl font-black font-mono uppercase rounded-2xl bg-slate-950 border-2 border-slate-700 text-cyan-300 focus:border-cyan-400 focus:ring-4 focus:ring-cyan-500/20 focus:outline-none transition-all shadow-inner" data-index="4">
                    </div>
                </div>

                <!-- Botão de Autenticação -->
                <button type="submit" id="btnSubmit2fa"
                        class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-indigo-600 hover:from-cyan-400 hover:via-blue-500 hover:to-indigo-500 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-cyan-500/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 cursor-pointer">
                    <span>⚡</span>
                    <span>Validar Código e Entrar</span>
                </button>
            </form>

            <!-- Ações Secundárias (Reenviar / Cancelar) -->
            <div class="pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-400">
                <form action="<?= \Zyrtex\Core\Response::url('/login/2fa/reenviar') ?>" method="POST">
                    <?= $csrfField ?>
                    <button type="submit" class="text-cyan-400 hover:text-cyan-300 font-bold transition flex items-center gap-1">
                        <span>↻</span>
                        <span>Reenviar novo código</span>
                    </button>
                </form>

                <a href="<?= \Zyrtex\Core\Response::url('/logout') ?>" class="text-slate-500 hover:text-slate-300 transition">
                    &larr; Voltar ao Login
                </a>
            </div>

            <!-- Rodapé Informativo -->
            <div class="text-center pt-1">
                <p class="text-[10px] text-slate-500">
                    O código é válido por 10 minutos. Nunca informe este token a ninguém.
                </p>
            </div>
        </div>

    </div>
</div>

<script>
// Manipulação inteligente dos 5 inputs estilo Steam Guard
document.addEventListener('DOMContentLoaded', () => {
    const inputs = Array.from(document.querySelectorAll('.steam-digit'));
    const fullInput = document.getElementById('fullCodeInput');
    const form = document.getElementById('form2fa');

    function updateFullCode() {
        const code = inputs.map(i => i.value.trim().toUpperCase()).join('');
        fullInput.value = code;
        return code;
    }

    inputs.forEach((input, idx) => {
        // Auto-avanço para o próximo dígito ao digitar
        input.addEventListener('input', (e) => {
            const val = input.value.trim().toUpperCase();
            input.value = val ? val.charAt(val.length - 1) : '';

            updateFullCode();

            if (input.value && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
                inputs[idx + 1].select();
            }

            // Se todos os 5 preenchidos, submete automaticamente
            if (updateFullCode().length === 5) {
                form.submit();
            }
        });

        // Suporte a Backspace
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                inputs[idx - 1].focus();
            }
        });

        // Suporte para colar código completo (Ctrl+V)
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').trim().toUpperCase();
            if (pasted) {
                const chars = pasted.replace(/[^A-Z0-9]/g, '').split('').slice(0, 5);
                chars.forEach((ch, i) => {
                    if (inputs[i]) {
                        inputs[i].value = ch;
                    }
                });
                const code = updateFullCode();
                if (code.length === 5) {
                    inputs[4].focus();
                    form.submit();
                } else if (inputs[chars.length]) {
                    inputs[chars.length].focus();
                }
            }
        });
    });

    form.addEventListener('submit', (e) => {
        const code = updateFullCode();
        if (code.length < 5) {
            e.preventDefault();
            alert('Por favor, informe todos os 5 caracteres do código Zyrtex Guard.');
            inputs[code.length < 5 ? code.length : 0].focus();
        }
    });
});
</script>
