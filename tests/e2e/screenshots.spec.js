/**
 * Captures the README screenshots: npm run screenshots
 */
const { test, expect } = require( '@playwright/test' );
const { weekdayAhead } = require( './helpers' );

const OUT = 'docs/screenshots';
const shot = ( page, name ) =>
	page.screenshot( {
		path: `${ OUT }/${ name }.jpg`,
		type: 'jpeg',
		quality: 82,
	} );

test.use( { viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 } );

test( 'home', async ( { page } ) => {
	await page.goto( '/', { waitUntil: 'networkidle' } );
	await page.waitForTimeout( 1200 ); // Let the hero animation finish.
	await shot( page, 'home' );
} );

test( 'space with a selection', async ( { page } ) => {
	const date = weekdayAhead( 20 );
	await page.goto( '/spaces/lighthouse-room/', { waitUntil: 'networkidle' } );
	await Promise.all( [
		page.waitForResponse( ( response ) =>
			response.url().includes( `availability?date=${ date }` )
		),
		page.getByLabel( 'Date' ).fill( date ),
	] );
	await page.getByRole( 'button', { name: '10:00', exact: true } ).click();
	await page.getByRole( 'button', { name: '11:00', exact: true } ).click();
	await expect( page.locator( '.hive-booking__summary' ) ).toContainText(
		'2 h'
	);
	await shot( page, 'space' );
} );

test( 'space finder', async ( { page } ) => {
	await page.goto( '/spaces/?space_type=meeting_room', {
		waitUntil: 'networkidle',
	} );
	await page.locator( '.hive-finder__filters' ).scrollIntoViewIfNeeded();
	await page.evaluate( () => window.scrollBy( 0, -120 ) );
	await shot( page, 'finder' );
} );

test( 'pricing and events', async ( { page } ) => {
	await page.goto( '/pricing/', { waitUntil: 'networkidle' } );
	await page.getByRole( 'button', { name: /Yearly/ } ).click();
	await page.locator( '.hive-plans' ).scrollIntoViewIfNeeded();
	await shot( page, 'pricing' );
} );

test( 'my bookings', async ( { page } ) => {
	await page.goto( '/account/', { waitUntil: 'networkidle' } );
	await shot( page, 'account' );
} );

test( 'mobile', async ( { browser } ) => {
	const context = await browser.newContext( {
		viewport: { width: 390, height: 844 },
		deviceScaleFactor: 2,
		isMobile: true,
	} );
	const page = await context.newPage();
	await page.goto( '/', { waitUntil: 'networkidle' } );
	await page.waitForTimeout( 1200 );
	await page.screenshot( {
		path: `${ OUT }/mobile.jpg`,
		type: 'jpeg',
		quality: 82,
	} );
	await context.close();
} );

test( 'admin bookings screen', async ( { browser } ) => {
	const context = await browser.newContext( {
		viewport: { width: 1440, height: 900 },
	} );
	const page = await context.newPage();
	await page.goto( '/wp-login.php' );
	await page.getByLabel( 'Username or Email Address' ).fill( 'admin' );
	await page.getByLabel( 'Password', { exact: true } ).fill( 'password' );
	await page.getByRole( 'button', { name: 'Log In' } ).click();
	await page.goto( '/wp-admin/admin.php?page=hive-bookings&when=all' );
	await page.screenshot( {
		path: `${ OUT }/admin.jpg`,
		type: 'jpeg',
		quality: 82,
	} );
	await context.close();
} );
