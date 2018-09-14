S3_ACCESS_KEY=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name product-feed-service-prod --query 'Stacks[0].Outputs[?OutputKey==`PluginUserAccessKeyId`].OutputValue' --output text)
S3_ACCESS_KEY_SECRET=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name product-feed-service-prod --query 'Stacks[0].Outputs[?OutputKey==`PluginUserAccessKeySecret`].OutputValue' --output text)

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
echo "S3_ACCESS_KEY: ${S3_ACCESS_KEY}"
echo "S3_ACCESS_KEY_SECRET: ${S3_ACCESS_KEY_SECRET}"
echo "=========================="

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../../${PLUGIN_NAME}" "${BUILD_DIR}/${PLUGIN_NAME}"
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
