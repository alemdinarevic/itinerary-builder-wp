<?php
/**
 * Front-end output of the Trip header block.
 *
 * Each stop is a plain link to the first day at that place, so it works
 * without JavaScript; style.scss adds smooth scrolling.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

// Set by the parent Itinerary: [ [ 'name' => ..., 'href' => '#...' ], ... ].
$trip_itinerary_stops = $block->context['trip-itinerary/stops'] ?? array();

// The button goes to its own link, or else scrolls to the Trip price.
$trip_itinerary_href   = ! empty( $attributes['buttonUrl'] ) ? $attributes['buttonUrl'] : ( $block->context['trip-itinerary/priceHref'] ?? '' );
$trip_itinerary_button = ! empty( $attributes['buttonText'] ) && '' !== $trip_itinerary_href;

if ( ! $trip_itinerary_stops && ! $trip_itinerary_button ) {
	return;
}
?>
<nav <?php echo get_block_wrapper_attributes( array( 'aria-label' => __( 'Trip stops', 'trip-itinerary' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wp-block-trip-itinerary-header__scroll">
		<svg class="wp-block-trip-itinerary-header__pin" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.4" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
		<?php if ( $trip_itinerary_stops ) : ?>
			<ul class="wp-block-trip-itinerary-header__stops">
				<?php foreach ( $trip_itinerary_stops as $trip_itinerary_stop ) : ?>
					<li><a class="wp-block-trip-itinerary-header__stop" href="<?php echo esc_attr( $trip_itinerary_stop['href'] ); ?>"><?php echo esc_html( $trip_itinerary_stop['name'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php if ( $trip_itinerary_button ) : ?>
		<a class="wp-block-trip-itinerary-header__button" href="<?php echo esc_url( $trip_itinerary_href ); ?>"><?php echo esc_html( trip_itinerary_plain_text( $attributes['buttonText'] ) ); ?> <span aria-hidden="true">→</span></a>
	<?php endif; ?>
</nav>
