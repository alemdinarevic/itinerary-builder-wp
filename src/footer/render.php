<?php
/**
 * Front-end output of the Trip footer block.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_logo = trip_itinerary_footer_logo_html( $attributes['logo'] ?? null );
?>
<footer <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="wp-block-trip-itinerary-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php echo $trip_itinerary_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>
	</a>
	<?php if ( ! empty( $attributes['links'] ) ) : ?>
		<p class="wp-block-trip-itinerary-footer__links"><?php echo wp_kses_post( $attributes['links'] ); ?></p>
	<?php endif; ?>
	<?php if ( ! empty( $attributes['credit'] ) ) : ?>
		<p class="wp-block-trip-itinerary-footer__credit"><?php echo wp_kses_post( $attributes['credit'] ); ?></p>
	<?php endif; ?>
</footer>
