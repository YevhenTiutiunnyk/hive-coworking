/**
 * Prefix of the finder's query parameters. Matches SpaceFinder::PARAM_PREFIX.
 */
export const PREFIX = 'space_';

/**
 * URL of the current page with the finder's filters replaced.
 *
 * Parameters that do not belong to the finder (such as `page_id`) are kept.
 *
 * @param {string}                  href    Current URL.
 * @param {Iterable<Array<string>>} entries Form entries, e.g. from FormData.
 * @return {string} New URL.
 */
export function filterUrl( href, entries ) {
	const url = new URL( href );

	[ ...url.searchParams.keys() ]
		.filter( ( key ) => key.startsWith( PREFIX ) )
		.forEach( ( key ) => url.searchParams.delete( key ) );

	for ( const [ key, value ] of entries ) {
		if ( value !== '' && value !== '0' ) {
			url.searchParams.append( key, value );
		}
	}

	return url.toString();
}
