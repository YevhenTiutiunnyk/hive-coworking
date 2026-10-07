/**
 * External dependencies
 */
import { describe, expect, it } from 'vitest';

/**
 * Internal dependencies
 */
import { markCancelled } from './bookings';

describe( 'markCancelled', () => {
	const bookings = [
		{
			id: 1,
			status: 'confirmed',
			statusLabel: 'Confirmed',
			canCancel: true,
		},
		{
			id: 2,
			status: 'confirmed',
			statusLabel: 'Confirmed',
			canCancel: true,
		},
	];

	it( 'marks the booking as cancelled and keeps the others', () => {
		expect( markCancelled( bookings, 2, 'Cancelled' ) ).toEqual( [
			bookings[ 0 ],
			{
				id: 2,
				status: 'cancelled',
				statusLabel: 'Cancelled',
				canCancel: false,
			},
		] );
	} );

	it( 'does not change the original list', () => {
		markCancelled( bookings, 1, 'Cancelled' );

		expect( bookings[ 0 ].status ).toBe( 'confirmed' );
	} );
} );
