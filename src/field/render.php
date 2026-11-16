<?php
/**
 * Front-end output of the Field block, after the Offtryp form inputs.
 *
 * A password field gets a button that shows and hides it; src/field/view.js
 * makes it work with the Interactivity API. Without JavaScript the field is
 * an ordinary password input.
 *
 * @package TripItinerary
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Rendered inner blocks (none).
 * @var WP_Block $block      Block instance.
 */

$trip_itinerary_type     = in_array( $attributes['type'] ?? 'text', array( 'text', 'email', 'tel', 'password' ), true ) ? $attributes['type'] : 'text';
$trip_itinerary_password = 'password' === $trip_itinerary_type;
$trip_itinerary_id       = wp_unique_id( 'trip-field-' );
$trip_itinerary_label    = trip_itinerary_plain_text( $attributes['label'] ?? '' );
$trip_itinerary_help     = $attributes['help'] ?? '';

// What browsers and password managers should fill in.
$trip_itinerary_autocomplete = array(
	'email'    => 'email',
	'tel'      => 'tel',
	'password' => 'current-password',
)[ $trip_itinerary_type ] ?? 'on';

$trip_itinerary_wrapper = array( 'class' => $trip_itinerary_password ? 'is-password' : '' );
if ( $trip_itinerary_password ) {
	$trip_itinerary_wrapper['data-wp-interactive'] = 'trip-itinerary/field';
}
?>
<div
	<?php echo get_block_wrapper_attributes( $trip_itinerary_wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php
	if ( $trip_itinerary_password ) {
		echo wp_interactivity_data_wp_context( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress.
			array(
				'visible'   => false,
				'showLabel' => __( 'Show password', 'trip-itinerary' ),
				'hideLabel' => __( 'Hide password', 'trip-itinerary' ),
			)
		);
	}
	?>
>
	<label class="wp-block-trip-itinerary-field__label" for="<?php echo esc_attr( $trip_itinerary_id ); ?>">
		<?php echo esc_html( $trip_itinerary_label ); ?>
		<?php if ( ! empty( $attributes['required'] ) ) : ?>
			<span class="wp-block-trip-itinerary-field__required" aria-hidden="true">*</span>
		<?php endif; ?>
	</label>
	<div class="wp-block-trip-itinerary-field__control">
		<input
			class="wp-block-trip-itinerary-field__input"
			id="<?php echo esc_attr( $trip_itinerary_id ); ?>"
			type="<?php echo esc_attr( $trip_itinerary_type ); ?>"
			<?php if ( ! empty( $attributes['name'] ) ) : ?>
				name="<?php echo esc_attr( $attributes['name'] ); ?>"
			<?php endif; ?>
			<?php if ( ! empty( $attributes['placeholder'] ) ) : ?>
				placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
			<?php endif; ?>
			autocomplete="<?php echo esc_attr( $trip_itinerary_autocomplete ); ?>"
			<?php if ( $trip_itinerary_help ) : ?>
				aria-describedby="<?php echo esc_attr( $trip_itinerary_id ); ?>-help"
			<?php endif; ?>
			<?php echo ! empty( $attributes['required'] ) ? 'required' : ''; ?>
			<?php echo $trip_itinerary_password ? 'data-wp-bind--type="state.type"' : ''; ?>
		/>
		<?php if ( $trip_itinerary_password ) : ?>
			<button
				type="button"
				class="wp-block-trip-itinerary-field__toggle"
				aria-controls="<?php echo esc_attr( $trip_itinerary_id ); ?>"
				aria-pressed="false"
				aria-label="<?php esc_attr_e( 'Show password', 'trip-itinerary' ); ?>"
				data-wp-on--click="actions.toggle"
				data-wp-bind--aria-pressed="context.visible"
				data-wp-bind--aria-label="state.toggleLabel"
			>
				<?php // An eye: open while the password is hidden, crossed out while it shows. ?>
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" data-wp-bind--hidden="context.visible"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" hidden data-wp-bind--hidden="state.isHidden"><path d="M2 12s3.6-7 10-7c2 0 3.7.7 5.1 1.6M22 12s-3.6 7-10 7c-2 0-3.7-.7-5.1-1.6M9.9 9.9a3 3 0 0 0 4.2 4.2M3 3l18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			</button>
		<?php endif; ?>
	</div>
	<?php if ( $trip_itinerary_help ) : ?>
		<p class="wp-block-trip-itinerary-field__help" id="<?php echo esc_attr( $trip_itinerary_id ); ?>-help"><?php echo wp_kses_post( $trip_itinerary_help ); ?></p>
	<?php endif; ?>
</div>
