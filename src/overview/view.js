/**
 * Draws the Trip overview's map on the site. render.php puts the stops and
 * tile settings in data attributes; this module only runs on pages with an
 * overview that has a map.
 */
import { drawStopMap } from '../shared/stop-map';

document
	.querySelectorAll( '.wp-block-trip-itinerary-overview__map[data-stops]' )
	.forEach( ( element ) => {
		const stops = JSON.parse( element.dataset.stops );
		const tiles = JSON.parse( element.dataset.tiles );

		drawStopMap( element, stops, {
			tiles,
			// A marker goes to its day, like the stop's link in the list.
			onSelect: ( stop ) => {
				if ( stop.href ) {
					window.location.hash = stop.href;
				}
			},
		} );
	} );
