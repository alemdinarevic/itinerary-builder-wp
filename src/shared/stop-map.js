/**
 * Draws the Trip overview's map with Leaflet: a numbered marker for each
 * stop, in the same style as the timeline's circles, joined in order by a
 * dashed line. Used by the site (src/overview/view.js) and the editor's
 * preview (src/overview/edit.js).
 */
import L from 'leaflet';

// Used in the editor, which can't ask PHP. The site gets the tiles from
// trip_itinerary_map_tiles(), so a filter there changes both.
// The markers' size in pixels, as in the overview's style.scss.
const MARKER_SIZE = 30;

export const DEFAULT_TILES = {
	url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
	attribution:
		'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
};

/**
 * @param {HTMLElement} element             The map's container.
 * @param {Array}       stops               Stops from getMapStops(), with an optional href.
 * @param {Object}      options
 * @param {Object}      options.tiles       { url, attribution }.
 * @param {boolean}     options.interactive Whether people can drag and zoom.
 * @param {Function}    options.onSelect    Called with a stop when its marker is clicked.
 * @return {Object} The Leaflet map; call .remove() to clean up.
 */
export function drawStopMap(
	element,
	stops,
	{ tiles = DEFAULT_TILES, interactive = true, onSelect } = {}
) {
	const map = L.map( element, {
		// Scrolling the page shouldn't zoom the map.
		scrollWheelZoom: false,
		dragging: interactive,
		touchZoom: interactive,
		doubleClickZoom: interactive,
		boxZoom: false,
		keyboard: interactive,
		zoomControl: interactive,
		attributionControl: true,
	} );
	map.attributionControl.setPrefix( false );

	L.tileLayer( tiles.url, {
		attribution: tiles.attribution,
		maxZoom: 18,
	} ).addTo( map );

	const accent =
		window
			.getComputedStyle( element )
			.getPropertyValue( '--trip-accent-color' )
			.trim() || '#70b59e';
	const points = stops.map( ( stop ) => [ stop.lat, stop.lng ] );

	if ( points.length > 1 ) {
		L.polyline( points, {
			color: accent,
			weight: 2.5,
			dashArray: '2 7',
			lineCap: 'round',
			interactive: false,
		} ).addTo( map );
	}

	if ( points.length === 1 ) {
		map.setView( points[ 0 ], 11 );
	} else if ( points.length > 1 ) {
		map.fitBounds( points, { padding: [ 40, 40 ], maxZoom: 12 } );
	}

	// Markers are placed once the view is set, so we know where they land.
	// Stops close together, like a town and a mountain above it, would
	// cover each other's numbers, so a marker that lands on another is
	// nudged up and to the right until it's clear. The line still joins
	// the real positions.
	const placed = [];
	stops.forEach( ( stop ) => {
		const point = map.latLngToContainerPoint( [ stop.lat, stop.lng ] );
		const shift = [ 0, 0 ];
		const overlaps = () =>
			placed.some(
				( other ) =>
					Math.hypot(
						other.x - ( point.x + shift[ 0 ] ),
						other.y - ( point.y + shift[ 1 ] )
					) <
					MARKER_SIZE + 4
			);
		for ( let step = 0; step < 6 && overlaps(); step++ ) {
			shift[ 0 ] += 18;
			shift[ 1 ] -= 18;
		}
		placed.push( { x: point.x + shift[ 0 ], y: point.y + shift[ 1 ] } );

		const marker = L.marker( [ stop.lat, stop.lng ], {
			// Numbers only in the HTML, so place names can't inject markup.
			icon: L.divIcon( {
				className: 'wp-block-trip-itinerary-overview__pin',
				html: `<span>${ Number( stop.number ) }</span>`,
				iconSize: [ MARKER_SIZE, MARKER_SIZE ],
				iconAnchor: [
					MARKER_SIZE / 2 - shift[ 0 ],
					MARKER_SIZE / 2 - shift[ 1 ],
				],
			} ),
			title: stop.name,
			alt: stop.name,
			keyboard: interactive,
			riseOnHover: true,
		} ).addTo( map );
		if ( onSelect ) {
			marker.on( 'click', () => onSelect( stop ) );
		}
	} );

	return map;
}
