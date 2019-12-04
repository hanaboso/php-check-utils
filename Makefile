DC=docker-compose
DE=docker-compose exec -T app

.env:
	sed -e "s/{DEV_UID}/$(shell id -u)/g" \
		-e "s/{DEV_GID}/$(shell id -u)/g" \
		.env.dist >> .env; \

# Docker
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

# Composer
composer-install:
	$(DE) composer install --ignore-platform-reqs

composer-update:
	$(DE) composer update --ignore-platform-reqs

composer-outdated:
	$(DE) composer outdated

# Tests
codesniffer:
	$(DE) ./vendor/bin/phpcs --standard=./tests/ruleset.xml --colors -p HanabosoCodingStandard PhpUnit

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./phpstan.neon -l 8 HanabosoCodingStandard PhpUnit

test: docker-up-force composer-install fasttest

fasttest: codesniffer phpstan
