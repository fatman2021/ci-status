#!/bin/sh

# Update the application in production

git pull

SYMFONY_ENV=prod php composer.phar install --no-dev --optimize-autoloader
app/console cache:clear --env=prod --no-debug
