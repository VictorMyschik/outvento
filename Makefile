restart:
	docker-compose up -d --force-recreate

init:
	docker-compose down && docker-compose up -d --build

stop:
	docker-compose down

supervisor:
	docker-compose down supervisor && docker-compose up -d --build supervisor


