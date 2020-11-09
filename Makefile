DC=docker-compose
DE=docker-compose exec -T app

.env:
	sed -e "s/{DEV_UID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -u); else echo '1001'; fi)/g" \
		-e "s/{DEV_GID}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo $(shell id -g); else echo '1001'; fi)/g" \
		-e "s/{SSH_AUTH}/$(shell if [ "$(shell uname)" = "Linux" ]; then echo '${SSH_AUTH_SOCK}' | sed 's/\//\\\//g'; else echo '\/run\/host-services\/ssh-auth.sock'; fi)/g" \
		.env.dist > .env; \

# Docker
docker-up-force: .env
	$(DC) pull
	$(DC) up -d --force-recreate --remove-orphans

docker-down-clean: .env
	$(DC) down -v

# Composer
composer-install:
	$(DE) composer install
	$(DE) composer update --dry-run roave/security-advisories

composer-update:
	$(DE) composer update
	$(DE) composer update --dry-run roave/security-advisories
#	$(DE) composer normalize

composer-outdated:
	$(DE) composer outdated

# Tests
phpcodesniffer:
	$(DE) ./vendor/bin/phpcs --parallel=$$(nproc) --standard=./tests/ruleset.xml HanabosoCodingStandard PhpUnit

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./tests/phpstan.neon -l 8 HanabosoCodingStandard PhpUnit

phpunit:
	$(DE) ./vendor/bin/paratest -c ./phpunit.xml.dist -p $$(nproc) tests/Unit

test: docker-up-force composer-install fasttest

fasttest: phpcodesniffer phpstan phpunit
