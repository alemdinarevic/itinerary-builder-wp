<?php
/**
 * Server-side markup for the Day block's photo carousel. The Interactivity
 * API store in src/day/view.js makes it interactive on the page.
 *
 * @package TripItinerary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Where a photo sits in the stacked carousel. Mirrors slidePosition() in
 * src/shared/carousel.js, which takes over once the page is interactive.
 *
 * @param int $index   The photo's position in the list.
 * @param int $current The photo shown in the centre.
 * @param int $count   How many photos there are.
 * @return string center, right, left, right-far, left-far or hidden.
 */
function trip_itinerary_slide_position( $index, $current, $count ) {
	$offset = ( $index - $current + $count ) % $count;

	if ( 0 === $offset ) {
		return 'center';
	}
	if ( 1 === $offset ) {
		return 'right';
	}
	if ( $count - 1 === $offset ) {
		return 'left';
	}
	if ( 2 === $offset ) {
		return 'right-far';
	}
	if ( $count - 2 === $offset ) {
		return 'left-far';
	}
	return 'hidden';
}

/**
 * The image tag for one photo, preferring the media library's responsive
 * image (with srcset) and falling back to the stored URL if the attachment
 * has since been deleted.
 *
 * @param array $photo Stored photo: id, url, alt.
 * @return string HTML, or '' when there's nothing to show.
 */
function trip_itinerary_photo_image( array $photo ) {
	$attributes = array(
		'class'    => 'wp-block-trip-itinerary-carousel__image',
		'loading'  => 'lazy',
		'decoding' => 'async',
		// Every photo can move to the centre, so pick files for the centre's
		// size (see _carousel.scss), not the smaller size it starts at.
		// Otherwise a side photo brought forward would be blurry.
		'sizes'    => '(max-width: 600px) 240px, 360px',
	);

	$id = isset( $photo['id'] ) ? (int) $photo['id'] : 0;
	if ( $id ) {
		$image = wp_get_attachment_image( $id, 'large', false, $attributes );
		if ( $image ) {
			return str_replace( 'sizes="auto, ', 'sizes="', $image );
		}
	}

	if ( empty( $photo['url'] ) ) {
		return '';
	}

	return sprintf(
		'<img class="%s" src="%s" alt="%s" loading="lazy" decoding="async" />',
		esc_attr( $attributes['class'] ),
		esc_url( $photo['url'] ),
		esc_attr( $photo['alt'] ?? '' )
	);
}

/**
 * The photo carousel shown below a day.
 *
 * @param array  $photos Stored photos: id, url, alt, caption.
 * @param string $label  Accessible name, for example "Photos from day 1".
 * @return string HTML, or '' without photos.
 */
function trip_itinerary_carousel_html( array $photos, $label ) {
	$slides = array();
	foreach ( $photos as $photo ) {
		if ( ! is_array( $photo ) ) {
			continue;
		}
		$image = trip_itinerary_photo_image( $photo );
		if ( '' === $image ) {
			continue;
		}
		$id       = isset( $photo['id'] ) ? (int) $photo['id'] : 0;
		$caption  = $id ? wp_get_attachment_caption( $id ) : '';
		$slides[] = array(
			'image'   => $image,
			'caption' => $caption ? $caption : ( $photo['caption'] ?? '' ),
		);
	}

	$count = count( $slides );
	if ( 0 === $count ) {
		return '';
	}

	ob_start();
	?>
	<div
		class="wp-block-trip-itinerary-carousel"
		role="region"
		aria-roledescription="<?php esc_attr_e( 'carousel', 'trip-itinerary' ); ?>"
		aria-label="<?php echo esc_attr( $label ); ?>"
		data-count="<?php echo esc_attr( $count ); ?>"
		data-wp-interactive="trip-itinerary/carousel"
		<?php echo wp_interactivity_data_wp_context( array( 'current' => 0, 'count' => $count ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress. ?>
		data-wp-on--keydown="actions.onKeydown"
	>
		<div class="wp-block-trip-itinerary-carousel__track">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<figure
					class="wp-block-trip-itinerary-carousel__slide"
					role="group"
					aria-roledescription="<?php esc_attr_e( 'slide', 'trip-itinerary' ); ?>"
					<?php /* translators: 1: photo number, 2: number of photos. */ ?>
					aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'trip-itinerary' ), $index + 1, $count ) ); ?>"
					data-position="<?php echo esc_attr( trip_itinerary_slide_position( $index, 0, $count ) ); ?>"
					<?php echo wp_interactivity_data_wp_context( array( 'index' => $index ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress. ?>
					data-wp-bind--data-position="state.position"
					data-wp-bind--aria-hidden="state.isNotCurrent"
					data-wp-on--click="actions.show"
					<?php echo 0 === $index ? '' : 'aria-hidden="true"'; ?>
				>
					<?php echo $slide['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built by trip_itinerary_photo_image(). ?>
					<?php if ( $slide['caption'] ) : ?>
						<figcaption class="wp-block-trip-itinerary-carousel__credit">📸 <?php echo esc_html( $slide['caption'] ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endforeach; ?>
		</div>

		<?php if ( $count > 1 ) : ?>
			<div class="wp-block-trip-itinerary-carousel__controls">
				<button type="button" class="wp-block-trip-itinerary-carousel__arrow" data-wp-on--click="actions.previous" aria-label="<?php esc_attr_e( 'Previous photo', 'trip-itinerary' ); ?>">
					<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<div class="wp-block-trip-itinerary-carousel__dots">
					<?php for ( $index = 0; $index < $count; $index++ ) : ?>
						<button
							type="button"
							class="wp-block-trip-itinerary-carousel__dot"
							<?php /* translators: %d: photo number. */ ?>
							aria-label="<?php echo esc_attr( sprintf( __( 'Show photo %d', 'trip-itinerary' ), $index + 1 ) ); ?>"
							<?php echo 0 === $index ? 'aria-current="true"' : ''; ?>
							<?php echo wp_interactivity_data_wp_context( array( 'index' => $index ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress. ?>
							data-wp-bind--aria-current="state.isCurrent"
							data-wp-on--click="actions.show"
						></button>
					<?php endfor; ?>
				</div>
				<button type="button" class="wp-block-trip-itinerary-carousel__arrow" data-wp-on--click="actions.next" aria-label="<?php esc_attr_e( 'Next photo', 'trip-itinerary' ); ?>">
					<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Removes "auto" from the sizes of carousel photos.
 *
 * WordPress adds "auto" to lazy-loaded images when it filters post content,
 * which makes browsers pick a file for the photo's current size. Side photos
 * start small, so one brought to the centre would be blurry; the carousel's
 * own sizes (see trip_itinerary_photo_image()) ask for the centre's size.
 *
 * @param string $image An img tag in the content.
 * @return string
 */
function trip_itinerary_carousel_image_sizes( $image ) {
	if ( ! str_contains( $image, 'wp-block-trip-itinerary-carousel__image' ) ) {
		return $image;
	}
	return str_replace( 'sizes="auto, ', 'sizes="', $image );
}
add_filter( 'wp_content_img_tag', 'trip_itinerary_carousel_image_sizes' );
