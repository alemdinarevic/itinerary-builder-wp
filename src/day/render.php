<?php
/**
 * Front-end output of the Day block.
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

// Set by the parent Itinerary while it renders its days.
$trip_itinerary_index = $block->context['trip-itinerary/dayIndex'] ?? null;
?>
<?php
// The anchor the Trip header's stops link to.
$trip_itinerary_wrapper = isset( $block->context['trip-itinerary/dayId'] ) ? array( 'id' => $block->context['trip-itinerary/dayId'] ) : array();
?>
<article <?php echo get_block_wrapper_attributes( $trip_itinerary_wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	if ( is_int( $trip_itinerary_index ) ) {
		echo trip_itinerary_day_marker_html( $trip_itinerary_index ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper.
		echo trip_itinerary_day_label_html( $trip_itinerary_index, $block->context['trip-itinerary/startDate'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper.
	}
	?>
	<?php if ( ! empty( $attributes['title'] ) ) : ?>
		<h3 class="wp-block-trip-itinerary-day__title"><?php echo wp_kses_post( $attributes['title'] ); ?></h3>
	<?php endif; ?>
	<?php if ( ! empty( $attributes['location'] ) ) : ?>
		<p class="wp-block-trip-itinerary-day__location"><?php echo wp_kses_post( $attributes['location'] ); ?></p>
	<?php endif; ?>
	<div class="wp-block-trip-itinerary-day__content">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered inner blocks. ?>
	</div>
	<?php
	echo trip_itinerary_carousel_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper.
		(array) ( $attributes['photos'] ?? array() ),
		is_int( $trip_itinerary_index )
			/* translators: %d: day number, starting at 1. */
			? sprintf( __( 'Photos from day %d', 'trip-itinerary' ), $trip_itinerary_index + 1 )
			: __( 'Photos', 'trip-itinerary' )
	);
	?>
</article>
