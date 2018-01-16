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
* run code sniffer
```bash
./vendor/bin/phpcs --standard=./ruleset.xml --colors -p src/ tests/
```
* code sniffer docs: https://github.com/squizlabs/PHP_CodeSniffer
* coding standard: https://github.com/slevomat/coding-standard/

