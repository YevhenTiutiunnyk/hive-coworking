// @ts-check
const { defineConfig, devices } = require( '@playwright/test' );

/**
 * End-to-end tests run against the wp-env "tests" site (port 8889),
 * so they never touch the development site.
 */
module.exports = defineConfig( {
	testDir: './tests/e2e',
	globalSetup: require.resolve( './tests/e2e/global-setup.js' ),
	fullyParallel: false,
	workers: 1,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI
		? [ [ 'github' ], [ 'html', { open: 'never' } ] ]
		: 'list',
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8889',
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{ name: 'setup', testMatch: /auth\.setup\.js/ },
		{
			name: 'chromium',
			use: {
				...devices[ 'Desktop Chrome' ],
				storageState: 'tests/e2e/.auth/member.json',
			},
			dependencies: [ 'setup' ],
			testIgnore: /screenshots\.spec\.js/,
		},
		{
			name: 'screenshots',
			testMatch: /screenshots\.spec\.js/,
			use: {
				...devices[ 'Desktop Chrome' ],
				storageState: 'tests/e2e/.auth/member.json',
			},
			dependencies: [ 'setup' ],
		},
	],
} );
