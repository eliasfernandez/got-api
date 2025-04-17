null:
	@echo -n "No task given!"

bootstrap:
	docker compose up --build -d
	./composer install

up:
	docker compose down -v
	docker compose up --build -d

down:
	docker compose down -v

seed:
	./console doctrine:database:drop --force --if-exists
	./console doctrine:database:create
	./console doctrine:schema:create
	./console doctrine:fixtures:load -n

purge-queues:
	docker compose exec rabbitmq rabbitmqadmin purge queue name=messages

test-all:
	./phpunit --bootstrap=tests/bootstrap.php $(args)

test-unit:
	./phpunit --bootstrap=tests/bootstrap-unit.php --testsuite=unit $(args)

test-integration:
	./phpunit --bootstrap=tests/bootstrap.php --testsuite=integration $(args)

swagger:
	docker compose up --build -d
	open http://localhost:8080/api/swagger

