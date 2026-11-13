<?php
require __DIR__ . '/../app/bootstrap.php';
$user = require_auth();
$id = positive_id($_GET['id'] ?? 0);
$editing = $id > 0;
$book = $editing ? find_book($id) : null;
if ($editing && !$book) { flash('O livro solicitado não foi encontrado.', 'error'); redirect('index.php'); }
$errors = [];
$data = $book ?? ['titulo' => '', 'autor' => '', 'isbn' => '', 'editora' => 'Editora Fonteles', 'ano' => '', 'categoria' => '', 'preco' => '', 'quantidade' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    [$data, $errors] = validate_book($_POST);
    if (!$errors) {
        try {
            save_book($data, $id);
            flash($editing ? 'Livro atualizado com sucesso.' : 'Livro cadastrado com sucesso.');
            redirect('index.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? 0) === 1062) {
                $errors['isbn'] = 'Este ISBN já está cadastrado. Edite o registro existente para atualizar o estoque.';
            } else { throw $exception; }
        }
    }
    http_response_code(422);
}
$pageTitle = $editing ? 'Editar livro' : 'Cadastrar livro';
$activePage = $editing ? '' : 'novo';
function field_error(string $field, array $errors): string {
    return isset($errors[$field]) ? '<span class="field-error" id="error-' . e($field) . '">' . e($errors[$field]) . '</span>' : '';
}
function field_state(string $field, array $errors): string {
    return isset($errors[$field]) ? 'aria-invalid="true" aria-describedby="error-' . e($field) . '"' : '';
}
require __DIR__ . '/../app/views/header.php';
?>
<a href="index.php" class="back-link"><?= icon('arrow') ?>Voltar ao acervo</a>
<section class="page-heading form-heading"><div><p class="eyebrow"><?= $editing ? 'CUIDADO EM CADA DETALHE' : 'UM NOVO CAPÍTULO NO ACERVO' ?></p><h1><?= e($pageTitle) ?><span class="brand-dot">.</span></h1><p><?= $editing ? 'Atualize as informações e mantenha seu catálogo em dia.' : 'Preencha os dados abaixo para adicionar um título ao catálogo.' ?></p></div><span class="required-note">* Campos obrigatórios</span></section>
<?php if ($errors): ?><div class="notice error" role="alert">Não foi possível salvar. Revise os campos destacados abaixo.</div><?php endif; ?>
<form class="book-form" method="post" action="livro.php<?= $editing ? '?id=' . $id : '' ?>"><?= csrf_field() ?>
<section class="form-section"><div class="section-intro"><span class="section-number">01</span><div><h2>Informações do livro</h2><p>Os detalhes que identificam cada obra.</p></div></div><div class="form-grid">
<div class="field full"><label for="titulo">Título do livro *</label><input id="titulo" name="titulo" maxlength="180" value="<?= e($data['titulo']) ?>" placeholder="Ex.: Memórias de um novo tempo" required <?= field_state('titulo', $errors) ?>><?= field_error('titulo', $errors) ?></div>
<div class="field"><label for="autor">Autor *</label><input id="autor" name="autor" maxlength="140" value="<?= e($data['autor']) ?>" placeholder="Nome do autor" required <?= field_state('autor', $errors) ?>><?= field_error('autor', $errors) ?></div>
<div class="field"><label for="isbn">ISBN <span class="optional">(opcional)</span></label><input id="isbn" name="isbn" maxlength="25" value="<?= e($data['isbn']) ?>" placeholder="10 ou 13 caracteres, com ou sem hífens" <?= field_state('isbn', $errors) ?>><?= field_error('isbn', $errors) ?></div>
<div class="field"><label for="editora">Editora *</label><input id="editora" name="editora" maxlength="120" value="<?= e($data['editora']) ?>" required <?= field_state('editora', $errors) ?>><?= field_error('editora', $errors) ?></div>
<div class="field"><label for="ano">Ano de publicação *</label><input id="ano" name="ano" type="number" min="1450" max="<?= (int) date('Y') + 1 ?>" value="<?= e($data['ano']) ?>" placeholder="Ex.: <?= date('Y') ?>" required <?= field_state('ano', $errors) ?>><?= field_error('ano', $errors) ?></div>
<div class="field full"><label for="categoria">Categoria *</label><input id="categoria" name="categoria" maxlength="60" value="<?= e($data['categoria']) ?>" list="categorias" placeholder="Selecione ou digite uma categoria" required <?= field_state('categoria', $errors) ?>><datalist id="categorias"><option value="Literatura brasileira"><option value="Ficção"><option value="Poesia"><option value="Biografia"><option value="Educação"><option value="Tecnologia"><option value="Infantil"><option value="Negócios"></datalist><?= field_error('categoria', $errors) ?></div>
</div></section>
<section class="form-section"><div class="section-intro"><span class="section-number">02</span><div><h2>Preço e estoque</h2><p>Informações para acompanhar a disponibilidade.</p></div></div><div class="form-grid"><div class="field"><label for="preco">Preço unitário (R$) *</label><input id="preco" name="preco" type="text" inputmode="decimal" maxlength="11" value="<?= e($data['preco']) ?>" placeholder="0,00" required <?= field_state('preco', $errors) ?>><small>Use vírgula ou ponto para os centavos, sem separador de milhar.</small><?= field_error('preco', $errors) ?></div><div class="field"><label for="quantidade">Quantidade em estoque *</label><input id="quantidade" name="quantidade" type="number" min="0" max="1000000" step="1" value="<?= e($data['quantidade']) ?>" placeholder="0" required <?= field_state('quantidade', $errors) ?>><?= field_error('quantidade', $errors) ?></div></div></section>
<div class="form-actions"><span><?= icon('lock') ?>Os dados serão salvos no acervo da editora.</span><div><a href="index.php" class="button secondary">Cancelar</a><button class="button primary"><?= icon('check') ?><?= $editing ? 'Salvar alterações' : 'Cadastrar livro' ?></button></div></div>
</form>
<?php require __DIR__ . '/../app/views/footer.php'; ?>
