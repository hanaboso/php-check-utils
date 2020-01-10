#!/usr/bin/env sh

time -f "Elapsed time: %e s." php vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p 8 --coverage-text --whitelist src tests | tee /tmp/phpunit-coverage.log

DISTR=$(awk -F= '$1=="ID" { print $2 ;}' /etc/os-release);

if [ $DISTR = 'alpine' ];
  then COVERAGE=$(cat /tmp/phpunit-coverage.log | grep -oE '^  Lines.+%' | grep -oE ' \d+' | grep -oE '\d+');
  else COVERAGE=$(cat /tmp/phpunit-coverage.log | grep -oE '^  Lines.+%' | grep -oE ' [[:digit:]]+' | grep -oE '[[:digit:]]+');
fi

if [ $COVERAGE -lt $1 ];
then echo -e "\033[1;31mSorry, your coverage is too low ($COVERAGE% < $1%)!\033[0m"; exit 1;
else if [ $COVERAGE -eq 100 ];
    then echo -e "\033[1;32mFull coverage. Good job! ($COVERAGE% / 100%)!\033[0m"; exit 1;
    else echo -e "\033[1;32mCoverage ($COVERAGE%) is not great, but not terrible ($1%).\033[0m";
    fi
fi