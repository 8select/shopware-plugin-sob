#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"
while [ -h "${SOURCE}" ]; do
  CURRENT_DIR="$( cd -P "$( dirname "${SOURCE}" )" && pwd )"
  SOURCE="$(readlink "${SOURCE}")"
  [[ ${SOURCE} != /* ]] && SOURCE="${CURRENT_DIR}/${SOURCE}"
done

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

VERSION=${1}
BUCKET=${2}
PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Shopware-5.2.17_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../${PLUGIN_NAME}" "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
rm -rf vendor
composer install --no-interaction --no-progress --ignore-platform-reqs --no-dev --optimize-autoloader
cd ${BUILD_DIR}
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/plugin.xml
if [ $BUCKET == 'staging' ]
then
  sed -i '' "s@__BUCKET__@productfeed-prod.staging@g" ${PLUGIN_DIR}/Components/AWSUploader.php
  echo '========================================================================================================================'
  echo 'NOTE: This is a staging build. For production please make sure to to execute this script without `staging` parameter.'
  echo '========================================================================================================================'
else 
  sed -i '' "s@__BUCKET__@productfeed@g" ${PLUGIN_DIR}/Components/AWSUploader.php
fi
zip -q -r "${DIST_PATH}" ${PLUGIN_NAME}
echo "created release ${VERSION} at ${DIST_PATH}"
