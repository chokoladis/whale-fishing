include .env

build:
	docker-compose up --build -d
up-base:
	docker-compose up -d php nginx db
up-full:
	docker-compose up -d
down:
	docker-compose stop

reload:
	make down
	make up-base

#db
db-restore:
	gunzip -c dumps/wf.sql.gz | docker exec -i wf_db psql -U$(DB_USERNAME) -d$(DB_DATABASE);
db-export:
	docker exec wf_db pg_dump -U$(DB_USERNAME) $(DB_DATABASE) | gzip > dumps/wf_$(shell date +%F).sql.gz

app_bash:
	docker-compose exec -u www-data php bash

#testing
test-prepare-cleardata:
	docker exec -w /var/www/app wf_php php bin/console --env=test doctrine:database:create
	docker exec -w /var/www/app wf_php php bin/console --env=test doctrine:migrations:migrate

test-prepare-realdata:
	docker exec -w /var/www/app wf_php php bin/console --env=test doctrine:database:create
	docker exec wf_db pg_dump -U${DB_USERNAME} ${DB_DATABASE} > dumps/dump.sql
	docker exec -i wf_db psql -U${DB_USERNAME} ${DB_DATABASE}_test < dumps/dump.sql

test:
	docker exec -w /var/www/app wf_php php bin/phpunit
