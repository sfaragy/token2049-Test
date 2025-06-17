#!/bin/bash

set -e

# CONFIG
DOCKER_REGISTRY='registry.digitalocean.com'
DOCKER_REPO_BACKEND='token2049-prod/src'

DROPLET_IP='xxx.xxx.xxx.xxx'
DROPLET_USER='root'
PROJECT_DIR='token2049-prod'

DOCKER_ACCESS_TOKEN='dop_v1_xxx'
DOCKER_USER_NAME='solimankhulna@gmail.com'

# Run backend App tests
run_app_tests() {
    echo "Running backend (PHPUnit) tests..."
    docker-compose -f docker-compose.yml up --abort-on-container-exit --build app-test
}


# Build Docker images
# Docs: Builds the production Docker image for app. Uses the Dockerfile at root directory,
# Sets the build context to ./src where our app code lives.
# Targets the linux/amd64 platform (important for DigitalOcean).
# Tags the image with the full path to your DigitalOcean registry: i.e.e registry.digitalocean.com/token2049-prod/src.

build_app_image() {
    echo "Building backend..."
    docker build --platform linux/amd64 -t "${DOCKER_REGISTRY}/${DOCKER_REPO_BACKEND}" -f Dockerfile ./src
}


# Login & Push
# Authenticates local Docker client to DigitalOcean’s container registry.
# Uses a secure DOCKER_ACCESS_TOKEN (likely generated from the DigitalOcean account,
# if you assign my email as admin I can manage it. solimankhulna@gmail.com).
# Logs in to the registry (registry.digitalocean.com) as the configured user.
# Piping the token into docker login via --password-stdin avoids leaking credentials in shell history.
login_to_registry() {
    echo "${DOCKER_ACCESS_TOKEN}" | docker login -u "${DOCKER_USER_NAME}" --password-stdin "${DOCKER_REGISTRY}"
}

push_docker_images() {
    docker push "${DOCKER_REGISTRY}/${DOCKER_REPO_BACKEND}"
}

# Deploy
# Uploads freshly built Docker images to the DigitalOcean registry.
# We can build and push any required number of container as we need.
deploy_to_droplet() {
    echo "Deploying..."
    ssh "${DROPLET_USER}@${DROPLET_IP}" << EOF
        cd ${PROJECT_DIR}
        docker-compose pull
        docker-compose down
        docker-compose up -d
        docker exec token2049-prod-backend-api-1 php artisan migrate --force
        docker exec token2049-prod-backend-api-1 php artisan config:cache
EOF
}

# ==== RUN ====
echo "Starting deployment process..."

run_app_tests

#build_app_image

#login_to_registry
#push_docker_images
#deploy_to_droplet

echo "Deployment complete"
