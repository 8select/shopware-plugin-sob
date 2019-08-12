SHOP_CONNECTOR_URL=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name shop-connector-${STAGE} --query 'Stacks[0].Outputs[?OutputKey==`CustomDomainName`].OutputValue' --output text)

PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Shopware-5.2.17_${PROFILE}_${STAGE}_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "=========================="
echo "BUILDING"
echo "VERSION: ${VERSION}"
echo "PROFILE: ${PROFILE}"
echo "STAGE: ${STAGE}"
echo "SHOP_CONNECTOR_URL: ${SHOP_CONNECTOR_URL}"
echo "SHOP_URL_OVERRIDE: ${SHOP_URL_OVERRIDE}"
echo "=========================="

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../../${PLUGIN_NAME}" "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
rm -rf vendor
rm -rf tests
cd ${BUILD_DIR}
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/plugin.xml
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/Services/Export/Connector.php
sed -i '' "s@__VERSION__@${VERSION}@g" ${PLUGIN_DIR}/Setup/Helpers/Logger.php

sed -i '' "s@__SHOP_CONNECTOR_URL__@${SHOP_CONNECTOR_URL}@g" ${PLUGIN_DIR}/Services/Export/Connector.php
sed -i '' "s@__SHOP_CONNECTOR_URL__@${SHOP_CONNECTOR_URL}@g" ${PLUGIN_DIR}/Setup/Helpers/Logger.php

if [ ${PROFILE} == 'production' ] || [ ${SHOP_URL_OVERRIDE} == 'default' ]
then
  SHOP_URL_RETURN=""
else
  SHOP_URL_RETURN="return '${SHOP_URL_OVERRIDE}';"
fi

sed -i '' "s@//__SHOP_URL_OVERRIDE__@${SHOP_URL_RETURN}@g" ${PLUGIN_DIR}/Services/Dependencies/Provider.php

if [ ${PROFILE} == 'production' ]
then
  sed -i '' "s@__SUBDOMAIN__@wgt@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
else
  sed -i '' "s@__SUBDOMAIN__@wgt-${STAGE}.${PROFILE}@g" ${PLUGIN_DIR}/Resources/views/frontend/index/header.tpl
fi
