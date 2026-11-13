# Registro de validação

Revisão em 13/11/2026, no XAMPP instalado no computador do aluno. Ambiente observado: Apache 2.4.65, PHP 8.5.12 e MariaDB 10.11.16.

## Verificações executadas

| Cenário | Resultado observado |
| --- | --- |
| Sintaxe dos arquivos PHP | Sem erros nos arquivos verificados com `php -l` |
| Sintaxe do JavaScript | Sem erros com `node --check` |
| Regras automatizadas | 22 verificações aprovadas em `tests/validation.php` |
| Instalação da base local | Banco e tabelas criados, com nove livros demonstrativos e administrador |
| Login correto | Acesso concedido ao acervo |
| Senha incorreta | Entrada recusada com mensagem genérica |
| Cadastro | Título fictício “Gestão de Acervos: Organização e Tecnologia” salvo com R$ 59,90 e dez unidades |
| Persistência | Mesmo registro, id 10, conferido diretamente no phpMyAdmin |
| Edição | Estoque alterado de 10 para 11 e depois retornado a 10; alterações persistidas |
| Indicadores | 10 títulos, 71 unidades e R$ 2.843,90; ao alterar o estoque para 11, 72 unidades e R$ 2.903,80 |
| Busca dinâmica | “gestão” encontrou o título correspondente sem recarregar a página inteira |
| Busca com entrada semelhante a SQL | `' OR 1=1 --` foi tratado como texto e retornou zero resultados |
| Paginação | Segunda página exibiu os registros 9–10 de dez títulos |
| Exclusão | Diálogo abriu com o título correto; cancelar preservou o registro |
| Logout e página protegida | Ao tentar abrir o cadastro sem sessão, houve redirecionamento ao login |
| API sem sessão | Requisição recusada com a mensagem de sessão expirada |
| Arquivo interno pelo Apache | Acesso direto ao SQL retornou 403 Forbidden |
| Preparador temporário | Retirado do servidor; endereço retornou 404 Not Found |
| Tela estreita | Login e painel inspecionados em viewport de 390 px, sem transbordamento horizontal da página |

## Limites desta revisão

O envio final da exclusão não foi executado durante a revisão por interface: foi verificada a confirmação e seu cancelamento. O bloqueio após cinco falhas de login e a expiração após 30 minutos constam do código, mas esses intervalos não foram exercitados de ponta a ponta. A revisão de segurança não é uma auditoria ou um teste de invasão completo.

Não foram feitos testes de carga, de usuários concorrentes, de navegadores antigos ou de publicação em hospedagem externa. O código-fonte é disponibilizado em https://github.com/EvertonFonteles/EditoraFonteles. Publicar o repositório não equivale a hospedar a aplicação PHP ou o banco de dados. O relatório acadêmico e as capturas são fornecidos separadamente.
