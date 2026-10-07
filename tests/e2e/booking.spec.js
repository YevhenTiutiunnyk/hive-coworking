const { test, expect } = require( '@playwright/test' );
const { weekdayAhead, bookThroughApi, spaceIdOnPage } = require( './helpers' );

const SPACE_URL = '/spaces/lighthouse-room/';

/**
 * Hive Harbour, where the Lighthouse Room is, is open 07:00–21:00 on weekdays.
 *
 * @param {import('@playwright/test').Page} page Page.
 * @param {string}                          date YYYY-MM-DD.
 */
async function openDay( page, date ) {
	// Wait for the view scripts, so the Interactivity API handles the date change.
	await page.goto( SPACE_URL, { waitUntil: 'networkidle' } );

	await Promise.all( [
		page.waitForResponse( ( response ) =>
			response.url().includes( `availability?date=${ date }` )
		),
		page.getByLabel( 'Date' ).fill( date ),
	] );
	await expect(
		page.getByRole( 'button', { name: '09:00', exact: true } )
	).toBeEnabled();
}

test.describe( 'guests', () => {
	test.use( { storageState: { cookies: [], origins: [] } } );

	test( 'see free slots and are asked to log in', async ( { page } ) => {
		await page.goto( SPACE_URL );

		await expect(
			page.getByRole( 'list', { name: 'Time slots' } )
		).toBeVisible();
		await expect(
			page.getByRole( 'link', { name: 'Log in to book' } )
		).toHaveAttribute( 'href', /wp-login\.php/ );
		await expect(
			page.getByRole( 'button', { name: 'Book', exact: true } )
		).toHaveCount( 0 );
	} );
} );

test.describe( 'members', () => {
	test( 'book two consecutive slots', async ( { page } ) => {
		await openDay( page, weekdayAhead( 10 ) );

		await page
			.getByRole( 'button', { name: '14:00', exact: true } )
			.click();
		await page
			.getByRole( 'button', { name: '15:00', exact: true } )
			.click();
		await expect( page.locator( '.hive-booking__summary' ) ).toContainText(
			'14:00–16:00 · 2 h · €80.00'
		);

		await page.getByRole( 'button', { name: 'Book', exact: true } ).click();

		await expect(
			page.getByRole( 'status' ).filter( { hasText: 'Booked:' } )
		).toContainText( 'Lighthouse Room' );
		await expect(
			page.getByRole( 'button', { name: '14:00', exact: true } )
		).toBeDisabled();
		await expect(
			page.getByRole( 'button', { name: '15:00', exact: true } )
		).toBeDisabled();
		await expect(
			page.getByRole( 'button', { name: '16:00', exact: true } )
		).toBeEnabled();
	} );

	test( 'see a clear error when someone else books the slot first', async ( {
		page,
	} ) => {
		const date = weekdayAhead( 13 );
		await openDay( page, date );
		await page
			.getByRole( 'button', { name: '10:00', exact: true } )
			.click();

		// Another booking for the same hour arrives while the page is open.
		const response = await bookThroughApi( page, {
			space_id: await spaceIdOnPage( page ),
			start: `${ date }T10:00:00`,
			end: `${ date }T11:00:00`,
		} );
		expect( response.status() ).toBe( 201 );

		await page.getByRole( 'button', { name: 'Book', exact: true } ).click();

		await expect( page.locator( '.hive-booking__message' ) ).toHaveText(
			'This space is already booked for part of that time.'
		);
		await expect( page.locator( '.hive-booking__message' ) ).toHaveClass(
			/is-error/
		);
	} );

	test( 'cancel a booking from their account', async ( { page } ) => {
		const date = weekdayAhead( 16 );
		await openDay( page, date );
		const response = await bookThroughApi( page, {
			space_id: await spaceIdOnPage( page ),
			start: `${ date }T17:00:00`,
			end: `${ date }T18:00:00`,
		} );
		expect( response.status() ).toBe( 201 );

		await page.goto( '/account/' );
		const row = page
			.locator( '.hive-account__item' )
			.filter( { hasText: '17:00–18:00' } );
		await expect( row ).toContainText( 'Confirmed' );

		page.once( 'dialog', ( dialog ) => dialog.accept() );
		await row.getByRole( 'button', { name: 'Cancel' } ).click();

		await expect( page.locator( '.hive-account__message' ) ).toHaveText(
			'Booking cancelled.'
		);
		await expect( row ).toContainText( 'Cancelled' );
		await expect(
			row.getByRole( 'button', { name: 'Cancel' } )
		).toBeHidden();
	} );
} );
