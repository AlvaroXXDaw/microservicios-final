up:
	docker compose -f compose.yml up -d --build

down:
	docker compose -f compose.yml down

logs:
	docker compose -f compose.yml logs -f

install:
	docker compose -f compose.yml exec -T backend composer install

migrate:
	docker compose -f compose.yml exec -T backend php bin/console doctrine:migrations:migrate --no-interaction
