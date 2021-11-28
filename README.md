# symfony-docker
Тестовое задание симфони + докер

## Разворачивание контейнера
docker-compose up -d

## Разворачивание проекта
docker exec -it library-php-cli bash

Далее в консоли:

composer install

php bin/console doctrine:database:create

php bin/console doctrine:migrations:migrate
