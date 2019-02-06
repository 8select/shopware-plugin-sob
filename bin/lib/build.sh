SHOP_CONNECTOR_URL=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name shop-connector-prod --query 'Stacks[0].Outputs[?OutputKey==`CustomDomainName`].OutputValue' --output text)

PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Shopware-5.2.17_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "=========================="
echo "BUILDING"
echo "VERSION: ${VERSION}"
echo "PROFILE: ${PROFILE}"
echo "=========================="

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../../${PLUGIN_NAME}" "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
rm -rf vendor
cd ${BUILD_DIR}
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/plugin.xml
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/Services/Export/Connector.php
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/Setup/Helpers/Logger.php

sed -i '' "s@__SHOP_CONNECTOR_URL__@${SHOP_CONNECTOR_URL}@g" ${PLUGIN_DIR}/Services/Export/Connector.php
sed -i '' "s@__SHOP_CONNECTOR_URL__@${SHOP_CONNECTOR_URL}@g" ${PLUGIN_DIR}/Setup/Helpers/Logger.php

if [ ${PROFILE} == 'production' ]
then
  sed -i '' "s@__SUBDOMAIN__@wgt@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
else
  sed -i '' "s@__SUBDOMAIN__@wgt-prod.${PROFILE}@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
fi
