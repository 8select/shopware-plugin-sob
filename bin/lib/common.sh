#!/usr/bin/env bash -e

SOURCE="${BASH_SOURCE[0]}"
while [ -h "${SOURCE}" ]; do
  CURRENT_DIR="$( cd -P "$( dirname "${SOURCE}" )" && pwd )"
  SOURCE="$(readlink "${SOURCE}")"
  [[ ${SOURCE} != /* ]] && SOURCE="${CURRENT_DIR}/${SOURCE}"
done

CURRENT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

VERSION=${1}
PROFILE=${2:-staging}
STAGE=${3:-prod}
SHOP_CONNECTOR_OVERRIDE=${4:-default}
SHOP_URL_OVERRIDE=${5:-default}