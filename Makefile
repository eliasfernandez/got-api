.PHONY: behat db

null:
	@echo -n "No task given!"

up:
	docker compose down -v
	docker compose up -d

seed:
	./console doctrine:database:drop --force --if-exists
	./console doctrine:database:create
	./console doctrine:schema:create
	./console doctrine:fixtures:load -n

