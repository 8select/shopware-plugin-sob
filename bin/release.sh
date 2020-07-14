#!/usr/bin/env bash -e
. $(dirname "$0")/lib/common.sh

if [ "$#" -lt 1 ]; then
    echo "Illegal number of parameters"
    echo "Usage:"
    echo "bin/release.sh <version> [<profile>] [<stage>] [<shop-connector-url>] [<shop-url>]"
    exit 1
fi

. $(dirname "$0")/lib/build.sh

zip -q -r "${DIST_PATH}" ${PLUGIN_NAME}
echo "created release ${VERSION} at ${DIST_PATH}"
