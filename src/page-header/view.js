/**
 * The Page header's menu button on phones, with the Interactivity API.
 * Escape closes the menu and returns focus to the button.
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

store( 'trip-itinerary/page-header', {
	state: {
		// True once this script runs; the header's is-enhanced class turns on
		// the menu button. Without the script the links simply stay visible.
		get isEnhanced() {
			return true;
		},
		get toggleLabel() {
			const { open, openLabel, closeLabel } = getContext();
			return open ? closeLabel : openLabel;
		},
	},
	actions: {
		toggle() {
			const context = getContext();
			context.open = ! context.open;
		},
		onKeydown( event ) {
			const context = getContext();
			if ( event.key === 'Escape' && context.open ) {
				context.open = false;
				getElement()
					.ref.querySelector(
						'.wp-block-trip-itinerary-page-header__menu-button'
					)
					?.focus();
			}
		},
	},
} );
