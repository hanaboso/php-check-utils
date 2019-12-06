#!/usr/bin/env sh

coverage=$(php vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 4 --coverage-text --whitelist src tests | grep -oE '^  Lines.+%' | grep -oE ' \d+' | grep -oE '\d+')

if [[ $coverage -lt $1 ]];
then echo -e "\033[1;31mSorry, your coverage is too low ($coverage% < $1%)!\033[0m"; exit 1;
else echo -e "\033[1;32mCoverage ($coverage%) is not great, but not terrible ($1%).\033[0m";
fi
