<?php
/**
 * Componente de Mini-Tutorial / Dicas de Autoatendimento (Tips Card)
 * @var string $title
 * @var string $description
 * @var array $steps
 * @var string|null $externalUrl
 * @var string|null $buttonLabel
 * @var string|null $link
 * @var string|null $linkText
 */
$steps = $steps ?? [];
$targetUrl = $link ?? $externalUrl ?? null;
$btnText = $linkText ?? $buttonLabel ?? 'Configurar Agora &rarr;';
?>
<div class="rounded-2xl border border-slate-800 bg-slate-900/90 p-5 shadow-sm hover:border-slate-700/80 transition flex flex-col justify-between">
    <div class="flex items-start gap-3.5">
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 font-bold text-sm">
            💡
        </div>
        <div class="flex-1 space-y-1">
            <h4 class="text-sm font-bold text-white tracking-tight">
                <?= htmlspecialchars($title ?? 'Dica de Configuração', ENT_QUOTES, 'UTF-8') ?>
            </h4>
            <?php if (!empty($description)): ?>
                <p class="text-xs text-slate-400 leading-relaxed">
                    <?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <?php if (!empty($steps)): ?>
                <ol class="mt-3 space-y-1.5 text-xs text-slate-300">
                    <?php foreach ($steps as $index => $step): ?>
                        <li class="flex items-start gap-2">
                            <span class="inline-flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full bg-indigo-900/60 text-[10px] font-bold text-indigo-300">
                                <?= $index + 1 ?>
                            </span>
                            <span><?= $step ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($targetUrl)): ?>
        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-end">
            <a href="<?= htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') ?>" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 border border-indigo-500/30 transition">
                <span><?= $btnText ?></span>
            </a>
        </div>
    <?php endif; ?>
</div>
