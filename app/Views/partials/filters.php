<form method="get" action="/index.php" class="filter-form" id="filters"><input type="hidden" name="r" value="<?= e($mapMode ? '/mapa' : '/ocorrencias') ?>">
    <div><label for="q" class="form-label">Pesquisar</label><input class="form-control" name="q" id="q" placeholder="O que você procura?" value="<?= e($_GET['q'] ?? '') ?>" maxlength="100"></div>
    <div><label for="categoria" class="form-label">Categoria</label><select class="form-select" name="categoria" id="categoria">
            <option value="">Todas as categorias</option><?php foreach ($categories as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ($_GET['categoria'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option><?php endforeach ?>
        </select></div>
    <div><label for="bairro" class="form-label">Bairro</label><select class="form-select" name="bairro" id="bairro">
            <option value="">Todos os bairros</option><?php foreach ($neighborhoods as $b): ?><option value="<?= (int)$b['id'] ?>" <?= ($_GET['bairro'] ?? '') == $b['id'] ? 'selected' : '' ?>><?= e($b['nome']) ?></option><?php endforeach ?>
        </select></div>
    <div><label for="situacao" class="form-label">Situação</label><select class="form-select" name="situacao" id="situacao">
            <option value="">Todas as situações</option><?php foreach (\App\Services\OccurrenceState::PUBLIC as $s): ?><option value="<?= e($s) ?>" <?= ($_GET['situacao'] ?? '') === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach ?>
        </select></div>
    <div><label for="inicio" class="form-label">Publicado a partir de</label><input type="date" class="form-control" id="inicio" name="inicio" value="<?= e($_GET['inicio'] ?? '') ?>"></div>
    <div><label for="fim" class="form-label">Publicado até</label><input type="date" class="form-control" id="fim" name="fim" value="<?= e($_GET['fim'] ?? '') ?>"></div><button class="btn btn-primary align-self-end">Aplicar filtros</button><a class="align-self-end btn btn-link" href="<?= e(url($mapMode ? '/mapa' : '/ocorrencias')) ?>">Limpar</a>
</form>