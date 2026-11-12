#!/usr/bin/env bash
#
# Creates the demo itinerary page and makes it the site's homepage.
# Runs automatically after `yarn wp-env start` and is safe to run again:
# an existing demo page is reused, not duplicated.

set -euo pipefail

cd "$(dirname "$0")/.."

# Run WP-CLI inside the wp-env container, from this plugin's folder, so the
# demo content file can be passed by its relative path.
wp() {
	./node_modules/.bin/wp-env run cli \
		--env-cwd="wp-content/plugins/$(basename "$PWD")" \
		wp "$@" 2>/dev/null
}

page_id=$(wp post list --post_type=page --name=itinerary-demo --format=ids)

if [ -z "$page_id" ]; then
	page_id=$(wp post create demo/itinerary-demo.html \
		--post_type=page \
		--post_status=publish \
		--post_title="Itinerary demo" \
		--post_name=itinerary-demo \
		--porcelain)
	echo "Created the demo page (ID $page_id)."
fi

wp option update show_on_front page >/dev/null
wp option update page_on_front "$page_id" >/dev/null

echo "The demo itinerary is the homepage: http://localhost:8888"
