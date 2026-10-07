/**
 * WordPress dependencies
 */
import { getContext, store } from '@wordpress/interactivity';

/**
 * Internal dependencies
 */
import { endpoint, request } from '../shared/api';
import { markCancelled } from './bookings';

const { state } = store( 'hive/account', {
	state: {
		get isUpcomingTab() {
			return getContext().tab === 'upcoming';
		},
		get isPastTab() {
			return getContext().tab === 'past';
		},
		get showEmptyUpcoming() {
			const context = getContext();
			return context.tab === 'upcoming' && context.upcoming.length === 0;
		},
		get showEmptyPast() {
			const context = getContext();
			return context.tab === 'past' && context.past.length === 0;
		},
		get isCancelled() {
			return getContext().booking.status === 'cancelled';
		},
		get isError() {
			return getContext().messageType === 'error';
		},
		get isPending() {
			const context = getContext();
			return context.pendingId === context.booking.id;
		},
	},

	actions: {
		showUpcoming() {
			getContext().tab = 'upcoming';
		},

		showPast() {
			getContext().tab = 'past';
		},

		*cancel() {
			const context = getContext();
			const { id } = context.booking;

			// eslint-disable-next-line no-alert -- A native confirm is accessible and enough here.
			if ( ! window.confirm( state.strings.confirmCancel ) ) {
				return;
			}

			context.pendingId = id;
			context.message = '';

			try {
				yield request(
					endpoint( state.restUrl, `bookings/${ id }` ),
					{
						method: 'PATCH',
						body: JSON.stringify( { status: 'cancelled' } ),
					},
					state.nonce
				);
				context.upcoming = markCancelled(
					context.upcoming,
					id,
					state.strings.statusLabel
				);
				context.message = state.strings.cancelled;
				context.messageType = 'success';
			} catch ( error ) {
				context.message = error.message || state.strings.error;
				context.messageType = 'error';
			} finally {
				context.pendingId = 0;
			}
		},
	},
} );
