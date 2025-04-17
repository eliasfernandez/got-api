# Game Of Thrones RESTful API

This project implements a RESTful API to manage and search Game of Thrones characters and actors, using a JSON dataset as the initial data source. It demonstrates domain-driven design (DDD), message-based synchronization, and a clean architecture pattern, all containerized via Docker Compose.

## Tech Stack

* Language: PHP 8.2 (Symfony 7.2)
* Database: PostgreSQL
* Search Engine: Elasticsearch
* Message Queue: RabbitMQ
* ORM: Doctrine
* Testing: PHPUnit
* Containerization: Docker + Docker Compose

## Getting Started

### Prerequisites

* Docker + Docker Compose installed
* Make installed (optional but recommended)

### Quick Setup

```bash
# Extract the files or clone the repo
git clone https://github.com/eliasfernandez/got-api.git
cd got-api

# Provision all the containers and install composer dependencies
make bootstrap

# Seed the database and Elasticsearch
make seed

```
This will start PHP, PostgreSQL, Elasticsearch, and RabbitMQ containers, and initialize the database with sample data.

### Running the tests

```bash
# Unit tests (no database or infrastructure)
make test-unit

# Integration tests
make test-integration

# All tests (includes integration)
make test-all
```

You can also pass test paths or filters:

```bash
make test-unit args="tests/Domain/Actor"
```

### Project structure

```
src/
├── Application/       # Use cases, commands, handlers
├── Domain/            # Core entities, interfaces, exceptions
├── Infrastructure/    # Doctrine, Elasticsearch, and RabbitMQ implementations
└── Interface/         # HTTP controllers
```

### Openapi structure

This API is based on the openapi.yml located on the project folder. To open it an play with the API requests, start the containers and visit: http://localhost:8080/api/swagger or use the make command:
```bash
make swagger
```

### Data Sync Architecture

Changes to entities (create, update, delete) are published to RabbitMQ. A background consumer listens and syncs Elasticsearch accordingly. This ensures eventual consistency across systems.

### Useful Make Commands

| Command               | Description                    |
|-----------------------|--------------------------------|
| make bootstrap        | Provision the containers       |
| make up               | Start all containers           |
| make down             | Stop containers                |
| make seed             | Setup database and seed        |
| make test-all         | Run full test suite            |
| make test-unit        | Run unit tests only            |
| make test-integration | Run integration tests only     |
| make purge-queues     | Purge Rabbitmq messages queue  |
| make swagger          | Open a swagger ui to play with |

