#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"
while [ -h "${SOURCE}" ]; do
  CURRENT_DIR="$( cd -P "$( dirname "${SOURCE}" )" && pwd )"
  SOURCE="$(readlink "${SOURCE}")"
  [[ ${SOURCE} != /* ]] && SOURCE="${CURRENT_DIR}/${SOURCE}"
done

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

VERSION=${1}
PROFILE=${2}
S3_ACCESS_KEY=${3}
S3_ACCESS_KEY_SECRET=${4}

if [ "$#" -ne 4 ]; then
    echo "Illegal number of parameters"
    echo "Usage:"
    echo "bin/release.sh <version> <profile> <S3_ACCESS_KEY> <S3_ACCESS_KEY_SECRET>"
    exit 1
fi

PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Shopware-5.2.17_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "=========================="
echo "BUILDING"
echo "VERSION: ${VERSION}"
echo "PROFILE: ${PROFILE}"
echo "S3_ACCESS_KEY: ${S3_ACCESS_KEY}"
echo "S3_ACCESS_KEY_SECRET: ${S3_ACCESS_KEY_SECRET}"
echo "=========================="

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../${PLUGIN_NAME}" "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
rm -rf vendor
composer install --no-interaction --no-progress --ignore-platform-reqs --no-dev --optimize-autoloader
cd ${BUILD_DIR}
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/plugin.xml

if [ ${PROFILE} == 'production' ]
then
  sed -i '' "s@__SUBDOMAIN__@productfeed@g" ${PLUGIN_DIR}/Components/AWSUploader.php
  sed -i '' "s@__SUBDOMAIN__@wgt@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
else
  sed -i '' "s@__SUBDOMAIN__@productfeed-prod.${PROFILE}@g" ${PLUGIN_DIR}/Components/AWSUploader.php
  sed -i '' "s@__SUBDOMAIN__@wgt-prod.${PROFILE}@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
fi

sed -i '' "s@__S3_PLUGIN_USER_ACCESS_KEY__@${S3_ACCESS_KEY}@g" ${PLUGIN_DIR}/Components/AWSUploader.php
sed -i '' "s@__S3_PLUGIN_USER_ACCESS_KEY_SECRET__@${S3_ACCESS_KEY_SECRET}@g" ${PLUGIN_DIR}/Components/AWSUploader.php

zip -q -r "${DIST_PATH}" ${PLUGIN_NAME}
echo "created release ${VERSION} at ${DIST_PATH}"
