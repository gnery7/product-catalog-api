#!/bin/sh
set -e

echo "==> Rodando o linter (PSR-12)..."
docker compose run --rm app composer lint

echo "==> Rodando a analise estatica (PHPStan)..."
docker compose run --rm app composer stan

echo "==> Rodando os testes automatizados..."
docker compose run --rm app composer test

echo "==> Tudo passou! Codigo pronto para producao."
