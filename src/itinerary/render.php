<?php
/**
 * Front-end output of the Itinerary block.
 *
 * The Itinerary renders its child blocks itself, instead of printing the
 * pre-rendered $content, so it can pass each Day its position and anchor,
 * the Trip overview the days' locations, the Trip header the stops and
 * where the price is, and the Trip price its anchor.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks.
 * @var WP_Block $block      Block instance.
 */

if ( trip_itinerary_has_legacy_markup( $block ) ) {
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Saved block markup, already filtered.
	return;
}

// Each day's location and map position, in order, for the Trip overview and
// the header's stops.
$trip_itinerary_locations   = array();
$trip_itinerary_coordinates = array();
foreach ( $block->inner_blocks as $trip_itinerary_child ) {
	if ( 'trip-itinerary/day' === $trip_itinerary_child->name ) {
		$trip_itinerary_locations[]   = trip_itinerary_plain_text( $trip_itinerary_child->attributes['location'] ?? '' );
		$trip_itinerary_coordinates[] = trip_itinerary_coordinates( $trip_itinerary_child->attributes );
	}
}

// Ids the Trip header links to: "<itinerary>-day-1", "-day-2"... The prefix
// is the Itinerary's anchor when it has one, so links stay stable, or a
// unique id, so two itineraries on one page don't clash.
$trip_itinerary_prefix = ! empty( $attributes['anchor'] ) ? $attributes['anchor'] : wp_unique_id( 'trip-' );
$trip_itinerary_day_id = static function ( $index ) use ( $trip_itinerary_prefix ) {
	return $trip_itinerary_prefix . '-day-' . ( $index + 1 );
};

// The "Summary only" style keeps the days' data for the header, overview and
// price, but doesn't show the days, so there's nothing for stops to link to.
$trip_itinerary_summary = in_array( 'is-style-summary', explode( ' ', $attributes['className'] ?? '' ), true );

$trip_itinerary_stops = array_map(
	static function ( $stop ) use ( $trip_itinerary_day_id, $trip_itinerary_summary ) {
		return array(
			'name' => $stop['name'],
			'href' => $trip_itinerary_summary ? '' : '#' . $trip_itinerary_day_id( $stop['dayIndex'] ),
		);
	},
	trip_itinerary_stops( $trip_itinerary_locations )
);

// The Trip price's anchor, for the Trip header's button.
$trip_itinerary_price_id   = $trip_itinerary_prefix . '-price';
$trip_itinerary_price_href = '';
foreach ( $block->inner_blocks as $trip_itinerary_child ) {
	if ( 'trip-itinerary/price' === $trip_itinerary_child->name ) {
		$trip_itinerary_price_href = '#' . $trip_itinerary_price_id;
	}
}

$trip_itinerary_children = '';
$trip_itinerary_day      = 0;

foreach ( $block->inner_blocks as $trip_itinerary_child ) {
	if ( 'trip-itinerary/day' === $trip_itinerary_child->name ) {
		$trip_itinerary_child->context['trip-itinerary/dayId']    = $trip_itinerary_day_id( $trip_itinerary_day );
		$trip_itinerary_child->context['trip-itinerary/dayIndex'] = $trip_itinerary_day++;
	}
	if ( 'trip-itinerary/overview' === $trip_itinerary_child->name ) {
		$trip_itinerary_child->context['trip-itinerary/locations']   = $trip_itinerary_locations;
		$trip_itinerary_child->context['trip-itinerary/dayIds']      = $trip_itinerary_summary ? array() : array_map( $trip_itinerary_day_id, array_keys( $trip_itinerary_locations ) );
		$trip_itinerary_child->context['trip-itinerary/coordinates'] = $trip_itinerary_coordinates;
	}
	if ( 'trip-itinerary/header' === $trip_itinerary_child->name ) {
		$trip_itinerary_child->context['trip-itinerary/stops']     = $trip_itinerary_stops;
		$trip_itinerary_child->context['trip-itinerary/priceHref'] = $trip_itinerary_price_href;
	}
	if ( 'trip-itinerary/price' === $trip_itinerary_child->name ) {
		$trip_itinerary_child->context['trip-itinerary/priceId'] = $trip_itinerary_price_id;
	}
	if ( $trip_itinerary_summary && 'trip-itinerary/day' === $trip_itinerary_child->name ) {
		continue;
	}
	$trip_itinerary_children .= $trip_itinerary_child->render();
}
?>
<section <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h2 class="wp-block-trip-itinerary-itinerary__title"><?php echo wp_kses_post( $attributes['title'] ); ?></h2>
	<?php endif; ?>
	<div class="wp-block-trip-itinerary-itinerary__days">
		<?php echo $trip_itinerary_children; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks. ?>
	</div>
</section>
