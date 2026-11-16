<?php
/**
 * Front-end output of the User menu block, after the Offtryp user dropdown.
 *
 * Personal to whoever views the page: logged-in visitors get their initial,
 * name and email, and WordPress's own log-out link; others get a Log in
 * button. On sites with full-page caching, pages with this block must not
 * be cached for logged-in visitors (most caching plugins already skip them).
 *
 * The initial is shown instead of a Gravatar photo, so the page makes no
 * requests to an outside service.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_here = home_url( add_query_arg( array() ) );

if ( ! is_user_logged_in() ) : ?>
	<div <?php echo get_block_wrapper_attributes( array( 'class' => 'is-logged-out' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="wp-block-button is-style-trip-secondary-dark">
			<a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( wp_login_url( $trip_itinerary_here ) ); ?>"><?php esc_html_e( 'Log in', 'trip-itinerary' ); ?></a>
		</div>
	</div>
	<?php
	return;
endif;

$trip_itinerary_user    = wp_get_current_user();
$trip_itinerary_name    = trim( $trip_itinerary_user->first_name . ' ' . $trip_itinerary_user->last_name );
$trip_itinerary_name    = '' !== $trip_itinerary_name ? $trip_itinerary_name : $trip_itinerary_user->display_name;
$trip_itinerary_initial = mb_strtoupper( mb_substr( $trip_itinerary_name, 0, 1 ) );
$trip_itinerary_panel   = wp_unique_id( 'trip-user-menu-' );
?>
<div
	<?php
	echo get_block_wrapper_attributes( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'data-wp-interactive'        => 'trip-itinerary/user-menu',
			'data-wp-class--is-open'     => 'context.open',
			'data-wp-on-document--click' => 'actions.closeOnOutsideClick',
			'data-wp-on--keydown'        => 'actions.onKeydown',
		)
	);
	echo wp_interactivity_data_wp_context( array( 'open' => false ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress.
	?>
>
	<button
		type="button"
		class="wp-block-trip-itinerary-user-menu__button"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $trip_itinerary_panel ); ?>"
		<?php /* translators: %s: the user's name. */ ?>
		aria-label="<?php echo esc_attr( sprintf( __( 'Account menu for %s', 'trip-itinerary' ), $trip_itinerary_name ) ); ?>"
		data-wp-on--click="actions.toggle"
		data-wp-bind--aria-expanded="context.open"
	>
		<span class="wp-block-trip-itinerary-user-menu__initial" aria-hidden="true"><?php echo esc_html( $trip_itinerary_initial ); ?></span>
	</button>

	<div class="wp-block-trip-itinerary-user-menu__panel" id="<?php echo esc_attr( $trip_itinerary_panel ); ?>" hidden data-wp-bind--hidden="!context.open">
		<div class="wp-block-trip-itinerary-user-menu__user">
			<span class="wp-block-trip-itinerary-user-menu__initial" aria-hidden="true"><?php echo esc_html( $trip_itinerary_initial ); ?></span>
			<span>
				<span class="wp-block-trip-itinerary-user-menu__name"><?php echo esc_html( $trip_itinerary_name ); ?></span>
				<span class="wp-block-trip-itinerary-user-menu__email"><?php echo esc_html( $trip_itinerary_user->user_email ); ?></span>
			</span>
		</div>
		<?php if ( ! empty( $attributes['links'] ) ) : ?>
			<nav class="wp-block-trip-itinerary-user-menu__links" aria-label="<?php esc_attr_e( 'Account', 'trip-itinerary' ); ?>">
				<?php echo wp_kses_post( $attributes['links'] ); ?>
			</nav>
		<?php endif; ?>
		<a class="wp-block-trip-itinerary-user-menu__logout" href="<?php echo esc_url( wp_logout_url( $trip_itinerary_here ) ); ?>">
			<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l5-5-5-5M15 12H4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
			<?php esc_html_e( 'Log out', 'trip-itinerary' ); ?>
		</a>
	</div>
</div>
