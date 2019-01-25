#!/bin/bash

# npm install -g github-release-cli
# Setup Oauth before creating a release:
# vi ~/.bash_profile
# Add this line: export GITHUB_TOKEN=[Your token goes here]
# source ~/.bash_profile
# More info: https://help.github.com/articles/creating-a-personal-access-token-for-the-command-line/

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ..

version="$2"
repo="$1"
attachment=
if [ ! -z "$3" ]; then
	attachment="$3"
fi
attachment2=
if [ ! -z "$4" ]; then
	attachment2="$4"
fi
attachments="$attachment $attachment2"
changed=
if ! git diff --exit-code > /dev/null; then
	changed="file(s) modified"
elif ! git diff --cached --exit-code > /dev/null; then
	changed="file(s) staged"
fi
if [ ! -z "$changed" ]; then
	echo "Commiting..."
	git commit -a -m "Prepare for v$version release"
	git push
fi

echo "Creating new GitHub release"
export GIT_RELEASE_NOTES="$(git log $(git describe --tags --abbrev=0)..HEAD --pretty=format:'%h %B')"
github-release upload \
	--owner Strategy11 \
	--repo $repo \
	--tag "v$version" \
	--name "v$version" \
	--body "${GIT_RELEASE_NOTES}" \
		$attachments
echo "New version created."
