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
	$(DE) composer install --ignore-platform-reqs
	$(DE) composer update --dry-run roave/security-advisories --ignore-platform-reqs

composer-update:
	$(DE) composer update --ignore-platform-reqs
	$(DE) composer update --dry-run roave/security-advisories --ignore-platform-reqs
	$(DE) composer normalize

composer-outdated:
	$(DE) composer outdated --ignore-platform-reqs

# Tests
phpcodesniffer:
	$(DE) ./vendor/bin/phpcs --parallel=$$(nproc) --standard=./tests/ruleset.xml HanabosoCodingStandard PhpUnit TwigCs

phpstan:
	$(DE) ./vendor/bin/phpstan analyse -c ./tests/phpstan.neon -l 8 HanabosoCodingStandard PhpUnit TwigCs tests/Traits

unit:
	$(DE) ./vendor/bin/paratest -c ./tests/phpunit.xml.dist tests/Unit

test: docker-up-force composer-install fasttest

fasttest: phpcodesniffer phpstan unit
