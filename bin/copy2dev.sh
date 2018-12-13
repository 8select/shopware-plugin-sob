#!/usr/bin/env bash -e
. $(dirname "$0")/lib/common.sh

if [ "$#" -ne 3 ]; then
    echo "Illegal number of parameters"
    echo "Usage:"
    echo "bin/copy2dev.sh <version> <profile> <container-id>"
    echo "Example:"
    echo "bin/copy2dev.sh 1.11.0-staging staging b7f42c7b1b73"
    exit 1
fi

if [ "${PROFILE}" = "production" ]; then
    echo "don't use the production environment during development"
    echo "images can not be downloaded from your machine - testing without images sucks"
    exit 1
fi

. $(dirname "$0")/lib/build.sh

CONTAINER=${3}

PLUGINDIR="/shopware/custom/plugins"
SHOPWARE_CLI_BIN="bin/console"

PLUGIN_NAME="CseEightselectBasic"
TEMP_DIR=`mktemp -d`

# copy repository to docker dev shop

docker cp "${BUILD_DIR}/${PLUGIN_NAME}" "${CONTAINER}:${PLUGINDIR}"
docker exec -i ${CONTAINER} sh -c "php ${SHOPWARE_CLI_BIN} sw:plugin:refresh"
docker exec -i ${CONTAINER} sh -c "php ${SHOPWARE_CLI_BIN} sw:plugin:update CseEightselectBasic"
docker exec -i ${CONTAINER} sh -c "php ${SHOPWARE_CLI_BIN} sw:cache:clear"

echo "Working directory successfully copied to ${PLUGINDIR} in docker container ${CONTAINER}"
