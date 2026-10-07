/**
 * WordPress dependencies
 */
import { getContext, store, withSyncEvent } from '@wordpress/interactivity';

/**
 * Internal dependencies
 */
import { endpoint, format, request } from '../shared/api';
import { EMPTY, isSelected, selectSlot, selectedRange } from './selection';

const slotIndex = ( context ) =>
	context.slots.findIndex( ( slot ) => slot.start === context.slot.start );

const { state, actions } = store( 'hive/booking', {
	state: {
		get isSlotSelected() {
			const context = getContext();
			return isSelected( context.selection, slotIndex( context ) );
		},
		get isClosed() {
			const context = getContext();
			return ! context.isLoading && context.slots.length === 0;
		},
		get range() {
			const context = getContext();
			return selectedRange( context.slots, context.selection );
		},
		get summary() {
			const range = state.range;
			if ( ! range ) {
				return state.strings.selectPrompt;
			}

			const context = getContext();
			const price = new Intl.NumberFormat( state.locale, {
				style: 'currency',
				currency: state.currency,
			} ).format( range.hours * context.hourlyPrice );

			return `${ format( state.strings.summary, range.startLabel, range.endLabel, String( range.hours ) ) } · ${ price }`;
		},
		get canSubmit() {
			const context = getContext();
			return (
				state.canBook &&
				state.range !== null &&
				! context.isSubmitting &&
				! context.isLoading
			);
		},
		get isError() {
			return getContext().messageType === 'error';
		},
		get isSuccess() {
			return getContext().messageType === 'success';
		},
	},

	actions: {
		toggleSlot() {
			const context = getContext();
			context.selection = selectSlot(
				context.slots,
				context.selection,
				slotIndex( context )
			);
			context.message = '';
		},

		changeDate: withSyncEvent( function* ( event ) {
			const context = getContext();
			context.date = event.target.value;
			yield actions.loadSlots();
		} ),

		*loadSlots() {
			const context = getContext();
			context.isLoading = true;
			context.selection = EMPTY;

			try {
				const data = yield request(
					endpoint(
						state.restUrl,
						`spaces/${ context.spaceId }/availability`,
						{ date: context.date }
					),
					{},
					state.nonce
				);
				context.slots = data.slots.map( ( slot ) => ( {
					...slot,
					label: slot.start.slice( 11, 16 ),
				} ) );
			} catch ( error ) {
				context.slots = [];
				context.message = error.message || state.strings.error;
				context.messageType = 'error';
			} finally {
				context.isLoading = false;
			}
		},

		*book() {
			const range = state.range;
			if ( ! range ) {
				return;
			}

			const context = getContext();

			context.isSubmitting = true;
			context.message = '';

			try {
				const booking = yield request(
					endpoint( state.restUrl, 'bookings' ),
					{
						method: 'POST',
						body: JSON.stringify( {
							space_id: context.spaceId,
							start: range.start,
							end: range.end,
						} ),
					},
					state.nonce
				);

				const day = new Intl.DateTimeFormat( state.locale, {
					dateStyle: 'medium',
				} ).format( new Date( `${ context.date }T12:00:00` ) );
				context.message = format(
					state.strings.booked,
					booking.space_title,
					day,
					range.startLabel,
					range.endLabel
				);
				context.messageType = 'success';

				yield actions.loadSlots();
			} catch ( error ) {
				context.message = error.message || state.strings.error;
				context.messageType = 'error';
			} finally {
				context.isSubmitting = false;
			}
		},
	},
} );
