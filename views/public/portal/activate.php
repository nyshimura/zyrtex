<?php
/**
 * View: Ativação de Conta do Cliente no Portal (Acesso Transparente)
 */
$csrfField = \Zyrtex\Core\CSRF::field();
$status = $status ?? 'ready';
$client = $client ?? [];
$token = $token ?? '';
$message = $message ?? '';
?>

<div class="min-h-[80vh] flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full space-y-6">

        <?php if ($status === 'ready'): ?>
            <!-- CARD DE ATIVAÇÃO COM SUCESSO -->
            <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6 shadow-2xl relative overflow-hidden">
                <!-- Efeito Glow Decorativo -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Cabeçalho -->
                <div class="text-center space-y-2 relative">
                    <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-3xl mx-auto shadow-xl shadow-emerald-500/20">
                        ✓
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight pt-1">
                        Ativação de Conta
                    </h1>
                    <p class="text-xs text-slate-400">
                        Você foi cadastrado(a) na oficina <strong class="text-indigo-400"><?= htmlspecialchars($client['business_name'] ?? 'Assistência Técnica') ?></strong>
                    </p>
                </div>

                <!-- Detalhes do Cliente -->
                <div class="p-4 rounded-2xl bg-slate-950/80 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">Titular:</span>
                        <span class="font-bold text-white"><?= htmlspecialchars($client['name'] ?? '') ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">E-mail:</span>
                        <span class="text-slate-300 font-mono text-[11px]"><?= htmlspecialchars($client['email'] ?? '') ?></span>
                    </div>
                    <?php if (!empty($client['phone'])): ?>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-bold uppercase tracking-wider text-[10px]">WhatsApp:</span>
                        <span class="text-slate-300 font-mono text-[11px]"><?= htmlspecialchars($client['phone'] ?? '') ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Benefícios do Portal -->
                <div class="space-y-2 text-xs text-slate-300">
                    <span class="text-[10px] uppercase font-black tracking-wider text-slate-500 block">Com sua conta ativa você poderá:</span>
                    <div class="space-y-1.5 text-[11px]">
                        <div class="flex items-center gap-2 text-slate-300">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>Acompanhar laudos técnicos e ordens de serviço em tempo real</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-300">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>Aprovar orçamentos com 1 clique diretamente do celular</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-300">
                            <span class="text-emerald-400 font-bold">✓</span>
                            <span>Acessar comprovantes de pagamento e termos de garantia</span>
                        </div>
                    </div>
                </div>

                <!-- Formulário de Ativação -->
                <form action="<?= \Zyrtex\Core\Response::url('/portal/ativar') ?>" method="POST" class="space-y-4">
                    <?= $csrfField ?>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <!-- Opção de Criar Senha Pessoal (Opcional) -->
                    <div class="pt-2 border-t border-slate-800">
                        <button type="button" onclick="document.getElementById('passwordBox').classList.toggle('hidden')" 
                                class="text-xs text-indigo-400 hover:text-indigo-300 font-bold flex items-center justify-between w-full py-1">
                            <span>🔒 Definir senha pessoal para o portal (Opcional)</span>
                            <span class="text-[10px] text-slate-500">expandir &darr;</span>
                        </button>

                        <div id="passwordBox" class="hidden space-y-3 pt-3">
                            <p class="text-[10px] text-slate-400">
                                Você pode acessar sempre com seu WhatsApp ou CPF, mas se preferir pode definir uma senha pessoal agora:
                            </p>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Senha (Mínimo 6 caracteres):</label>
                                <input type="password" name="password" minlength="6" placeholder="••••••••"
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Confirmar Senha:</label>
                                <input type="password" name="password_confirmation" minlength="6" placeholder="••••••••"
                                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <span>🚀</span>
                        <span>Confirmar e Ativar Minha Conta</span>
                    </button>
                </form>

                <div class="text-center">
                    <span class="text-[10px] text-slate-500 flex items-center justify-center gap-1.5">
                        <span>🛡️</span>
                        <span>Segurança e Privacidade garantidas pela plataforma Zyrtex.</span>
                    </span>
                </div>
            </div>

        <?php else: ?>
            <!-- CARD DE ERRO OU EXPIRAÇÃO -->
            <div class="rounded-3xl bg-slate-900 border border-slate-800 p-6 sm:p-8 space-y-6 shadow-2xl text-center">
                <div class="w-16 h-16 rounded-3xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-3xl mx-auto">
                    ⚠️
                </div>

                <div class="space-y-2">
                    <h1 class="text-xl font-black text-white">
                        <?= ($status === 'expired_token') ? 'Link Expirado' : 'Link Não Encontrado' ?>
                    </h1>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        <?= htmlspecialchars($message) ?>
                    </p>
                </div>

                <div class="pt-2 flex flex-col gap-2">
                    <a href="<?= \Zyrtex\Core\Response::url('/portal') ?>" 
                       class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition text-center shadow-md shadow-indigo-600/30">
                        Ir para o Portal do Cliente
                    </a>
                    <?php if (!empty($client['shop_phone'])): ?>
                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $client['shop_phone']) ?>?text=Ola,%20meu%20link%20de%20ativacao%20do%20portal%20expirou.%20Poderia%20reenviar?" target="_blank"
                       class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition text-center border border-slate-700">
                        Falar com a Oficina no WhatsApp
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
