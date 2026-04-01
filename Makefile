# ============================================================================
# DOCKER COMPOSE MANAGEMENT
# ============================================================================

restart:
	docker compose up -d --force-recreate

init:
	docker compose down && docker compose up -d --build

start:
	docker compose down && docker compose up -d

stop:
	docker compose down

supervisor:
	docker compose down supervisor && docker compose up -d --build supervisor

init-dev:
	docker compose -f docker-compose.dev.yml down && docker compose -f docker-compose.dev.yml up -d --build

supervisor-dev:
	docker compose -f docker-compose.dev.yml down supervisor && docker compose -f docker-compose.dev.yml up -d --build supervisor

bash:
	docker exec -it php_outvento bash

bash-dev:
	docker exec -it php_travel bash

# ============================================================================
# PHP / ARTISAN / COMPOSER (Production env)
# ============================================================================

artisan:
	docker compose exec -T php php artisan $(filter-out $@,$(MAKECMDGOALS))

composer:
	docker compose exec -T php composer $(filter-out $@,$(MAKECMDGOALS))

npm:
	docker compose exec -T php npm $(filter-out $@,$(MAKECMDGOALS))

migrate:
	docker compose exec -T php php artisan migrate

purge:
	docker compose exec -T php php artisan purge

swagger:
	docker compose exec -T php php artisan l5-swagger:generate

# ============================================================================
# TESTS (Production env)
# ============================================================================

test:
	docker compose exec -T php vendor/bin/phpunit

test-unit:
	docker compose exec -T php vendor/bin/phpunit tests/Unit

test-feature:
	docker compose exec -T php vendor/bin/phpunit tests/Feature

test-coverage:
	docker compose exec -T php vendor/bin/phpunit --coverage-html=storage/coverage

# ============================================================================
# PHP / ARTISAN / COMPOSER (Development env)
# ============================================================================

dev-artisan:
	docker compose -f docker-compose.dev.yml exec -T php php artisan $(filter-out $@,$(MAKECMDGOALS))

dev-composer:
	docker compose -f docker-compose.dev.yml exec -T php composer $(filter-out $@,$(MAKECMDGOALS))

dev-npm:
	docker compose -f docker-compose.dev.yml exec -T php npm $(filter-out $@,$(MAKECMDGOALS))

dev-migrate:
	docker compose -f docker-compose.dev.yml exec -T php php artisan migrate

dev-purge:
	docker compose -f docker-compose.dev.yml exec -T php php artisan purge

dev-swagger:
	docker compose -f docker-compose.dev.yml exec -T php php artisan l5-swagger:generate

# ============================================================================
# TESTS (Development env)
# ============================================================================

dev-test:
	docker compose -f docker-compose.dev.yml exec -T php vendor/bin/phpunit

dev-test-unit:
	docker compose -f docker-compose.dev.yml exec -T php vendor/bin/phpunit tests/Unit

dev-test-feature:
	docker compose -f docker-compose.dev.yml exec -T php vendor/bin/phpunit tests/Feature

dev-test-coverage:
	docker compose -f docker-compose.dev.yml exec -T php vendor/bin/phpunit --coverage-html=storage/coverage

# ============================================================================
# HELP
# ============================================================================

help:
	@echo "Available commands:"
	@echo ""
	@echo "Docker Compose:"
	@echo "  make init              - Initialize project (down + up -d --build)"
	@echo "  make start             - Start containers (down + up -d)"
	@echo "  make restart           - Restart containers (up -d --force-recreate)"
	@echo "  make stop              - Stop containers"
	@echo "  make bash              - Open bash in PHP container (prod)"
	@echo "  make bash-dev          - Open bash in PHP container (dev)"
	@echo ""
	@echo "Artisan / Composer (Production):"
	@echo "  make artisan <cmd>     - Run artisan command (e.g., make artisan db:seed)"
	@echo "  make composer <cmd>    - Run composer command (e.g., make composer install)"
	@echo "  make npm <cmd>         - Run npm command (e.g., make npm install)"
	@echo "  make migrate           - Run migrations"
	@echo "  make purge             - Run purge command"
	@echo "  make swagger           - Generate swagger docs"
	@echo ""
	@echo "Tests (Production):"
	@echo "  make test              - Run all tests"
	@echo "  make test-unit         - Run unit tests"
	@echo "  make test-feature      - Run feature tests"
	@echo "  make test-coverage     - Run tests with coverage report"
	@echo ""
	@echo "Development (use dev- prefix):"
	@echo "  make dev-artisan       - Artisan in dev env"
	@echo "  make dev-composer      - Composer in dev env"
	@echo "  make dev-npm           - NPM in dev env"
	@echo "  make dev-test          - Tests in dev env"
	@echo "  make dev-migrate       - Migrations in dev env"
	@echo ""

.PHONY: help
