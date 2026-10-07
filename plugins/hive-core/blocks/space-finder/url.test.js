/**
 * External dependencies
 */
import { describe, expect, it } from 'vitest';

/**
 * Internal dependencies
 */
import { filterUrl } from './url';

describe( 'filterUrl', () => {
	it( 'writes non-empty filters to the query string', () => {
		const url = filterUrl( 'https://example.com/spaces/', [
			[ 'space_location', '12' ],
			[ 'space_type', '' ],
			[ 'space_capacity', '4' ],
			[ 'space_amenity[]', 'wifi' ],
			[ 'space_amenity[]', 'projector' ],
		] );

		expect( url ).toBe(
			'https://example.com/spaces/?space_location=12&space_capacity=4&space_amenity%5B%5D=wifi&space_amenity%5B%5D=projector'
		);
	} );

	it( 'replaces previous filters and keeps other parameters', () => {
		const url = filterUrl(
			'https://example.com/?page_id=5&space_type=hot_desk&space_amenity%5B%5D=wifi',
			[ [ 'space_type', 'meeting_room' ] ]
		);

		expect( url ).toBe(
			'https://example.com/?page_id=5&space_type=meeting_room'
		);
	} );

	it( 'drops zero capacity', () => {
		expect(
			filterUrl( 'https://example.com/spaces/', [
				[ 'space_capacity', '0' ],
			] )
		).toBe( 'https://example.com/spaces/' );
	} );
} );
