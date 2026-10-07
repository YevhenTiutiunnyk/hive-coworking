const { test: setup, expect } = require( '@playwright/test' );
const { MEMBER_LOGIN, MEMBER_PASSWORD } = require( './config' );

setup( 'log in as the demo member', async ( { page } ) => {
	await page.goto( '/wp-login.php' );
	await page.getByLabel( 'Username or Email Address' ).fill( MEMBER_LOGIN );
	await page
		.getByLabel( 'Password', { exact: true } )
		.fill( MEMBER_PASSWORD );
	await page.getByRole( 'button', { name: 'Log In' } ).click();
	await expect( page ).not.toHaveURL( /wp-login\.php/ );

	await page
		.context()
		.storageState( { path: 'tests/e2e/.auth/member.json' } );
} );
