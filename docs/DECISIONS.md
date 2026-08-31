# Notas de Desenvolvimento

Este documento reúne o histórico de requisitos, bugs encontrados e decisões técnicas tomadas ao longo do desenvolvimento deste projeto. O README principal fica com a visão geral e o quick start; aqui fica o "porquê" por trás de cada escolha.

## Sobre o Projeto
- A aplicação atende a empresa fictícia XPTO Ltda., que precisava de ajustes em seu sistema de cadastro de produtos;
- O sistema permite o cadastro, edição e remoção de _produtos_ e _categorias de produtos_ para uma _empresa_;
- Para que sejam possíveis os cadastros, alterações e remoções é necessário um usuário administrador;
- O sistema possui categorias padrão que pertencem a todas as empresas, bem como categorias personalizadas dedicadas a uma dada empresa. As categorias padrão são: (`clothing`, `phone`, `computer` e `house`) e **devem** aparecer para todas as _empresas_;
- O sistema tem um relatório de dados dedicado ao cliente.

## Sobre a API
As rotas estão divididas em:
  - _CRUD_ de _categorias_;
  - _CRUD_ de _produtos_;
  - Rota de busca de um _relatório_ que retorna um _html_.

A coleção do [Postman](https://www.postman.com/) usada no desenvolvimento e documentação está em `docs/postman-api.json`, e a documentação da API em `docs/api-docs.pdf`.
  - A documentação assume que a url base é `localhost:8000`, mas qualquer outra configurada ao subir o servidor funciona;
  - É necessário o _header_ `admin_user_id` com o id de um usuário administrador para acessar a maior parte das rotas (ver seção "Tratamento de erros e validação do admin_user_id" abaixo). Pesquisando no banco é possível ver os id's de usuários disponíveis.

## Sobre o Banco de Dados
O projeto começou com um banco _SQLite_ simples, com um arquivo de backup em `db/db-backup.sqlite3` contendo o estado inicial dos dados (usado depois pra remontar a seed do MySQL). Ele foi substituído por MySQL rodando em container — ver "Migrando o banco de SQLite para MySQL" abaixo.

### Migrations
- Funcionalidades que exigem modificações no banco de dados (dados ou estrutura) ficam contidas em _migrations_, nunca enviadas diretamente com o banco;
- A biblioteca utilizada para migrations foi o [**_Phinx_**](https://book.cakephp.org/phinx/0/en/index.html);
- Toda migration criada pode ser revertida (método `down()`);
- Comandos: `composer create-migration` (criar), `composer migrate` (rodar), `composer rollback` (reverter).

## Requisitos do Projeto
Lista original de ajustes e funcionalidades que guiaram o desenvolvimento.

### Alterações em funcionalidades existentes

**Categorias**
- [x] A categoria vinha errada na listagem de produtos para alguns casos (_exemplo: produto `blue trouser` vinha na categoria `phone`_);
- [x] Alguns produtos vinham com a categoria `null` ao serem pesquisados individualmente (_exemplo: produto `iphone 8`_);
- [x] Um produto cadastrado em mais de uma categoria (`king size bed`) aparecia **apenas** na primeira categoria na busca individual.

**Filtros e Ordenamento** — na listagem de produtos:
- [x] Filtrar produtos ativos ou inativos;
- [x] Filtrar produtos por categoria;
- [x] Ordenar produtos por data de cadastro.

**Relatório**
- [x] A coluna de logs do relatório precisava trazer (Nome do usuário, Tipo de alteração e Data), por exemplo: `(John Doe, Criação, 01/12/2023 12:50:30)`.

**Logs**
- [x] Descobrir qual usuário mudou o preço do produto `iphone 8` por último.

**Correção de bug**
- [x] `composer test` apontava erros que precisavam ser resolvidos, com documentação da causa e da solução.

### Novas funcionalidades

**Traduções**
- [x] Cadastro de traduções para categorias via `POST /categories/:id` com uma lista de `{lang_code, label}`;
- [x] Traduções repetidas não podem ser cadastradas — se uma do lote for repetida, nenhuma persiste;
- [x] Parâmetro opcional `lang` nas buscas de produtos/categorias, com fallback pro inglês quando não há tradução ou o parâmetro não é informado;
- [x] Rota e coleção do Postman atualizadas.

**Estoque**
- [x] Cadastro de estoque do produto (padrão `0` se omitido);
- [x] Atualização de estoque;
- [x] Filtro por quantidade mínima em estoque na busca.

**Comentários** — área de discussão sobre produtos:
- [x] Criar comentário em um produto;
- [x] Responder um comentário (qualquer comentário pode ser respondido, sem limite de profundidade);
- [x] Remover um comentário próprio;
- [x] Curtir um comentário;
- [x] Listar comentários de um produto em estrutura hierárquica.

### Docker
Premissas do ambiente Docker:
- Uso por outros devs sem necessidade de editar arquivos pra inserir dados específicos de máquina;
- PHP servido na porta **8000** do host.

**Itens obrigatórios**
- [x] Ambiente Docker com PHP na porta 8000;
- [x] Migrations executáveis/criáveis por Docker;
- [x] Testes unitários executáveis por Docker.

**Desafios**
- [x] Substituir SQLite por MySQL servido em container;
- [x] Novos testes unitários para funcionalidades faltantes;
- [x] Linter disponível via Docker;
- [x] Análise estática disponível via Docker;
- [x] Script `check_deploy.sh` rodando lint + análise estática + testes como pipeline de validação.

## Notas de Desenvolvimento

### Correções de bugs

**Categoria errada na listagem de produtos**

Alguns produtos vinham com a categoria errada na listagem, como o `blue trouser` aparecendo na categoria `phone`. O problema estava no join da listagem: ele usava o id da linha da `product_category` em vez do `cat_id`, então a categoria vinha sem relação com o produto. Ajustei o join para o `cat_id` e as categorias começaram a puxar corretamente. Com isso, vários produtos que nem estavam aparecendo voltaram para a listagem (12 dos 18 estavam invisíveis, porque o join errado descartava eles).

**Categoria null na busca individual**

Alguns produtos vinham com a categoria `null` ao serem pesquisados individualmente, como o `iphone 8`. Isso acontecia porque as categorias padrão têm `company_id` NULL no banco (elas não pertencem a nenhuma empresa específica, valem para todas), e a busca exigia `company_id` igual à empresa do admin, filtro que o NULL nunca passava. Arrumei o filtro para aceitar também os casos em que o `company_id` é NULL.

**Produto em várias categorias aparecendo em apenas uma**

O produto `king size bed` foi cadastrado em mais de uma categoria, mas aparecia apenas na `furniture` na busca individual. O motivo é que o código buscava só a primeira categoria do produto e o model tinha um campo único `category` no singular. Refiz para buscar todas as categorias do produto e o campo virou `categories`, retornando a lista com o nome de cada uma.

**Erros nos testes unitários**

O `composer test` falhava no teste do `hydrateByFetch`: ele fazia o preço em int, então de 99.99 ficava 99, e o teste comparava com 99.99. A solução foi trocar o cast para float. O mesmo bug truncava o preço na API de verdade, a `king size bed` de 4520.83 ficava 4520 na busca individual.

**Coluna de logs do relatório**

O relatório não mostrava a coluna de logs corretamente. A coluna imprimia a palavra "Array" no lugar dos dados. Para chegar ao formato pedido (Nome do usuário, Tipo de alteração, Data), fiz a consulta dos logs buscar também o nome do usuário (antes só vinha o id), os tipos de alteração passaram a ser exibidos em português (Criação, Atualização, Remoção) e a data segue o padrão dd/mm/aaaa hh:mm:ss. Também corrigi dois efeitos que a alteração das categorias tinha causado no relatório: produtos com mais de uma categoria apareciam duplicados e a coluna de categorias vinha vazia. Agora cada produto aparece uma vez, com as categorias agrupadas.

**Qual usuário mudou o preço do iphone 8 por último**

Não existe registro de qual campo foi alterado, os logs só guardam que o produto sofreu uma alteração. As duas últimas atualizações do iphone 8 foram de rivers às 18:08:12 e de corin às 18:12:10 do dia 22/12/2023. Então quem mudou o preço por último foi corin.

### Mudanças na API

Um produto pode pertencer a várias categorias, e um campo de texto único não representa isso. Por isso o campo `category` (texto) virou `categories` (lista) nas rotas `GET /products` e `GET /products/{id}`.

### Filtros e ordenação na listagem de produtos

A listagem `GET /products` aceita três parâmetros opcionais via query string:

- `active`: filtra por produtos ativos (`1`) ou inativos (`0`)
- `category`: filtra por categoria, recebendo o id dela (ex: `category=6`)
- `order`: ordena pela data de cadastro, aceitando `asc` ou `desc`

Os filtros podem ser combinados (ex: `/products?active=1&category=6&order=desc`) e a listagem retorna apenas os itens dentro dos filtros. Sem nenhum parâmetro, a listagem retorna todos os produtos, como antes.

Se algum parâmetro vier com valor inválido, a API responde com status 400 e uma mensagem dizendo qual foi o problema, por exemplo: `{"error": "valor do parametro active invalido"}`.

No filtro de categoria, um produto que pertence a mais de uma categoria aparece na busca de qualquer uma delas, e o campo `categories` continua mostrando todas. A `king size bed` filtrada por `house` aparece com `["furniture", "house"]`.

### Traduções de categorias

A rota `POST /categories/:id` recebe uma lista de traduções (com `lang_code` e `label`) e cadastra todas de uma vez. O cadastro é atômico: se uma única tradução do lote for repetida (dentro do próprio lote ou já existente no banco), nenhuma é cadastrada e a API responde 400 com a mensagem "traducao repetida, nenhuma foi cadastrada".

Nas buscas de produtos e de categorias existe o parâmetro opcional `lang` (ex: `/products?lang=pt`), que retorna os nomes das categorias traduzidos. Sem o parâmetro, ou quando a categoria não tem tradução no idioma pedido, o texto vem em inglês, que é o padrão.

Para guardar as traduções, existe uma migration com a tabela `category_translation` e um índice único de categoria + idioma, que faz o próprio banco impedir traduções repetidas. A migration tem o `down()` funcional (testado aplicar, reverter e reaplicar).

### Estoque de produtos

Os produtos têm uma contagem de estoque. No cadastro, o campo `stock` pode ser informado junto dos outros dados e, se não for informado, o estoque começa em 0. O campo não aceita valor negativo ou inválido, nesses casos a API responde 400.

A atualização é feita pelo `PUT /products/{id}` normal, mandando o campo `stock` no corpo. Se o campo não vier na atualização, o estoque atual é mantido — regra diferente de propósito da regra do cadastro: editar um produto sem falar de estoque não pode zerar o estoque dele.

Na listagem existe o filtro `min_stock` (ex: `/products?min_stock=1`), que retorna só os produtos com estoque maior ou igual ao valor pedido. Ele pode ser combinado com os outros filtros, e valor inválido recebe 400.

### Comentários nos produtos

Os produtos têm uma área de comentários. É possível comentar em um produto, responder qualquer comentário (inclusive responder uma resposta, sem limite de profundidade), curtir um comentário, apagar os próprios comentários e listar todos os comentários de um produto em uma estrutura hierárquica.

Rotas:

- `POST /products/{id}/comments` cria um comentário no produto
- `POST /comments/{id}/replies` responde um comentário
- `POST /comments/{id}/like` curte um comentário (cada usuário pode curtir cada comentário uma vez, a segunda tentativa recebe 400)
- `DELETE /comments/{id}` remove um comentário (só o próprio autor pode, tentar remover comentário de outra pessoa recebe 403)
- `GET /products/{id}/comments` lista os comentários do produto

Na remoção, toda a cascata vai junto: as respostas do comentário (e as respostas delas) e as curtidas são removidas na mesma transação, para não ficar resposta perdida quebrando a estrutura.

A listagem retorna a estrutura hierárquica: os comentários principais do produto, cada um com autor, contagem de curtidas e a lista de respostas aninhadas, respostas dentro de respostas.

### Testes automatizados

Testes cobrindo a atomicidade do cadastro de traduções de categorias, as regras da estrutura de comentários (herança do produto nas respostas, curtida duplicada, permissão de remoção e a cascata) e os filtros da listagem de produtos (ativo/inativo, categoria, estoque mínimo e tradução). Os testes rodam contra um banco SQLite em memória, isolado do banco de desenvolvimento, para não depender nem interferir nos dados reais.

Para viabilizar isso, os services aceitam a conexão do banco por parâmetro no construtor, além de continuarem buscando a conexão padrão sozinhos quando nenhuma é informada.

### Linter (PSR-12)

PHP_CodeSniffer (`squizlabs/php_codesniffer`), configurado com o padrão PSR-12. Para verificar: `docker compose run --rm app composer lint`. Para corrigir automaticamente o que for possível: `docker compose run --rm app composer lint-fix`.

O lint encontrou indentação incorreta, estruturas de controle sem chaves e espaços em branco sobrando, corrigidos em sua maioria pelo `lint-fix`. O que sobrou foram avisos de linha longa (mais de 120 caracteres) na assinatura de vários métodos dos controllers — quebrei a assinatura de todos no mesmo formato (um parâmetro por linha) para manter consistência em vez de corrigir só os casos que tinham nome de método mais longo.

### Análise estática (PHPStan)

O PHPStan analisa o código sem executar, procurando erros de lógica e de tipo: método que não existe, tipo incompatível, valor que pode vir null sem tratamento. Para rodar: `docker compose run --rm app composer stan`.

Comecei testando no nível 0 (mais permissivo), subindo até o nível 5, onde parei por não ver motivo pra ir além pro escopo do projeto.

No nível 5 apareceu um erro no `JsonResponseMiddleware`: ele declarava que devolvia a implementação concreta de resposta do Slim, mas o método que chama só garante devolver a interface genérica de resposta. Corrigido para falar com a interface em vez da implementação específica, seguindo o PSR-15.

### check_deploy.sh

Script na raiz do projeto que roda três validações em sequência: lint (PSR-12), PHPStan (tipos e lógica) e testes automatizados. Se qualquer passo falhar, o script para imediatamente (`set -e`) e não chega na mensagem final de sucesso.

Para rodar: `sh check_deploy.sh`. No Windows precisa de um terminal que entenda shell script, como o Git Bash.

### Migrando o banco de SQLite para MySQL

Troca do SQLite pelo MySQL, servido em container. As tabelas originais do projeto (produto, categoria, usuário, empresa e os vínculos entre eles) nunca tiveram uma migration própria, só existiam dentro do `db.sqlite3` original — foi preciso criar uma migration com o esquema dessas tabelas, com data anterior às demais, pra rodar primeiro.

Optei por não colocar chaves estrangeiras de verdade no MySQL. O SQLite original também as declarava no texto, mas nunca as impunha de fato, e o banco tem logs apontando para usuários que não existem (o "usuario desconhecido" do relatório) — uma chave estrangeira real impediria esses registros de existir.

Usando o `db-backup.sqlite3` (estado original do banco), remontei os dados de fábrica através de uma seed, com os mesmos ids e valores do banco desde o primeiro commit do projeto.

Para validar a troca: derrubei todas as migrations até o banco ficar vazio, migrei e semeei de novo do zero, e bati nas rotas da API para confirmar que os dados voltavam exatamente como no estado de fábrica (18 produtos, 6 categorias, e assim por diante).

### Tratamento de erros e validação do admin_user_id

Esquecer o header `admin_user_id` fazia a API responder com um erro cru do PHP, sem explicação. Duas soluções complementares:

O middleware de erro do Slim transforma qualquer exceção não tratada numa resposta JSON limpa, em vez da página de erro crua do PHP.

Um middleware específico (`RequireAdminUserMiddleware`) valida o header antes de qualquer rota rodar: se estiver ausente ou não for um número, responde `400`; se o id não corresponder a um usuário real no banco, responde `401`. As rotas que não usam esse header (como `/companies`) ficam de fora dessa validação.

### Observações (na ordem do desenvolvimento)

1. No preparo do ambiente Docker, o comando de reverter migration era `rollback` no README e `rollback-migration` no `composer.json` — alterei o `composer.json` para `rollback`.

2. A listagem de produtos usava o id do usuário como o id da empresa. Funcionava porque o banco só tinha uma empresa e todos os admins eram dela, mas teria problemas no futuro. Modifiquei para descobrir a empresa do admin na tabela `admin_user` antes de filtrar.

3. Na tabela de logs havia dois registros apontando para usuários que não existem no banco (ids 5 e 6). Com o LEFT JOIN esses logs continuam aparecendo no relatório, e no lugar do nome aparece "usuario desconhecido", pra manter a informação da alteração mesmo sem saber quem fez.

4. Durante o desenvolvimento do fallback das traduções, avaliei fazer a escolha do texto via PHP ou via SQL, e optei pelo COALESCE no SQL, que roda em qualquer banco. Assim as operações de conjunto (filtros e joins) ficam no banco e a apresentação fica no PHP.

5. Primeiro contato com o Phinx nas migrations. Primeiro teste deu errado: arquivo na pasta errada, nome da classe fora do padrão esperado e pasta de destino sem permissão. Depois de entender o fluxo certo (criar pelo `composer create-migration` via docker), a migration saiu reversível e validada nos dois sentidos.

6. Atualizei a coleção do Postman com as novas funções: `insertTranslations` (facilita testar a estrutura de `lang`), parâmetros de busca documentados nas requests, e removidas barras finais nas URLs que causavam 404.

7. Na remoção de comentários, cogitei o estilo Reddit (comentário removido vira "comentario removido", preservando respostas). Mas pensando na proposta de loja, não faz sentido manter esse histórico de discussão — apagar remove de verdade, levando a cascata de respostas e curtidas.

8. Entendi melhor como o PHPUnit testa código dependente de banco usando SQLite em memória. No meio do caminho, o banco de teste retornava array em vez de objeto por faltar configurar o mesmo modo de busca (fetch mode) que o `DB.php` usa.

9. Instalando o linter, o composer avisou sobre 5 vulnerabilidades conhecidas nas dependências (slim/slim, symfony/yaml e phpunit/phpunit). Rodei `composer audit`, atualizei slim/slim e symfony/yaml dentro da faixa já permitida pelo `composer.json`, e mudei phpunit de 9.5 fixo pra faixa `^9.6`. Depois o `composer audit` não encontrou mais vulnerabilidades.

10. Boa parte da indentação inconsistente que fui corrigindo provavelmente vinha do Prettier rodando no editor, que por padrão não segue PSR-12. Optei por manter o PHP_CodeSniffer mesmo assim, pra garantir que a verificação funcione igual pra qualquer pessoa que clone o projeto, e não só na configuração pessoal do editor.

11. Rodando o ambiente docker num notebook novo, o git reclamou do diretório `/app` (aviso de "dubious ownership"): o container roda como root, mas o volume `/app` pertence ao usuário do host. Resolvido com `git config --global --add safe.directory /app` no Dockerfile.

12. Várias rotas devolviam sucesso ou um erro de PHP cru em vez de explicar o que faltou: buscar um produto inexistente gerava avisos de PHP e um objeto vazio, atualizar/remover um id inexistente respondia 200 como se tivesse dado certo, e dava pra comentar num produto inexistente ou cadastrar um produto amarrado a uma empresa/categoria inexistente (sem chave estrangeira real entre essas tabelas). Ajustadas as rotas de produtos, categorias e comentários pra checar a existência do registro antes de agir.

13. No relatório (`GET /report`, que devolve HTML), os dados do produto eram colocados direto na tabela sem tratamento — um título com `<script>` seria interpretado pelo navegador, um risco de XSS. Corrigido passando os textos por `htmlspecialchars` antes de montar a tabela.

14. A categoria sempre filtrava por `company_id` (`getAll`, `getOne`, `updateOne`, `deleteOne`), mas o produto nunca fazia isso: qualquer admin conseguia ver, editar ou remover produto de outra empresa, e cadastrar produto em nome de uma empresa que não era a dele. Ajustado o produto pra seguir o mesmo padrão da categoria: `getOne`, `updateOne` e `deleteOne` só enxergam produtos da empresa do próprio admin, e o cadastro só aceita o `company_id` da empresa dele.

15. Criado o seed `SegundaEmpresaSeeder` (rodado via `composer seed-segunda-empresa`, fora do seed padrão) pra comprovar na prática o isolamento entre empresas do item 14: insere uma segunda empresa, um admin dela e um produto, permitindo testar pelo Postman que o admin de uma empresa recebe 404 ao tentar ver/editar/remover produto de outra.

16. O `db-backup.sqlite3` nunca tinha sido versionado, porque a regra `db/db*.sqlite3` do `.gitignore` original pegava tanto ele quanto o banco de trabalho. Como o backup é um arquivo de referência deliberado (usado pra extrair os dados de fábrica da seed), ajustado o `.gitignore` pra continuar ignorando o `db.sqlite3` mas permitir o `db-backup.sqlite3`.

17. Revisando os services (Product, Category, Company), boa parte das queries montava o SQL concatenando as variáveis direto na string (ex: `WHERE id = {$id}`), inclusive campos de texto vindos do corpo da requisição como o título do produto — abrindo brecha pra SQL injection. Troquei essa montagem por parâmetros nomeados via PDO (`:campo` + `execute([...])`) em todos os pontos afetados, no mesmo padrão que o `CommentService` já usava. O único trecho que não dá pra parametrizar é a direção do `ORDER BY` (PDO não aceita bind ali), trocado por uma checagem estrita que só aceita `ASC` ou `DESC`.

18. A lógica de montar o `JOIN` de tradução de categoria (`category_translation`) estava copiada em quatro lugares diferentes, um no `ProductService` e três no `CategoryService`. Extraída pra uma trait (`BuildsCategoryTranslationClause`), usada pelas duas classes.

19. Alguns métodos dos services devolviam tipos diferentes dependendo do caminho: `ProductService::insertOne` podia devolver `true`, `false` ou uma string tipo `'empresa invalida'`; o mesmo em `CommentService::insertLike` (`'duplicado'`) e `deleteOne` (`'proibido'`). Troquei esses casos por exceptions de domínio (`InvalidCompanyException`, `InvalidCategoryException`, `DuplicateLikeException`, `ForbiddenActionException`, `ProductNotFoundException`), lançadas pelo service e capturadas no controller pra montar a mesma resposta HTTP de antes. Os métodos agora têm um único tipo de retorno declarado (`bool` ou `\PDOStatement`).

20. Depois dessas correções, rodei o `check_deploy.sh` de novo pra confirmar que lint, PHPStan e testes continuavam passando sem apontamento novo.

21. Ao preparar o projeto pra portfólio, renomeei o namespace de `Contatoseguro\TesteBackend` para `App` (e o das classes de teste para `Tests`), atualizando `composer.json` (nome do pacote, autoload e autoload-dev). Aproveitei pra atualizar o `squizlabs/php_codesniffer`, já que o `composer update` acusou uma vulnerabilidade de OS command injection (CVE-2026-67434) numa versão anterior.
