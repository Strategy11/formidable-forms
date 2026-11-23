#!/bin/bash

# Stop packaging if any script cannot be minimized.
set -euo pipefail

find "$1" -type f -name '*.js' ! -name '*.min.js' -print0 | while IFS= read -r -d '' script; do
	npx --no-install google-closure-compiler \
		--js="$script" \
		--js_output_file="${script%.js}.min.js" \
		--compilation_level=WHITESPACE
done
