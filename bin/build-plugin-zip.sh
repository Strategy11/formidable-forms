#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
GREEN_BOLD='\033[1;32m';
RED_BOLD='\033[1;31m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
error () {
	echo -e "\n${RED_BOLD}$1${COLOR_RESET}\n"
}
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
success () {
	echo -e "\n${GREEN_BOLD}$1${COLOR_RESET}\n"
}
warning () {
	echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}

status "Time to release"

# Make sure there are no changes in the working tree. Release builds should be
# traceable to a particular commit and reliably reproducible.
changed=
if ! git diff --exit-code > /dev/null; then
	changed="file(s) modified"
elif ! git diff --cached --exit-code > /dev/null; then
	changed="file(s) staged"
fi
if [ ! -z "$changed" ]; then
	git status
	error "ERROR: Cannot build plugin zip with dirty working tree.
       Commit your changes and try again."
	exit 1
fi

# Do a dry run of the repository reset. Prompting the user for a list of all
# files that will be removed should prevent them from losing important files!
status "Resetting the repository to pristine condition."
git clean -df --dry-run
warning "About to delete everything above! Is this okay?"
echo -n "[y]es/[n]o: "
read answer
if [ "$answer" != "${answer#[Yy]}" ]; then
	# Remove ignored files to reset repository to pristine condition. Previous
	# test ensures that changed files abort the plugin build.
	status "Cleaning working directory..."
	git clean -df
else
	error "Fair enough; aborting. Tidy up your repo and try again."
	exit 1
fi

echo -n "Version Number: "
read version

# Run the build.
status "Installing dependencies..."
npm install
status "Generating build..."
npm run build
status "Minimizing JS"
npx google-closure-compiler --js=js/formidable.js --js_output_file=js/formidable.min.js --compilation_level=WHITESPACE

# Modify files with new version number. Use a temp file
# because the new file reads from the original so we need
# to avoid writing to that file at the same time.
php bin/set-php-version.php formidable $version > formidable.tmp.php
mv formidable.tmp.php formidable.php

php bin/set-php-version.php classes/helpers/FrmAppHelper $version > FrmAppHelper.tmp.php
mv FrmAppHelper.tmp.php classes/helpers/FrmAppHelper.php

# WP.org can't scan js files for strings. So they need to be added into
# a PHP file so they can be read and included for translations
status "Adding JS strings to PHP"
npx pot-to-php languages/formidablejs.pot languages/formidable-js-strings.php formidable
status "Preparing POT file"
npm run makepot

success "Done. You've built Formidable $version! 🎉 "

warning "Commit changes and create a release?"
echo -n "[y]es/[n]o: "
read answer
if [ "$answer" != "${answer#[Yy]}" ]; then
	status "Commiting..."
	git commit -m "Prepare for v$version release"
	status "Creating new GitHub release"
	git_commit= git rev-parse HEAD
	git tag -a v$version -m "Release v$version"
	git push --tags
	success "New version created."
else
	error "Changes not commited."
	exit 1
fi
