<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950 text-slate-100 antialiased select-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PDV Totem Express - Zyrtex</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body { 
            font-family: 'Inter', sans-serif;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }
        /* Custom scrollbar para telas touch */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(99, 102, 241, 0.4);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="h-full flex flex-col bg-slate-950 text-slate-100 overflow-hidden">

    <!-- Flash Messages (Toasts) -->
    <?php \Zyrtex\Core\View::partial('flash_messages', ['flashes' => $flashes ?? []]); ?>

    <!-- TOPBAR DO TOTEM (Estilo Fast-Food) -->
    <header class="h-16 bg-slate-900 border-b border-slate-800 px-4 sm:px-6 flex items-center justify-between shrink-0 shadow-md">
        
        <!-- Lado Esquerdo: Voltar ao Painel & Nome da Loja -->
        <div class="flex items-center gap-4">
            <a href="<?= \Zyrtex\Core\Response::url('/admin') ?>" 
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold transition border border-slate-700">
                <span>&larr;</span>
                <span class="hidden sm:inline">Voltar ao Painel</span>
            </a>

            <div class="h-6 w-px bg-slate-800 hidden sm:block"></div>

            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-sm shadow-md shadow-indigo-600/30">
                    Z
                </span>
                <div>
                    <h1 class="text-sm font-extrabold text-white leading-none">
                        <?= htmlspecialchars($tenant['business_name'] ?? 'Minha Oficina', ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <span class="text-[10px] text-emerald-400 font-semibold tracking-wide flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Terminal PDV Balcão Touch Screen (RF04)
                    </span>
                </div>
            </div>
        </div>

        <!-- Lado Direito: Operador & Atalhos -->
        <div class="flex items-center gap-3">
            <div class="hidden md:flex flex-col text-right">
                <span class="text-xs font-bold text-slate-200"><?= htmlspecialchars($user['name'] ?? 'Operador', ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-[10px] text-slate-500">Caixa Livre</span>
            </div>

            <a href="<?= \Zyrtex\Core\Response::url('/admin/estoque') ?>" 
               class="px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                📦 Estoque
            </a>
            
            <a href="<?= \Zyrtex\Core\Response::url('/admin/os') ?>" 
               class="px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-xs font-semibold border border-slate-700 transition">
                📋 Ordens de Serviço
            </a>
        </div>
    </header>

    <!-- ÁREA PRINCIPAL DO TOTEM (Ocupa todo o resto da tela) -->
    <main class="flex-1 overflow-hidden">
        <?= $content ?? '' ?>
    </main>

</body>
</html>
