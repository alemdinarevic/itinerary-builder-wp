<?php
/**
 * Front-end output of the Trip overview block. Two parts, after the
 * Offtryp itinerary page:
 *
 * - A hero: the trip's dates large, then "3 days · 2 people · from
 *   Sarajevo", over a photo of the country when one is picked.
 * - The stops: each place once, one below the other, with a map beside
 *   them that numbers the same stops. Each stop links to its first day.
 *
 * src/overview/edit.js renders the same markup in the editor.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_start       = $block->context['trip-itinerary/startDate'] ?? '';
$trip_itinerary_locations   = $block->context['trip-itinerary/locations'] ?? array(); // Set by the parent Itinerary.
$trip_itinerary_day_ids     = $block->context['trip-itinerary/dayIds'] ?? array(); // Set by the parent Itinerary.
$trip_itinerary_coordinates = $block->context['trip-itinerary/coordinates'] ?? array(); // Set by the parent Itinerary.

// The dates go on their own line; the rest share the line below.
$trip_itinerary_dates = '';
$trip_itinerary_facts = array();
foreach ( trip_itinerary_summary_items( $trip_itinerary_start, (int) ( $block->context['trip-itinerary/travellers'] ?? 0 ), $trip_itinerary_locations ) as $trip_itinerary_item ) {
	if ( 'dates' === $trip_itinerary_item['key'] ) {
		$trip_itinerary_dates   = $trip_itinerary_item['value'];
		$trip_itinerary_facts[] = $trip_itinerary_item['detail'];
	} else {
		$trip_itinerary_facts[] = $trip_itinerary_item['value'];
	}
}

$trip_itinerary_stops     = trip_itinerary_stops( $trip_itinerary_locations );
$trip_itinerary_map_stops = trip_itinerary_map_stops( $trip_itinerary_locations, $trip_itinerary_coordinates );
$trip_itinerary_photo     = '';
if ( ! empty( $attributes['background']['id'] ) ) {
	$trip_itinerary_photo = wp_get_attachment_image(
		(int) $attributes['background']['id'],
		'full',
		false,
		array(
			'class'         => 'wp-block-trip-itinerary-overview__photo',
			// Decorative: the facts on top say what matters.
			'alt'           => '',
			// Near the top of the page, so load it straight away.
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'sizes'         => '(max-width: 900px) 100vw, 900px',
		)
	);
}

$trip_itinerary_hero = $trip_itinerary_dates || $trip_itinerary_facts || $trip_itinerary_photo;

// Nothing to show yet: render nothing, rather than an empty frame.
if ( ! $trip_itinerary_hero && ! $trip_itinerary_stops ) {
	return;
}

$trip_itinerary_href = static function ( $day_index ) use ( $trip_itinerary_day_ids ) {
	return isset( $trip_itinerary_day_ids[ $day_index ] ) ? '#' . $trip_itinerary_day_ids[ $day_index ] : '';
};
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => $trip_itinerary_photo ? 'has-photo' : '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $trip_itinerary_hero ) : ?>
		<div class="wp-block-trip-itinerary-overview__hero">
			<?php echo $trip_itinerary_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- From wp_get_attachment_image(). ?>
			<div class="wp-block-trip-itinerary-overview__summary">
				<?php if ( $trip_itinerary_dates ) : ?>
					<p class="wp-block-trip-itinerary-overview__dates"><?php echo esc_html( $trip_itinerary_dates ); ?></p>
				<?php endif; ?>
				<?php if ( $trip_itinerary_facts ) : ?>
					<p class="wp-block-trip-itinerary-overview__facts">
						<?php foreach ( $trip_itinerary_facts as $trip_itinerary_index => $trip_itinerary_fact ) : ?>
							<?php if ( $trip_itinerary_index > 0 ) : ?>
								<span class="wp-block-trip-itinerary-overview__separator" aria-hidden="true">·</span>
							<?php endif; ?>
							<span><?php echo esc_html( $trip_itinerary_fact ); ?></span>
						<?php endforeach; ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $trip_itinerary_stops ) : ?>
		<div class="wp-block-trip-itinerary-overview__stops<?php echo $trip_itinerary_map_stops ? ' has-map' : ''; ?>">
			<ol class="wp-block-trip-itinerary-overview__list" aria-label="<?php esc_attr_e( 'Stops', 'trip-itinerary' ); ?>">
				<?php
				foreach ( $trip_itinerary_stops as $trip_itinerary_number => $trip_itinerary_stop ) :
					$trip_itinerary_date = trip_itinerary_date( $trip_itinerary_start, $trip_itinerary_stop['dayIndex'] );
					$trip_itinerary_link = $trip_itinerary_href( $trip_itinerary_stop['dayIndex'] );
					$trip_itinerary_tag  = $trip_itinerary_link ? 'a' : 'span';
					?>
					<li class="wp-block-trip-itinerary-overview__stop">
						<<?php echo $trip_itinerary_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- "a" or "span". ?> class="wp-block-trip-itinerary-overview__link"<?php echo $trip_itinerary_link ? ' href="' . esc_attr( $trip_itinerary_link ) . '"' : ''; ?>>
							<span class="wp-block-trip-itinerary-overview__node" aria-hidden="true"><?php echo esc_html( number_format_i18n( $trip_itinerary_number + 1 ) ); ?></span>
							<span class="wp-block-trip-itinerary-overview__place"><?php echo esc_html( $trip_itinerary_stop['name'] ); ?></span>
							<?php if ( $trip_itinerary_date ) : ?>
								<time class="wp-block-trip-itinerary-overview__date" datetime="<?php echo esc_attr( $trip_itinerary_date->format( 'Y-m-d' ) ); ?>">
									<?php
									echo esc_html(
										trip_itinerary_format_date(
											$trip_itinerary_date,
											/* translators: Date format beside each stop in the trip overview, see https://www.php.net/manual/datetime.format.php */
											_x( 'D j M', 'overview stop date format', 'trip-itinerary' )
										)
									);
									?>
								</time>
							<?php endif; ?>
						</<?php echo $trip_itinerary_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					</li>
				<?php endforeach; ?>
			</ol>

			<?php
			if ( $trip_itinerary_map_stops ) :
				$trip_itinerary_map_data = array_map(
					static function ( $stop ) use ( $trip_itinerary_href ) {
						return array(
							'name'   => $stop['name'],
							'number' => $stop['number'],
							'lat'    => $stop['lat'],
							'lng'    => $stop['lng'],
							'href'   => $trip_itinerary_href( $stop['dayIndex'] ),
						);
					},
					$trip_itinerary_map_stops
				);
				?>
				<div
					class="wp-block-trip-itinerary-overview__map"
					role="img"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: list of places. */ __( 'Map of the trip: %s', 'trip-itinerary' ), implode( ', ', wp_list_pluck( $trip_itinerary_map_stops, 'name' ) ) ) ); ?>"
					data-stops="<?php echo esc_attr( wp_json_encode( $trip_itinerary_map_data ) ); ?>"
					data-tiles="<?php echo esc_attr( wp_json_encode( trip_itinerary_map_tiles() ) ); ?>"
				></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
