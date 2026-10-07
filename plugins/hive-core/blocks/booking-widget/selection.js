/**
 * Pure helpers for selecting a contiguous range of slots.
 *
 * A selection is `{ start, end }` with slot indexes; `{ start: -1, end: -1 }` means nothing is selected.
 */

export const EMPTY = Object.freeze( { start: -1, end: -1 } );

/**
 * The selection after a slot is clicked.
 *
 * Clicking after a single selected slot extends the range if every slot in between is free;
 * any other click starts a new selection, and clicking the only selected slot clears it.
 *
 * @param {Array<{available: boolean}>}  slots     Slots of the day.
 * @param {{start: number, end: number}} selection Current selection.
 * @param {number}                       index     Clicked slot.
 * @return {{start: number, end: number}} New selection.
 */
export function selectSlot( slots, selection, index ) {
	if ( ! slots[ index ]?.available ) {
		return selection;
	}

	const { start, end } = selection;

	if ( start === index && end === index ) {
		return EMPTY;
	}

	const extendsSingleSlot = start !== -1 && start === end && index > start;
	if (
		extendsSingleSlot &&
		slots.slice( start, index + 1 ).every( ( slot ) => slot.available )
	) {
		return { start, end: index };
	}

	return { start: index, end: index };
}

/**
 * Whether a slot is part of the selection.
 *
 * @param {{start: number, end: number}} selection Current selection.
 * @param {number}                       index     Slot index.
 * @return {boolean} True when selected.
 */
export function isSelected( selection, index ) {
	return (
		selection.start !== -1 &&
		index >= selection.start &&
		index <= selection.end
	);
}

/**
 * Details of the selected range, or null.
 *
 * @param {Array<{start: string, end: string, label: string}>} slots     Slots of the day.
 * @param {{start: number, end: number}}                       selection Current selection.
 * @return {?{start: string, end: string, startLabel: string, endLabel: string, hours: number}} Range.
 */
export function selectedRange( slots, selection ) {
	if ( selection.start === -1 ) {
		return null;
	}

	const first = slots[ selection.start ];
	const last = slots[ selection.end ];

	return {
		start: first.start,
		end: last.end,
		startLabel: first.label,
		endLabel: timeOf( last.end ),
		hours: ( Date.parse( last.end ) - Date.parse( first.start ) ) / 3600000,
	};
}

/**
 * HH:MM part of an ISO 8601 time, as written (in the site timezone).
 *
 * @param {string} iso ISO 8601 time with offset.
 * @return {string} Time such as "12:00".
 */
function timeOf( iso ) {
	return iso.slice( 11, 16 );
}
