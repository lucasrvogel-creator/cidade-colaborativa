<div class="row g-3"><?php foreach ($rows as $item): ?><div class="col-md-6 col-xl-4"><article class="report-card h-100">
<div class="d-flex justify-content-between align-items-start gap-2"><span class="category-icon"><i class="bi bi-<?= e(preg_match('/^[a-z0-9-]+$/',$item['icone']??'')?$item['icone']:'geo-alt') ?>" aria-hidden="true"></i></span><span class="status status-<?= e($item['status']) ?>"><?= e(status_label($item['status'])) ?></span></div>
<p class="eyebrow mt-4"><?= e($item['categoria']??'Ocorrência') ?></p><h3><a href="<?= e(url('/ocorrencia',['id'=>$item['id']])) ?>"><?= e($item['titulo']) ?></a></h3>
<p class="text-secondary report-description"><?= e(mb_strimwidth($item['descricao'],0,140,'…')) ?></p>
<?php if (!empty($item['demonstracao'])): ?><span class="badge text-bg-light border">Dado fictício de demonstração</span><?php endif ?>
<div class="report-meta"><span><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($item['bairro']??'Rio do Sul') ?></span><span><?= e(datebr($item['publicado_em']??$item['criado_em'])) ?></span></div></article></div><?php endforeach ?></div>
<?php if (!$rows): ?><div class="empty-state"><i class="bi bi-inbox" aria-hidden="true"></i><h2>Nenhuma ocorrência por aqui</h2><p>Experimente outros filtros ou contribua com um novo relato.</p></div><?php endif ?>
