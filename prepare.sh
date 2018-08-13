#!/usr/bin/env bash

docker stop $(docker ps -aq --filter name=db) > /dev/null

tmp_id=$(docker-compose run -d db)
db_ip=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}' ${tmp_id})
db_url=$(echo "mysql://FuzzerUser:FuzzerPassword123@${db_ip}:3306/FuzzyDuplicates")

pushd $(pwd)
cd ./symfony
php composer.phar install
php composer.phar dump-autoload

export DATABASE_URL=${db_url}

chmod +x ../wait-for-it.sh
../wait-for-it.sh -h ${db_ip} -p 3306 -t 30 -- echo "db online"

php bin/console doctrine:database:create --if-not-exists --no-interaction
php bin/console doctrine:migrations:migrate --no-interaction

popd

docker stop ${tmp_id}