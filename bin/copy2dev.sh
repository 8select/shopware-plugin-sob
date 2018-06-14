#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
CONTAINER=${1}
VERSION=${2}
PLUGINDIR="/shopware/custom/plugins"
SHOPWARE_CLI_BIN="bin/console"

PLUGIN_NAME="CseEightselectBasic"
PLUGIN_DIR="${CONTAINER}:${PLUGINDIR}/${PLUGIN_NAME}"
TEMP_DIR=`mktemp -d`

cp -r ${CURRENT_DIR}/../${PLUGIN_NAME} ${TEMP_DIR}

sed -i '' "s@__VERSION__@${VERSION}@g" ${TEMP_DIR}/${PLUGIN_NAME}/plugin.xml
sed -i '' "s@__BUCKET__@productfeed-prod.staging@g" ${TEMP_DIR}/${PLUGIN_NAME}/Components/AWSUploader.php
sed -i '' "s@__BUCKET__@wgt-prod.staging@g" ${TEMP_DIR}/${PLUGIN_NAME}/Resources/views/frontend/index/header.tpl
sed -i '' "s@__BUCKET__@wgt-prod.staging@g" ${TEMP_DIR}/${PLUGIN_NAME}/Resources/views/frontend/checkout/finish.tpl

docker cp "${TEMP_DIR}/${PLUGIN_NAME}" "${CONTAINER}:${PLUGINDIR}"
docker exec -i ${CONTAINER} sh -c "php ${SHOPWARE_CLI_BIN} sw:plugin:refresh"
docker exec -i ${CONTAINER} sh -c "php ${SHOPWARE_CLI_BIN} sw:plugin:update CseEightselectBasic"

echo "Working directory successfully copied to ${PLUGINDIR} in docker container ${CONTAINER}"
