#!/bin/bash

# Exit if any command fails.
set -e

# Change to the expected directory.
cd "$(dirname "$0")"
cd ../..

folder="$1"
filename="$2"
version="$3"
file2=
if [ ! -z "$4" ]; then
	version="$4"
	file2="$3"
	php formidable/bin/set-php-version.php ../$folder/$file2 $version > $folder/$file2.tmp.php
	mv $folder/$file2.tmp.php $folder/$file2.php
	echo "Changed $folder/$file2.php to $version"
fi

php formidable/bin/set-php-version.php ../$folder/$filename $version > $folder/$filename.tmp.php
mv $folder/$filename.tmp.php $folder/$filename.php
echo "Changed $folder/$filename.php to $version"
