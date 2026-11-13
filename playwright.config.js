/**
 * End-to-end tests, run with `yarn test:e2e` against the wp-env test site
 * (http://localhost:8889). Builds on the config @wordpress/scripts ships,
 * which logs in as admin before the tests start.
 */
const { defineConfig } = require( '@playwright/test' );
const baseConfig = require( '@wordpress/scripts/config/playwright.config' );

module.exports = defineConfig( {
	...baseConfig,
	testDir: './tests/e2e',
	webServer: {
		...baseConfig.webServer,
		command: 'yarn wp-env start',
	},
} );
