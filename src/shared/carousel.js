/**
 * Where each photo sits in the stacked carousel: the current photo in the
 * centre, up to two on each side getting smaller, and the rest hidden. The
 * CSS in src/day/carousel.scss turns each position into a size and offset.
 *
 * trip_itinerary_slide_position() in includes/carousel.php has the same rules
 * in PHP, for the first render on the site.
 *
 * @param {number} index   The photo's position in the list.
 * @param {number} current The photo shown in the centre.
 * @param {number} count   How many photos there are.
 * @return {'center'|'right'|'left'|'right-far'|'left-far'|'hidden'} Position.
 */
export function slidePosition( index, current, count ) {
	const offset = ( index - current + count ) % count;

	// The order matters for small sets: with 3 photos, offset 2 is also
	// "count - 1", and should be on the left rather than far right.
	if ( offset === 0 ) {
		return 'center';
	}
	if ( offset === 1 ) {
		return 'right';
	}
	if ( offset === count - 1 ) {
		return 'left';
	}
	if ( offset === 2 ) {
		return 'right-far';
	}
	if ( offset === count - 2 ) {
		return 'left-far';
	}
	return 'hidden';
}

/**
 * Wraps around in both directions: after the last photo comes the first.
 *
 * @param {number} index
 * @param {number} count
 * @return {number} An index between 0 and count - 1.
 */
export function wrapIndex( index, count ) {
	return ( ( index % count ) + count ) % count;
}
