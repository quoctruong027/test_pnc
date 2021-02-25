#!/bin/bash
cd "$(dirname "$0")"
cd ..
echo $PWD
wp i18n make-pot $PWD $PWD/languages/woofunnels-upstroke-power-pack.pot --exclude=".github,.git,node_modules,woofunnels,admin/includes/wfacpkirki,admin/assets,assets"
