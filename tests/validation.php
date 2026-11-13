<?php
declare(strict_types=1);
// Executa as regras reais do domínio sem criar, modificar ou apagar dados no banco.
require __DIR__ . '/../app/functions.php';
require __DIR__ . '/../app/books.php';

$passed = 0;
function check(bool $condition, string $description): void {
    global $passed;
    if (!$condition) { fwrite(STDERR, "FALHOU: $description\n"); exit(1); }
    echo "OK: $description\n";
    $passed++;
}
$base = ['titulo' => 'Livro de teste', 'autor' => 'Autor de teste', 'isbn' => '', 'editora' => 'Editora Fonteles', 'ano' => '2026', 'categoria' => 'Tecnologia', 'preco' => '59,90', 'quantidade' => '10'];
[$data, $errors] = validate_book($base);
check(!$errors && $data['preco'] === '59.90', 'Preço com vírgula é normalizado sem perda de centavos.');
check(!validate_book(array_replace($base, ['quantidade' => '0', 'preco' => '0']))[1], 'Estoque e preço zero são válidos.');
check(isset(validate_book(array_replace($base, ['quantidade' => '-1']))[1]['quantidade']), 'Estoque negativo é recusado.');
check(isset(validate_book(array_replace($base, ['quantidade' => '1.5']))[1]['quantidade']), 'Estoque fracionário é recusado.');
check(isset(validate_book(array_replace($base, ['quantidade' => '1000001']))[1]['quantidade']), 'Estoque acima do limite é recusado.');
check(isset(validate_book(array_replace($base, ['preco' => '1.234,56']))[1]['preco']), 'Preço ambíguo com separador de milhar é recusado.');
check(isset(validate_book(array_replace($base, ['preco' => '-5']))[1]['preco']), 'Preço negativo é recusado.');
check(isset(validate_book(array_replace($base, ['preco' => '10.999']))[1]['preco']), 'Preço com três casas decimais é recusado.');
check(isset(validate_book(array_replace($base, ['preco' => '100000000']))[1]['preco']), 'Preço acima da capacidade do DECIMAL(10,2) é recusado.');
check(isset(validate_book(array_replace($base, ['ano' => '1449']))[1]['ano']), 'Ano anterior ao limite é recusado.');
check(isset(validate_book(array_replace($base, ['ano' => (string) ((int) date('Y') + 2)]))[1]['ano']), 'Ano futuro fora da faixa é recusado.');
check(isset(validate_book(array_replace($base, ['titulo' => '   ']))[1]['titulo']), 'Título em branco é recusado.');
check(isset(validate_book(array_replace($base, ['titulo' => str_repeat('Á', 181)]))[1]['titulo']), 'Limite do título considera caracteres acentuados.');
check(isset(validate_book(array_replace($base, ['autor' => ['conteudo inesperado']]))[1]['autor']), 'Parâmetro recebido como array não provoca erro fatal.');
check(valid_isbn('9788535914849'), 'ISBN-13 com dígito verificador correto é aceito.');
check(valid_isbn('0306406152'), 'ISBN-10 com dígito verificador correto é aceito.');
check(valid_isbn('080442957X'), 'ISBN-10 terminado em X é aceito.');
check(!valid_isbn('9788535914840'), 'ISBN com dígito verificador incorreto é recusado.');
check(!validate_book(array_replace($base, ['isbn' => '978-85-359-1484-9']))[1], 'ISBN com hífens é normalizado e validado.');
check(e('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', 'Conteúdo HTML é escapado antes da exibição.');
check(positive_id('1 OR 1=1') === 0 && positive_id('-1') === 0, 'Identificadores inválidos são recusados.');
check(money('1234.50') === 'R$ 1.234,50', 'Valores são exibidos no formato brasileiro.');
echo "\n$passed verificações aprovadas.\n";
