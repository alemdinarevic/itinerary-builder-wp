/**
 * Makes the photo carousel interactive on the site, using the WordPress
 * Interactivity API. The server renders the first state (includes/carousel.php);
 * this store takes over from there.
 *
 * Each carousel has its own context, { current, count }, and each slide and
 * dot adds its { index }. Child contexts inherit from the carousel's, so a
 * slide can read `current` and set it.
 */
import { store, getContext } from '@wordpress/interactivity';

import { slidePosition, wrapIndex } from '../shared/carousel';

const { state, actions } = store( 'trip-itinerary/carousel', {
	state: {
		// Derived state: getters run against the context of the element
		// whose directive reads them.
		get position() {
			const { index, current, count } = getContext();
			return slidePosition( index, current, count );
		},
		get isCurrent() {
			const { index, current } = getContext();
			return index === current;
		},
		get isNotCurrent() {
			return ! state.isCurrent;
		},
	},
	actions: {
		show() {
			const context = getContext();
			context.current = context.index;
		},
		previous() {
			const context = getContext();
			context.current = wrapIndex( context.current - 1, context.count );
		},
		next() {
			const context = getContext();
			context.current = wrapIndex( context.current + 1, context.count );
		},
		// Left and right arrow keys move between photos while focus is
		// anywhere in the carousel.
		onKeydown( event ) {
			if ( event.key === 'ArrowLeft' ) {
				event.preventDefault();
				actions.previous();
			} else if ( event.key === 'ArrowRight' ) {
				event.preventDefault();
				actions.next();
			}
		},
	},
} );
