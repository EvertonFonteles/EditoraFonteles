# Guia de estudo · Editora Fonteles

Este guia acompanha o código entregue. A melhor preparação é seguir o fluxo no VS Code, executar cada ação e observar o que acontece na tela e no phpMyAdmin.

## 1. Entenda as três partes

**Interface:** HTML organiza os campos, botões e a tabela. CSS define aparência, espaçamento e adaptação a telas menores. JavaScript melhora a interação, como pesquisar sem recarregar a página.

**Servidor:** o PHP verifica o login, valida os campos, decide se a operação é permitida e consulta ou altera o banco. Essas regras precisam estar no servidor porque os controles do navegador podem ser contornados.

**Banco:** MySQL/MariaDB guarda usuários e livros mesmo quando o navegador é fechado. Uma variável JavaScript desapareceria ao encerrar a página; um registro persistido continua disponível.

O XAMPP reúne Apache, PHP e MariaDB. O módulo é chamado MySQL no painel, mas a versão instalada usa MariaDB, um banco relacional compatível com o protocolo e o SQL usados no projeto.

## 2. Siga um cadastro do começo ao fim

1. O navegador abre `public/livro.php`. O arquivo carrega `app/bootstrap.php`.
2. O bootstrap inicia a sessão e carrega as funções e as regras do acervo.
3. `require_auth()` verifica se há usuário autenticado. Sem sessão, a página redireciona ao login.
4. O PHP monta o formulário em HTML. O formulário inclui um token CSRF oculto.
5. Ao clicar em cadastrar, o navegador envia os campos por POST ao próprio `livro.php`.
6. O PHP confere o token e chama `validate_book()` em `app/books.php`.
7. Se houver erro, o formulário mantém os dados digitados e destaca os campos que precisam de correção.
8. Se estiver válido, `save_book()` prepara um `INSERT` e envia os valores separadamente ao PDO.
9. O banco grava o livro e atribui um identificador automático.
10. O servidor redireciona à listagem, que consulta novamente o banco e recalcula os indicadores.

O redirecionamento após salvar é chamado de Post/Redirect/Get. Ele evita que atualizar a página de resultado repita o envio anterior do formulário. Não é uma garantia geral contra todo clique duplicado ou toda operação concorrente.

## 3. Traduza CRUD para as telas

| Operação | Significado | Onde aparece | SQL principal |
| --- | --- | --- | --- |
| Create | Criar | Cadastrar livro | INSERT |
| Read | Ler | Listagem e pesquisa | SELECT |
| Update | Atualizar | Editar livro | UPDATE |
| Delete | Excluir | Confirmação de exclusão | DELETE |

Na edição, o identificador do livro determina o registro existente. A regra de validação é a mesma do cadastro. Na exclusão, abrir o endereço por GET apenas apresenta uma confirmação; a remoção exige POST e token válido.

## 4. Explique a busca sem recarregar

Abra `public/assets/app.js` e procure `async function search`.

O JavaScript aguarda 300 milissegundos após uma pausa na digitação. Em seguida, envia uma requisição GET para `api/livros.php`, passando texto e página desejada. O PHP verifica a sessão e consulta o banco usando PDO. A resposta é JSON com um fragmento HTML e informações da paginação. O navegador substitui apenas a área de resultados.

O fragmento HTML usa a mesma apresentação da listagem inicial; os textos do banco são escapados no PHP. O uso de `innerHTML` aqui depende dessa origem controlada e desse escape. Não seria seguro inserir ali qualquer texto recebido de terceiros sem tratamento.

`AbortController` cancela a busca anterior quando uma nova começa. Um contador também evita que uma resposta antiga substitua a atual. O botão Buscar continua funcionando. Sem JavaScript, o formulário envia uma busca convencional e o PHP recarrega a página com o resultado.

## 5. Mostre o papel do PDO

Em `app/database.php`, a conexão PDO usa `utf8mb4`, lança exceções em erros e desativa a emulação de prepared statements.

Em `app/books.php`, uma consulta contém marcadores como `:titulo` e `:id`. O texto do SQL é preparado primeiro; os valores são enviados separadamente com `execute()`. Assim, um título com aspas não vira parte do comando SQL. Essa separação ajuda a prevenir SQL Injection.

Os valores de limite e deslocamento da paginação são inteiros calculados pelo PHP. Não se interpola na consulta o texto digitado na busca. O `%` utilizado pelo LIKE é incluído pelo servidor; curingas digitados pelo usuário são escapados para pesquisar literalmente esses caracteres.

## 6. Explique o login sem decorar nomes

O instalador cria o administrador e transforma a senha em um hash com `password_hash()`. Na entrada, `password_verify()` compara a senha informada com esse hash. Não se descriptografa a senha, pois o hash não é uma senha criptografada reversível.

Depois de autenticar, o PHP registra o identificador e o nome do usuário na sessão e troca o identificador dessa sessão. O navegador recebe um cookie que permite associar novas requisições à mesma sessão. Após 30 minutos sem atividade, o acesso expira. O botão Sair encerra a sessão.

O sistema não tem cadastro público de usuários, recuperação por e-mail nem múltiplos papéis. O único perfil previsto é administrador. A conta é bloqueada temporariamente após cinco senhas incorretas consecutivas; isso não substitui controles de produção contra ataques distribuídos.

## 7. Diferencie três proteções

**SQL Injection:** parâmetros PDO separam os valores dos comandos de banco.

**XSS:** `htmlspecialchars()` transforma caracteres como `<` e `>` para que um texto cadastrado seja exibido como texto, sem se tornar uma tag executável.

**CSRF:** um token imprevisível vinculado à sessão acompanha cada formulário de alteração. O servidor rejeita envios sem o token correto. Ele protege contra uma página externa tentar disparar uma operação usando uma sessão já aberta.

Essas medidas resolvem problemas diferentes e precisam trabalhar juntas.

## 8. Entenda as regras do cadastro

- Título, autor, editora e categoria têm limites de tamanho.
- O ano deve estar entre 1450 e o ano seguinte ao atual, permitindo lançamentos próximos.
- A quantidade deve ser inteira, entre zero e um milhão.
- O preço admite até duas casas decimais, vírgula ou ponto como separador e nenhum separador de milhar.
- O ISBN é opcional. Se preenchido, precisa ter formato e dígito verificador válidos e ser único.
- Livro sem ISBN não significa duplicidade garantida: títulos homônimos ou diferentes edições podem existir. Não há bloqueio por título.

O banco armazena o preço como `DECIMAL`, não como texto com `R$`. A formatação brasileira só aparece na interface. Exemplos: `59,90` é validado e enviado como `59.90`; a tela exibe `R$ 59,90`.

## 9. Explique os indicadores com um exemplo

Se o acervo contém um título com 10 unidades a R$ 59,90, ele contribui com um título cadastrado, dez exemplares e R$ 599,00 no valor do acervo.

Os indicadores usam todos os livros do banco, mesmo enquanto a listagem está filtrada por uma pesquisa. A contagem ao lado da paginação corresponde apenas ao resultado da busca.

O valor do acervo é uma estimativa pelo preço de venda cadastrado. Não representa faturamento, lucro nem custo de aquisição: o sistema não registra vendas, despesas ou custos.

## 10. Roteiro de apresentação de cinco minutos

1. Apresente o problema: manter o acervo em planilhas ou anotações dificulta localizar títulos e atualizar estoques.
2. Entre no sistema e explique o acesso administrativo.
3. Mostre os três indicadores e diferencie títulos de exemplares.
4. Cadastre um livro, explicando que preço e quantidade são validados também pelo PHP.
5. Pesquise parte do título e mostre que a página inteira não recarrega.
6. Edite a quantidade e observe a mudança dos indicadores.
7. Mostre a confirmação de exclusão e cancele caso queira preservar o registro.
8. Abra o phpMyAdmin, selecione `fonteles_acervo` e `livros`, e localize o registro salvo.
9. No VS Code, mostre `public/livro.php`, `app/books.php` e `database/schema.sql` para explicar a separação de responsabilidades.

## 11. Perguntas que o professor pode fazer

**Por que PHP?** É uma tecnologia exigida pela atividade e executa no servidor do XAMPP. O navegador recebe HTML ou JSON gerado pelo PHP.

**Por que não usar só JavaScript?** JavaScript no navegador não deve guardar a senha do banco nem decidir sozinho se uma alteração está autorizada. O PHP faz essas verificações no servidor.

**Por que usar banco relacional?** Ele permite persistência, tipos de dados, chave primária, unicidade de ISBN e consultas agregadas para os indicadores.

**O que acontece com um ISBN repetido?** O banco tem uma restrição UNIQUE; o PHP trata o erro e orienta a editar o registro existente.

**O que ocorre quando a conexão com o banco falha?** O sistema apresenta uma mensagem genérica e registra os detalhes no log do PHP, para não expor informações internas na tela.

**Qual a diferença entre autenticação e autorização?** Autenticação identifica o usuário. Autorização decide o que ele pode fazer. Neste escopo, todas as páginas administrativas exigem o mesmo perfil autenticado.

**O painel identifica os autores mais buscados?** Ainda não. Isso exigiria registrar eventos de pesquisa; é uma possibilidade de evolução, não uma funcionalidade entregue.

**Por que há somente duas tabelas?** O escopo usa um acervo compartilhado e um perfil administrativo. Autor, editora e categoria são textos livres para manter o cadastro enxuto. Se o sistema evoluir para várias relações por obra, uma nova modelagem poderá separar essas entidades.

## 12. Pequenos exercícios para aprender

1. Cadastre um livro sem ISBN e confirme como ele aparece no banco.
2. Tente preço negativo e observe a mensagem. Leia a regra correspondente em `validate_book()`.
3. Faça uma busca sem resultado e depois limpe o campo.
4. Encontre no CSS os trechos `@media` e veja o que muda abaixo de 560 pixels.
5. Com a sessão encerrada, tente abrir a página de cadastro pelo endereço direto.
6. Leia a função `dashboard()` e explique o que cada `SUM` soma.

Use o guia como apoio ao entendimento e formule suas próprias respostas. Se a instituição exigir declarar assistência de ferramentas no desenvolvimento, siga a orientação institucional.
