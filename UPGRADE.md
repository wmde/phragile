# Upgrading Phragile

When upgrading Phragile to a newer version **always** run

    composer install
    php artisan migrate

after updating the files in the root directory of your application.

## From earlier releases to release 3.0.0

Snapshots created with Phragile versions earlier than 3.0.0 must be migrated in order to be used with Phragile 3.0.0.
In order to migrate snapshots

* make a backup of Phragile database
* run migration snapshot migration command:

        php artisan snapshots:migrate

## From 1.1 release to 2.0.0

### Migrate your Snapshots

To support newer versions of Phabricator, Phragile uses manifest.search instead of manifest.query requests since 2.0.0. Therefore older snapshot data needs to be migrated with

    php artisan snapshots:migrate    

## From 1.0 release to 1.1

Phragile 1.1. starts using Conduit API Tokens instead of the deprecated Conduit API Certificate.

* The setting for the Phragile bot user needs to be updated in the `.env` file. Use `PHRAGILE_BOT_API_TOKEN=` instead of `PHRAGILE_BOT_CERTIFICATE=`
* When running browser tests corresponding changes have to be done in the `behat_custom.yml` file. Use `conduit_api_token:` instead of `conduit_certificate:`
