# CI build status dashboard

The CI dashboard is a web application that shows a CI status summary for all your repositories.

## Installation

First, you need to create a GitHub application so that you can have an application ID and secret token.

Run these commands to install the application in production.

```
$ composer install --no-dev -optimize-autoloader
$ php app/console cache:clear --env=prod --no-debug
```

Locally, you can run these:

```
$ composer install
$ php app/console cache:clear
```
