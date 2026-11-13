<?php
declare(strict_types=1);

// Somente linha de comando: nenhum instalador ou criação de usuário fica exposto na web.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../app/database.php';
require __DIR__ . '/../app/books.php';

function ask(string $label): string {
    echo $label;
    return trim(fgets(STDIN) ?: '');
}

try {
    $config = database_config();
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $config['database'])) {
        throw new RuntimeException('Nome de banco inválido. Use apenas letras, números e sublinhado.');
    }
    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $config['host'], $config['port']);
    $server = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $server->exec('CREATE DATABASE IF NOT EXISTS `' . $config['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $connection = db();
    $connection->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    echo "Banco e tabelas preparados. Nenhum registro existente foi apagado.\n";

    if ((int) db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === 0) {
        $name = getenv('FONTELES_ADMIN_NAME') ?: ask('Nome do administrador: ');
        $email = getenv('FONTELES_ADMIN_EMAIL') ?: ask('E-mail de acesso: ');
        $password = getenv('FONTELES_ADMIN_PASSWORD') ?: ask('Senha (mínimo 10 caracteres; entrada visível neste terminal): ');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 10 || strlen($password) > 72 || $password !== trim($password)) {
            throw new RuntimeException('Informe nome de 2 a 100 caracteres, e-mail válido e senha de 10 a 72 bytes sem espaços nas extremidades. Execute novamente.');
        }
        $statement = db()->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :hash)');
        $statement->execute(['nome' => trim($name), 'email' => mb_strtolower(trim($email)), 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
        echo "Administrador criado. A senha foi armazenada como hash.\n";
    } else {
        echo "Já existe administrador. O acesso existente foi preservado.\n";
    }

    if (in_array('--demo', $argv, true) && (int) db()->query('SELECT COUNT(*) FROM livros')->fetchColumn() === 0) {
        $samples = [
            ['Dom Casmurro', 'Machado de Assis', '1899', 'Literatura brasileira', '39.90', '12'],
            ['O Cortiço', 'Aluísio Azevedo', '1890', 'Literatura brasileira', '34.90', '8'],
            ['Iracema', 'José de Alencar', '1865', 'Literatura brasileira', '29.90', '6'],
            ['A Moreninha', 'Joaquim Manuel de Macedo', '1844', 'Literatura brasileira', '24.90', '0'],
            ['Os Sertões', 'Euclides da Cunha', '1902', 'Literatura brasileira', '49.90', '5'],
            ['Espumas Flutuantes', 'Castro Alves', '1870', 'Poesia', '27.90', '9'],
            ['Triste Fim de Policarpo Quaresma', 'Lima Barreto', '1915', 'Literatura brasileira', '32.90', '7'],
            ['Memórias Póstumas de Brás Cubas', 'Machado de Assis', '1881', 'Literatura brasileira', '42.90', '10'],
            ['O Guarani', 'José de Alencar', '1857', 'Literatura brasileira', '36.90', '4'],
        ];
        db()->beginTransaction();
        foreach ($samples as [$title, $author, $year, $category, $price, $quantity]) {
            save_book(['titulo' => $title, 'autor' => $author, 'isbn' => '', 'editora' => 'Edição demonstrativa', 'ano' => $year, 'categoria' => $category, 'preco' => $price, 'quantidade' => $quantity], 0);
        }
        db()->commit();
        echo "9 registros demonstrativos adicionados. Preços e estoques são fictícios; anos correspondem à publicação original das obras.\n";
    }
    echo "Instalação concluída. Inicie o servidor e acesse login.php.\n";
} catch (Throwable $exception) {
    if (isset($connection) && $connection->inTransaction()) { $connection->rollBack(); }
    fwrite(STDERR, 'Instalação não concluída: ' . $exception->getMessage() . "\n");
    exit(1);
}
