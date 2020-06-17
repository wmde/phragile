![Phragile](https://raw.githubusercontent.com/wmde/phragile/master/public/images/phragile.png)
========

Sprint overviews for your Phabricator projects!

[![Build Status](https://travis-ci.org/wmde/phragile.svg)](https://travis-ci.org/wmde/phragile)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/wmde/phragile/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/wmde/phragile/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/phragile/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/wmde/phragile/?branch=master)

## DISCONTINUED
Phragile has mainly been superseded by [Phabricator Reports](https://phabricator.wikimedia.org/project/reports/1277/), so the development is discontinued.

## About
With Phragile you can log in using your Phabricator account to create sprints for your projects on Phabricator. Phragile will then automatically generate burndown charts, pie charts and a sortable and filterable sprint backlog for you.

Built with ♥ in Berlin by [Wikimedia Deutschland](http://wikimedia.de).

## Issue Tracker
If you find a bug or want to propose a new feature please report it on [Phabricator](https://phabricator.wikimedia.org/maniphest/task/create/?projects=phragile).

## Features
1. Phabricator OAuth Login
2. Create projects
3. Create sprints which will be synced with Phabricator projects
4. Sprint overviews with burndown diagrams and pie charts
5. Take snapshots of your sprints to archive sprint data at any point

For more information see the [product backlog](https://github.com/wmde/phragile/wiki/Backlog)

## Installation

### Requirements

* PHP 5.5 or later
* MySQL, SQLite or PostgreSQL
* Phabricator (see below)

### Preparation

* [Install Phabricator](https://secure.phabricator.com/book/phabricator/article/installation_guide/)

    Phragile >= 3.0.0 requires Phabricator release 2016 Week 15 or newer.

* [Activate Phabricator OAuth](https://github.com/wmde/phragile/wiki/Activating-Phabricator-OAuth)
* Add a  custom field for story points to http://yourphabricator/config/edit/maniphest.custom-field-definitions/   
    e.g.: ```{ "yourcompany:story_points": { "name": "Story Points", "type": "int" } }```

    **OR** [Install the sprint extension](https://github.com/wikimedia/phabricator-extensions-Sprint)

### Installation
* Clone this repository
* Run `composer install` in the repository’s root directory
* Copy `.env.example` to `.env` and edit the file according to the instructions
* Run `php artisan migrate`
* Make `storage/` writable for the server process

### Server configuration

**nginx**

rewrite rule example

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

## Upgrading

Please read the [UPGRADE.md](UPGRADE.md) for documentation on how to upgrade from one release to another.

## Tests
### Acceptance tests
1. Copy `behat_custom.yml.example` to `behat_custom.yml` and edit the file according to the instructions
2. Run `vendor/bin/behat`

### Unit tests
Run `phpunit`
