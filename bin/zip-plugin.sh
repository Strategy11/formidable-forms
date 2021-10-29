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

source="$1"
version="$2"
zipname="$source-$version.zip"
destination="$source"

# Package the zip
cd ..
rm -rf $zipname
if [ ! -z "$3" ]; then
	destination="$3"
	rm -rf $destination
	rsync -avz --exclude 'node_modules' --exclude '*.git/*' --exclude 'tests' $source/ $destination/
fi

# TODO: there is no need to create a zip for the Lite version. Instead, rsync to the svn repo folder

# Generate the plugin zip file.
status "Creating archive..."
zip -r $zipname $destination \
	-x "*/.*" \
	-x "*/.git/*" \
	-x "*/.github/*" \
	-x "*/.phpunit.result.cache" \
	-x "*/bin/*" \
	-x "*/scss/*" \
	-x "*/css/*.css.map" \
	-x "*/changelog.txt" \
	-x "*/composer.json" \
	-x "*/composer.lock" \
	-x "*/formidableforms.css" \
	-x "*/js/src/*" \
	-x "*/js/frm.min.js" \
	-x "*/dropzone.js" \
	-x "*/formidable-js.pot" \
	-x "*/node_modules/*" \
	-x "*/npm-debug.log" \
	-x "*/results.log" \
	-x "*/package.json" \
	-x "*/package-lock.json" \
	-x "*/phpcs.xml" \
	-x "*/phpunit.xml" \
	-x "*/psalm.stubs.php" \
	-x "*/psalm.xml" \
	-x "*/readme.md" \
	-x "*/README.md" \
	-x "*/tests/*" \
	-x "*/vendor/*" \
	-x "*/temp.xml" \
	-x "formidable-pro/views/*" \
	-x "formidable-views/js/dom.js" \
	-x "formidable-views/js/editor.js" \
	-x "formidable-views/js/index.js" \
	-x "*/webpack.config.js" \
	-x "*.zip"

if [ ! -z "$3" ]; then
	rm -rf $destination
fi

status "Done. You've built Formidable $version! 🎉 "
