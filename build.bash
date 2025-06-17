# Script bash to create the downloadable archive of this WordPress plugin
readonly PLUGIN_NAME="tisseurs-frontend-publisher"
cd src/languages
msgfmt -o tisseurs-frontend-publisher-en_US.mo tisseurs-frontend-publisher-en_US.po
msgfmt -o tisseurs-frontend-publisher-fr_FR.mo tisseurs-frontend-publisher-fr_FR.po
cd ../..
if [ ! -d dist ]; then
  mkdir dist
fi
rm -rf dist/${PLUGIN_NAME}.zip
readonly WORKING_DIR=$(mktemp -d)
mkdir ${WORKING_DIR}/${PLUGIN_NAME}
cp -r src/* ${WORKING_DIR}/${PLUGIN_NAME}
CURRENT_DIR=$(pwd)
cd ${WORKING_DIR}
zip -r ${PLUGIN_NAME}.zip ${PLUGIN_NAME}/*
mv ${PLUGIN_NAME}.zip ${CURRENT_DIR}/dist
cd ${CURRENT_DIR}
rm -rf ${WORKING_DIR}