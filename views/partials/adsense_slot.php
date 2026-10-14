<?php
// Anúncios do AdSense são renderizados apenas para contas gratuitas (Freemium)
if (empty($tenant['is_premium'])):
    $adScript = $app['adsense_code'] ?? null;
?>
    <div class="my-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-800 bg-slate-900/60 p-4 text-center transition">
        <span class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">Publicidade</span>
        <div class="flex h-20 w-full max-w-lg items-center justify-center rounded-xl bg-slate-950/80 border border-slate-800/80 text-xs text-slate-400 font-medium">
            <?php if (!empty($adScript)): ?>
                <?= $adScript ?>
            <?php else: ?>
                <span class="flex items-center gap-2 text-slate-500">
                    <span>📢</span>
                    <span>Espaço de Anúncio Patrocinado (Google AdSense)</span>
                </span>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
