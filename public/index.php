<?php
require __DIR__ . '/../app/bootstrap.php';
$user = require_auth();
$pageTitle = 'Visão do acervo';
$activePage = 'acervo';
$query = mb_substr(input($_GET, 'q'), 0, 180);
$catalog = catalog($query, positive_id($_GET['pagina'] ?? 1));
$stats = dashboard();
require __DIR__ . '/../app/views/header.php';
?>
<section class="page-heading"><div><p class="eyebrow">SEU CATÁLOGO, EM ORDEM</p><h1>Visão do acervo<span class="brand-dot">.</span></h1><p>Acompanhe seus livros e cuide de cada detalhe do catálogo.</p></div><a class="button primary" href="livro.php"><?= icon('plus') ?>Cadastrar livro</a></section>
<section class="stats-grid" aria-label="Indicadores gerais do acervo">
    <article class="stat-card"><div><span>Títulos cadastrados</span><span class="stat-icon"><?= icon('book') ?></span></div><strong><?= number_format((int) $stats['titulos'], 0, ',', '.') ?></strong><p>Obras diferentes no catálogo</p></article>
    <article class="stat-card"><div><span>Exemplares em estoque</span><span class="stat-icon"><?= icon('stack') ?></span></div><strong><?= number_format((int) $stats['exemplares'], 0, ',', '.') ?></strong><p>Unidades disponíveis no acervo</p></article>
    <article class="stat-card"><div><span>Valor total do acervo</span><span class="stat-icon"><?= icon('coin') ?></span></div><strong class="money-stat"><?= money($stats['valor']) ?></strong><p>Soma do preço × quantidade de cada título</p></article>
</section>
<section class="catalog-card" aria-labelledby="catalog-title"><div class="catalog-heading"><div><h2 id="catalog-title">Livros do acervo <span class="count-badge"><?= (int) $stats['titulos'] ?></span></h2><p>Consulte, atualize e organize seus títulos.</p></div><span class="subtle-label">CATÁLOGO EDITORIAL</span></div>
    <form action="index.php" method="get" class="search-form" role="search" id="search-form"><label for="search" class="sr-only">Buscar pelo título do livro</label><div class="search-field"><?= icon('search') ?><input id="search" type="search" name="q" maxlength="180" value="<?= e($query) ?>" placeholder="Buscar pelo título do livro..." autocomplete="off"></div><button class="button secondary">Buscar</button><a href="index.php" class="text-link" id="clear-search" <?= $query === '' ? 'hidden' : '' ?>>Limpar</a></form>
    <p id="search-status" class="search-status" role="status" aria-live="polite"></p>
    <div id="catalog-results"><?php require __DIR__ . '/../app/views/catalog.php'; ?></div>
</section>
<p class="bottom-note"><?= icon('lock') ?>Seu acervo em um só lugar. Acesso exclusivo à administração.</p>
<?php require __DIR__ . '/../app/views/footer.php'; ?>
