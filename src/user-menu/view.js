/**
 * The User menu's dropdown, with the Interactivity API: opens from the
 * avatar button, closes on a click elsewhere or Escape.
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

const { actions } = store( 'trip-itinerary/user-menu', {
	actions: {
		toggle() {
			const context = getContext();
			context.open = ! context.open;
		},
		close( { focusButton = false } = {} ) {
			const context = getContext();
			if ( ! context.open ) {
				return;
			}
			context.open = false;
			if ( focusButton ) {
				getElement()
					.ref.closest( '.wp-block-trip-itinerary-user-menu' )
					?.querySelector(
						'.wp-block-trip-itinerary-user-menu__button'
					)
					?.focus();
			}
		},
		closeOnOutsideClick( event ) {
			if ( ! getElement().ref.contains( event.target ) ) {
				actions.close();
			}
		},
		onKeydown( event ) {
			if ( event.key === 'Escape' ) {
				actions.close( { focusButton: true } );
			}
		},
	},
} );
