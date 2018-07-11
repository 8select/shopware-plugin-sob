#!/usr/bin/env bash -e
. $(dirname "$0")/common.sh

zip -q -r "${DIST_PATH}" ${PLUGIN_NAME}
echo "created release ${VERSION} at ${DIST_PATH}"
