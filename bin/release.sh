#!/usr/bin/env bash -e

VERSION=${1}

cp EightSelect/plugin.xml EightSelect/plugin.org.xml
sed -i '' "s@__VERSION__@${VERSION}@g" EightSelect/plugin.xml
zip -q -r dist/8select_CSE_Shopware-5.2_${VERSION}.zip EightSelect
mv EightSelect/plugin.org.xml EightSelect/plugin.xml
echo "created release ${VERSION} at dist/8select_CSE_Shopware-5.2_${VERSION}.zip"