include .env

build:
	docker-compose up --build -d
up:
	docker-compose up -d
down:
	docker-compose stop

reload:
	make down
	make up

#db
db-restore:
	gunzip -c dumps/what_if.sql.gz | docker exec -i what-if_mysql mysql -u$(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE);
db-export:
	docker exec what-if_mysql mysqldump -u$(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE) | gzip > dumps/what_if_$(shell date +%F).sql.gz

#composer
install-composer:
	docker exec -w /var/www/what_if what-if_php composer install --no-interaction --prefer-dist --optimize-autoloader
update-composer:
	docker exec -w /var/www/what_if what-if_php composer update --no-interaction

app_bash:
	docker-compose exec -u www-data php bash
