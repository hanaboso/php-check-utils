#!/usr/bin/env sh

while [ "$#" -gt 0 ]; do case $1 in
  -c|--coverage) coverage="$2"; shift;;
  -p|--processes) processes="$2"; shift;;
  -t|--tests) tests="$2"; shift;;
  -w|--whitelist) whitelist="$2"; shift;;
  -e|--exclude-group) exclude="$2"; shift;;
  *) echo -e "\033[1;31mUsage: coverage.sh -c <coverage> -p <processes> -t <tests> -w <whitelist> -e <exclude-group>\033[0m"; exit 1;;
esac; shift; done

if [ "$coverage" = '' ];
    then coverage=100;
fi

if [ "$processes" = '' ];
    then processes=$(nproc);
fi

if [ "$tests" = '' ];
    then tests='tests';
fi

if [ "$whitelist" = '' ];
    then whitelist='src';
fi

if [ "$exclude" != '' ];
    then exclude="--exclude-group $exclude";
fi

time -f "Elapsed time: %e s." php vendor/bin/paratest -c ./vendor/hanaboso/php-check-utils/phpunit.xml.dist -p $processes --coverage-text /tmp/phpunit-coverage.log --cache-directory /tmp --coverage-filter $whitelist $exclude $tests
DISTR=$(awk -F= '$1=="ID" { print $2 ;}' /etc/os-release);

if [ $DISTR = 'alpine' ];
  then COVERAGE=$(cat /tmp/phpunit-coverage.log | grep -oE '^.{0,8}  Lines.+%' | grep -oE ' \d+' | grep -oE '\d+');
  else COVERAGE=$(cat /tmp/phpunit-coverage.log | grep -oE '^.{0,8}  Lines.+%' | grep -oE ' [[:digit:]]+' | grep -oE '[[:digit:]]+');
fi

if [ $COVERAGE -lt $coverage ];
    then echo -e "\033[1;31mSorry, your coverage is too low ($COVERAGE% < $coverage%)!\033[0m"; exit 1;
else if [ $COVERAGE -eq 100 ];
    then echo -e "\033[1;32mFull coverage. Good job! ($COVERAGE% / 100%)!\033[0m";
    else echo -e "\033[1;32mCoverage ($COVERAGE%) is not great, but not terrible ($coverage%).\033[0m";
    fi
fi
