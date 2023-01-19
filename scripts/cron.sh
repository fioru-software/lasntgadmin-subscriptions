#! /bin/sh

export COMPOSER_AUTOLOAD_FILEPATH=/usr/local/src/vendor/autoload.php

for hook in $(/usr/local/bin/php /usr/local/bin/wp cron event list --next_run_relative=now --fields=hook --format=ids --path=/var/www/html)
do
    /usr/local/bin/php /usr/local/bin/wp cron event run $hook --path=/var/www/html --exec='$_GET["smqProcessQueue"]=1;$_GET["key"]="secret";'
done
