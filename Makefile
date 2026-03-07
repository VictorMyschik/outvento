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

swagger:
	docker compose exec php php artisan l5-swagger:generate

bash:
	docker exec -it php_travel bash

purge:
	docker compose exec php php artisan purge

migrate:
	docker compose exec php php artisan migrate
