# CI build status dashboard

The CI dashboard is a web application that shows a CI status summary for all your repositories.

## Installation

### Generate a Github OAuth application
First, you need to create a GitHub application so that you can have an application ID and secret token.

Go to https://github.com/settings/applications/new

### Setup the code on production

These commands will setup the app and ask you for the application Client ID and Client secret:
```
$ composer install --no-dev --optimize-autoloader
$ php app/console cache:clear --env=prod --no-debug
```

### Setup on local machine

Locally, you can run these:

```
$ composer install
$ php app/console cache:clear
```


Note: on Ubuntu you may need `sudo apt-get install php5-intl`
