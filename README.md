Hanaboso - PHP CHECK UTILS
===============

[![Build Status](https://travis-ci.org/hanaboso/php-check-utils.svg?branch=master)](https://travis-ci.org/hanaboso/php-check-utils)
[![Downloads](https://img.shields.io/packagist/dt/hanaboso/php-check-utils)](https://packagist.org/packages/hanaboso/php-check-utils)

Installation
-----------
* Download package via composer
```bash
composer require --dev hanaboso/php-check-utils
```

CodeSniffer
-----------
* PHP_CodeSniffer docs: https://github.com/squizlabs/PHP_CodeSniffer
* Slevomat Coding Standard docs: https://github.com/slevomat/coding-standard/
* run PHP_CodeSniffer
```bash
./vendor/bin/phpcs --standard=./ruleset.xml -p src/ tests/
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

CodeFixer
---------
* PHP_CodeSniffer docs: https://github.com/squizlabs/PHP_CodeSniffer
* run PHP_CodeSnifferFixer
```bash
./vendor/bin/phpcbf --standard=./ruleset.xml -p src/ tests/
```

PhpStan
-------
* PHPStan docs: https://github.com/phpstan/phpstan
* run PHPStan
```bash
./vendor/bin/phpstan analyse -c phpstan.neon -l 8 src/ tests/
```
* phpstan.neon for app
```neon
includes:
    - vendor/hanaboso/php-check-utils/phpstan.neon # hanaboso rules
parameters
    excludes_analyse:
    ignoreErrors:
    symfony:
        container_xml_path: %rootDir%/../../../var/cache/dev/srcDevDebugProjectContainer.xml

```

CodeCoverage
-------
* run Coverage.sh
```bash
./vendor/hanaboso/php-check-utils/bin/coverage.sh 100
```

PhpStorm - Code Style
--------
* Import code style XML file from ./vendor/hanaboso/php-check-utils/phpstorm.xml to PhpStorm
* Open import window
```
File -> Settings -> Code Style -> PHP -> Import Scheme -> Intellij IDEA code style XML
```
