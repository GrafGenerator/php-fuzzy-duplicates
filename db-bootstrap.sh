#!/usr/bin/env bash

docker-compose down
docker-compose rm -fv
rm -rf ./mysql/*
