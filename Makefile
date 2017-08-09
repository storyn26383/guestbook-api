database=$(shell basename `pwd`)

include .env

init: .env
	composer install
	make initdb

initdb: .env
	@if [ ${DB_PASSWORD} ]; then\
		echo 'CREATE DATABASE IF NOT EXISTS `${DB_DATABASE}`' | mysql -u${DB_USERNAME} -h${DB_HOST} -p${DB_PASSWORD};\
	else\
		echo 'CREATE DATABASE IF NOT EXISTS `${DB_DATABASE}`' | mysql -u${DB_USERNAME} -h${DB_HOST};\
	fi
	php artisan migrate:reset
	php artisan migrate

.env:
	@if [ ! -f .env ]; then\
		echo -n 'copy .env.example to .env ...';\
		cp .env.example .env;\
		sed -i -e 's/\(DB_DATABASE=\)homestead/\1${database}/g' .env;\
		echo 'OK';\
	fi
