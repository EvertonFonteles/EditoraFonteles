<?php
declare(strict_types=1);

function find_book(int $id): ?array
{
    $statement = db()->prepare('SELECT * FROM livros WHERE id = :id');
    $statement->execute(['id' => $id]);
    return $statement->fetch() ?: null;
}

function catalog(string $query, int $page): array
{
    $query = mb_substr($query, 0, 180);
    // Escapar curingas faz a busca tratar % e _ como caracteres literais.
    $params = $query === '' ? [] : ['query' => '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%'];
    $where = $query === '' ? '' : " WHERE titulo LIKE :query ESCAPE '!'";
    $count = db()->prepare('SELECT COUNT(*) FROM livros' . $where);
    $count->execute($params);
    $total = (int) $count->fetchColumn();
    $pages = max(1, (int) ceil($total / 8));
    $page = min(max(1, $page), $pages);
    $offset = ($page - 1) * 8;
    // Os números interpolados são inteiros calculados pelo servidor; o texto usa parâmetro PDO.
    $statement = db()->prepare('SELECT * FROM livros' . $where . " ORDER BY criado_em DESC, id DESC LIMIT 8 OFFSET $offset");
    $statement->execute($params);
    return ['books' => $statement->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page, 'query' => $query];
}

function dashboard(): array
{
    return db()->query('SELECT COUNT(*) AS titulos, COALESCE(SUM(quantidade), 0) AS exemplares, COALESCE(SUM(preco * quantidade), 0) AS valor FROM livros')->fetch();
}

function valid_isbn(string $isbn): bool
{
    if (preg_match('/^\d{13}$/D', $isbn)) {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - $sum % 10) % 10 === (int) $isbn[12];
    }
    if (preg_match('/^\d{9}[\dX]$/D', $isbn)) {
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ($isbn[$i] === 'X' ? 10 : (int) $isbn[$i]) * (10 - $i);
        }
        return $sum % 11 === 0;
    }
    return false;
}

function validate_book(array $source): array
{
    $data = [];
    $errors = [];
    foreach (['titulo' => 180, 'autor' => 140, 'editora' => 120, 'categoria' => 60] as $field => $limit) {
        $data[$field] = input($source, $field);
        if ($data[$field] === '' || mb_strlen($data[$field]) > $limit) {
            $errors[$field] = "Preencha este campo com até $limit caracteres.";
        }
    }
    $data['isbn'] = strtoupper(str_replace(['-', ' '], '', input($source, 'isbn')));
    if ($data['isbn'] !== '' && !valid_isbn($data['isbn'])) {
        $errors['isbn'] = 'Informe um ISBN-10 ou ISBN-13 válido, ou deixe em branco.';
    }
    $data['ano'] = input($source, 'ano');
    if (!filter_var($data['ano'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1450, 'max_range' => (int) date('Y') + 1]])) {
        $errors['ano'] = 'Informe um ano entre 1450 e ' . ((int) date('Y') + 1) . '.';
    }
    $data['quantidade'] = input($source, 'quantidade');
    if (filter_var($data['quantidade'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1000000]]) === false) {
        $errors['quantidade'] = 'Informe uma quantidade inteira entre 0 e 1.000.000.';
    }
    $data['preco'] = str_replace(',', '.', input($source, 'preco'));
    if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/D', $data['preco'])) {
        $errors['preco'] = 'Informe um valor de 0 a 99.999.999,99, com até duas casas decimais.';
    }
    return [$data, $errors];
}

function save_book(array $data, int $id): void
{
    $data['isbn'] = $data['isbn'] === '' ? null : $data['isbn'];
    if ($id > 0) {
        $statement = db()->prepare('UPDATE livros SET titulo=:titulo, autor=:autor, isbn=:isbn, editora=:editora, ano=:ano, categoria=:categoria, preco=:preco, quantidade=:quantidade WHERE id=:id');
        $data['id'] = $id;
    } else {
        $statement = db()->prepare('INSERT INTO livros (titulo, autor, isbn, editora, ano, categoria, preco, quantidade) VALUES (:titulo, :autor, :isbn, :editora, :ano, :categoria, :preco, :quantidade)');
    }
    $statement->execute($data);
}
