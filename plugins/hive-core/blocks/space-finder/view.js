/**
 * WordPress dependencies
 */
import { getContext, store, withSyncEvent } from '@wordpress/interactivity';

/**
 * Internal dependencies
 */
import { filterUrl } from './url';

const { actions } = store( 'hive/space-finder', {
	actions: {
		submit: withSyncEvent( function* ( event ) {
			event.preventDefault();
			yield actions.navigate( event.target );
		} ),

		change: withSyncEvent( function* ( event ) {
			yield actions.navigate( event.target.form );
		} ),

		/**
		 * Loads the filtered results without a full page reload.
		 * The server renders the new results; the router swaps them in.
		 *
		 * @param {HTMLFormElement} form Filter form.
		 */
		*navigate( form ) {
			const context = getContext();
			context.isLoading = true;

			try {
				const { actions: router } =
					yield import( '@wordpress/interactivity-router' );
				yield router.navigate(
					filterUrl(
						window.location.href,
						new window.FormData( form )
					)
				);
			} finally {
				context.isLoading = false;
			}
		},
	},
} );
