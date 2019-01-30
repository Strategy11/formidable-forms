#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
COLOR_RESET='\033[0m';
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}

# Generate the plugin zip file.
status "Creating archive..."

version="$1"

# Package the zip
cd ..
rm -rf formidable-$version.zip

status "Creating Lite archive..."
zip -r formidable-$version.zip $version \
	-x "*/.*" \
	-x "*/.git/*" \
	-x "*/bin/*" \
	-x "*/composer.json" \
	-x "*/js/src/*" \
	-x "*/js/frm.min.js" \
	-x "*node_modules/*" \
	-x "*/npm-debug.log" \
	-x "*/package.json" \
	-x "*/package-lock.json" \
	-x "*/phpcs.xml" \
	-x "*/phpunit.xml" \
	-x "*/tests/*" \
	-x "*/webpack.config.js" \
	-x "*.zip"

status "Done. You've built Formidable $version! 🎉 "
