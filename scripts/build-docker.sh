#!/usr/bin/env bash
pushd "$(dirname "$0")" || exit
source .env
cd ..
docker image rm "$PROJECT_NAME":latest
docker run --rm -i hadolint/hadolint:latest-debian < scripts/Dockerfile
docker build -t $PROJECT_NAME -f scripts/Dockerfile .
popd || exit
