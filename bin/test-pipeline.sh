#!/bin/bash

set -e

echo "🚀 Subindo containers..."
docker compose up -d

echo "⏳ Aguardando banco subir..."
sleep 5

echo "📦 Rodando migrations..."
docker compose exec app php bin/migrate.php

echo "✅ Ambiente pronto!"
echo ""
echo "Próximos passos:"
echo "1. Criar order:"
echo "   docker compose exec app php bin/create-order.php 123 50.99"
echo ""
echo "2. Processar orders:"
echo "   docker compose exec app php bin/process-order.php"
echo ""
echo "3. Rodar projection:"
echo "   docker compose exec app php bin/run-projection-consumer.php"
echo ""
echo "4. Subir servidor:"
echo "   docker compose exec app php -S 0.0.0.0:8000 -t public"
echo ""
echo "5. Acessar:"
echo "   http://localhost:8000/orders"