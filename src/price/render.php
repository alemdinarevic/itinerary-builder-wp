<?php
/**
 * Front-end output of the Trip price block.
 *
 * The block's id is what the Trip header's button links to; style.scss
 * highlights the card when it's the link target.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks: what's included and excluded.
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_wrapper = isset( $block->context['trip-itinerary/priceId'] ) ? array( 'id' => $block->context['trip-itinerary/priceId'] ) : array();
$trip_itinerary_request = ! empty( $attributes['requestText'] ) && ! empty( $attributes['requestUrl'] );
?>
<section <?php echo get_block_wrapper_attributes( $trip_itinerary_wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="wp-block-trip-itinerary-price__card">
		<?php if ( ! empty( $attributes['amount'] ) ) : ?>
			<?php // On one line: whitespace between the spans would show as a gap. ?>
			<p class="wp-block-trip-itinerary-price__amount"><span class="wp-block-trip-itinerary-price__value"><?php echo esc_html( trip_itinerary_plain_text( $attributes['amount'] ) ); ?></span><?php if ( ! empty( $attributes['per'] ) ) : ?><span class="wp-block-trip-itinerary-price__per"><?php echo esc_html( trip_itinerary_plain_text( $attributes['per'] ) ); ?></span><?php endif; ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $attributes['note'] ) ) : ?>
			<p class="wp-block-trip-itinerary-price__note"><?php echo wp_kses_post( $attributes['note'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== trim( $content ) ) : ?>
			<div class="wp-block-trip-itinerary-price__details">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered inner blocks. ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $trip_itinerary_request || ! empty( $attributes['requestNote'] ) ) : ?>
		<div class="wp-block-trip-itinerary-price__request">
			<?php if ( ! empty( $attributes['requestNote'] ) ) : ?>
				<p class="wp-block-trip-itinerary-price__request-note"><?php echo wp_kses_post( $attributes['requestNote'] ); ?></p>
			<?php endif; ?>
			<?php if ( $trip_itinerary_request ) : ?>
				<a class="wp-block-trip-itinerary-price__button" href="<?php echo esc_url( $attributes['requestUrl'] ); ?>"><?php echo esc_html( trip_itinerary_plain_text( $attributes['requestText'] ) ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</section>
