# COVID-19 BCO - backend

## Introduction
This repository contains the backend implementation of the Dutch COVID-19 BCO.

* The backend is located in the repository you are currently viewing.
* The iOS app can be found here: ...
* The Android app can be found here: ...

## Overview

* console: implements a docker image that can be used to manually or periodically (e.g. cron) run commands
* api: implements the API

## Development

To run a local development environment:

- Create an `.env` file (you can registerCase a copy of `.env.example` to get started). 
- Run `bin/setup-dev`

## Testing

You can run the unit tests using `bin/phpunit`.

## Development & Contribution process

The development team works on the repository in a private fork (for reasons of compliance with existing processes) and shares its work as often as possible.

If you plan to make non-trivial changes, we recommend to open an issue beforehand where we can discuss your planned changes.
This increases the chance that we might be able to use your contribution (or it avoids doing work if there are reasons why we wouldn't be able to use it).

Note that all commits should be signed using a gpg key.

