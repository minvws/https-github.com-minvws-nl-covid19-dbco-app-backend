# COVID-19 DBCO - backend

## Introduction
This repository contains the backend implementation of the Dutch COVID-19 DBCO app.

* The backend (api and portal) is located in the repository you are currently viewing.
* The iOS app can be found here: https://github.com/minvws/nl-covid19-dbco-app-ios
* The Android app can be found here: https://github.com/minvws/nl-covid19-dbco-app-android
* Designs can be found here: https://github.com/minvws/nl-covid19-dbco-app-design
* Technical documentation can be found here: https://github.com/minvws/nl-covid19-dbco-app-coordination

## Overview

* worker: implements a docker image that can be used to manually or periodically (e.g. cron) run commands
* api: implements the APIs 
* portal: implements a (potentially temporary) portal for healthcare (BCO) workers.

## Development

Prerequisites: A working Docker environment

Steps to run a local development environment:

- Create an `.env` file (you can create a copy of `.env.example` to get started). 
- Generate some passwords and enter them in the various .env file settings that are passwords
- Run `bin/setup-dev` to set up the environment (initialize database, install dependencies).

If the command has completed successfully, you will be running 4 docker instances:
* The private api will run on port 8081 on localhost
* The public api will run on port 8082
* The healthcare api will run on port 8083
* The healthcare portal will run on port 8084

If your development environment gets messed up, run bin/reset-dev to rebuild the environment.

## Testing

You can run the unit tests using `bin/phpunit`. 

## Development & Contribution process

The development team works on the repository in a private fork (for reasons of compliance with existing processes) and shares its work as often as possible.

If you plan to make non-trivial changes, we recommend to open an issue beforehand where we can discuss your planned changes.
This increases the chance that we might be able to use your contribution (or it avoids doing work if there are reasons why we wouldn't be able to use it).

Note that all commits should be signed using a gpg key.

