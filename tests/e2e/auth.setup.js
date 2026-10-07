const { test: setup } = require( '@playwright/test' );
const { MEMBER_LOGIN, MEMBER_PASSWORD } = require( './config' );
const { logIn } = require( './helpers' );

setup( 'log in as the demo member', async ( { page } ) => {
	await logIn( page, MEMBER_LOGIN, MEMBER_PASSWORD );
	await page
		.context()
		.storageState( { path: 'tests/e2e/.auth/member.json' } );
} );
