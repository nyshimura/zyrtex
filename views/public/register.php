<?php
/**
 * View: Cadastro de Nova Oficina (Onboarding SaaS)
 */
$csrf = \Zyrtex\Core\CSRF::token();
$niches = $niches ?? [];
$old = $old ?? [];
?>

<div class="py-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto">
    
    <!-- Cabeçalho -->
    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 mb-3">
            ✨ Plataforma 100% Gratuita e Ilimitada
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
            Cadastre sua Assistência Técnica
        </h1>
        <p class="mt-3 text-base text-slate-400 max-w-2xl mx-auto">
            Crie sua conta agora mesmo. Em menos de 1 minuto você terá sua Landing Page no ar, seu subdomínio exclusivo e o sistema completo pronto para emitir Ordens de Serviço.
        </p>
    </div>

    <!-- Card Principal do Formulário -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">
        
        <form action="<?= \Zyrtex\Core\Response::url('/cadastro') ?>" method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <!-- SEÇÃO 1: Escolha do Nicho -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-base font-bold text-white">
                        1. Qual é o segmento principal da sua assistência? <span class="text-rose-500">*</span>
                    </label>
                </div>
                <p class="text-xs text-slate-400 mb-5">
                    O nicho define todos os campos técnicos, checklists preventivos e termos de garantia da sua OS.
                </p>

                <!-- Cards dos Nichos -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php 
                    $selectedNiche = $old['niche'] ?? 'cellphones';
                    foreach ($niches as $code => $info): 
                        $isSelected = ($selectedNiche === $code);
                    ?>
                        <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 <?= $isSelected ? 'border-indigo-500 bg-indigo-950/30' : 'border-slate-800 bg-slate-950/50 hover:border-slate-700' ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-lg">
                                        <?php if ($code === 'cellphones'): ?>📱<?php elseif ($code === 'computers'): ?>💻<?php elseif ($code === 'automotive'): ?>🚗<?php else: ?>🔌<?php endif; ?>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($info['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <span class="text-[11px] text-slate-400">Identificador: <?= htmlspecialchars($info['identifier_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                                <input type="radio" name="niche" value="<?= $code ?>" <?= $isSelected ? 'checked' : '' ?> required
                                       class="w-4 h-4 text-indigo-600 border-slate-700 focus:ring-indigo-500">
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-800/80 text-[11px] text-slate-400">
                                <strong>Checklist:</strong> <?= count($info['checklist']) ?> verificações técnicas personalizadas.
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SEÇÃO 2: Dados da Oficina e Subdomínio -->
            <div class="pt-6 border-t border-slate-800 space-y-5">
                <h2 class="text-base font-bold text-white">
                    2. Dados da Oficina e Endereço Web
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="business_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Nome da Assistência / Oficina <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="business_name" name="business_name" required
                               value="<?= htmlspecialchars($old['business_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Ex: Smart Tech Assistência Especializada"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="whatsapp" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            WhatsApp de Atendimento <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="whatsapp" name="whatsapp" required
                               value="<?= htmlspecialchars($old['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Ex: 11999998888 (com DDD)"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <!-- Subdomínio Exclusivo -->
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                        Escolha seu Subdomínio Exclusivo (URL Pública) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center rounded-xl bg-slate-950 border border-slate-700 overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 transition-all">
                        <span class="pl-4 pr-1 text-slate-500 text-sm font-mono select-none">https://</span>
                        <input type="text" id="slug" name="slug" required pattern="[a-z0-9-]+" minlength="3" maxlength="40"
                               value="<?= htmlspecialchars($old['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="sua-oficina"
                               autocomplete="off" spellcheck="false"
                               class="flex-1 py-3 px-2 bg-transparent text-indigo-400 font-semibold placeholder-slate-600 focus:outline-none text-sm font-mono lowercase">
                        <span class="pr-4 pl-1 text-slate-400 text-sm font-mono select-none font-bold">.zyrtex.com.br</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5 flex items-center justify-between">
                        <span>Apenas letras minúsculas, números e traços. Espaços e acentos são convertidos automaticamente.</span>
                    </p>
                </div>
            </div>

            <!-- SEÇÃO 3: Usuário Administrador (Dono) -->
            <div class="pt-6 border-t border-slate-800 space-y-5">
                <h2 class="text-base font-bold text-white">
                    3. Seus Dados de Acesso (Conta Master)
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="owner_name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Seu Nome Completo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="owner_name" name="owner_name" required
                               value="<?= htmlspecialchars($old['owner_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Ex: Carlos Silva"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Seu E-mail Corporativo <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="seuemail@oficina.com.br"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Criar Senha Segura <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required minlength="6"
                               placeholder="Mínimo 6 caracteres"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label for="password_confirm" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            Confirmar Senha <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="6"
                               placeholder="Repita a senha criada"
                               class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>

            <!-- Termos de Uso, Mercado Pago e Política de Cookies (Aceite Legal Obrigatório) -->
            <div class="pt-4 border-t border-slate-800">
                <label class="flex items-start gap-3 cursor-pointer group">
                    <input type="checkbox" name="terms" value="1" required
                           class="w-4 h-4 mt-0.5 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <span class="text-xs text-slate-400 leading-relaxed group-hover:text-slate-300 transition">
                        Declaro que li e concordo integralmente com os 
                        <a href="<?= \Zyrtex\Core\Response::url('/termos') ?>" target="_blank" class="text-indigo-400 hover:text-indigo-300 font-bold underline">Termos de Uso, Política de Privacidade e Cookies</a>. 
                        Estou ciente de que o plano gratuito é viabilizado por anúncios Google AdSense e doações voluntárias, concordo com o processamento de pagamentos via Mercado Pago e assumo o papel de Controlador dos dados dos clientes da minha oficina perante a LGPD.
                    </span>
                </label>
            </div>

            <!-- Botão de Submissão -->
            <div>
                <button type="submit"
                        class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-extrabold text-base shadow-xl shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200 transform hover:-translate-y-0.5">
                    Criar Minha Oficina Grátis e Entrar no Painel &rarr;
                </button>
            </div>

        </form>

        <!-- Link para quem já tem conta -->
        <div class="text-center mt-6 pt-6 border-t border-slate-800/80">
            <p class="text-sm text-slate-400">
                Já possui uma assistência cadastrada?
                <a href="<?= \Zyrtex\Core\Response::url('/login') ?>" class="font-bold text-indigo-400 hover:text-indigo-300 ml-1 underline decoration-indigo-500/50 underline-offset-4">
                    Fazer Login
                </a>
            </p>
        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('business_name');
    const slugInput = document.getElementById('slug');
    let userEditedSlug = <?= !empty($old['slug']) ? 'true' : 'false' ?>;

    function sanitizeSlug(val) {
        return val
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Remove acentos (ç, ã, é -> c, a, e)
            .toLowerCase()                                      // Força minúsculas
            .replace(/[^a-z0-9]+/g, '-')                       // Substitui espaços e caracteres especiais por traço
            .replace(/^-+/, '')                                // Remove traço inicial
            .replace(/-{2,}/g, '-');                           // Remove múltiplos traços seguidos (--)
    }

    if (slugInput) {
        slugInput.addEventListener('input', function() {
            userEditedSlug = true;
            slugInput.value = sanitizeSlug(slugInput.value);
        });

        slugInput.addEventListener('blur', function() {
            slugInput.value = slugInput.value.replace(/-+$/, ''); // Remove traço no final ao sair do campo
        });
    }

    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (!userEditedSlug || slugInput.value.trim() === '') {
                slugInput.value = sanitizeSlug(nameInput.value).replace(/-+$/, '');
            }
        });
    }
});
</script>
