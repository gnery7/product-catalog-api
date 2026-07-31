# DESAFIO BACKEND

## Sobre o teste
O objetivo do teste é avaliar suas habilidades como programador **backend PHP**.

Você receberá uma aplicação que necessita de ajustes descritos pelo cliente e correções de bugs apontadas, deve resolvê-los com a maior qualidade e organização de código possível. Serão avaliados: domínio da linguagem, resolução de problemas, performance, segurança e organização.

Existe uma seção ao fim do _README_ chamada **"Suas Respostas, Dúvidas e Observações"** reservada para documentação do processo de desenvolvimento, mudanças na API e lógica também devem ser documentadas.

## Configuração do Ambiente

### Requisitos
- _PHP >= 8.0_ e [extensões](https://www.php.net/manual/pt_BR/extensions.php) (**não esquecer de instalar as seguintes extensões: _pdo_, _pdo_sqlite_ e _sqlite3_**);
- [_SQLite_](https://www.sqlite.org/index.html);
- [_Composer_](https://getcomposer.org/).

### Instalação
- Instalar dependências pelo _composer_ com `composer install` na raiz do projeto;
- Servir a pasta _public_ do projeto através de algum servidor.
  (_Sugestão [PHP Built in Server](https://www.php.net/manual/en/features.commandline.webserver.)_. Exemplo para servir a pasta public: `php -S localhost:8000 -t public`)

## Sobre a entrega
>[!CAUTION]
> A entrega deve ser realizada em um repositório **_PRIVADO_** do **GitHub**;
> 
> Você deve adicionar os usuários [`pedrosobucki`](https://github.com/pedrosobucki) e [`aloefflerj`](https://github.com/aloefflerj) como colaboradores do repositório com permissão de leitura para que seu teste possa ser avaliado.

- A primeira etapa é realizar um commit inicial com o código sem nenhuma modificação;
- As modificações devem estar separadas por commits coerentes com as funcionalidades e mudanças realizadas ao longo do processo, não um único commit com todas as modificaçẽs;
- As soluções elaboradas e implementadas por você devem ser apresentadas na seção **Suas Respostas, Dúvidas e Observações** ao fim do _README_.

## Sobre o Projeto
- O cliente XPTO Ltda. contratou seu serviço para realizar alguns ajustes em seu sistema de cadastro de produtos;
- O sistema permite o cadastro, edição e remoção de _produtos_ e _categorias de produtos_ para uma _empresa_;
- Para que sejam possíveis os cadastros, alterações e remoções é necessário um usuário administrador;
- O sistema possui categorias padrão que pertencem a todas as empresas, bem como categorias personalizadas dedicadas a uma dada empresa. As categorias padrão são: (`clothing`, `phone`, `computer` e `house`) e **devem** aparecer para todas as _empresas_;
- O sistema tem um relatório de dados dedicado ao cliente.

## Sobre a API
As rotas estão divididas em:
  - _CRUD_ de _categorias_;
  - _CRUD_ de _produtos_;
  - Rota de busca de um _relatório_ que retorna um _html_.

Deve ser utilizado o [Postman](https://www.postman.com/) para desenvolvimento e documentação, o arquivo para importação das rotas se encontra em `docs/postman-api.json`.

> [!WARNING]
> É importante que se adicione o _header_ `admin_user_id` com o id do usuário desejado ao acessar as rotas para simular o uso de um usuário no sistema.

A documentação da API se encontra na pasta `docs/api-docs.pdf`
  - A documentação assume que a url base é `localhost:8000` mas você pode usar qualquer outra url ao configurar o servidor;
  - O _header_ `admin_user_id` na documentação está indicado com valor `1` mas pode ser usado o id de qualquer outro usuário caso deseje (_pesquisando no banco de dados é possível ver os outros id's de usuários_).

## Sobre o Banco de Dados
- O banco de dados é um _sqlite_ simples e já vem com dados preenchidos por padrão no projeto;
- O banco tem um arquivo de backup em `db/db-backup.sqlite3` com o estado inicial do projeto caso precise ser "resetado".

### Migrations
- Funcionalidades que exijam modificações no banco de dados (seja nos dados ou estrutura) **devem estar contidas em _migrations_**, não enviadas diretamente com o banco;
- **Seu arquivo de banco** `db.sqlite3` **não será utilizado para avaliação** do teste, por isso é importante persistir mudanças necessárias em migrations;
- A biblioteca utilizada para migrations foi o [**_Phinx_**](https://book.cakephp.org/phinx/0/en/index.html);
- As migrations criadas **devem poder ser revertidas** (método `down()`);
- Para interagir com as migrations, você pode usar os seguintes comandos:
- - Criar nova migration: `composer create-migration`
- - Rodar migrations: `composer migrate`
- - Reverter migration: `composer rollback`

# Demandas
Abaixo, as solicitações do cliente:

## Alterações
Modificações requisitadas pelo cliente em funcionalidades já existentes

### Categorias
- [x] A categoria está vindo errada na listagem de produtos para alguns casos (_exemplo: produto `blue trouser` está vindo na categoria `phone`_);
- [x] Alguns produtos estão vindo com a categoria `null` ao serem pesquisados individualmente (_exemplo: produto `iphone 8`_);
- [x] Cadastrei o produto `king size bed` em mais de uma categoria, mas ele aparece **apenas** na categoria `furniture` na busca individual do produto.

### Filtros e Ordenamento
Para a listagem de produtos:
- [x] Gostaria de poder filtrar os produtos ativos ou inativos;
- [x] Gostaria de poder filtrar os produtos por categoria;
- [x] Gostaria de poder ordenar os produtos por data de cadastro.

### Relatório
- [ ] O relatório não está mostrando a coluna de logs corretamente, se possível, gostaria de trazer no seguinte formato:
  (Nome do usuário, Tipo de alteração e Data),
  (Nome do usuário, Tipo de alteração e Data),
  (Nome do usuário, Tipo de alteração e Data)
  Exemplo:
  (John Doe, Criação, 01/12/2023 12:50:30),
  (Jane Doe, Atualização, 11/12/2023 13:51:40),
  (Joe Doe, Remoção, 21/12/2023 14:52:50)

### Logs
- [ ] Gostaria de saber qual usuário mudou o preço do produto `iphone 8` por último.

### Correção de bug
- [x] Ao rodar os teste unitários com `composer test` são apontados erros. Eles precisam ser resolvidos, com documentação sobre a causa e a solução.

## Features
Novas funcionalidades requisitadas pelo cliente

> [!WARNING]
> Preste atenção, funcionalidades que exijam mudanças no banco de dados devem conter tais modificações em uma ou mais **migrations**.

### Traduções
- [ ] Quero disponibilizar meu sistema para fora do país, crie uma funcionalidade de cadastro de traduções para as categorias que segue o seguinte contrato:
```
POST "$base_url/categories/:id"

{
  "translations": [
    {
      "lang_code": "en",
      "label": "home"
    },
    {
      "lang_code": "pt",
      "label": "casa"
    }
  ]
}
```
- [ ] Não deve ser possível cadastrar traduções repetidas, se uma única tradução repetida foi enviada, nenhuma deve persistir;
- [ ] Ao buscar por produtos/categorias, o parâmetro adicional opcional "_`lang`_" pode ser passado para determinar a linguagem em que a categoria deve ser retornada;
- [ ] Caso não haja categoria correspondente ou não seja especificado por parâmetro, retornar em inglês;
- [ ] Inclua a rota e as modificações na coleção do Postman no repositório.

### Estoque
Além das informações já disponíveis do produto, desejo acrescentar também uma contagem de estoque para cada, a qual deve seguir algumas regras:
- [ ] Posso cadastrar a quantidade do estoque assim que cadastro um produto, mas se não for informado assumo que o estoque é _0_;
- [ ] Posso atualizar o estoque de um produto;
- [ ] Ao buscar um produto, posso filtrar por uma quantidade mínima em estoque.

### Comentários
Quero que os usuários do sistema possam discutir sobre os produtos em uma área de comentários.

Para isso, novas rotas devem ser criadas:
- [ ] Criar um novo comentário no produto
- [ ] Responder um comentário já realizado (todo comentário pode ser diretamente respondido)
- [ ] Remover um comentário feito por mim
- [ ] Curtir um comentário
- [ ] Listar todos os comentários de um produto em um objeto com hierarquia de comentários

##
**Seu trabalho é atender às demandas solicitadas pelo cliente.**

Caso julgue necessário, podem ser adicionadas ou modificadas as rotas da api. Caso altere, por favor, explique o porquê e indique as alterações nesse `README`.

Sinta-se a vontade para refatorar o que achar pertinente, considerando questões como arquitetura, padrões de código, padrões restful, _segurança_ e quaisquer outras boas práticas. Levaremos em conta essas mudanças.

## Docker
Você deve servir a aplicação por meio de um ambiente docker

Para efetuar a criação do ambiente docker, partimos de algumas premissas:
- O ambiente tem o **objetivo de ser utilizado por outros devs**, então deve funcionar sem necessidade de alteração de arquivos para inserção de dados específicos de máquina (quando baixo o repositório quero subir o container sem que fazer edições no ambiente docker);
- Levando o ponto anterior em conta, é inteligente não deixar a criação do container para o final;
- A funcionalidade de **PHP** deve rodar na porta **8000** do host.

### Itens obrigatórios
- [x] Criar um ambiente docker que sobe a aplicação **PHP** na porta **8000**;
- [x] Possibilitar que as **_migrations_ possam ser executadas/criadas por docker** (especificar comando);
- [x] Possibilitar que os **_testes unitários_ sejam executados por docker** (especificar comando).

### Desafios
- [ ] Substituir o banco serverless **SQLite** por um banco como **MySQL**/**PostgreSQL**/outro e servir por container;
- [ ] Escrever **novos testes unitários** para funcionalidades faltantes;
- [ ] Implementar um **Linter** e disponibilizar por docker (especificar comando);
- [ ] Implementar **análise estática** e disponibilizar por docker (especificar comando);
- [ ] Escrever um script "_`check_deploy.sh`_" que faz todas as validações implementadas como uma pipeline e determina se o código está pronto para produção.

## Suas Respostas, Dúvidas e Observações

### Correções de bugs

**Categoria errada na listagem de produtos**

O cliente relatou que alguns produtos vinham com a categoria errada na listagem, como o `blue trouser` aparecendo na categoria `phone`. O problema estava no join da listagem: ele usava o id da linha da `product_category` em vez do `cat_id`, então a categoria vinha sem relação com o produto. Ajustei o join para o `cat_id` e as categorias começaram a puxar corretamente. Com isso, vários produtos que nem estavam aparecendo voltaram para a listagem (12 dos 18 estavam invisíveis, porque o join errado descartava eles).

**Categoria null na busca individual**

O cliente relatou que alguns produtos vinham com a categoria `null` ao serem pesquisados individualmente, como o `iphone 8`. Isso acontecia porque as categorias padrão têm `company_id` NULL no banco (elas não pertencem a nenhuma empresa específica, valem para todas), e a busca exigia `company_id` igual à empresa do admin, filtro que o NULL nunca passava. Arrumei o filtro para aceitar também os casos em que o `company_id` é NULL.

**Produto em várias categorias aparecendo em apenas uma**

O cliente cadastrou a `king size bed` em mais de uma categoria, mas ela aparecia apenas na `furniture` na busca individual. O motivo é que o código buscava só a primeira categoria do produto e o model tinha um campo único `category` no singular. Refiz para buscar todas as categorias do produto e o campo virou `categories`, retornando a lista com o nome de cada uma.

**Erros nos testes unitários**

O `composer test` falhava no teste do `hydrateByFetch`: ele fazia o preço em int, então de 99.99 ficava 99, e o teste comparava com 99.99. A solução foi trocar o cast para float. O mesmo bug truncava o preço na API de verdade, a `king size bed` de 4520.83 ficava 4520 na busca individual.

### Mudanças na API

Um produto pode pertencer a várias categorias, e um campo de texto único não representa isso. Por isso o campo `category` (texto) virou `categories` (lista) nas rotas `GET /products` e `GET /products/{id}`. Quem consome a API precisa ajustar a leitura desse campo.

### Filtros e ordenação na listagem de produtos

A listagem `GET /products` agora aceita três parâmetros opcionais via query string:

- `active`: filtra por produtos ativos (`1`) ou inativos (`0`)
- `category`: filtra por categoria, recebendo o id dela (ex: `category=6`)
- `order`: ordena pela data de cadastro, aceitando `asc` ou `desc`

Os filtros podem ser combinados (ex: `/products?active=1&category=6&order=desc`) e a listagem retorna apenas os itens que estão dentro dos filtros. Sem nenhum parâmetro, a listagem continua retornando todos os produtos, como antes.

Se algum parâmetro vier com valor inválido, a API responde com status 400 e uma mensagem dizendo qual foi o problema, por exemplo: `{"error": "valor do parametro active invalido"}`.

No filtro de categoria, um produto que pertence a mais de uma categoria aparece na busca de qualquer uma delas, e o campo `categories` continua mostrando todas. A `king size bed` filtrada por `house` aparece com `["furniture", "house"]`.

### Observações
Por algum motivo a listagem de produtos usava o id do usuário como o id da empresa. Funcionava porque o banco só tem uma empresa e todos os admins são dela, mas teria problemas no futuro. Modifiquei para descobrir a empresa do admin na tabela `admin_user` antes de filtrar.

Observações: Durante o preparo do ambiente do docker, eu notei que no `README` o comando de reverter migration era `rollback`, no `composer.json` era `rollback-migration`, optei por alterar no `composer.json` para apenas `rollback` para seguir o `README`.

### Ambiente Docker
- Subir a aplicação: `docker compose up -d` (porta 8000)
- Instalar dependências: `docker compose run --rm app composer install`
- Rodar migrations: `docker compose run --rm app composer migrate`
- Criar migration: `docker compose run --rm app composer create-migration`
- Reverter migration: `docker compose run --rm app composer rollback`
- Rodar testes: `docker compose run --rm app composer test`
