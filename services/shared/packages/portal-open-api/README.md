# Portal OpenAPI

## Introduction

This package contains an [OpenApi specification](https://www.openapis.org/) for the DBCO portal api.

The specification is split up into multiple files and can be linted, compiled and previewed using [Redocly](https://redocly.com/).

# Setup & build

Install all requirements with:

    yarn install

Now run the following command to check all specifications.

    yarn generate

Preview the current specification with `yarn preview-docs` and visit `http://127.0.0.1:8080`:

To bundle the latest specification and build the correspoding client, run `generate`.

> For other commands please check out the `package.json` or visit the [Redocly docs](https://redocly.com/docs/cli/).

## Typescript

Redocly can also build an `axios` client that is fully typed with Typescript. There is also an option to only generate the types of the specification itself using the [OpenAPI typescript generator](https://www.npmjs.com/package/openapi-typescript) by running `transform-ts`.
