const { test, expect } = require( '@playwright/test' );

test( 'filters spaces without reloading the page', async ( { page } ) => {
	await page.goto( '/spaces/' );
	await expect( page.getByRole( 'status' ) ).toHaveText( '12 spaces found' );
	await page.evaluate( () => ( window.hiveSameDocument = true ) );

	await page
		.getByLabel( 'Location' )
		.selectOption( { label: 'Hive Greenhouse' } );

	await expect( page.getByRole( 'status' ) ).toHaveText( '4 spaces found' );
	await expect( page ).toHaveURL( /space_location=\d+/ );

	await page.getByRole( 'checkbox', { name: 'Projector' } ).check();

	await expect( page.getByRole( 'status' ) ).toHaveText( '1 space found' );
	await expect(
		page.getByRole( 'heading', { name: 'Fern Boardroom' } )
	).toBeVisible();
	expect( await page.evaluate( () => window.hiveSameDocument ) ).toBe( true );
} );

test( 'filtered URLs work without JavaScript', async ( { browser } ) => {
	const context = await browser.newContext( { javaScriptEnabled: false } );
	const page = await context.newPage();

	await page.goto( '/spaces/?space_type=hot_desk' );

	await expect( page.getByRole( 'status' ) ).toHaveText( '3 spaces found' );
	await context.close();
} );
