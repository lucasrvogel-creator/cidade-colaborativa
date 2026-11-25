<?php foreach ($notices as $notice): ?><article class="surface mb-3">
        <p class="eyebrow">AVISO DA PLATAFORMA · <?= e(datebr($notice['publicado_em'])) ?></p>
        <h2 class="h4"><?= e($notice['titulo']) ?></h2>
        <p class="preserve-lines"><?= e($notice['descricao']) ?></p><a href="<?= e($notice['url_fonte']) ?>" rel="noopener noreferrer">Fonte: <?= e($notice['fonte']) ?></a>
    </article><?php endforeach ?>
<?php if (!$notices): ?><div class="empty-state">
        <h2>Nenhum aviso publicado</h2>
        <p>Quando houver informações verificadas, elas aparecerão aqui com a fonte.</p>
    </div><?php endif ?>