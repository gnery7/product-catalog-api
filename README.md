# Product Catalog API

API REST multi-empresa para catálogo de produtos: categorias com tradução por idioma, controle de estoque, comentários hierárquicos com curtidas, relatório em HTML e isolamento total de dados entre empresas.

Construída em PHP 8.3 com Slim Framework, MySQL via Docker, migrations versionadas e uma suíte de qualidade (testes automatizados, análise estática e lint) rodando como pipeline local.

## Funcionalidades

- **Produtos** — CRUD completo, múltiplas categorias por produto, controle de estoque, filtros (ativo/inativo, categoria, estoque mínimo) e ordenação por data de cadastro
- **Categorias** — CRUD, categorias globais (padrão) e por empresa, tradução por idioma com fallback automático
- **Comentários** — criação, respostas aninhadas em qualquer profundidade, curtidas (uma por usuário), remoção em cascata
- **Relatório** — HTML com histórico de alterações de cada produto (quem alterou, o quê, quando)
- **Isolamento multi-empresa** — cada administrador só enxerga e altera dados da própria empresa
- **Autenticação simples** — header `admin_user_id`, validado por middleware antes de qualquer rota protegida rodar

## Stack

- PHP 8.3 + [Slim Framework 4](https://www.slimframework.com/)
- MySQL 8, servido via Docker
- [Phinx](https://book.cakephp.org/phinx/0/en/index.html) para migrations reversíveis
- PHPUnit para testes automatizados (SQLite em memória, isolado do banco de desenvolvimento)
- PHPStan (nível 5) para análise estática
- PHP_CodeSniffer (PSR-12) para lint

## Como rodar

```bash
docker compose up -d
docker compose run --rm app composer install
docker compose run --rm app composer migrate
docker compose run --rm app composer seed
```

A API sobe em `http://localhost:8000`. Toda rota, exceto `/companies`, exige o header `admin_user_id` com o id de um administrador existente.

## Testes e qualidade

```bash
docker compose run --rm app composer test   # PHPUnit
docker compose run --rm app composer stan   # PHPStan (nível 5)
docker compose run --rm app composer lint   # PSR-12
sh check_deploy.sh                          # roda os três em sequência, para no primeiro erro
```

## Documentação da API

- Coleção Postman: [`docs/postman-api.json`](docs/postman-api.json)
- Documentação em PDF: [`docs/api-docs.pdf`](docs/api-docs.pdf)

## Destaques de engenharia

- **Segurança**: toda query usa parâmetros nomeados via PDO — nenhuma interpolação direta de valor de usuário em SQL, inclusive nos pontos que não aceitam bind (como a direção de `ORDER BY`), resolvidos com whitelist estrita.
- **Erros modelados como exceptions**: regras de negócio (empresa/categoria inválida, comentário duplicado, ação proibida) usam exceptions de domínio próprias em vez de retornos com tipos mistos (`bool|string`), mantendo a assinatura dos métodos consistente.
- **DRY entre services**: a lógica de tradução de categoria (compartilhada entre produtos e categorias) foi extraída para uma trait reaproveitada pelas duas classes.
- **Migrations reversíveis** com Phinx, incluindo a migração de SQLite para MySQL preservando os dados de fábrica originais.
- **Pipeline de validação local** (`check_deploy.sh`) rodando lint, análise estática e testes antes de considerar o código pronto.

Para o histórico completo — requisitos originais, bugs encontrados e o raciocínio por trás de cada decisão — ver [`docs/DECISIONS.md`](docs/DECISIONS.md).
