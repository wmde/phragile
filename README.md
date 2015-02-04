phragile
========

An application to bring the agile needs of WMDE software development department to phabricator

## About
A product backlog with user stories can be found on [the backlog wiki page](https://github.com/wmde/phragile/wiki/Backlog).

## Installation
* requires PHP >= 5.4 and MySQL, SQLite or PostgreSQL
* [Install Phabricator](https://secure.phabricator.com/book/phabricator/article/installation_guide/)
* [Activate Phabricator OAuth](https://github.com/wmde/phragile/wiki/Activating-Phabricator-OAuth)
* Clone this repository
* Run `composer update` in the repository’s root directory
* Use [Homestead](http://laravel.com/docs/4.2/homestead) or adjust `app/config/local/database.php` accordingly
* Copy `.env.local.php.example` to `.env.local.php` and edit the file according to the instructions

## Tests
1. Copy `behat_custom.yml.example` to `behat_custom.yml` and edit the file according to the instructions
2. Run `vendor/bin/behat`
