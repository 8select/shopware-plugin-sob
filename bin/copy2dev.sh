#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
CONTAINER=${1}
VERSION=${2}
DOCKERDIR="/shopware/custom/plugins"

PLUGIN_NAME="CseEightselectBasic"
PLUGIN_DIR="${CONTAINER}:${DOCKERDIR}/${PLUGIN_NAME}"

sed -i '' "s@__VERSION__@${VERSION}@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/plugin.xml
sed -i '' "s@__BUCKET__@productfeed-prod.staging@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/Components/AWSUploader.php
sed -i '' "s@__BUCKET__@wgt-prod.staging@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/Resources/views/frontend/index/header.tpl
sed -i '' "s@__BUCKET__@wgt-prod.staging@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/Resources/views/frontend/checkout/finish.tpl

docker cp -a "${CURRENT_DIR}/../${PLUGIN_NAME}" "${CONTAINER}:${DOCKERDIR}"

sed -i '' "s@${VERSION}@__VERSION__@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/plugin.xml
sed -i '' "s@productfeed-prod.staging@__BUCKET__@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/Components/AWSUploader.php
sed -i '' "s@wgt-prod.staging@__BUCKET__@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/Resources/views/frontend/index/header.tpl
sed -i '' "s@wgt-prod.staging@__BUCKET__@g" ${CURRENT_DIR}/../${PLUGIN_NAME}/Resources/views/frontend/checkout/finish.tpl

echo "Working directory successfully copied to ${DOCKERDIR} in docker container ${CONTAINER}"
