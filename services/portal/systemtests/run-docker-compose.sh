#!/usr/bin/env bash
set -eo pipefail
cd ../../../bin
./docker-compose-dev "$@"