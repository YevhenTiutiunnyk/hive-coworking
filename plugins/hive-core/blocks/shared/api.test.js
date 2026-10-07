/**
 * External dependencies
 */
import { describe, expect, it } from 'vitest';

/**
 * Internal dependencies
 */
import { endpoint, format } from './api';

describe( 'endpoint', () => {
	it( 'appends the path to a pretty REST URL', () => {
		expect(
			endpoint(
				'https://example.com/wp-json/hive/v1/',
				'spaces/5/availability',
				{ date: '2030-01-08' }
			)
		).toBe(
			'https://example.com/wp-json/hive/v1/spaces/5/availability?date=2030-01-08'
		);
	} );

	it( 'appends the path to the rest_route parameter when permalinks are plain', () => {
		expect(
			endpoint( 'https://example.com/?rest_route=/hive/v1/', 'bookings', {
				scope: 'past',
			} )
		).toBe(
			'https://example.com/?rest_route=%2Fhive%2Fv1%2Fbookings&scope=past'
		);
	} );

	it( 'works without parameters', () => {
		expect(
			endpoint( 'https://example.com/wp-json/hive/v1/', 'bookings' )
		).toBe( 'https://example.com/wp-json/hive/v1/bookings' );
	} );
} );

describe( 'format', () => {
	it( 'replaces numbered placeholders', () => {
		expect(
			format( '%1$s on %2$s, %3$s', 'Room', 'Monday', '10:00' )
		).toBe( 'Room on Monday, 10:00' );
	} );

	it( 'replaces repeated placeholders', () => {
		expect( format( '%1$s and %1$s', 'a' ) ).toBe( 'a and a' );
	} );
} );
