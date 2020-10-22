#!/bin/bash
BASE_PATH=$(dirname $0)/.
cd $BASE_PATH
docker build -t oracle/database:18.4.0-xe ./base
