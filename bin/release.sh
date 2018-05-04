#!/usr/bin/env bash -e

VERSION=2

cp -r EightSelect bin/EightSelect
sed -i '' "s@__VERSION__@${VERSION}@g" bin/EightSelect/plugin.xml
cd bin/EightSelect && composer install && cd ../..
zip -r dist/8select_CSE_Shopware-5.2_${VERSION}.zip bin/EightSelect
rm -rf bin/EightSelect
echo "created release ${VERSION} at dist/8select_CSE_Shopware-5.2_${VERSION}.zip"

