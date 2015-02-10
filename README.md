![Phragile](https://raw.githubusercontent.com/wmde/phragile/master/public/images/phragile.png)
========

Sprint overviews for your Phabricator projects!

## About
A product backlog with user stories can be found on [the backlog wiki page](https://github.com/wmde/phragile/wiki/Backlog).
Log in using your Phabricator account to create sprints for your projects on Phabricator. Phragile will then automatically generate burndown charts, pie charts and a sortable and filterable sprint backlog for you.

Built by [Jakob Warkotsch](https://github.com/jakobw) as a thesis project at [Freie Universität Berlin](http://fu-berlin.de) in cooperation with [Wikimedia Deutschland](http://wikimedia.de).

## Features
1. Phabricator OAuth Login
2. Create projects
3. Create sprints which will be synced with Phabricator projects
4. Sprint overviews with burndown diagrams and pie charts
5. Take snapshots of your sprints to archive sprint data at any point

For more information see the [product backlog](https://github.com/wmde/phragile/wiki/Backlog)

## Installation
* requires PHP >= 5.4 and MySQL, SQLite or PostgreSQL
* [Install Phabricator](https://secure.phabricator.com/book/phabricator/article/installation_guide/)
* [Activate Phabricator OAuth](https://github.com/wmde/phragile/wiki/Activating-Phabricator-OAuth)
* Clone this repository
* Run `composer update` in the repository’s root directory
* Copy `.env.example` to `.env` and edit the file according to the instructions
* Run `php artisan migrate`

## Tests
### Acceptance tests
1. Copy `behat_custom.yml.example` to `behat_custom.yml` and edit the file according to the instructions
2. Run `vendor/bin/behat`
### Unit tests
Run `phpunit`
