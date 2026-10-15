<?php
/**
 * View: Portal do Cliente (RF15)
 * Área exclusiva e autenticada do cliente final para consulta de histórico de aparelhos,
 * aprovação digital de orçamentos em 1 clique e pagamento Pix por OS individual ou agrupada.
 */
$csrf = \Zyrtex\Core\CSRF::token();
$tenant = $tenant ?? [];
$client = $client ?? null;
$orders = $orders ?? [];
$pendingOrders = $pendingOrders ?? [];
$initialSelectedIds = $initialSelectedIds ?? [];
$initialPixTotal = $initialPixTotal ?? 0.0;
$initialOsNumbersStr = $initialOsNumbersStr ?? '';
$initialPixPayload = $initialPixPayload ?? '';
$initialQrCodeUrl = $initialQrCodeUrl ?? '';
$initialWhatsAppUrl = $initialWhatsAppUrl ?? '';
$totalPending = $totalPending ?? 0.0;
$totalPaid = $totalPaid ?? 0.0;
$activeCount = $activeCount ?? 0;
$nicheConfig = $nicheConfig ?? [];

$statusMap = [
    'budget' => ['label' => 'Orçamento Gerado', 'class' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30'],
    'approved' => ['label' => 'Aprovado / Na Fila', 'class' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/30'],
    'in_progress' => ['label' => 'Em Manutenção na Bancada', 'class' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/30'],
    'waiting_parts' => ['label' => 'Aguardando Peça', 'class' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30'],
    'ready' => ['label' => 'Pronto para Retirada', 'class' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30'],
    'delivered' => ['label' => 'Entregue / Concluído', 'class' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30'],
    'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30'],
];

$cleanShopPhone = preg_replace('/\D/', '', $tenant['whatsapp_number'] ?? ($tenant['phone'] ?? ''));
$pixEndpoint = \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal/gerar-pix');
$statusEndpoint = \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal/status-pagamento');
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <?php if (!$client): ?>
        <!-- ================================================================= -->
        <!-- TELA DE LOGIN DO CLIENTE (NÃO AUTENTICADO)                       -->
        <!-- ================================================================= -->
        <div class="max-w-md mx-auto">
            
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-xl shadow-slate-950/5 space-y-6">
                
                <!-- Cabeçalho do Card de Login -->
                <div class="text-center space-y-2">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl shadow-inner">
                        🛡️
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        Área Exclusiva do Cliente
                    </span>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                        Portal do Cliente
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        <?= htmlspecialchars($tenant['business_name'] ?? 'Assistência Técnica', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>

                <!-- Formulário de Autenticação -->
                <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal/login') ?>" method="POST" class="space-y-4">
                    <?= \Zyrtex\Core\CSRF::field() ?>

                    <!-- Campo: CPF ou E-mail -->
                    <div class="space-y-1.5">
                        <label for="portal_login" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            CPF ou E-mail Cadastrado
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text" id="portal_login" name="login" required autocomplete="username"
                                   placeholder="Seu CPF (só números ou formatado) ou E-mail"
                                   class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                        <p class="text-[10px] text-slate-400">Digite seu CPF cadastrado no balcão ou seu e-mail de ativação.</p>
                    </div>

                    <!-- Campo: Senha de Acesso -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="portal_password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                Senha de Acesso
                            </label>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="portal_password" name="password" required autocomplete="current-password"
                                   placeholder="Digite sua senha cadastrada"
                                   class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            <button type="button" onclick="togglePasswordVisibility('portal_password', this)"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition text-sm"
                                    title="Exibir ou ocultar senha">
                                👁️
                            </button>
                        </div>
                    </div>

                    <!-- Botão de Acesso -->
                    <button type="submit"
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-indigo-600 hover:bg-indigo-500 shadow-lg shadow-indigo-600/25 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <span>Entrar no Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>

                <!-- Divisor / Acesso Rápido por QR Code -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3 text-center">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Quer apenas acompanhar uma Ordem de Serviço sem senha?
                    </p>
                    <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio') ?>"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200/60 dark:border-indigo-800/60 transition">
                        <span>📱 Rastrear por QR Code / Código de OS &rarr;</span>
                    </a>
                </div>

                <!-- Suporte da Oficina -->
                <?php if (!empty($cleanShopPhone)): ?>
                    <div class="pt-2 text-center">
                        <a href="https://wa.me/55<?= $cleanShopPhone ?>?text=<?= urlencode('Olá! Preciso de ajuda para acessar o Portal do Cliente.') ?>" target="_blank"
                           class="text-[11px] text-slate-400 hover:text-emerald-500 transition inline-flex items-center gap-1">
                            <span>💬 Esqueceu sua senha ou precisa de ajuda? Fale no WhatsApp</span>
                        </a>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Benefícios em destaque -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6 text-left">
                <div class="p-3.5 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <span class="text-base">⚡</span>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Aprovação Rápida</h4>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Autorize o reparo direto no seu celular.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <span class="text-base">📸</span>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Fotos da Bancada</h4>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Veja imagens do defeito e peças trocadas.</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-1">
                    <span class="text-base">💳</span>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">Pix por OS</h4>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Selecione quais OSs deseja pagar no Pix.</p>
                </div>
            </div>

        </div>

    <?php else: ?>
        <!-- ================================================================= -->
        <!-- PAINEL DO CLIENTE AUTENTICADO                                     -->
        <!-- ================================================================= -->

        <!-- Topbar / Boas-vindas do Portal -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-700 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Cliente Autenticada
                    </span>
                    <span class="text-xs text-slate-400">&bull; <?= htmlspecialchars($client['document'] ?? $client['email'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Olá, <?= htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8') ?>!
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Acompanhe todos os seus aparelhos, aprove orçamentos em 1 clique e realize pagamentos na <strong class="text-slate-700 dark:text-slate-200"><?= htmlspecialchars($tenant['business_name'] ?? 'Oficina', ENT_QUOTES, 'UTF-8') ?></strong>.
                </p>
            </div>

            <!-- Botões de Ação do Usuário -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal/sair') ?>"
                   class="px-4 py-2.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-bold transition flex items-center gap-1.5">
                    <span>🚪 Sair da Conta</span>
                </a>
            </div>
        </div>

        <!-- Cards de Resumo Rápido (KPIs do Cliente) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Meus Aparelhos</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white font-mono mt-0.5 block">
                        <?= count($orders) ?>
                    </span>
                    <span class="text-[10px] text-slate-500">Ordens de serviço registradas</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-bold">
                    📱
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Em Andamento</span>
                    <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-mono mt-0.5 block">
                        <?= $activeCount ?>
                    </span>
                    <span class="text-[10px] text-slate-500">Serviços ativos na oficina</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-bold">
                    ⚙️
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Faturas em Aberto</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block">
                        R$ <?= number_format($totalPending, 2, ',', '.') ?>
                    </span>
                    <span class="text-[10px] text-slate-500"><?= count($pendingOrders) ?> OS aguardando pagamento</span>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold">
                    💳
                </div>
            </div>

        </div>

        <!-- Slot Estratégico de Publicidade no Portal do Cliente -->
        <?php \Zyrtex\Core\View::partial('adsense_slot', ['tenant' => $tenant, 'slot_context' => 'portal']); ?>

        <!-- Navegação em Abas do Portal (Aparelhos / Extrato & Pagamentos Pix) -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <button type="button" onclick="switchPortalTab('aparelhos')" id="portal-tab-btn-aparelhos"
                    class="portal-tab-btn px-4 py-2.5 rounded-xl font-bold text-xs transition flex items-center gap-2 bg-indigo-600 text-white shadow-sm">
                <span>📱 Meus Aparelhos & Histórico</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-white/20 text-white font-mono"><?= count($orders) ?></span>
            </button>

            <button type="button" onclick="switchPortalTab('financeiro')" id="portal-tab-btn-financeiro"
                    class="portal-tab-btn px-4 py-2.5 rounded-xl font-bold text-xs transition flex items-center gap-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <span>💳 Pagamento Pix por OS & Extrato</span>
                <?php if ($totalPending > 0): ?>
                    <span class="px-2 py-0.5 rounded-full text-[10px] bg-amber-500/20 text-amber-500 font-mono font-bold"><?= count($pendingOrders) ?> pendente(s)</span>
                <?php endif; ?>
            </button>
        </div>

        <!-- ================================================================= -->
        <!-- ABA 1: MEUS APARELHOS & HISTÓRICO DE ORDENS DE SERVIÇO             -->
        <!-- ================================================================= -->
        <div id="portal-tab-aparelhos" class="portal-tab-pane space-y-4">
            
            <?php if (empty($orders)): ?>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-10 text-center text-slate-400 space-y-2 border border-slate-200 dark:border-slate-700">
                    <p class="text-base font-bold text-slate-900 dark:text-white">Nenhum aparelho encontrado para este cadastro.</p>
                    <p class="text-xs text-slate-500">Se você acabou de deixar seu aparelho no balcão, aguarde alguns instantes até a equipe cadastrar a Ordem de Serviço.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($orders as $ord): 
                        $st = $statusMap[$ord['status']] ?? ['label' => $ord['status'], 'class' => 'bg-slate-100 text-slate-600 border-slate-300'];
                        $isBudget = ($ord['status'] === 'budget');
                        $hasPendingParts = ((int)$ord['pending_parts_count'] > 0);
                        $needsClientAction = ($isBudget || $hasPendingParts);
                        $isPendingPayment = ($ord['payment_status'] !== 'paid');
                    ?>
                        <div class="bg-white dark:bg-slate-800 rounded-2xl border <?= $needsClientAction ? 'border-amber-400 dark:border-amber-500/60 shadow-md ring-2 ring-amber-400/20' : 'border-slate-200/80 dark:border-slate-700' ?> overflow-hidden transition hover:border-indigo-500/40">
                            
                            <!-- Cabeçalho do Card da OS -->
                            <div class="p-5 sm:p-6 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-lg shrink-0">
                                            <?= $needsClientAction ? '⚠️' : '🔧' ?>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-black text-lg text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($ord['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold border <?= $st['class'] ?>">
                                                    <?= $st['label'] ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Entrada em <?= date('d/m/Y \à\s H:i', strtotime($ord['created_at'])) ?> &bull; Garantia de <?= (int)$ord['warranty_days'] ?> dias
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right flex sm:flex-col justify-between items-center sm:items-end">
                                        <span class="text-[11px] uppercase tracking-wider text-slate-400 block font-semibold">Valor do Reparo:</span>
                                        <span class="text-xl font-black text-slate-900 dark:text-white font-mono">
                                            R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Detalhes do Aparelho & Defeito -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900 text-xs">
                                    <div>
                                        <span class="text-slate-400 block font-semibold">Equipamento / Modelo:</span>
                                        <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($ord['equipment_info'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block font-semibold"><?= htmlspecialchars($nicheConfig['identifier_name'] ?? 'IMEI / Serial', ENT_QUOTES, 'UTF-8') ?>:</span>
                                        <code class="font-mono text-indigo-600 dark:text-indigo-400 font-bold"><?= htmlspecialchars($ord['identifier_code'], ENT_QUOTES, 'UTF-8') ?></code>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block font-semibold">Situação Financeira:</span>
                                        <span class="<?= $ord['payment_status'] === 'paid' ? 'text-emerald-500 font-bold' : 'text-amber-500 font-semibold' ?>">
                                            <?= $ord['payment_status'] === 'paid' ? '✓ Pago / Quitado' : '⏳ Pendente na Retirada' ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if (!empty($ord['simplified_diagnosis_ai'])): ?>
                                    <div class="p-4 rounded-xl bg-purple-500/10 border border-purple-500/20 text-xs space-y-1">
                                        <span class="text-purple-600 dark:text-purple-400 font-bold flex items-center gap-1.5 text-[11px] uppercase tracking-wider">
                                            <span>💡</span> Laudo Técnico Explicado:
                                        </span>
                                        <p class="text-slate-700 dark:text-slate-300 leading-relaxed font-medium">
                                            <?= nl2br(htmlspecialchars($ord['simplified_diagnosis_ai'], ENT_QUOTES, 'UTF-8')) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <!-- Alerta & Botão de Aprovação em 1 Clique (RF15) -->
                                <?php if ($isBudget): ?>
                                    <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex flex-col sm:flex-row items-center justify-between gap-3">
                                        <div class="text-xs text-amber-800 dark:text-amber-200">
                                            <p class="font-extrabold text-sm text-amber-900 dark:text-amber-300">Orçamento Aguardando Sua Aprovação</p>
                                            <p class="mt-0.5">Nossos técnicos finalizaram o diagnóstico. Autorize para iniciarmos o reparo imediatamente.</p>
                                        </div>
                                        <form action="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/portal/aprovar') ?>" method="POST"
                                              onsubmit="return confirm('Confirma a aprovação deste orçamento no valor de R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>?');"
                                              class="shrink-0 w-full sm:w-auto">
                                            <?= \Zyrtex\Core\CSRF::field() ?>
                                            <input type="hidden" name="os_id" value="<?= (int)$ord['id'] ?>">
                                            <button type="submit" 
                                                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                                <span>✓ Aprovar Orçamento (1 Clique)</span>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>

                                <!-- Botões de Ação da OS -->
                                <div class="pt-2 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 dark:border-slate-700/60">
                                    <span class="text-[11px] text-slate-400">
                                        <?= (int)$ord['media_count'] ?> foto(s) anexada(s) no prontuário
                                    </span>

                                    <div class="flex flex-wrap items-center gap-2">
                                        <?php if ($isPendingPayment): ?>
                                            <!-- Botão de Pagamento Exclusivo desta OS -->
                                            <button type="button" onclick="paySpecificOs(<?= (int)$ord['id'] ?>)"
                                                    class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 transition flex items-center gap-1.5 shadow-sm">
                                                <span>💳 Pagar esta OS via Pix (R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>) &rarr;</span>
                                            </button>
                                        <?php endif; ?>

                                        <a href="<?= \Zyrtex\Core\Tenant::publicUrl($tenant['slug'] ?? null, '/rastreio?os=' . urlencode($ord['os_number'])) ?>" 
                                           class="px-4 py-2 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 transition flex items-center gap-1.5">
                                            <span>🔍 Ver Prontuário & Fotos &rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- ================================================================= -->
        <!-- ABA 2: EXTRATO FINANCEIRO & PAGAMENTO PIX POR OS SELECIONADA      -->
        <!-- ================================================================= -->
        <div id="portal-tab-financeiro" class="portal-tab-pane hidden space-y-6">

            <?php if (empty($pendingOrders)): ?>
                <!-- Estado: Nenhuma OS Pendente -->
                <div class="bg-gradient-to-br from-emerald-950/20 via-slate-900 to-slate-950 p-8 sm:p-10 rounded-3xl border border-emerald-500/30 text-center space-y-3">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 mx-auto flex items-center justify-center text-3xl">
                        ✓
                    </div>
                    <h3 class="text-xl font-bold text-white">Tudo em dia! Nenhuma fatura pendente</h3>
                    <p class="text-xs sm:text-sm text-slate-400 max-w-md mx-auto">
                        Todas as suas ordens de serviço anteriores já foram quitadas. O total acumulado de pagamentos realizados é de <strong class="text-emerald-400 font-mono">R$ <?= number_format($totalPaid, 2, ',', '.') ?></strong>.
                    </p>
                </div>

            <?php else: ?>

                <!-- SEÇÃO INTERATIVA: SELEÇÃO DE ORDENS DE SERVIÇO PARA PAGAR -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-6">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 mb-1">
                                <span>⚡ Pagamento por Ordem de Serviço</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                                Selecione as OSs que deseja Pagar
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Você pode pagar uma OS individual ou marcar várias de uma vez para gerar um Pix único consolidado.
                            </p>
                        </div>

                        <!-- Botões de Ação Rápida de Seleção -->
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="selectAllOs(true)"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 transition">
                                ✓ Marcar Todas
                            </button>
                            <button type="button" onclick="selectAllOs(false)"
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white transition">
                                ✕ Desmarcar
                            </button>
                        </div>
                    </div>

                    <!-- Tabela / Lista de OSs Pendentes com Checkbox -->
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">Pagar</th>
                                    <th class="py-3 px-4">Ordem de Serviço</th>
                                    <th class="py-3 px-4">Equipamento</th>
                                    <th class="py-3 px-4">Entrada</th>
                                    <th class="py-3 px-4">Situação</th>
                                    <th class="py-3 px-4 text-right">Valor da OS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 font-medium">
                                <?php foreach ($pendingOrders as $po): 
                                    $isChecked = in_array((int)$po['id'], $initialSelectedIds, true);
                                    $pst = $statusMap[$po['status']] ?? ['label' => $po['status'], 'class' => 'bg-slate-100 text-slate-700'];
                                ?>
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition cursor-pointer" onclick="toggleOsRowCheckbox(event, <?= (int)$po['id'] ?>)">
                                    <td class="py-3.5 px-4 text-center">
                                        <input type="checkbox" id="os_chk_<?= (int)$po['id'] ?>"
                                               class="os-pix-checkbox w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 cursor-pointer"
                                               value="<?= (int)$po['id'] ?>"
                                               data-id="<?= (int)$po['id'] ?>"
                                               data-number="<?= htmlspecialchars($po['os_number'], ENT_QUOTES, 'UTF-8') ?>"
                                               data-val="<?= (float)$po['total_value'] ?>"
                                               data-model="<?= htmlspecialchars($po['equipment_info'], ENT_QUOTES, 'UTF-8') ?>"
                                               <?= $isChecked ? 'checked' : '' ?>
                                               onchange="onSelectionChanged()">
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($po['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200">
                                        <?= htmlspecialchars($po['equipment_info'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-400">
                                        <?= date('d/m/Y', strtotime($po['created_at'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border <?= $pst['class'] ?>">
                                            <?= $pst['label'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-black text-slate-900 dark:text-white text-sm">
                                        R$ <?= number_format((float)$po['total_value'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Barra de Resumo da Seleção -->
                    <div class="flex flex-col sm:flex-row items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 gap-4">
                        <div>
                            <span class="text-[11px] text-slate-400 uppercase tracking-wider font-bold block">
                                Total das Ordens Selecionadas:
                            </span>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span id="pix_total_amount_display" class="font-mono font-black text-2xl text-emerald-500 dark:text-emerald-400">
                                    R$ <?= number_format($initialPixTotal, 2, ',', '.') ?>
                                </span>
                                <span id="pix_selection_count_label" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 font-mono">
                                    <?= count($initialSelectedIds) ?> OS selecionada(s)
                                </span>
                            </div>
                        </div>

                        <button type="button" onclick="requestPixForSelection()" id="btn_refresh_pix"
                                class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-extrabold uppercase tracking-wider text-white bg-indigo-600 hover:bg-indigo-500 shadow-md shadow-indigo-600/25 transition flex items-center justify-center gap-2">
                            <span id="btn_refresh_icon">⚡</span>
                            <span id="btn_refresh_text">Gerar Pix para Seleção</span>
                        </button>
                    </div>

                </div>

                <!-- CARD DO PIX GERADO DINAMICAMENTE PARA AS OSs SELECIONADAS -->
                <div id="pix_payment_card" class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-emerald-950/40 via-slate-900 to-slate-950 border border-emerald-500/30 space-y-6 shadow-xl">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-800">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 block flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                                Cobrança Pix Gerada com Sucesso
                            </span>
                            <h3 class="text-xl font-bold text-white mt-0.5">
                                Pague com Pix Instantâneo
                            </h3>
                        </div>

                        <div class="text-right">
                            <span class="text-xs text-slate-400 block font-semibold">Valor do Pix:</span>
                            <span id="pix_card_total_display" class="text-3xl font-black text-emerald-400 font-mono tracking-tight">
                                R$ <?= number_format($initialPixTotal, 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>

                    <!-- Chips com as OSs incluídas nesta cobrança Pix -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs text-slate-400 font-semibold">OSs Inclusas neste Pix:</span>
                        <div id="selected_os_chips" class="flex flex-wrap gap-1.5">
                            <?php foreach ($initialSelectedIds as $sid): 
                                foreach ($pendingOrders as $po) {
                                    if ((int)$po['id'] === $sid) {
                                        echo '<span class="px-2.5 py-1 rounded-xl text-[11px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">' . htmlspecialchars($po['os_number'], ENT_QUOTES, 'UTF-8') . ' (R$ ' . number_format((float)$po['total_value'], 2, ',', '.') . ')</span>';
                                    }
                                }
                            endforeach; ?>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center pt-2">
                        
                        <!-- QR Code Pix -->
                        <div class="md:col-span-4 flex flex-col items-center text-center space-y-3">
                            <div class="p-3 bg-white rounded-3xl shadow-2xl inline-block relative group">
                                <img id="pix_qrcode_img" 
                                     src="<?= htmlspecialchars($initialQrCodeUrl, ENT_QUOTES, 'UTF-8') ?>" 
                                     alt="QR Code Pix" 
                                     class="w-48 h-48 rounded-xl object-contain">
                                <div id="pix_qrcode_spinner" class="absolute inset-0 bg-white/90 rounded-3xl flex items-center justify-center hidden">
                                    <div class="w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </div>
                            <span class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                                <span>📷</span> Aponte a câmera do seu banco para escanear
                            </span>
                        </div>

                        <!-- Chave Pix, Copia e Cola & WhatsApp -->
                        <div class="md:col-span-8 space-y-4">
                            
                            <!-- Pix Copia e Cola -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                                    Código Pix Copia e Cola:
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="text" id="pix_copia_cola_input" readonly 
                                           value="<?= htmlspecialchars($initialPixPayload, ENT_QUOTES, 'UTF-8') ?>"
                                           class="flex-1 px-4 py-3 rounded-xl bg-slate-950 border border-slate-700 text-emerald-300 font-mono text-xs select-all focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <button type="button" onclick="copyPixCopiaCola()"
                                            id="btn_copy_pix"
                                            class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider transition shrink-0 flex items-center gap-1.5 shadow-lg shadow-emerald-600/20">
                                        <span>📋</span>
                                        <span id="btn_copy_pix_text">Copiar Código</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Dados do Favorecido e Chave -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3.5 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs text-slate-400">
                                <div>
                                    <span class="text-slate-500 block font-semibold">Favorecido:</span>
                                    <span id="pix_beneficiary_display" class="font-bold text-slate-200">
                                        <?= htmlspecialchars($tenant['pix_beneficiary'] ?? $tenant['business_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block font-semibold">Chave da Oficina:</span>
                                    <span id="pix_key_display" class="font-mono font-bold text-emerald-400">
                                        <?= htmlspecialchars($tenant['pix_key'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Botão Enviar Comprovante no WhatsApp -->
                            <?php if (!empty($cleanShopPhone)): ?>
                                <div class="pt-1">
                                    <a id="pix_whatsapp_link" 
                                       href="<?= htmlspecialchars($initialWhatsAppUrl, ENT_QUOTES, 'UTF-8') ?>" 
                                       target="_blank"
                                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-600 border border-emerald-600 transition shadow-md">
                                        <span>💬 Enviar Comprovante no WhatsApp da Loja</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <!-- Aviso de nenhuma OS selecionada (quando desmarcado tudo) -->
                <div id="pix_empty_selection_alert" class="p-8 rounded-3xl bg-slate-900/60 border border-slate-800 text-center space-y-2 hidden">
                    <span class="text-3xl block">⚠️</span>
                    <h4 class="text-base font-bold text-white">Nenhuma Ordem de Serviço Selecionada</h4>
                    <p class="text-xs text-slate-400">Marque ao menos uma ordem de serviço na lista acima para gerar o Pix correspondente.</p>
                </div>

            <?php endif; ?>

            <!-- Tabela Geral de Extrato de Ordens de Serviço (Histórico Completo) -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700 overflow-hidden shadow-sm">
                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Histórico Geral de Faturas e Serviços</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Histórico detalhado de todas as ordens registradas no seu cadastro</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-slate-400">
                        Total Geral: R$ <?= number_format($totalPaid + $totalPending, 2, ',', '.') ?>
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                        <thead class="bg-slate-50 dark:bg-slate-900 text-slate-500 uppercase font-bold text-[10px] border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-3 px-4">Ordem de Serviço</th>
                                <th class="py-3 px-4">Equipamento</th>
                                <th class="py-3 px-4">Data</th>
                                <th class="py-3 px-4">Situação</th>
                                <th class="py-3 px-4 text-right">Valor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($ord['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?= htmlspecialchars($ord['equipment_info'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500">
                                        <?= date('d/m/Y', strtotime($ord['created_at'])) ?>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <?php if ($ord['payment_status'] === 'paid'): ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                ✓ Pago
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                                ⏳ Pendente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        R$ <?= number_format((float)$ord['total_value'], 2, ',', '.') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    <?php endif; ?>

</div>

<script>
const PIX_ENDPOINT = <?= json_encode($pixEndpoint) ?>;
const STATUS_ENDPOINT = <?= json_encode($statusEndpoint) ?>;
const CSRF_TOKEN = <?= json_encode($csrf) ?>;
let debouncePixTimer = null;
let pollPaymentInterval = null;

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.innerText = '🙈';
    } else {
        input.type = 'password';
        btn.innerText = '👁️';
    }
}

function switchPortalTab(tabId) {
    document.querySelectorAll('.portal-tab-pane').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.portal-tab-btn').forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-sm');
        btn.classList.add('text-slate-500', 'dark:text-slate-400');
    });

    const activePane = document.getElementById('portal-tab-' + tabId);
    if (activePane) activePane.classList.remove('hidden');

    const activeBtn = document.getElementById('portal-tab-btn-' + tabId);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-500', 'dark:text-slate-400');
        activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-sm');
    }
}

// Quando o cliente clica em "Pagar esta OS via Pix" na lista de aparelhos
function paySpecificOs(osId) {
    switchPortalTab('financeiro');

    // Desmarca todas as outras e marca apenas esta OS
    document.querySelectorAll('.os-pix-checkbox').forEach(chk => {
        chk.checked = (parseInt(chk.value, 10) === parseInt(osId, 10));
    });

    onSelectionChanged(true);

    // Scroll suave até a seção do Pix
    setTimeout(() => {
        const card = document.getElementById('pix_payment_card');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('ring-4', 'ring-emerald-400/50');
            setTimeout(() => card.classList.remove('ring-4', 'ring-emerald-400/50'), 1500);
        }
    }, 150);
}

// Clique na linha da tabela para marcar/desmarcar o checkbox
function toggleOsRowCheckbox(event, osId) {
    // Se o clique foi direto no input, não duplica
    if (event.target.tagName.toLowerCase() === 'input') return;
    const chk = document.getElementById('os_chk_' + osId);
    if (chk) {
        chk.checked = !chk.checked;
        onSelectionChanged();
    }
}

// Marcar ou desmarcar todas
function selectAllOs(check) {
    document.querySelectorAll('.os-pix-checkbox').forEach(chk => {
        chk.checked = check;
    });
    onSelectionChanged(true);
}

// Disparado sempre que a seleção de checkboxes é alterada
function onSelectionChanged(immediate = false) {
    const checkedBoxes = Array.from(document.querySelectorAll('.os-pix-checkbox:checked'));
    const totalDisplay = document.getElementById('pix_total_amount_display');
    const cardTotalDisplay = document.getElementById('pix_card_total_display');
    const countLabel = document.getElementById('pix_selection_count_label');
    const chipsContainer = document.getElementById('selected_os_chips');
    const paymentCard = document.getElementById('pix_payment_card');
    const emptyAlert = document.getElementById('pix_empty_selection_alert');

    if (checkedBoxes.length === 0) {
        if (totalDisplay) totalDisplay.innerText = 'R$ 0,00';
        if (countLabel) countLabel.innerText = '0 OS selecionadas';
        if (paymentCard) paymentCard.classList.add('hidden');
        if (emptyAlert) emptyAlert.classList.remove('hidden');
        return;
    }

    if (paymentCard) paymentCard.classList.remove('hidden');
    if (emptyAlert) emptyAlert.classList.add('hidden');

    // Cálculo em tempo real no cliente
    let sum = 0;
    let chipsHtml = '';
    const osIds = [];

    checkedBoxes.forEach(chk => {
        const val = parseFloat(chk.dataset.val || '0');
        const num = chk.dataset.number || '';
        sum += val;
        osIds.push(parseInt(chk.value, 10));
        chipsHtml += `<span class="px-2.5 py-1 rounded-xl text-[11px] font-mono font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">${num} (R$ ${val.toLocaleString('pt-BR', { minimumFractionDigits: 2 })})</span>`;
    });

    const formattedSum = 'R$ ' + sum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (totalDisplay) totalDisplay.innerText = formattedSum;
    if (cardTotalDisplay) cardTotalDisplay.innerText = formattedSum;
    if (countLabel) countLabel.innerText = `${checkedBoxes.length} OS selecionada(s)`;
    if (chipsContainer) chipsContainer.innerHTML = chipsHtml;

    // Dispara requisição ao servidor com debounce
    clearTimeout(debouncePixTimer);
    if (immediate) {
        requestPixForSelection();
    } else {
        debouncePixTimer = setTimeout(() => {
            requestPixForSelection();
        }, 300);
    }
}

// Chama o endpoint POST /portal/gerar-pix para gerar o QR code e payload oficial com os IDs selecionados
function requestPixForSelection() {
    const checkedBoxes = Array.from(document.querySelectorAll('.os-pix-checkbox:checked'));
    if (checkedBoxes.length === 0) return;

    const osIds = checkedBoxes.map(c => parseInt(c.value, 10));
    const spinner = document.getElementById('pix_qrcode_spinner');
    const qrImg = document.getElementById('pix_qrcode_img');
    const copiaColaInput = document.getElementById('pix_copia_cola_input');
    const waLink = document.getElementById('pix_whatsapp_link');

    if (spinner) spinner.classList.remove('hidden');

    const formData = new FormData();
    formData.append('csrf_token', CSRF_TOKEN);
    osIds.forEach(id => formData.append('os_ids[]', id));

    fetch(PIX_ENDPOINT, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (spinner) spinner.classList.add('hidden');
        if (data.success) {
            if (qrImg && data.qrcode_url) qrImg.src = data.qrcode_url;
            if (copiaColaInput && data.pix_code) copiaColaInput.value = data.pix_code;
            if (waLink && data.whatsapp_url) waLink.href = data.whatsapp_url;
            startPaymentStatusPolling(osIds);
        } else if (data.message) {
            alert(data.message);
        }
    })
    .catch(err => {
        if (spinner) spinner.classList.add('hidden');
        console.error('Erro ao gerar Pix:', err);
    });
}

// Monitora em tempo real se o pagamento das OSs foi aprovado
function startPaymentStatusPolling(osIds) {
    clearInterval(pollPaymentInterval);
    if (!osIds || osIds.length === 0) return;

    pollPaymentInterval = setInterval(() => {
        const query = osIds.map(id => 'os_ids[]=' + encodeURIComponent(id)).join('&');
        fetch(STATUS_ENDPOINT + '?' + query, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.all_paid) {
                clearInterval(pollPaymentInterval);
                const banner = document.createElement('div');
                banner.className = 'fixed bottom-6 right-6 z-50 bg-emerald-600 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-3 animate-bounce border border-emerald-400';
                banner.innerHTML = `
                    <div class="text-2xl">🎉</div>
                    <div>
                        <div class="font-bold text-sm">Pagamento Confirmado!</div>
                        <div class="text-xs text-emerald-100">Suas Ordens de Serviço foram quitadas. Atualizando tela...</div>
                    </div>
                `;
                document.body.appendChild(banner);
                setTimeout(() => {
                    window.location.reload();
                }, 1800);
            }
        })
        .catch(() => {});
    }, 4000);
}

// Inicia polling inicial se houver OSs pré-selecionadas na carga da página
document.addEventListener('DOMContentLoaded', () => {
    const checked = Array.from(document.querySelectorAll('.os-pix-checkbox:checked')).map(c => parseInt(c.value, 10));
    if (checked.length > 0) {
        startPaymentStatusPolling(checked);
    }
});

// Copiar código Pix para área de transferência
function copyPixCopiaCola() {
    const input = document.getElementById('pix_copia_cola_input');
    if (!input) return;
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        const textSpan = document.getElementById('btn_copy_pix_text');
        if (textSpan) {
            const orig = textSpan.innerText;
            textSpan.innerText = '✓ Código Copiado!';
            setTimeout(() => { textSpan.innerText = orig; }, 2500);
        }
    });
}
</script>
