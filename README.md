# CI Status dashboard

The CI Status dashboard is a web application that shows a build status summary for all your repositories.

It will fetch the continuous integration status for repositories on [Travis-CI.org](https://travis-ci.org/) or [Travis-CI.com](https://travis-ci.com/).

## Installation

### Create a Github application for OAuth authentication

You need to [create a GitHub application](https://github.com/settings/applications/new) so that you can have an Client ID and Client secret to integrate with GitHub's API.

This is necessary to allow users to login into *CI Status* with their GitHub account.

### Production setup

These commands will setup the application and ask you for the application Client ID and Client secret:

```
$ composer install --no-dev --optimize-autoloader
$ app/console cache:clear --env=prod --no-debug
```

To improve security and privacy, be aware that users GitHub tokens are stored in the sessions. You need to take care of how those sessions are stored on your server to protect those tokens. It is recommended that you set up a short expiration time and ensure that the session files are correctly garbage-collected.

### Local setup

CI Status is a standard Symfony application:

```
$ composer install
$ app/console cache:clear
```

Note: on Ubuntu you may need to run `sudo apt-get install php5-intl`.
