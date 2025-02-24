#!/usr/bin/env bash
set -eo pipefail

BASE_PATH=$(dirname $0)/../../..
BASE_PATH=$(readlink -f "$BASE_PATH")

COMPOSE_FILE="$BASE_PATH/docker-compose/docker-compose.e2e.yml"
DOCKER_COMPOSE="docker compose -p nl-covid19-dbco-app-backend-private-e2e --env-file $BASE_PATH/.env.e2e --project-directory=$BASE_PATH --file $COMPOSE_FILE"

$DOCKER_COMPOSE "$@"
