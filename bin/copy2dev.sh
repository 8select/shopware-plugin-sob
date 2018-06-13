#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
DESTINATION=${1}
VERSION=${2}

PLUGIN_NAME="CseEightselectBasic"
PLUGIN_DIR="${DESTINATION}/${PLUGIN_NAME}"

cp -r "${CURRENT_DIR}/../${PLUGIN_NAME}" "${DESTINATION}"

sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/plugin.xml
sed -i '' "s@__BUCKET__@productfeed-prod.staging@g" ${PLUGIN_DIR}/Components/AWSUploader.php
sed -i '' "s@__BUCKET__@wgt-prod.staging@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
sed -i '' "s@__BUCKET__@wgt-prod.staging@g" ${PLUGIN_DIR}/Resources/views/frontend/checkout/finish.tpl


echo "Working directory successfully copied to ${DESTINATION}"
