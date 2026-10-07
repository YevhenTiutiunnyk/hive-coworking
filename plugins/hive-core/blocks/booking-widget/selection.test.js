/**
 * External dependencies
 */
import { describe, expect, it } from 'vitest';

/**
 * Internal dependencies
 */
import { EMPTY, selectSlot, isSelected, selectedRange } from './selection';

const slot = ( hour, available = true ) => ( {
	start: `2030-01-08T${ hour }:00:00+01:00`,
	end: `2030-01-08T${ hour + 1 }:00:00+01:00`,
	label: `${ hour }:00`,
	available,
} );

const slots = [
	slot( 10 ),
	slot( 11 ),
	slot( 12, false ),
	slot( 13 ),
	slot( 14 ),
];

describe( 'selectSlot', () => {
	it( 'starts a selection on the first click', () => {
		expect( selectSlot( slots, EMPTY, 0 ) ).toEqual( { start: 0, end: 0 } );
	} );

	it( 'extends the selection to a later slot', () => {
		expect( selectSlot( slots, { start: 0, end: 0 }, 1 ) ).toEqual( {
			start: 0,
			end: 1,
		} );
	} );

	it( 'clears the selection when the only selected slot is clicked again', () => {
		expect( selectSlot( slots, { start: 1, end: 1 }, 1 ) ).toEqual( EMPTY );
	} );

	it( 'starts over when clicking before the selection', () => {
		expect( selectSlot( slots, { start: 1, end: 1 }, 0 ) ).toEqual( {
			start: 0,
			end: 0,
		} );
	} );

	it( 'starts over instead of extending across a booked slot', () => {
		expect( selectSlot( slots, { start: 0, end: 1 }, 3 ) ).toEqual( {
			start: 3,
			end: 3,
		} );
	} );

	it( 'starts over when clicking inside a longer selection', () => {
		expect( selectSlot( slots, { start: 3, end: 4 }, 4 ) ).toEqual( {
			start: 4,
			end: 4,
		} );
	} );

	it( 'ignores unavailable slots', () => {
		expect( selectSlot( slots, { start: 0, end: 0 }, 2 ) ).toEqual( {
			start: 0,
			end: 0,
		} );
	} );
} );

describe( 'isSelected', () => {
	it( 'is true for slots inside the selection', () => {
		const selection = { start: 0, end: 1 };

		expect(
			[ 0, 1, 2 ].map( ( index ) => isSelected( selection, index ) )
		).toEqual( [ true, true, false ] );
	} );

	it( 'is false for an empty selection', () => {
		expect( isSelected( EMPTY, 0 ) ).toBe( false );
	} );
} );

describe( 'selectedRange', () => {
	it( 'returns start, end and length in hours', () => {
		expect( selectedRange( slots, { start: 0, end: 1 } ) ).toEqual( {
			start: slots[ 0 ].start,
			end: slots[ 1 ].end,
			startLabel: '10:00',
			endLabel: '12:00',
			hours: 2,
		} );
	} );

	it( 'returns null without a selection', () => {
		expect( selectedRange( slots, EMPTY ) ).toBeNull();
	} );
} );
