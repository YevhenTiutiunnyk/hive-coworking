/**
 * The first weekday (Monday to Friday) at least `days` from today, as YYYY-MM-DD.
 * Far enough ahead that the site's timezone does not matter, and on a weekday because
 * opening hours differ at weekends.
 *
 * @param {number} days Minimum days from today.
 * @return {string} Date.
 */
function weekdayAhead( days ) {
	const date = new Date();
	date.setDate( date.getDate() + days );
	while ( [ 0, 6 ].includes( date.getDay() ) ) {
		date.setDate( date.getDate() + 1 );
	}
	return date.toISOString().slice( 0, 10 );
}

/**
 * Books a space through the REST API as the logged-in member, using the nonce on the current page.
 *
 * @param {import('@playwright/test').Page} page Page showing a booking widget.
 * @param {Object}                          data space_id, start, end.
 * @return {Promise<import('@playwright/test').APIResponse>} Response.
 */
async function bookThroughApi( page, data ) {
	const nonce = await page.evaluate(
		() =>
			JSON.parse(
				document.getElementById(
					'wp-script-module-data-@wordpress/interactivity'
				).textContent
			).state[ 'hive/booking' ].nonce
	);

	return page.request.post( '/wp-json/hive/v1/bookings', {
		headers: { 'X-WP-Nonce': nonce },
		data,
	} );
}

/**
 * The space ID from the booking widget's context on the current page.
 *
 * @param {import('@playwright/test').Page} page Page showing a booking widget.
 * @return {Promise<number>} Space ID.
 */
async function spaceIdOnPage( page ) {
	const context = await page
		.locator( '.hive-booking' )
		.getAttribute( 'data-wp-context' );
	return JSON.parse( context ).spaceId;
}

/**
 * Logs in through wp-login.php.
 *
 * The login page clears and focuses the username field 200 ms after load (wp_attempt_focus),
 * so filling the form earlier races with it. Waiting past that timer makes logins reliable.
 *
 * @param {import('@playwright/test').Page} page     Page.
 * @param {string}                          login    Username.
 * @param {string}                          password Password.
 */
async function logIn( page, login, password ) {
	await page.goto( '/wp-login.php', { waitUntil: 'load' } );
	await page.waitForTimeout( 400 );

	await page.getByLabel( 'Username or Email Address' ).fill( login );
	await page.getByLabel( 'Password', { exact: true } ).fill( password );
	await page.getByRole( 'button', { name: 'Log In' } ).click();
	await page.waitForURL(
		( url ) => ! url.pathname.endsWith( 'wp-login.php' ),
		{ timeout: 30_000 }
	);
}

module.exports = { weekdayAhead, bookThroughApi, spaceIdOnPage, logIn };
