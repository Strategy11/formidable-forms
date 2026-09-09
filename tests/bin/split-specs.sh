#!/usr/bin/env bash
#
# Print the comma-separated Cypress `--spec` list for one shard of a
# parallel E2E run.
#
# Usage: tests/bin/split-specs.sh <total_shards> <shard_index>
#   total_shards - how many shards the run is split across
#   shard_index  - 0-based index of the shard to print
#
# Specs are discovered by globbing, not listed explicitly, so a newly added
# spec is always picked up by some shard instead of being silently dropped.
#
# Balancing uses line count as a stand-in for runtime, assigning the largest
# spec first to whichever shard is currently lightest (greedy longest-
# processing-time). Plain round-robin lands the two biggest specs on the same
# shard and leaves it running ~4.5x longer than the shortest one; since the
# whole job is only as fast as its slowest shard, that wastes most of the
# parallelism. Line count is a rough proxy - swap in real per-spec durations
# here if the shards drift out of balance.

set -euo pipefail

total="${1:?usage: split-specs.sh <total_shards> <shard_index>}"
index="${2:?usage: split-specs.sh <total_shards> <shard_index>}"

if [ "$total" -lt 1 ] || [ "$index" -lt 0 ] || [ "$index" -ge "$total" ]; then
	echo "split-specs.sh: shard index $index out of range for $total shards" >&2
	exit 1
fi

cd "$( dirname "$0" )/../.."

# Keep the sort locale-independent so every shard in a run agrees on the
# ordering, and therefore on the partition.
export LC_ALL=C

find tests/cypress/e2e -type f \
	\( -name '*.cy.js' -o -name '*.cy.jsx' -o -name '*.cy.ts' -o -name '*.cy.tsx' \) \
	-exec sh -c 'for f do printf "%s\t%s\n" "$( wc -l < "$f" )" "$f"; done' sh {} + |
	sort -t"$( printf '\t' )" -k1,1nr -k2,2 |
	awk -F"$( printf '\t' )" -v total="$total" -v want="$index" '
		{
			best = 0;
			for ( s = 1; s < total; s++ ) {
				if ( load[ s ] < load[ best ] ) {
					best = s;
				}
			}
			load[ best ] += $1;
			if ( best == want ) {
				out = ( out == "" ? $2 : out "," $2 );
			}
		}
		END { print out }
	'
