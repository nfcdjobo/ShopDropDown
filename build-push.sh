#!/bin/bash
set -e
DOCKER_USER="nfcdjobo"          # ton namespace Docker Hub (voir note ci-dessous)
TAG="${1:-latest}"
IMAGE="$DOCKER_USER/shopdropdown:$TAG"

docker build --platform linux/amd64 -t "$IMAGE" .
docker push "$IMAGE"
