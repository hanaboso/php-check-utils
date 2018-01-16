PHP CHECK UTILS
===============

Installation
-----------
* add dependency to composer.json
```json
{
  "require": {
      "hanaboso/php-check-utils": "version"
  },
  "repositories": [
      {
        "type": "git",
        "url": "https://github.com/hanaboso/php-check-utils"
      }
  ]
}
```
* Install package
```bash
composer install
```

CodeSniffer
-----------
* code sniffer docs: https://github.com/squizlabs/PHP_CodeSniffer
* coding standard: https://github.com/slevomat/coding-standard/
* run code sniffer
```bash
./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/
```
* ruleset.xml for app
```xml
<?xml version="1.0"?>
<ruleset name="HANABOSO CODE STYLE">
    <rule ref="./vendor/hanaboso/php-check-utils/ruleset.xml"/>
    <rule ref="SlevomatCodingStandard.Files.TypeNameMatchesFileName">
        <properties>
            <property name="rootNamespaces" type="array" value="
                src=>MyBundle, 
                tests=>Tests 
            "/>
        </properties>
    </rule>
</ruleset>

```

PhpStan
-------
* phpstan docs: https://github.com/phpstan/phpstan
* phpstan.neon for app
```neon
includes:
    - vendor/phpstan/phpstan-doctrine/extension.neon # doctrine extension
    - vendor/phpstan/phpstan-phpunit/extension.neon # phpunit extension
    - vendor/phpstan/phpstan-phpunit/rules.neon # phpunit rules
    - vendor/hanaboso/php-check-utils/phpstan.neon # hanaboso rules
```

