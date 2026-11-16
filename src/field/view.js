/**
 * The password field's show/hide button, with the Interactivity API. The
 * server renders the hidden state (render.php); `visible` in the field's
 * context switches the input's type and the button's label and icon.
 */
import { store, getContext } from '@wordpress/interactivity';

store( 'trip-itinerary/field', {
	state: {
		get type() {
			return getContext().visible ? 'text' : 'password';
		},
		get toggleLabel() {
			const { visible, showLabel, hideLabel } = getContext();
			return visible ? hideLabel : showLabel;
		},
		get isHidden() {
			return ! getContext().visible;
		},
	},
	actions: {
		toggle() {
			const context = getContext();
			context.visible = ! context.visible;
		},
	},
} );
