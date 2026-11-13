<?php
/**
 * Front-end output of the Trip overview block: one line of facts (dates,
 * length, travellers), then the trip as a route line, with a numbered stop
 * per day linking to that day.
 *
 * src/overview/edit.js renders the same markup in the editor.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_start     = $block->context['trip-itinerary/startDate'] ?? '';
$trip_itinerary_locations = $block->context['trip-itinerary/locations'] ?? array(); // Set by the parent Itinerary.
$trip_itinerary_day_ids   = $block->context['trip-itinerary/dayIds'] ?? array(); // Set by the parent Itinerary.

$trip_itinerary_items = trip_itinerary_summary_items(
	$trip_itinerary_start,
	(int) ( $block->context['trip-itinerary/travellers'] ?? 0 ),
	$trip_itinerary_locations
);

// "4 – 6 Dec 2026", "3 days", "2 people": the dates stand out, the rest is quieter.
$trip_itinerary_facts = array();
foreach ( $trip_itinerary_items as $trip_itinerary_item ) {
	$trip_itinerary_facts[] = array( $trip_itinerary_item['value'], 'dates' === $trip_itinerary_item['key'] );
	if ( ! empty( $trip_itinerary_item['detail'] ) ) {
		$trip_itinerary_facts[] = array( $trip_itinerary_item['detail'], false );
	}
}

// The route line only adds something once there are places or dates to
// show; without either it would just repeat the days' numbers.
$trip_itinerary_route = $trip_itinerary_locations && ( array_filter( $trip_itinerary_locations, 'strlen' ) || trip_itinerary_date( $trip_itinerary_start, 0 ) );

// Nothing to show yet: render nothing, rather than an empty frame.
if ( ! $trip_itinerary_facts && ! $trip_itinerary_route ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $trip_itinerary_facts ) : ?>
		<p class="wp-block-trip-itinerary-overview__facts">
			<?php foreach ( $trip_itinerary_facts as $trip_itinerary_index => $trip_itinerary_fact ) : ?>
				<?php if ( $trip_itinerary_index > 0 ) : ?>
					<span class="wp-block-trip-itinerary-overview__separator" aria-hidden="true">·</span>
				<?php endif; ?>
				<span class="wp-block-trip-itinerary-overview__fact<?php echo $trip_itinerary_fact[1] ? ' is-strong' : ''; ?>"><?php echo esc_html( $trip_itinerary_fact[0] ); ?></span>
			<?php endforeach; ?>
		</p>
	<?php endif; ?>

	<?php if ( $trip_itinerary_route ) : ?>
		<ol class="wp-block-trip-itinerary-overview__route" aria-label="<?php esc_attr_e( 'Days', 'trip-itinerary' ); ?>">
			<?php
			foreach ( array_values( $trip_itinerary_locations ) as $trip_itinerary_index => $trip_itinerary_place ) :
				$trip_itinerary_date = trip_itinerary_date( $trip_itinerary_start, $trip_itinerary_index );
				/* translators: %d: day number, starting at 1. */
				$trip_itinerary_day_name = sprintf( __( 'Day %d', 'trip-itinerary' ), $trip_itinerary_index + 1 );
				$trip_itinerary_href     = isset( $trip_itinerary_day_ids[ $trip_itinerary_index ] ) ? '#' . $trip_itinerary_day_ids[ $trip_itinerary_index ] : '';
				?>
				<li class="wp-block-trip-itinerary-overview__stop">
					<?php if ( $trip_itinerary_href ) : ?>
						<a class="wp-block-trip-itinerary-overview__link" href="<?php echo esc_attr( $trip_itinerary_href ); ?>">
					<?php else : ?>
						<span class="wp-block-trip-itinerary-overview__link">
					<?php endif; ?>
						<span class="wp-block-trip-itinerary-overview__node" aria-hidden="true"><?php echo esc_html( number_format_i18n( $trip_itinerary_index + 1 ) ); ?></span>
						<?php if ( '' !== $trip_itinerary_place ) : ?>
							<span class="wp-block-trip-itinerary-overview__day"><?php echo esc_html( $trip_itinerary_day_name ); ?>: </span>
							<span class="wp-block-trip-itinerary-overview__place"><?php echo esc_html( $trip_itinerary_place ); ?></span>
						<?php else : ?>
							<span class="wp-block-trip-itinerary-overview__place"><?php echo esc_html( $trip_itinerary_day_name ); ?></span>
						<?php endif; ?>
						<?php if ( $trip_itinerary_date ) : ?>
							<time class="wp-block-trip-itinerary-overview__date" datetime="<?php echo esc_attr( $trip_itinerary_date->format( 'Y-m-d' ) ); ?>">
								<?php
								echo esc_html(
									trip_itinerary_format_date(
										$trip_itinerary_date,
										/* translators: Date format under each stop in the trip overview, see https://www.php.net/manual/datetime.format.php */
										_x( 'D j M', 'overview stop date format', 'trip-itinerary' )
									)
								);
								?>
							</time>
						<?php endif; ?>
					<?php echo $trip_itinerary_href ? '</a>' : '</span>'; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</div>
