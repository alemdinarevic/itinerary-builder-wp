/**
 * Trip date helpers for the editor. includes/helpers.php has the same logic
 * in PHP for the site, so both show the same dates.
 *
 * Dates are calendar days ("2026-11-13"), not moments in time, so they are
 * handled in UTC throughout. Using local time would shift them by a day for
 * anyone east or west of UTC.
 */
import { dateI18n } from '@wordpress/date';

const ISO_DATE = /^\d{4}-\d{2}-\d{2}$/;

/**
 * The date `offset` days after `startDate`.
 *
 * @param {string|undefined} startDate Trip start, as YYYY-MM-DD.
 * @param {number}           offset    Days to add (0 for the first day).
 * @return {Date|null} The date, or null when there is no valid start date.
 */
export function tripDate( startDate, offset ) {
	if ( ! ISO_DATE.test( startDate ?? '' ) ) {
		return null;
	}
	const [ year, month, day ] = startDate.split( '-' ).map( Number );
	return new Date( Date.UTC( year, month - 1, day + offset ) );
}

/**
 * @param {Date} date
 * @return {string} The date as YYYY-MM-DD, for the datetime attribute.
 */
export function toIsoDate( date ) {
	return date.toISOString().slice( 0, 10 );
}

/**
 * Formats a date in the site's language, using PHP date format characters
 * (the same ones wp_date() takes in PHP).
 *
 * @param {Date}   date
 * @param {string} format For example 'D, j M'.
 * @return {string} The formatted date.
 */
export function formatTripDate( date, format ) {
	return dateI18n( format, date, 'UTC' );
}
