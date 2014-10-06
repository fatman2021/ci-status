#!/bin/sh

# Update the application in production

git pull

SYMFONY_ENV=prod composer install --no-dev --optimize-autoloader
app/console cache:clear --env=prod --no-debug