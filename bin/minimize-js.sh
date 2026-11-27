#!/bin/bash

# Stop packaging if any script cannot be minimized.
set -euo pipefail

compiler="$(dirname "$0")/../node_modules/.bin/google-closure-compiler"

find "$1" -type f -name '*.js' ! -name '*.min.js' -print0 | while IFS= read -r -d '' script; do
	"$compiler" \
		--js="$script" \
		--js_output_file="${script%.js}.min.js" \
		--compilation_level=WHITESPACE
done
