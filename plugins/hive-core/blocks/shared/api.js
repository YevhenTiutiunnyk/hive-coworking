/**
 * Small helpers shared by the view scripts.
 *
 * View scripts are ES modules and cannot import classic scripts such as `@wordpress/api-fetch`,
 * so requests use fetch() with the REST nonce passed in the Interactivity API state.
 */

/**
 * Full URL of a REST endpoint.
 *
 * Supports both `/wp-json/hive/v1/` and `?rest_route=/hive/v1/` (sites with plain permalinks).
 *
 * @param {string}                 restUrl Base URL of the hive/v1 namespace, ending in a slash.
 * @param {string}                 path    Path inside the namespace.
 * @param {Object<string, string>} params  Query parameters.
 * @return {string} URL.
 */
export function endpoint( restUrl, path, params = {} ) {
	const url = new URL( restUrl );
	const route = url.searchParams.get( 'rest_route' );

	if ( route !== null ) {
		url.searchParams.set( 'rest_route', route + path );
	} else {
		url.pathname += path;
	}

	Object.entries( params ).forEach( ( [ key, value ] ) =>
		url.searchParams.set( key, value )
	);

	return url.toString();
}

/**
 * Fills `%1$s`-style placeholders, as produced by PHP translation functions.
 *
 * @param {string}    template Text with placeholders.
 * @param {...string} values   Replacement values.
 * @return {string} Text.
 */
export function format( template, ...values ) {
	return template.replace(
		/%(\d+)\$s/g,
		( match, position ) => values[ position - 1 ] ?? match
	);
}

/**
 * Sends a JSON request and returns the decoded body. Throws with the API's message on errors.
 *
 * @param {string} url     Endpoint URL.
 * @param {Object} options fetch() options.
 * @param {string} nonce   REST nonce, empty for guests.
 * @return {Promise<Object>} Response body.
 */
export async function request( url, options = {}, nonce = '' ) {
	const response = await fetch( url, {
		...options,
		credentials: 'same-origin',
		headers: {
			Accept: 'application/json',
			...( options.body ? { 'Content-Type': 'application/json' } : {} ),
			...( nonce ? { 'X-WP-Nonce': nonce } : {} ),
		},
	} );
	const body = await response.json().catch( () => ( {} ) );

	if ( ! response.ok ) {
		throw new Error( body.message || response.statusText );
	}

	return body;
}
