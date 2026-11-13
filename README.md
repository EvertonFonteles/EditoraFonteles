# Editora Fonteles · Gestão de acervo

Sistema acadêmico para administrar o catálogo de uma livraria, com identidade visual sóbria, cores claras e acesso administrativo.

**Tecnologias:** PHP 8.1+, HTML5, CSS3, JavaScript e MySQL 5.7+ / MariaDB 10.4+. Não exige Composer, npm nem frameworks. Para que as restrições `CHECK` sejam aplicadas pelo próprio MySQL, use MySQL 8.0.16+; as mesmas regras também são verificadas pelo PHP.

## O que o sistema faz

- Login com senha armazenada como hash, sessão e encerramento de acesso.
- Cadastro, listagem, edição e exclusão de livros com confirmação.
- Busca pelo título com Fetch API e alternativa por formulário convencional.
- Paginação de oito registros por página, inclusive nos resultados da busca.
- Painel com títulos cadastrados, exemplares em estoque e valor do acervo.
- Validação no navegador e no servidor; ISBN opcional com dígito verificador.
- Layout adaptável a computador e celular, com rolagem horizontal restrita à tabela.

Um registro representa **um título**, e o campo quantidade representa suas unidades. Não é um sistema de vendas, empréstimos ou controle individual de exemplares.

## Instalação no Windows com XAMPP

1. Copie esta pasta para `C:\xampp\htdocs\editora-fonteles`.
2. Abra o XAMPP Control Panel e clique em **Start** para Apache e MySQL. Não precisa instalar os serviços do Windows.
3. Abra a pasta do projeto no VS Code. No terminal PowerShell do VS Code, execute:

```powershell
& 'C:\xampp\php\php.exe' '.\scripts\install.php' --demo
```

4. Informe seu nome, um e-mail para acesso e uma senha de pelo menos dez caracteres. O e-mail funciona como identificador local: o sistema não envia mensagens e não exige que ele exista na internet. A digitação da senha fica visível no terminal do instalador; use uma senha exclusiva para este projeto.
5. Abra **http://localhost/editora-fonteles/public/** no navegador e entre com o acesso criado.

O instalador cria o banco `fonteles_acervo` e as tabelas, sem apagar dados existentes. Se já houver um administrador, ele preserva o acesso. `--demo` adiciona nove livros apenas quando o acervo está vazio; omita essa opção para começar sem exemplos.

Os preços e estoques dos exemplos são fictícios. A editora é identificada como “Edição demonstrativa”; os anos correspondem à publicação original das obras, não a uma edição comercial real da Editora Fonteles.

**Revisão neste computador:** o projeto foi instalado no XAMPP e há um acesso local informado em um arquivo separado da pasta do código. Esse arquivo não faz parte deste repositório. A instalação em outro computador exige criar um novo administrador.

### Se seu MySQL usa outra porta ou senha

Copie `config/database.example.php` para `config/local.php` e altere os dados de conexão. O arquivo `local.php` está ignorado pelo Git. O padrão local do XAMPP é usuário `root`, senha vazia e porta `3306`; isso serve apenas ao ambiente de desenvolvimento.

### Alternativa sem iniciar o Apache

Com o MySQL em execução, abra o terminal na pasta do projeto:

```powershell
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t public
```

Acesse http://127.0.0.1:8000. Mantenha o terminal aberto e use `Ctrl+C` para parar. O servidor embutido do PHP é destinado a desenvolvimento.

### Importação pelo phpMyAdmin

O caminho automático recomendado é o instalador. Se preferir conferir o SQL manualmente, crie o banco `fonteles_acervo` com colação `utf8mb4_unicode_ci`, selecione-o e importe `database/schema.sql`. Depois execute o instalador para criar o administrador: o SQL não contém usuários com senhas predefinidas.

## Organização do código

```text
app/
  bootstrap.php       Inicialização, cabeçalhos e sessão
  database.php        Configuração e conexão PDO
  functions.php       Autenticação, CSRF, escape e auxiliares
  books.php           Validações e consultas do acervo
  views/              Partes reutilizáveis das páginas
config/
  database.example.php Configuração de referência, sem segredos
database/
  schema.sql          Estrutura relacional do banco
public/               Páginas acessíveis pelo navegador
  api/livros.php      Resposta JSON da pesquisa e paginação
  assets/             CSS, JavaScript e ícone da marca
scripts/
  install.php         Instalação por linha de comando
tests/
  validation.php      Verificações das regras de entrada
docs/
  GUIA-DE-ESTUDO.md   Explicação do funcionamento e roteiro de apresentação
  VALIDACAO.md       O que foi efetivamente verificado
```

Em uma hospedagem, a raiz pública deve apontar para `public/`. No XAMPP, o `.htaccess` da raiz bloqueia acesso pelo navegador a código interno, configuração, testes e SQL. Ele depende de `mod_rewrite` e `AllowOverride`, habilitados na instalação usada para revisão. Não exponha a pasta do projeto por um servidor estático que ignore essas regras.

## Banco de dados

`usuarios` contém nome, e-mail único, hash da senha e informações de bloqueio por falhas de login. `livros` contém título, autor, ISBN único e opcional, editora, ano, categoria, preço, quantidade e datas de criação/atualização.

O ISBN vazio é convertido em `NULL`, permitindo vários livros sem ISBN. Um ISBN informado não pode ser repetido; para adicionar unidades do mesmo título, edite a quantidade. O preço usa `DECIMAL(10,2)`, adequado a centavos, em vez de um número aproximado no armazenamento.

As duas tabelas têm finalidades diferentes e não precisam de uma chave estrangeira neste escopo: não se registra um proprietário diferente para cada livro. Todos os administradores acessam o mesmo acervo.

## Segurança implementada

- Consultas parametrizadas com PDO e prepared statements reais.
- Escape de saída com `htmlspecialchars` para exibir textos sem interpretá-los como HTML.
- Tokens CSRF nas operações que alteram dados, inclusive login e logout.
- Exclusão somente por POST; abrir o link de exclusão não remove dados.
- `password_hash` e `password_verify`; a senha original não é gravada.
- Regeneração do identificador de sessão ao entrar e expiração por 30 minutos de inatividade.
- Cookie `HttpOnly`, `SameSite=Lax` e `Secure` quando a conexão usa HTTPS.
- Bloqueio da conta por 15 minutos após cinco falhas consecutivas; a contagem fica no banco.
- Cabeçalhos CSP, prevenção de enquadramento e proibição de cache das páginas PHP.
- Detalhes internos de exceções vão para o log do PHP; a interface mostra uma mensagem genérica.

Para uso comercial real, ainda seriam necessários HTTPS na hospedagem, usuário de banco com permissões limitadas, rotina de backup, recuperação de acesso, revisão de segurança e limitação de tentativas também por origem. Este projeto atende ao escopo acadêmico; não inclui essas operações de produção.

## Verificação

Execute no terminal da pasta do projeto:

```powershell
& 'C:\xampp\php\php.exe' '.\tests\validation.php'
```

O teste não altera o banco. O resultado da revisão inicial foi **22 verificações aprovadas**. A validação de sintaxe de todos os arquivos PHP e do JavaScript também passou. Consulte `docs/VALIDACAO.md` para os cenários observados no navegador e os limites da verificação.

## Código-fonte e entrega acadêmica

Repositório público do projeto: **https://github.com/EvertonFonteles/EditoraFonteles**.

O repositório reúne o conteúdo desta pasta: código completo, SQL, testes e documentação. Não publique credenciais locais, `config/local.php`, arquivos de sessão, diretórios de dados do MySQL nem o arquivo separado de acesso local. O `.gitignore` protege os arquivos de configuração particulares.

O relatório acadêmico é fornecido separadamente, com o link do repositório, as três capturas exigidas e a análise de negócio. As capturas demonstram o mesmo título no formulário, na listagem e na tabela do banco. Revise o relatório com suas palavras e estude o funcionamento antes da apresentação. Publicar o código no GitHub não hospeda o sistema PHP nem seu banco: a aplicação continua sendo executada no XAMPP.
