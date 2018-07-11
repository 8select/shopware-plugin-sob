#!/usr/bin/env bash -e
. $(dirname "$0")/common.sh

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
