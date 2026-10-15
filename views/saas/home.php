<?php
$saasSettings = $saasSettings ?? [];
$heroBadge = $saasSettings['saas_hero_badge'] ?? 'Software 100% Gratuito para Sempre &bull; Sem Limite de OS';
$heroTitle = $saasSettings['saas_hero_title'] ?? 'A Gestão Mais <span class="bg-gradient-to-r from-indigo-400 via-indigo-300 to-emerald-400 bg-clip-text text-transparent">Rápida e Sem Travamentos</span> para sua Assistência.';
$heroSubtitle = $saasSettings['saas_hero_subtitle'] ?? 'Ordens de serviço dinâmicas por nicho, PDV estilo totem touch-screen, mini QR Code adesivo para garantia, inteligência artificial gratuita para laudos e portal do cliente com aprovação digital. Tudo em carregamento instantâneo.';
$pixKey = $saasSettings['saas_pix_key'] ?? 'apoio@zyrtex.com.br';
$pixBeneficiary = $saasSettings['saas_pix_beneficiary'] ?? 'Equipe de Desenvolvimento Zyrtex';
$pixMessage = $saasSettings['saas_pix_message'] ?? 'O Zyrtex é mantido com muito carinho por desenvolvedores apaixonados pelo ecossistema de assistências técnicas. Se o sistema está economizando tempo e aumentando suas vendas, considere fazer uma contribuição espontânea via Pix de qualquer valor!';
?>
<!-- Hero Section da Plataforma SaaS Zyrtex -->
<section class="relative overflow-hidden pt-12 pb-24 md:pt-20 md:pb-32 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/30 via-transparent to-transparent pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        
        <!-- Badge Destaque -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 mb-6 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span><?= $heroBadge ?></span>
        </div>

        <!-- Headline Principal -->
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight max-w-5xl mx-auto leading-none">
            <?= $heroTitle ?>
        </h1>

        <!-- Subtítulo -->
        <p class="mt-6 text-lg sm:text-xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
            <?= htmlspecialchars($heroSubtitle, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <!-- Botões de Ação Hero -->
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/cadastro" 
               class="w-full sm:w-auto px-8 py-4 rounded-2xl text-base font-extrabold text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-xl shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                <span>Criar Minha Oficina Grátis Agora</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            
            <a href="<?= \Zyrtex\Core\Tenant::publicUrl('demo-assistencia') ?>" 
               class="w-full sm:w-auto px-8 py-4 rounded-2xl text-base font-bold text-slate-200 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Ver Demonstração ao Vivo</span>
            </a>
        </div>

        <!-- Prova Social / Destaques -->
        <div class="mt-12 flex flex-wrap items-center justify-center gap-6 sm:gap-12 text-xs font-semibold text-slate-400">
            <span class="flex items-center gap-1.5">✓ Zero instalação de apps pesados</span>
            <span class="flex items-center gap-1.5">✓ Não pedimos cartão de crédito</span>
            <span class="flex items-center gap-1.5">✓ Backup no seu Google Drive</span>
            <span class="flex items-center gap-1.5">✓ Imprime em bobinas 80mm e 58mm</span>
        </div>

    </div>
</section>

<!-- Seção: Nichos Atendidos (RF02) -->
<section id="nichos" class="py-20 bg-slate-900 border-t border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Personalizado para seu Negócio</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">
                Escolha seu Nicho e Tenha Formulários e Checklists Prontos
            </h2>
            <p class="text-slate-400 text-sm mt-3">
                Ao se cadastrar, toda a estrutura do Zyrtex se molda especificamente ao seu ramo de atuação:
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- Nicho 1: Celulares -->
            <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-2xl mb-4">
                    📱
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Smartphones & Tablets</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Controle de IMEI, padrão/senha de tela, saúde de bateria e checklist completo de touch, câmeras e conectividade.
                </p>
            </div>

            <!-- Nicho 2: Computadores -->
            <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-2xl mb-4">
                    💻
                </div>
                <h3 class="text-lg font-bold text-white mb-2">PCs & Notebooks</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Registro de processador, memória RAM, SSD/HD, carregador entregue junto e teste de cooler, dobradiças e portas USB.
                </p>
            </div>

            <!-- Nicho 3: Automotivo -->
            <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-2xl mb-4">
                    🚗
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Oficina Mecânica & Auto</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Identificação por Placa e Chassi, controle de Quilometragem (KM), nível de combustível e checklist de freios e suspensão.
                </p>
            </div>

            <!-- Nicho 4: Linha Branca -->
            <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 hover:border-indigo-500/50 transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center font-bold text-2xl mb-4">
                    ⚡
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Eletrodomésticos</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Geladeiras, microondas e máquinas de lavar. Controle de tensão (110V/220V), vazamentos e placas de potência.
                </p>
            </div>

        </div>

    </div>
</section>

<!-- Seção: Recursos Revolucionários -->
<section id="recursos" class="py-24 bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Recursos de Outro Nível</span>
            <h2 class="text-3xl sm:text-5xl font-black text-white mt-2">
                Tudo que você sempre quis em um sistema, sem a lentidão habitual.
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <!-- Recurso 1: PDV Totem Fast-Food -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <span class="text-3xl mb-4 block">🍔</span>
                    <h3 class="text-xl font-bold text-white mb-2">PDV Estilo Totem Fast-Food</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Interface feita sob medida para monitores grandes e telas touch screen. Cards gigantes de produtos, toque rápido e checkout em segundos no balcão.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-indigo-400 font-semibold">
                    ✓ Otimizado para telas sensíveis ao toque
                </div>
            </div>

            <!-- Recurso 2: Selo Adesivo QR Code -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <span class="text-3xl mb-4 block">🏷️</span>
                    <h3 class="text-xl font-bold text-white mb-2">Selo QR Code & Lacre de Garantia</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Imprima mini-adesivos para colar no aparelho ou peças. Ao escanear o QR Code pelo celular, cliente ou técnico acessam imediatamente a OS completa.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-indigo-400 font-semibold">
                    ✓ Formatos 50x30mm, 40x25mm e A4
                </div>
            </div>

            <!-- Recurso 3: IA que Traduz Laudos -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <span class="text-3xl mb-4 block">🤖</span>
                    <h3 class="text-xl font-bold text-white mb-2">Laudo Inteligente com IA Gratuita</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Você digita o diagnóstico técnico com termos difíceis e a IA (Hugging Face gratuita) traduz em linguagem simples e educada para enviar no WhatsApp do cliente leigo.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-indigo-400 font-semibold">
                    ✓ Zero custo de inteligência artificial
                </div>
            </div>

            <!-- Recurso 4: Portal do Cliente com Aceite Legal -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <span class="text-3xl mb-4 block">📱</span>
                    <h3 class="text-xl font-bold text-white mb-2">Portal do Cliente com Aceite Digital</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Seu cliente recebe o link, vê as peças orçadas e clica em "Aprovar Orçamento". O sistema grava IP, data e hora com validade probatória de autorização.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-indigo-400 font-semibold">
                    ✓ Histórico de manutenções e faturas
                </div>
            </div>

            <!-- Recurso 5: Impressão Multiformato -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <span class="text-3xl mb-4 block">🖨️</span>
                    <h3 class="text-xl font-bold text-white mb-2">Impressão Multiformato</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Escolha seu formato preferido em 1 clique: Bobina térmica larga 80mm, mini bobina 58mm (maquininha) ou folha A4 com 2 vias e termos de abandono (CDC).
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-indigo-400 font-semibold">
                    ✓ Compatível com todas as impressoras
                </div>
            </div>

            <!-- Recurso 6: Prontuário Antifraude -->
            <div class="bg-gradient-to-b from-slate-900 to-slate-950 p-8 rounded-3xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <span class="text-3xl mb-4 block">🛡️</span>
                    <h3 class="text-xl font-bold text-white mb-2">Prontuário Antifraude</h3>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        Histórico completo de cada aparelho rastreado por IMEI, Serial ou Placa. Saiba se o equipamento já foi aberto antes, por qual técnico e se está na garantia.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800 text-xs text-indigo-400 font-semibold">
                    ✓ Blindagem contra golpes de garantia
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Seção: Por que o Zyrtex é 100% Gratuito? (Transparência Total) -->
<section id="por-que-gratis" class="py-20 bg-slate-900 border-t border-b border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        
        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Transparência & Comunidade</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mt-2">
            Como o Zyrtex Pode Ser Gratuito Sem Pegadinhas?
        </h2>
        
        <div class="mt-8 space-y-4 text-slate-300 text-sm leading-relaxed text-left bg-slate-950 p-8 rounded-3xl border border-slate-800">
            <p>
                💡 <strong>Nossa Filosofia:</strong> Acreditamos que nenhuma assistência técnica ou oficina deve pagar mensalidades abusivas de R$ 150 a R$ 300 por mês apenas para emitir ordens de serviço e vender no balcão.
            </p>
            <p>
                📢 <strong>Como mantemos os servidores ligados?</strong> Nosso modelo é o <strong>Freemium Sustentável</strong>:
            </p>
            <ul class="space-y-2 list-disc list-inside text-slate-400 pl-2">
                <li><strong class="text-white">Anúncios Discretos (Google AdSense):</strong> Inseridos de forma não invasiva no sistema gratuito.</li>
                <li><strong class="text-white">Recursos Premium Opcionais:</strong> Quem quiser navegar 100% livre de qualquer anúncio no sistema paga uma anuidade acessível de apenas R$ 99/ano.</li>
                <li><strong class="text-white">Doações da Comunidade:</strong> Usuários que faturam alto com o sistema e desejam apoiar o projeto nos pagam um café via Pix voluntário.</li>
            </ul>
            <p class="text-xs text-emerald-400 pt-2 font-semibold">
                Resultado: Você usa um sistema completo, seguro e rápido sem gastar nenhum centavo do seu fluxo de caixa.
            </p>
        </div>

    </div>
</section>

<!-- Seção: Tabela Comparativa de Planos (Plano Anual Sem Anúncios) -->
<section id="planos" class="py-24 bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">Preços Justos & Acessíveis</span>
            <h2 class="text-3xl sm:text-5xl font-black text-white mt-2">
                Escolha o Plano Ideal para sua Oficina
            </h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            
            <!-- Plano 1: Gratuito para Sempre -->
            <div class="bg-slate-900 rounded-3xl p-8 border border-slate-800 flex flex-col justify-between relative">
                <div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-800 text-slate-300 inline-block mb-4">
                        Plano Popular
                    </span>
                    <h3 class="text-2xl font-bold text-white">Zyrtex Free</h3>
                    <p class="text-xs text-slate-400 mt-1">Perfeito para quem está começando ou quer economizar.</p>
                    
                    <div class="my-6">
                        <span class="text-4xl font-black text-white">R$ 0</span>
                        <span class="text-xs text-slate-400">/ mês para sempre</span>
                    </div>

                    <ul class="space-y-3 text-xs text-slate-300">
                        <li class="flex items-center gap-2">✓ Ordens de Serviço Ilimitadas</li>
                        <li class="flex items-center gap-2">✓ PDV Totem Fast-Food Touch Screen</li>
                        <li class="flex items-center gap-2">✓ Prontuário Antifraude por Serial/IMEI</li>
                        <li class="flex items-center gap-2">✓ Selos Adesivos QR Code (Lacre de Garantia)</li>
                        <li class="flex items-center gap-2">✓ Laudos com Inteligência Artificial Grátis</li>
                        <li class="flex items-center gap-2">✓ Subdomínio Grátis: <code class="text-indigo-400">suaoficina.zyrtex.com.br</code></li>
                        <li class="flex items-center gap-2 text-slate-500">• Inserção de anúncios discretos no painel</li>
                    </ul>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-800">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/cadastro" 
                       class="w-full py-3.5 rounded-xl font-bold text-sm text-center text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition block">
                        Começar Grátis Agora
                    </a>
                </div>
            </div>

            <!-- Plano 2: Plano Anual Sem Anúncios (Premium) -->
            <div class="bg-gradient-to-b from-indigo-950/50 via-slate-900 to-slate-900 rounded-3xl p-8 border-2 border-indigo-500/50 flex flex-col justify-between relative shadow-2xl shadow-indigo-500/10">
                <div class="absolute -top-3.5 right-8 px-3 py-1 rounded-full text-xs font-extrabold bg-gradient-to-r from-indigo-500 to-emerald-400 text-white shadow-md">
                    RECOMENDADO PARA OFICINAS
                </div>

                <div>
                    <span class="text-xs font-bold px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 inline-block mb-4">
                        Experiência Limpa (Ad-Free)
                    </span>
                    <h3 class="text-2xl font-bold text-white">Plano Anual Sem Anúncios</h3>
                    <p class="text-xs text-slate-400 mt-1">Sua assistência técnica sem distrações ou propagandas.</p>
                    
                    <div class="my-6">
                        <span class="text-4xl font-black text-white">R$ 197</span>
                        <span class="text-xs text-slate-400">/ ano único (menos de R$ 0,55/dia)</span>
                    </div>

                    <ul class="space-y-3 text-xs text-slate-200">
                        <li class="flex items-center gap-2 text-emerald-400 font-semibold">✓ ZERO Anúncios na vitrine, no rastreio de OS e no portal</li>
                        <li class="flex items-center gap-2 text-emerald-400 font-semibold">✓ Subdomínio exclusivo: <code class="text-white">suaoficina.zyrtex.com.br</code></li>
                        <li class="flex items-center gap-2">✓ URL amigável: <code class="text-slate-300">zyrtex.com.br/suaoficina</code></li>
                        <li class="flex items-center gap-2">✓ Alterações de nome/slug ilimitadas e sem taxas</li>
                        <li class="flex items-center gap-2">✓ Selo de Oficina Verificada & maior credibilidade</li>
                        <li class="flex items-center gap-2">✓ Ícone PWA: instalável na tela inicial do celular</li>
                        <li class="flex items-center gap-2">✓ Suporte prioritário via WhatsApp</li>
                        <li class="flex items-center gap-2 text-slate-400">✓ Garantia de estorno de 7 dias (CDC)</li>
                    </ul>
                </div>

                <div class="mt-8 pt-6 border-t border-slate-800">
                    <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/cadastro?plan=anual" 
                       class="w-full py-3.5 rounded-xl font-bold text-sm text-center text-white bg-gradient-to-r from-indigo-600 to-emerald-500 hover:from-indigo-500 hover:to-emerald-400 shadow-lg shadow-indigo-500/25 transition block">
                        Assinar Plano Anual Sem Anúncios
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Seção: "Apoie o Zyrtex / Buy Me a Coffee" (Doações Voluntárias via Pix) -->
<section id="apoie" class="py-20 bg-gradient-to-b from-slate-900 to-slate-950 border-t border-slate-800">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="bg-gradient-to-br from-slate-900 via-indigo-950/40 to-slate-900 p-8 sm:p-12 rounded-3xl border border-indigo-500/30 text-center relative overflow-hidden shadow-2xl">
            <div class="max-w-2xl mx-auto">
                <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500/20 text-3xl mb-4 border border-amber-500/30">
                    ☕
                </span>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-white">
                    Pague um Café para os Desenvolvedores! (Buy Me a Coffee)
                </h3>
                <p class="mt-3 text-sm text-slate-300 leading-relaxed">
                    <?= htmlspecialchars($pixMessage, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <!-- Box Chave Pix Copia e Cola -->
                <div class="mt-8 p-4 bg-slate-950 rounded-2xl border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-left">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-400 block">Chave Pix Oficial de Apoio</span>
                        <code class="text-sm font-mono text-slate-200 select-all"><?= htmlspecialchars($pixKey, ENT_QUOTES, 'UTF-8') ?></code>
                    </div>
                    <span class="px-4 py-2 rounded-xl text-xs font-bold bg-indigo-600/30 text-indigo-300 border border-indigo-500/40">
                        Chave: <?= htmlspecialchars($pixKey, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>

                <p class="mt-4 text-xs text-slate-400">
                    Toda doação é reinvestida diretamente em servidores dedicados mais rápidos e novas funcionalidades gratuitas. Obrigado por fazer parte da comunidade! ❤️
                </p>
            </div>
        </div>

    </div>
</section>

<!-- CTA Final de Conversão -->
<section class="py-20 bg-slate-950 text-center border-t border-slate-900">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">
            Pronto para revolucionar o atendimento da sua assistência?
        </h2>
        <p class="mt-4 text-base text-slate-400 max-w-2xl mx-auto">
            Cadastre-se agora mesmo, escolha o nicho da sua loja e emita sua primeira Ordem de Serviço em menos de 2 minutos.
        </p>
        <div class="mt-8">
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>/cadastro" 
               class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl text-base font-extrabold text-white bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-xl shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5">
                <span>Criar Minha Oficina Grátis Agora</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>
