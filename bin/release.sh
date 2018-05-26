#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"
while [ -h "${SOURCE}" ]; do
  CURRENT_DIR="$( cd -P "$( dirname "${SOURCE}" )" && pwd )"
  SOURCE="$(readlink "${SOURCE}")"
  [[ ${SOURCE} != /* ]] && SOURCE="${CURRENT_DIR}/${SOURCE}"
done

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

VERSION=${1}
PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Shopware-5.2.11_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../${PLUGIN_NAME}" "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
composer install --no-interaction --no-progress --ignore-platform-reqs --no-dev
cd ${BUILD_DIR}
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/plugin.xml
zip -q -r "${DIST_PATH}" ${PLUGIN_NAME}
echo "created release ${VERSION} at ${DIST_PATH}"