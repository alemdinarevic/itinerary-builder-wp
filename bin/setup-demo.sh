#!/usr/bin/env bash
#
# Sets up the demo itinerary as the site's homepage (see bin/setup-demo.php).
# Runs automatically after `yarn wp-env start`.
#
#   yarn demo         Create the demo page if it's missing.
#   yarn demo:reset   Rebuild it from demo/trip.json.

set -euo pipefail

cd "$(dirname "$0")/.."

# Run WP-CLI inside the wp-env container, from this plugin's folder.
./node_modules/.bin/wp-env run cli \
	--env-cwd="wp-content/plugins/$(basename "$PWD")" \
	wp eval-file bin/setup-demo.php "$@" 2>/dev/null
