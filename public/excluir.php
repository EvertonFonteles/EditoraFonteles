<?php
require __DIR__ . '/../app/bootstrap.php';
$user = require_auth();
$id = positive_id(($_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET)['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $statement = db()->prepare('DELETE FROM livros WHERE id=:id');
    $statement->execute(['id' => $id]);
    flash($statement->rowCount() ? 'Livro excluído do acervo.' : 'O livro já não está disponível.');
    redirect('index.php');
}
$book = find_book($id);
if (!$book) { flash('O livro solicitado não foi encontrado.', 'error'); redirect('index.php'); }
$pageTitle = 'Confirmar exclusão';
require __DIR__ . '/../app/views/header.php';
?>
<div class="confirmation-page"><p class="eyebrow">GESTÃO DO ACERVO</p><h1>Excluir este livro?</h1><p>O título <strong><?= e($book['titulo']) ?></strong> será removido do acervo. Esta ação não pode ser desfeita.</p><form action="excluir.php" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><div class="dialog-actions"><a href="index.php" class="button secondary">Cancelar</a><button class="button danger">Excluir livro</button></div></form></div>
<?php require __DIR__ . '/../app/views/footer.php'; ?>
