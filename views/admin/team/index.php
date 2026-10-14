<?php
/**
 * View: Gestão de Equipe, Colaboradores & Controle de Permissões Granulares (RF03 / ACL)
 */
$csrf = \Zyrtex\Core\CSRF::token();
$tenant = $tenant ?? [];
$teamMembers = $teamMembers ?? [];
$currentUser = $user ?? [];

$roleNames = [
    'owner' => ['label' => 'Proprietário (Admin Geral)', 'badge' => 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30'],
    'manager' => ['label' => 'Gerente de Loja', 'badge' => 'bg-purple-500/20 text-purple-300 border-purple-500/30'],
    'technician' => ['label' => 'Técnico Especialista', 'badge' => 'bg-blue-500/20 text-blue-300 border-blue-500/30'],
    'attendant' => ['label' => 'Atendente / Balcão', 'badge' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'],
];
?>

<div class="space-y-6">

    <!-- Topbar & Botão Novo Membro -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>👥</span>
                <span>Equipe & Controle de Acessos</span>
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Cadastre técnicos e atendentes, personalize fotos de perfil e controle permissões módulo a módulo.
            </p>
        </div>

        <button type="button" onclick="openNewMemberModal()"
                class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2">
            <span>+ Convidar Novo Colaborador</span>
        </button>
    </div>

    <!-- Tabela de Membros da Equipe -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-950/60 text-slate-400 uppercase font-bold text-[10px] tracking-wider">
                        <th class="py-3 px-4">Colaborador</th>
                        <th class="py-3 px-4">Cargo / Função</th>
                        <th class="py-3 px-4">Permissões de Acesso</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    <?php foreach ($teamMembers as $m): 
                        $roleInfo = $roleNames[$m['role']] ?? ['label' => $m['role'], 'badge' => 'bg-slate-800 text-slate-400'];
                        $perms = json_decode($m['permissions_json'] ?? '[]', true) ?: [];
                        $isOwner = ($m['role'] === 'owner');
                        $isActive = ((int)$m['active'] === 1);
                        $initial = mb_strtoupper(mb_substr($m['name'], 0, 1, 'UTF-8'), 'UTF-8');
                    ?>
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Colaborador / Avatar -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-800 border border-slate-700 shrink-0 flex items-center justify-center text-white font-black text-sm">
                                        <?php if (!empty($m['avatar_url'])): ?>
                                            <img src="<?= htmlspecialchars(asset($m['avatar_url']), ENT_QUOTES, 'UTF-8') ?>" 
                                                 alt="<?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span><?= $initial ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block text-sm">
                                            <?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="text-slate-400 text-[11px] font-mono">
                                            <?= htmlspecialchars($m['email'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Cargo -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full font-bold text-[10px] border <?= $roleInfo['badge'] ?>">
                                    <?= $roleInfo['label'] ?>
                                </span>
                            </td>

                            <!-- Permissões Granulares -->
                            <td class="py-3.5 px-4">
                                <?php if ($isOwner): ?>
                                    <span class="text-[11px] font-bold text-indigo-400 flex items-center gap-1">
                                        <span>⚡</span> Acesso Total Irrestrito
                                    </span>
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1.5 max-w-xs">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= !empty($perms['os']) ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800/60 text-slate-600 line-through' ?>">
                                            OS
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= !empty($perms['pos']) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-800/60 text-slate-600 line-through' ?>">
                                            PDV
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= !empty($perms['stock']) ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800/60 text-slate-600 line-through' ?>">
                                            Estoque
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= !empty($perms['clients']) ? 'bg-blue-500/20 text-blue-300' : 'bg-slate-800/60 text-slate-600 line-through' ?>">
                                            Clientes
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= !empty($perms['financial']) ? 'bg-purple-500/20 text-purple-300' : 'bg-slate-800/60 text-slate-600 line-through' ?>">
                                            Financeiro
                                        </span>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= !empty($perms['settings']) ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-800/60 text-slate-600 line-through' ?>">
                                            Config.
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                <?php if ($isActive): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Ativo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Inativo
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Ações -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" 
                                            onclick='openEditMemberModal(<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold text-[11px] transition border border-slate-700">
                                        Editar
                                    </button>

                                    <?php if (!$isOwner && (int)$m['id'] !== (int)$currentUser['id']): ?>
                                        <form action="<?= \Zyrtex\Core\Response::url('/admin/equipe/toggle') ?>" method="POST"
                                              onsubmit="return confirm('Alterar status de acesso deste colaborador?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                            <button type="submit" 
                                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-semibold transition <?= $isActive ? 'text-rose-400 hover:bg-rose-950/40 border border-rose-800/40' : 'text-emerald-400 hover:bg-emerald-950/40 border border-emerald-800/40' ?>">
                                                <?= $isActive ? 'Desativar' : 'Ativar' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ===================================================================== -->
<!-- MODAL: NOVO COLABORADOR COM UPLOAD/LINK DE FOTO E PERMISSÕES           -->
<!-- ===================================================================== -->
<div id="modal-new-member" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4 overflow-y-auto">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl relative my-8">
        
        <button type="button" onclick="closeNewMemberModal()" 
                class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg font-bold">
            ✕
        </button>

        <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span>👤</span>
                <span>Convidar Colaborador</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Defina os dados, foto de perfil e permissões de acesso.</p>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/equipe') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Foto de Perfil / Avatar (Upload Server-Side OU Link Direto com GD) -->
            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                <span class="text-xs font-semibold text-slate-300 block uppercase tracking-wider">Foto do Colaborador (Fase 5):</span>
                
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-slate-900 border border-slate-700 shrink-0 flex items-center justify-center text-slate-500 font-bold text-xs" id="new_avatar_preview_box">
                        <img id="new_avatar_img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <span id="new_avatar_placeholder">Sem Foto</span>
                    </div>

                    <div class="flex-1 space-y-2">
                        <!-- Opção 1: Upload de Arquivo -->
                        <div>
                            <label class="block text-[10px] text-slate-400 font-medium mb-1">Upload do Computador/Celular:</label>
                            <input type="file" name="avatar_file" accept="image/*"
                                   onchange="previewImageInput(this, 'new_avatar_img', 'new_avatar_placeholder')"
                                   class="block w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                        </div>

                        <!-- Opção 2: Link / URL Direto -->
                        <div>
                            <label class="block text-[10px] text-slate-400 font-medium mb-1">Ou Cole a URL da Foto:</label>
                            <input type="url" name="avatar_url" placeholder="https://exemplo.com/foto.jpg"
                                   oninput="previewUrlInput(this.value, 'new_avatar_img', 'new_avatar_placeholder')"
                                   class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Básicos -->
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nome Completo *</label>
                    <input type="text" name="name" required placeholder="Ex: Carlos Silva"
                           class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">E-mail de Login *</label>
                        <input type="email" name="email" required placeholder="carlos@email.com"
                               class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Senha Inicial *</label>
                        <input type="password" name="password" required placeholder="Mínimo 6 dígitos"
                               class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Função / Cargo</label>
                    <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                        <option value="technician">Técnico de Bancada</option>
                        <option value="attendant">Atendente / Vendas Balcão</option>
                        <option value="manager">Gerente</option>
                    </select>
                </div>
            </div>

            <!-- Checklist de Permissões Granulares (ACL) -->
            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5">
                <span class="text-xs font-bold text-slate-300 block uppercase tracking-wider">Permissões de Acesso (Módulos):</span>
                
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-300">
                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_os" value="1" checked class="rounded border-slate-700 text-indigo-600">
                        <span>📋 Ordens de Serviço</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_pos" value="1" checked class="rounded border-slate-700 text-indigo-600">
                        <span>🛒 PDV / Caixa Balcão</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_stock" value="1" checked class="rounded border-slate-700 text-indigo-600">
                        <span>📦 Peças & Estoque</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_clients" value="1" checked class="rounded border-slate-700 text-indigo-600">
                        <span>👥 Clientes</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_financial" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>💰 Hub Financeiro</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_settings" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>⚙️ Configurações</span>
                    </label>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30">
                Salvar Colaborador &rarr;
            </button>
        </form>

    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL: EDITAR COLABORADOR COM UPLOAD/LINK DE FOTO E PERMISSÕES         -->
<!-- ===================================================================== -->
<div id="modal-edit-member" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4 overflow-y-auto">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-lg w-full p-6 space-y-5 shadow-2xl relative my-8">
        
        <button type="button" onclick="closeEditMemberModal()" 
                class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg font-bold">
            ✕
        </button>

        <div>
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <span>✏️</span>
                <span>Editar Colaborador & Permissões</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Atualize a foto de perfil, cargo e módulos autorizados.</p>
        </div>

        <form action="<?= \Zyrtex\Core\Response::url('/admin/equipe/editar') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id" id="edit_member_id" value="">

            <!-- Foto de Perfil / Avatar -->
            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Foto de Perfil:</span>
                    <label class="text-[11px] text-rose-400 hover:text-rose-300 flex items-center gap-1 cursor-pointer">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-slate-700 text-rose-600">
                        <span>Remover Foto</span>
                    </label>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full overflow-hidden bg-slate-900 border border-slate-700 shrink-0 flex items-center justify-center text-slate-500 font-bold text-xs" id="edit_avatar_preview_box">
                        <img id="edit_avatar_img" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <span id="edit_avatar_placeholder">Sem Foto</span>
                    </div>

                    <div class="flex-1 space-y-2">
                        <!-- Opção 1: Upload de Arquivo -->
                        <div>
                            <label class="block text-[10px] text-slate-400 font-medium mb-1">Nova Foto do Computador/Celular:</label>
                            <input type="file" name="avatar_file" accept="image/*"
                                   onchange="previewImageInput(this, 'edit_avatar_img', 'edit_avatar_placeholder')"
                                   class="block w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                        </div>

                        <!-- Opção 2: Link / URL Direto -->
                        <div>
                            <label class="block text-[10px] text-slate-400 font-medium mb-1">Ou Altere pela URL:</label>
                            <input type="url" name="avatar_url" id="edit_avatar_url" placeholder="https://exemplo.com/foto.jpg"
                                   oninput="previewUrlInput(this.value, 'edit_avatar_img', 'edit_avatar_placeholder')"
                                   class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dados Básicos -->
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nome Completo *</label>
                    <input type="text" name="name" id="edit_member_name" required
                           class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">E-mail *</label>
                        <input type="email" name="email" id="edit_member_email" required
                               class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nova Senha (Opcional)</label>
                        <input type="password" name="password" placeholder="Deixe em branco p/ manter"
                               class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                    </div>
                </div>

                <div id="box-role-select">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Função / Cargo</label>
                    <select name="role" id="edit_member_role" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                        <option value="technician">Técnico de Bancada</option>
                        <option value="attendant">Atendente / Vendas Balcão</option>
                        <option value="manager">Gerente</option>
                    </select>
                </div>
            </div>

            <!-- Checklist de Permissões Granulares (ACL) -->
            <div id="box-edit-permissions" class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2.5">
                <span class="text-xs font-bold text-slate-300 block uppercase tracking-wider">Permissões de Acesso (Módulos):</span>
                
                <div class="grid grid-cols-2 gap-2 text-xs text-slate-300">
                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_os" id="edit_perm_os" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>📋 Ordens de Serviço</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_pos" id="edit_perm_pos" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>🛒 PDV / Caixa Balcão</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_stock" id="edit_perm_stock" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>📦 Peças & Estoque</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_clients" id="edit_perm_clients" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>👥 Clientes</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_financial" id="edit_perm_financial" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>💰 Hub Financeiro</span>
                    </label>

                    <label class="flex items-center gap-2 p-2 rounded-lg bg-slate-900 border border-slate-800 hover:border-slate-700 cursor-pointer">
                        <input type="checkbox" name="perm_settings" id="edit_perm_settings" value="1" class="rounded border-slate-700 text-indigo-600">
                        <span>⚙️ Configurações</span>
                    </label>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-indigo-600/30">
                Salvar Alterações &rarr;
            </button>
        </form>

    </div>
</div>

<script>
function openNewMemberModal() {
    document.getElementById('modal-new-member').classList.remove('hidden');
}
function closeNewMemberModal() {
    document.getElementById('modal-new-member').classList.add('hidden');
}

function openEditMemberModal(member) {
    document.getElementById('edit_member_id').value = member.id;
    document.getElementById('edit_member_name').value = member.name || '';
    document.getElementById('edit_member_email').value = member.email || '';
    
    // Cargo
    const roleSelect = document.getElementById('edit_member_role');
    if (roleSelect) {
        roleSelect.value = member.role || 'technician';
    }

    // Se for o dono da oficina, oculta seletores restritos
    const boxRole = document.getElementById('box-role-select');
    const boxPerms = document.getElementById('box-edit-permissions');
    if (member.role === 'owner') {
        if (boxRole) boxRole.classList.add('hidden');
        if (boxPerms) boxPerms.classList.add('hidden');
    } else {
        if (boxRole) boxRole.classList.remove('hidden');
        if (boxPerms) boxPerms.classList.remove('hidden');
    }

    // Avatar preview
    const imgEl = document.getElementById('edit_avatar_img');
    const placeholderEl = document.getElementById('edit_avatar_placeholder');
    const urlInput = document.getElementById('edit_avatar_url');
    if (member.avatar_url) {
        imgEl.src = member.avatar_url;
        imgEl.classList.remove('hidden');
        placeholderEl.classList.add('hidden');
        urlInput.value = member.avatar_url;
    } else {
        imgEl.src = '';
        imgEl.classList.add('hidden');
        placeholderEl.classList.remove('hidden');
        urlInput.value = '';
    }

    // Permissões
    let perms = {};
    try {
        perms = typeof member.permissions_json === 'string' ? JSON.parse(member.permissions_json) : (member.permissions_json || {});
    } catch (e) {
        perms = {};
    }

    document.getElementById('edit_perm_os').checked = !!perms.os;
    document.getElementById('edit_perm_pos').checked = !!perms.pos;
    document.getElementById('edit_perm_stock').checked = !!perms.stock;
    document.getElementById('edit_perm_clients').checked = !!perms.clients;
    document.getElementById('edit_perm_financial').checked = !!perms.financial;
    document.getElementById('edit_perm_settings').checked = !!perms.settings;

    document.getElementById('modal-edit-member').classList.remove('hidden');
}

function closeEditMemberModal() {
    document.getElementById('modal-edit-member').classList.add('hidden');
}

function previewImageInput(input, imgId, placeholderId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById(imgId);
            const placeholder = document.getElementById(placeholderId);
            if (img) {
                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewUrlInput(url, imgId, placeholderId) {
    const img = document.getElementById(imgId);
    const placeholder = document.getElementById(placeholderId);
    if (url && url.trim().length > 5) {
        if (img) {
            img.src = url.trim();
            img.classList.remove('hidden');
        }
        if (placeholder) {
            placeholder.classList.add('hidden');
        }
    } else {
        if (img) {
            img.classList.add('hidden');
        }
        if (placeholder) {
            placeholder.classList.remove('hidden');
        }
    }
}
</script>
