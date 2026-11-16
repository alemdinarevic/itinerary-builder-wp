<?php
/**
 * Front-end output of the Page header block, after the Offtryp app header:
 * a logo linking home, links and a button on a translucent bar.
 *
 * On narrow screens the links fold into a menu opened by a button;
 * src/page-header/view.js makes it work with the Interactivity API. Without
 * JavaScript the button stays hidden and the links stay visible.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_links  = $attributes['links'] ?? '';
$trip_itinerary_button = ! empty( $attributes['buttonText'] ) && ! empty( $attributes['buttonUrl'] );
$trip_itinerary_menu   = wp_unique_id( 'trip-menu-' );

$trip_itinerary_classes = array();
if ( ! empty( $attributes['sticky'] ) ) {
	$trip_itinerary_classes[] = 'is-sticky';
}
if ( $trip_itinerary_links ) {
	$trip_itinerary_classes[] = 'has-links';
}
if ( '' !== trim( $content ) ) {
	$trip_itinerary_classes[] = 'has-actions';
}
?>
<header
	<?php
	echo get_block_wrapper_attributes( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'class'               => implode( ' ', $trip_itinerary_classes ),
			'data-wp-interactive' => 'trip-itinerary/page-header',
			'data-wp-class--is-open' => 'context.open',
			// Added only once the script runs, so the links stay visible without it.
			'data-wp-class--is-enhanced' => 'state.isEnhanced',
			'data-wp-on--keydown' => 'actions.onKeydown',
		)
	);
	echo wp_interactivity_data_wp_context( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress.
		array(
			'open'       => false,
			'openLabel'  => __( 'Open menu', 'trip-itinerary' ),
			'closeLabel' => __( 'Close menu', 'trip-itinerary' ),
		)
	);
	?>
>
	<a class="wp-block-trip-itinerary-page-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php echo trip_itinerary_logo_html( $attributes['logo'] ?? null, 'wp-block-trip-itinerary-page-header' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>
	</a>

	<?php if ( $trip_itinerary_links ) : ?>
		<button
			type="button"
			class="wp-block-trip-itinerary-page-header__menu-button"
			aria-controls="<?php echo esc_attr( $trip_itinerary_menu ); ?>"
			aria-expanded="false"
			aria-label="<?php esc_attr_e( 'Open menu', 'trip-itinerary' ); ?>"
			data-wp-on--click="actions.toggle"
			data-wp-bind--aria-expanded="context.open"
			data-wp-bind--aria-label="state.toggleLabel"
		>
			<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
		</button>
		<nav class="wp-block-trip-itinerary-page-header__nav" id="<?php echo esc_attr( $trip_itinerary_menu ); ?>" aria-label="<?php esc_attr_e( 'Main', 'trip-itinerary' ); ?>">
			<p class="wp-block-trip-itinerary-page-header__links"><?php echo wp_kses_post( $trip_itinerary_links ); ?></p>
		</nav>
	<?php endif; ?>

	<?php if ( '' !== trim( $content ) ) : ?>
		<div class="wp-block-trip-itinerary-page-header__actions">
			<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks: notifications, user menu. ?>
		</div>
	<?php endif; ?>

	<?php if ( $trip_itinerary_button ) : ?>
		<div class="wp-block-button is-style-trip-secondary-dark wp-block-trip-itinerary-page-header__button">
			<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $attributes['buttonUrl'] ); ?>"><?php echo esc_html( trip_itinerary_plain_text( $attributes['buttonText'] ) ); ?></a>
		</div>
	<?php endif; ?>
</header>
