/**
 * The places a trip visits, for the Trip header: each day's location once,
 * in the order they're first visited, with the day to jump to.
 * trip_itinerary_stops() in includes/helpers.php does the same in PHP.
 *
 * @param {string[]} locations Each day's location as plain text, in order.
 * @return {Array<{name: string, dayIndex: number}>} Unique stops.
 */
export function getStops( locations ) {
	const stops = [];
	const seen = new Set();

	locations.forEach( ( location, dayIndex ) => {
		const name = location.trim();
		// "Sarajevo" and "sarajevo" are the same stop.
		const key = name.toLocaleLowerCase();
		if ( name && ! seen.has( key ) ) {
			seen.add( key );
			stops.push( { name, dayIndex } );
		}
	} );

	return stops;
}

/**
 * The trip's stops with their map positions: the stops from getStops(),
 * each placed where the first day at that place with a position is. Stops
 * without one are left off the map. trip_itinerary_map_stops() in
 * includes/helpers.php does the same in PHP.
 *
 * @param {string[]}             locations   Each day's location, in order.
 * @param {Array<number[]|null>} coordinates Each day's [ lat, lng ] or null.
 * @return {Array<{name: string, dayIndex: number, number: number, lat: number, lng: number}>} Stops on the map.
 */
export function getMapStops( locations, coordinates ) {
	return getStops( locations ).flatMap( ( stop, index ) => {
		const key = stop.name.toLocaleLowerCase();
		const day = locations.findIndex(
			( location, dayIndex ) =>
				location.trim().toLocaleLowerCase() === key &&
				coordinates[ dayIndex ]
		);
		if ( day === -1 ) {
			return [];
		}
		const [ lat, lng ] = coordinates[ day ];
		return [ { ...stop, number: index + 1, lat, lng } ];
	} );
}

/**
 * A day's map position, if it has a valid one. Mirrors
 * trip_itinerary_coordinates() in includes/helpers.php.
 *
 * @param {Object} attributes Day attributes, with lat and lng.
 * @return {number[]|null} [ lat, lng ], or null.
 */
export function getCoordinates( attributes ) {
	const { lat, lng } = attributes;
	const valid =
		typeof lat === 'number' &&
		typeof lng === 'number' &&
		Math.abs( lat ) <= 90 &&
		Math.abs( lng ) <= 180;
	return valid ? [ lat, lng ] : null;
}
