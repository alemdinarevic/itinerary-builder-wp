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
