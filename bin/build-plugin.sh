#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
plugin="$1"
cd ../../$plugin

# Enable nicer messaging for build status.
BLUE_BOLD='\033[1;34m';
YELLOW_BOLD='\033[1;33m';
COLOR_RESET='\033[0m';
status () {
	echo -e "\n${BLUE_BOLD}$1${COLOR_RESET}\n"
}
warning () {
	echo -e "\n${YELLOW_BOLD}$1${COLOR_RESET}\n"
}

status "Time to release"

# Update any submodules
# git submodule foreach git pull origin master
# if ! git diff --submodule=diff --exit-code > /dev/null; then
#	git commit -am 'Update submodule'
# fi

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
	warning "ERROR: Cannot build plugin zip with dirty working tree.
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
	warning "Fair enough; aborting. Tidy up your repo and try again."
	exit 1
fi

# Run the build.
status "Installing dependencies..."
npm install
status "Generating build..."
npm run build
status "Minimizing JS..."
npm run minimize

# Modify files with new version number. Use a temp file
# because the new file reads from the original so we need
# to avoid writing to that file at the same time.
status "Updating version in PHP..."
version="$2"
npm run set-version -- $version

status "Preparing POT file..."
npm run makepot

# Generate the plugin zip file.
status "Creating archive..."
npm run zip -- $version

warning "Commit changes and create a release?"
echo -n "[y]es/[n]o: "
read answer
if [ "$answer" != "${answer#[Yy]}" ]; then
	npm run git-release -- $version
else
	echo "Changes not committed."
fi
