/**
 * The trip overview's content, for the editor. trip_itinerary_summary_items()
 * in includes/helpers.php builds the same list in PHP for the site.
 */
import { __, _n, _x, sprintf } from '@wordpress/i18n';

import { formatTripDate, tripDate } from './dates';

/**
 * "13 – 15 Nov 2026", "30 Nov – 2 Dec 2026" or "30 Dec 2026 – 2 Jan 2027".
 *
 * @param {Date} start
 * @param {Date} end
 * @return {string} The formatted range.
 */
function formatRange( start, end ) {
	/* translators: Date format for a single trip date, see https://www.php.net/manual/datetime.format.php */
	const full = _x( 'j M Y', 'trip date format', 'trip-itinerary' );

	if ( start.getTime() === end.getTime() ) {
		return formatTripDate( start, full );
	}

	let startFormat = full;
	if ( start.getUTCFullYear() === end.getUTCFullYear() ) {
		startFormat =
			start.getUTCMonth() === end.getUTCMonth()
				? /* translators: Start of a date range within one month. */
					_x( 'j', 'trip range start, same month', 'trip-itinerary' )
				: /* translators: Start of a date range within one year. */
					_x(
						'j M',
						'trip range start, same year',
						'trip-itinerary'
					);
	}

	return `${ formatTripDate( start, startFormat ) } – ${ formatTripDate(
		end,
		full
	) }`;
}

/**
 * @param {Object}   trip
 * @param {string}   [trip.startDate]  Trip start, as YYYY-MM-DD.
 * @param {number}   [trip.travellers] Number of travellers.
 * @param {string[]} trip.locations    Each day's location as plain text, in order;
 *                                     only the number of days is used.
 * @return {Array<{key: string, label: string, value: string, detail?: string}>} Items to show; empty when there's nothing to say.
 */
export function getTripSummary( { startDate, travellers, locations } ) {
	const items = [];
	const dayCount = locations.length;

	const start = tripDate( startDate, 0 );
	if ( start && dayCount > 0 ) {
		items.push( {
			key: 'dates',
			label: __( 'Dates', 'trip-itinerary' ),
			value: formatRange( start, tripDate( startDate, dayCount - 1 ) ),
			detail: sprintf(
				/* translators: %d: number of days. */
				_n( '%d day', '%d days', dayCount, 'trip-itinerary' ),
				dayCount
			),
		} );
	}

	if ( travellers > 0 ) {
		items.push( {
			key: 'travellers',
			label: __( 'Travellers', 'trip-itinerary' ),
			value: sprintf(
				/* translators: %d: number of people. */
				_n( '%d person', '%d people', travellers, 'trip-itinerary' ),
				travellers
			),
		} );
	}

	return items;
}
