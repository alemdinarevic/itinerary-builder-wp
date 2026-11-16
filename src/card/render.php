<?php
/**
 * Front-end output of the Itinerary card block, after the Offtryp
 * itineraries list: the trip's photo darkened, with its name, route and
 * dates on it, then its length and travellers, then buttons.
 *
 * Everything comes from the chosen page's itinerary; see
 * trip_itinerary_card_data().
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_card = trip_itinerary_card_data( (int) ( $attributes['pageId'] ?? 0 ) );

if ( ! $trip_itinerary_card ) {
	// Visible to editors only, to explain the empty space.
	if ( current_user_can( 'edit_posts' ) && wp_is_serving_rest_request() ) {
		printf( '<p %s>%s</p>', get_block_wrapper_attributes(), esc_html__( 'Choose a published page with an itinerary.', 'trip-itinerary' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return;
}
?>
<article <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a class="wp-block-trip-itinerary-card__cover" href="<?php echo esc_url( $trip_itinerary_card['url'] ); ?>">
		<?php
		if ( $trip_itinerary_card['photoId'] ) {
			echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- From WordPress.
				$trip_itinerary_card['photoId'],
				'medium_large',
				false,
				array(
					'class' => 'wp-block-trip-itinerary-card__photo',
					'alt'   => '',
					'sizes' => '320px',
				)
			);
		}
		?>
		<span class="wp-block-trip-itinerary-card__title"><?php echo esc_html( $trip_itinerary_card['title'] ); ?></span>
		<?php if ( $trip_itinerary_card['route'] ) : ?>
			<span class="wp-block-trip-itinerary-card__route"><?php echo esc_html( $trip_itinerary_card['route'] ); ?></span>
		<?php endif; ?>
		<?php if ( $trip_itinerary_card['dates'] ) : ?>
			<span class="wp-block-trip-itinerary-card__dates"><?php echo esc_html( $trip_itinerary_card['dates'] ); ?></span>
		<?php endif; ?>
	</a>
	<?php if ( $trip_itinerary_card['facts'] ) : ?>
		<p class="wp-block-trip-itinerary-card__facts"><?php echo esc_html( implode( ' · ', $trip_itinerary_card['facts'] ) ); ?></p>
	<?php endif; ?>
	<div class="wp-block-trip-itinerary-card__actions">
		<div class="wp-block-button is-style-trip-secondary">
			<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $trip_itinerary_card['url'] ); ?>">
				<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></svg>
				<?php esc_html_e( 'View online', 'trip-itinerary' ); ?>
			</a>
		</div>
		<?php if ( $trip_itinerary_card['priceUrl'] ) : ?>
			<div class="wp-block-button is-style-trip-primary">
				<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $trip_itinerary_card['priceUrl'] ); ?>"><?php esc_html_e( 'Request quotation', 'trip-itinerary' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</article>
