/**
 * The Notifications bell and panel, with the Interactivity API.
 *
 * The block's context holds every notification's id and the ids this
 * visitor has read; each notification adds its own { id }. Read ids are kept
 * in localStorage, so they're remembered per browser, without accounts.
 */
import { store, getContext, getElement } from '@wordpress/interactivity';

const STORAGE_KEY = 'trip-itinerary-read-notifications';

/**
 * @return {string[]} Ids read in this browser. Empty when storage is
 * unavailable, for example in some private windows.
 */
function loadRead() {
	try {
		const stored = JSON.parse( window.localStorage.getItem( STORAGE_KEY ) );
		return Array.isArray( stored ) ? stored : [];
	} catch {
		return [];
	}
}

/**
 * Adds ids to the ones remembered as read.
 *
 * @param {string[]} ids
 */
function saveRead( ids ) {
	try {
		const all = new Set( [ ...loadRead(), ...ids ] );
		window.localStorage.setItem(
			STORAGE_KEY,
			JSON.stringify( [ ...all ] )
		);
	} catch {
		// Not remembered after this page, but the page still updates.
	}
}

const { state, actions } = store( 'trip-itinerary/notifications', {
	state: {
		get unreadCount() {
			const { ids, read } = getContext();
			return ids.filter( ( id ) => ! read.includes( id ) ).length;
		},
		get hasUnread() {
			return state.unreadCount > 0;
		},
		// For one notification: its id is in its own context.
		get isUnread() {
			const { id, read } = getContext();
			return ! read.includes( id );
		},
		get bellLabel() {
			const { label, labelUnread } = getContext();
			return state.hasUnread
				? labelUnread.replace( '%d', state.unreadCount )
				: label;
		},
	},
	actions: {
		toggle() {
			const context = getContext();
			context.open = ! context.open;
		},
		close( { focusBell = false } = {} ) {
			const context = getContext();
			if ( ! context.open ) {
				return;
			}
			context.open = false;
			if ( focusBell ) {
				getElement()
					.ref.closest( '.wp-block-trip-itinerary-notifications' )
					?.querySelector(
						'.wp-block-trip-itinerary-notifications__bell'
					)
					?.focus();
			}
		},
		// Clicks anywhere else on the page close the panel.
		closeOnOutsideClick( event ) {
			if ( ! getElement().ref.contains( event.target ) ) {
				actions.close();
			}
		},
		onKeydown( event ) {
			if ( event.key === 'Escape' ) {
				actions.close( { focusBell: true } );
			}
		},
		markRead() {
			const context = getContext();
			if ( ! context.read.includes( context.id ) ) {
				// A new array, so everything reading `read` updates.
				context.read = [ ...context.read, context.id ];
				saveRead( [ context.id ] );
			}
		},
		markAllRead() {
			const context = getContext();
			context.read = [ ...context.ids ];
			saveRead( context.ids );
		},
	},
	callbacks: {
		// When the block starts up: the ids this browser has already read.
		loadReadState() {
			const context = getContext();
			const read = loadRead();
			context.read = context.ids.filter( ( id ) => read.includes( id ) );
		},
	},
} );
