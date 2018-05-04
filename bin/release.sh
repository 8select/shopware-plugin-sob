#!/usr/bin/env bash -e

VERSION=2

SCRIPTPATH="$( cd "$(dirname "$0")" ; pwd -P )"

cp -r $SCRIPTPATH/../EightSelect $SCRIPTPATH/EightSelect
sed -i '' "s@__VERSION__@${VERSION}@g" $SCRIPTPATH/EightSelect/plugin.xml
cd $SCRIPTPATH/EightSelect && composer install && cd ..
zip -r ../dist/8select_CSE_Shopware-5.2_${VERSION}.zip EightSelect
rm -rf $SCRIPTPATH/EightSelect
echo "created release ${VERSION} at dist/8select_CSE_Shopware-5.2_${VERSION}.zip"

