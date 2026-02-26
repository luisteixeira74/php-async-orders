# php-async-orders

## 📌 Overview

Projeto em PHP que modela o ciclo de vida de pedidos (order) com processamento assíncrono, aplicando Clean Architecture e princípios inspirados em DDD.

O foco do projeto é representar regras de negócio e fluxo assíncrono de forma explícita,
não sendo um CRUD tradicional.

## 🎯 Objetivos

- Demonstrar Clean Architecture em PHP
- Modelar o ciclo de vida de pedidos com estados bem definidos
- Processamento assíncrono orientado a eventos
- Infraestrutura desacoplada (InMemory → RabbitMQ)
- Testes focados em comportamento e regras de domínio

## 🧱 Arquitetura

- Domain: entidades, regras e invariantes
- Application: orquestração (UseCases)
- Infrastructure: detalhes técnicos substituíveis

Dependências sempre apontam para dentro (Domain não conhece Application nem Infrastructure).

## 🔄 Fluxo do Pedido

RECEIVED → PROCESSING → PROCESSED / FAILED

Os estados do pedido são controlados por um **Enum (`OrderStatus`)**, que funciona como
fonte da verdade para transições válidas dentro do domínio.

---

## 🧪 Testes

- PHPUnit
- Foco em UseCases e domínio
- Infra InMemory

## ▶️ Requisitos

- PHP **8.2+**
  - Uso de `enum`
  - Tipagem forte
  - Construtores promovidos

## ▶️ Como rodar os testes

vendor/bin/phpunit tests --testdox

## 🐳 Executando com Docker (PostgreSQL)

- Para rodar o projeto com banco real:
  - docker compose up -d

- Após subir os containers, execute as migrations:
  - docker exec -it php-async-orders-app php bin/migrate.php

- Criar um pedido via CLI:
- docker exec -it php-async-orders-app php bin/create-order.php 1 99.90

O banco utilizado é PostgreSQL, configurado via .env.

---

## 🚧 Próximos passos

- Worker assíncrono
- RabbitMQ
- Docker

## 🛠️ Roadmap

Estado atual: domínio modelado, use cases implementados,
infra com PostgreSQL via Docker e migrations automatizadas.
