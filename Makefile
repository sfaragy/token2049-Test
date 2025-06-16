.PHONY: build project_init start stop restart app-login db-login logs clear-log add-hosts-entry

build: stop add-hosts-entry
	docker-compose -f docker-compose.yml build

project_init: start
	docker exec -it token2049_app_1 /bin/bash -c "composer install"
	docker exec -it token2049_app_1 /bin/bash -c "php artisan storage:link"
	docker exec -it token2049_app_1 /bin/bash -c "php artisan key:generate"
	docker exec -it token2049_app_1 /bin/bash -c "php artisan migrate"
	docker exec -it token2049_app_1 /bin/bash -c "php artisan db:seed"
	docker exec -it token2049_app_1 chown -R www-data:www-data storage bootstrap/cache
	docker exec -it token2049_app_1 chmod -R 775 storage bootstrap/cache

stop:
	docker-compose -f docker-compose.yml down

start:
	docker-compose -f docker-compose.yml up --remove-orphans -d

restart: stop start

logs:
	docker-compose -f docker-compose.yml token2049_app_1 storage/logs --tail=10 -f $(c)

clear-log:
	docker exec -it token2049_app_1 /bin/bash -c "sed -i '/^/d' storage/logs/laravel.log"

app-login:
	docker exec -it token2049_app_1 /bin/bash

db-login:
	docker exec -it token2049_db_1 mysql -uroot -p"root"

add-hosts-entry:
	@if ! grep -q "token2049.local.com" /etc/hosts; then \
		echo "Adding token2049.local.com to /etc/hosts"; \
		echo "127.0.0.1   token2049.local.com" | sudo tee -a /etc/hosts; \
	else \
		echo "Entry for token2049.local.com already exists in /etc/hosts"; \
	fi