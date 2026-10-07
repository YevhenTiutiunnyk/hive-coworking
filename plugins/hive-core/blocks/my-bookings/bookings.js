/**
 * A copy of the list with one booking marked as cancelled.
 *
 * @param {Array<Object>} bookings Bookings shown in the list.
 * @param {number}        id       Cancelled booking.
 * @param {string}        label    Translated status label.
 * @return {Array<Object>} Updated list.
 */
export function markCancelled( bookings, id, label ) {
	return bookings.map( ( booking ) =>
		booking.id === id
			? {
					...booking,
					status: 'cancelled',
					statusLabel: label,
					canCancel: false,
				}
			: booking
	);
}
