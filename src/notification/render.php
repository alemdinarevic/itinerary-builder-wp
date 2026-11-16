<?php
/**
 * Front-end output of one Notification, inside the Notifications panel.
 * Whether it's been read is known only in the browser, so the server marks
 * every notification unread and src/notifications/view.js corrects it.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_title = $attributes['title'] ?? '';
$trip_itinerary_date  = ! empty( $attributes['date'] ) ? strtotime( $attributes['date'] . ' 12:00:00 UTC' ) : false;
?>
<li
	<?php echo get_block_wrapper_attributes( array( 'class' => 'is-unread' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php echo wp_interactivity_data_wp_context( array( 'id' => trip_itinerary_notification_id( $attributes ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress. ?>
	data-wp-class--is-unread="state.isUnread"
>
	<span class="wp-block-trip-itinerary-notification__dot" aria-hidden="true"></span>
	<div class="wp-block-trip-itinerary-notification__body">
		<?php if ( $trip_itinerary_date ) : ?>
			<p class="wp-block-trip-itinerary-notification__date">
				<svg viewBox="0 0 24 24" width="13" height="13" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				<time datetime="<?php echo esc_attr( gmdate( 'Y-m-d', $trip_itinerary_date ) ); ?>"><?php echo esc_html( wp_date( _x( 'j M Y', 'notification date format', 'trip-itinerary' ), $trip_itinerary_date, new DateTimeZone( 'UTC' ) ) ); ?></time>
			</p>
		<?php endif; ?>
		<p class="wp-block-trip-itinerary-notification__title">
			<?php if ( ! empty( $attributes['url'] ) ) : ?>
				<a href="<?php echo esc_url( $attributes['url'] ); ?>" data-wp-on--click="actions.markRead"><?php echo wp_kses_post( $trip_itinerary_title ); ?></a>
			<?php else : ?>
				<?php echo wp_kses_post( $trip_itinerary_title ); ?>
			<?php endif; ?>
		</p>
		<?php if ( ! empty( $attributes['description'] ) ) : ?>
			<p class="wp-block-trip-itinerary-notification__description"><?php echo wp_kses_post( $attributes['description'] ); ?></p>
		<?php endif; ?>
		<button type="button" class="wp-block-trip-itinerary-notification__mark" data-wp-on--click="actions.markRead" data-wp-bind--hidden="!state.isUnread"><?php esc_html_e( 'Mark as read', 'trip-itinerary' ); ?></button>
	</div>
</li>
