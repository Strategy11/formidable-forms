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

# Arguments after the destination are exclusions supplied by the plugin wrapper.
# Generate the plugin zip file.
status "Creating archive..."
zip -r $zipname $destination \
	-x "*/.DS_Store" \
	-x "*/.babelrc" \
	-x "*/.browserslistrc" \
	-x "*/.editorconfig" \
	-x "*/.eslintignore" \
	-x "*/.eslintrc.json" \
	-x "*/eslint.config.mjs" \
	-x "*/.eslintrc" \
	-x "*/.git" \
	-x "*/.git/*" \
	-x "*/.gitattributes" \
	-x "*/.githooks/*" \
	-x "*/.github/*" \
	-x "*/.gitignore" \
	-x "*/.windsurf/*" \
	-x "*/.devin/*" \
	-x "*/.vscode/*" \
	-x "*/.achecker.yml" \
	-x "*/.jshintignore" \
	-x "*/.php-cs-fixer.cache" \
	-x "*/.php-cs-fixer.php" \
	-x "*/.phpunit.result.cache" \
	-x "*.cache" \
	-x "*.cache.*" \
	-x "*/.stylelintrc.json" \
	-x "*/.wordpress-org/*" \
	-x "*/.wp-env.json" \
	-x "*/.prettierrc" \
	-x "*/.scannerwork" \
	-x "*/.jscsrc" \
	-x "*/.jshintrc" \
	-x "*/.jshintignore" \
	-x "*/.coderabbit.yaml" \
	-x "*/.deepsource.toml" \
	-x "*/.semgrepignore" \
	-x "*/.gitmodules" \
	-x "*/.oxlintrc.json" \
	-x "*/.phpunit.cache/*" \
	-x "*/bin/*" \
	-x "*/scss/*" \
	-x "*/css/*.css.map" \
	-x "*/js/*.js.map" \
	-x "*/changelog.txt" \
	-x "*/composer.json" \
	-x "*/composer.lock" \
	-x "*/composer.ci.json" \
	-x "*/composer.ci.lock" \
	-x "*/crowdin.yml" \
	-x "*/formidableforms.css" \
	-x "*/formidableforms[0-9]*.css" \
	-x "*/js/src/*" \
	-x "*/assets/src/*" \
	-x "*/blocks-src/*" \
	-x "*/js/frm.min.js" \
	-x "formidable/js/frmstrp.min.js" \
	-x "formidable/stripe/js/frmstrp.js" \
	-x "*/dropzone.js" \
	-x "*/formidable-js.pot" \
	-x "*/jest.config.js" \
	-x "*/node_modules/*" \
	-x "*/npm-debug.log" \
	-x "*/results.log" \
	-x "*/package.json" \
	-x "*/package-lock.json" \
	-x "*/phpcs.xml" \
	-x "*/phpunit*.xml" \
	-x "*/phpunit*.xml.dist" \
	-x "*/psalm.xml" \
	-x "*/phpstan*.neon" \
	-x "*/*.stubs.php" \
	-x "*/stubs.php" \
	-x "*/stubs" \
	-x "*/stubs-*" \
	-x "*/readme.md" \
	-x "*/README.md" \
	-x "*.md" \
	-x "*/docs/*" \
	-x "*/baseline-*/*" \
	-x "*/.lint-tools/*" \
	-x "*/.core-snapshot/*" \
	-x "*/.playwright-cli/*" \
	-x "*/qa-*.ps1" \
	-x "*/screens*.json" \
	-x "*/tests/*" \
	-x "$destination/vendor/*" \
	-x "$destination/formidable-payments/vendor/*" \
	-x "*/temp.xml" \
	-x "*/webpack.config.js" \
	-x "*.zip" \
	-x "*/rector.php" \
	-x "*/sonar-project.properties" \
	-x "*/.sonar_lock" \
	-x "*/report-task.txt" \
	-x "*/cypress.config.js" \
	-x "*/_typos.toml" \
	-x "*/infection.json5" \
	-x "*/scripts/install-hooks.sh" \
	-x "*/scripts/pre-push" \
	-x "*/mago.toml" \
	-x "*/webpack.dev.js" \
	-x "*/eslint-rules/*" \
	-x "*/phpcs-sniffs/*" \
	-x "$destination/venv/*" \
	-x "formidable/resources/*" \
	-x "*/cypress/*" \
	-x "*/AGENTS.md" \
	-x "*/setup.bat" \
	-x "*/phpstan*.neon.dist" \
	-x "*/phpcs*.xml.dist" \
	-x "*/webpack.config.cjs" \
	-x "*/cypress.preview.config.js" \
	"${@:4}"

if [ ! -z "$3" ]; then
	rm -rf $destination
fi

status "Done. You've built Formidable $version! 🎉 "
