#!/usr/bin/env bash
#
# Builds the distributable ZIP.
#
# ⚠️ THE FOLDER NAME INSIDE THE ZIP IS THE POINT. WordPress derives a plugin's
# slug from its directory, and the plugin checker derives the expected text
# domain from that slug. Checking a GitHub "Download ZIP" therefore reports a
# text domain mismatch on every translated string in the plugin, because the
# folder is named after the branch (wordpress-group-chat-main) rather than the
# plugin. Nothing is wrong with the code in that case; the container is just
# named wrongly. This script puts the files in a correctly named folder so the
# check reflects reality.
#
#   ./bin/build-zip.sh
#
# Produces dist/wp-group-chat.zip, containing a single wp-group-chat/ folder.

set -euo pipefail

SLUG="wp-group-chat"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD="$ROOT/dist/$SLUG"

command -v zip >/dev/null 2>&1 || { echo "zip is required but not installed." >&2; exit 1; }

rm -rf "$ROOT/dist"
mkdir -p "$BUILD"

# Everything tracked by git, minus what .distignore excludes. Using git's file
# list rather than a copy of the working tree keeps stray local files out.
excludes=()
while IFS= read -r line; do
	[[ -z "$line" || "$line" == \#* ]] && continue
	excludes+=( "$line" )
done < "$ROOT/.distignore"

while IFS= read -r file; do
	skip=false
	for pattern in "${excludes[@]}"; do
		if [[ "$file" == "$pattern" || "$file" == "$pattern"/* ]]; then
			skip=true
			break
		fi
	done
	[[ "$skip" == true ]] && continue
	mkdir -p "$BUILD/$(dirname "$file")"
	cp "$ROOT/$file" "$BUILD/$file"
done < <(git -C "$ROOT" ls-files)

( cd "$ROOT/dist" && zip -rq "$SLUG.zip" "$SLUG" )

echo "Built dist/$SLUG.zip"
echo
echo "Contents:"
( cd "$ROOT/dist" && unzip -l "$SLUG.zip" | sed 's/^/  /' )
