<?php
/**
 * Front-end output of the Notifications block, after the Offtryp
 * notifications panel: a bell with an unread count that opens a panel.
 *
 * Read notifications are remembered in the visitor's browser (localStorage),
 * so the server renders everything as unread and src/notifications/view.js
 * catches up as the page loads. Without JavaScript the bell does nothing.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered notifications.
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_ids = array();
foreach ( $block->inner_blocks as $trip_itinerary_child ) {
	if ( 'trip-itinerary/notification' === $trip_itinerary_child->name ) {
		$trip_itinerary_ids[] = trip_itinerary_notification_id( $trip_itinerary_child->attributes );
	}
}
$trip_itinerary_count = count( $trip_itinerary_ids );
$trip_itinerary_panel = wp_unique_id( 'trip-notifications-' );
?>
<div
	<?php
	echo get_block_wrapper_attributes( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		array(
			'data-wp-interactive'       => 'trip-itinerary/notifications',
			'data-wp-init'              => 'callbacks.loadReadState',
			'data-wp-class--is-open'    => 'context.open',
			'data-wp-on-document--click' => 'actions.closeOnOutsideClick',
			'data-wp-on--keydown'       => 'actions.onKeydown',
		)
	);
	echo wp_interactivity_data_wp_context( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress.
		array(
			'open'        => false,
			'ids'         => $trip_itinerary_ids,
			'read'        => array(),
			'label'       => __( 'Notifications', 'trip-itinerary' ),
			/* translators: %d: number of unread notifications. */
			'labelUnread' => __( 'Notifications, %d unread', 'trip-itinerary' ),
		)
	);
	?>
>
	<button
		type="button"
		class="wp-block-trip-itinerary-notifications__bell"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $trip_itinerary_panel ); ?>"
		<?php /* translators: %d: number of unread notifications. */ ?>
		aria-label="<?php echo esc_attr( $trip_itinerary_count ? sprintf( __( 'Notifications, %d unread', 'trip-itinerary' ), $trip_itinerary_count ) : __( 'Notifications', 'trip-itinerary' ) ); ?>"
		data-wp-on--click="actions.toggle"
		data-wp-bind--aria-expanded="context.open"
		data-wp-bind--aria-label="state.bellLabel"
	>
		<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0" fill="currentColor"/></svg>
		<span class="wp-block-trip-itinerary-notifications__badge" aria-hidden="true" data-wp-text="state.unreadCount" data-wp-bind--hidden="!state.hasUnread"<?php echo $trip_itinerary_count ? '' : ' hidden'; ?>><?php echo esc_html( $trip_itinerary_count ); ?></span>
	</button>

	<div class="wp-block-trip-itinerary-notifications__panel" id="<?php echo esc_attr( $trip_itinerary_panel ); ?>" role="region" aria-label="<?php esc_attr_e( 'Notifications', 'trip-itinerary' ); ?>" hidden data-wp-bind--hidden="!context.open">
		<div class="wp-block-trip-itinerary-notifications__header">
			<h2 class="wp-block-trip-itinerary-notifications__heading"><?php esc_html_e( 'Notifications', 'trip-itinerary' ); ?></h2>
			<span class="wp-block-trip-itinerary-notifications__count" data-wp-text="state.unreadCount" data-wp-bind--hidden="!state.hasUnread"<?php echo $trip_itinerary_count ? '' : ' hidden'; ?>><?php echo esc_html( $trip_itinerary_count ); ?></span>
		</div>
		<?php if ( $trip_itinerary_count ) : ?>
			<ul class="wp-block-trip-itinerary-notifications__list">
				<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered notifications. ?>
			</ul>
			<div class="wp-block-trip-itinerary-notifications__footer" data-wp-bind--hidden="!state.hasUnread">
				<button type="button" class="wp-block-trip-itinerary-notifications__mark-all" data-wp-on--click="actions.markAllRead">
					<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" focusable="false"><path d="M2 13l4 4 8-9M10 15l2 2 9-10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<?php esc_html_e( 'Mark all as read', 'trip-itinerary' ); ?>
				</button>
			</div>
		<?php else : ?>
			<p class="wp-block-trip-itinerary-notifications__empty"><?php esc_html_e( 'No new notifications', 'trip-itinerary' ); ?></p>
		<?php endif; ?>
	</div>
</div>
