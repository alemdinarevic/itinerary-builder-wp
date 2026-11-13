<?php
/**
 * Creates the demo itinerary page from demo/trip.json and makes it the
 * site's homepage. Run through bin/setup-demo.sh, which calls
 * `wp eval-file bin/setup-demo.php [reset]` inside wp-env.
 *
 * An existing demo page is left as it is, so edits made in the editor
 * survive restarts. Pass `reset` to rebuild it from trip.json.
 *
 * @package TripItinerary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

/**
 * Imports a demo photo into the media library once, and returns it in the
 * shape the Day block stores.
 *
 * @param string $file Name in demo/photos, without .jpg.
 * @param string $alt  Alternative text.
 * @return array Stored photo: id, url, alt, caption.
 */
function trip_itinerary_demo_photo( $file, $alt ) {
	return trip_itinerary_demo_image( "photos/{$file}.jpg", $alt, 'Placeholder image' );
}

/**
 * Imports an image from demo/ into the media library once.
 *
 * @param string $path    Path inside demo/, such as "photos/mostar-1.jpg".
 * @param string $alt     Alternative text.
 * @param string $caption Caption.
 * @return array id, url, alt, caption.
 */
function trip_itinerary_demo_image( $path, $alt, $caption = '' ) {
	$file = pathinfo( $path, PATHINFO_FILENAME );
	$ext  = pathinfo( $path, PATHINFO_EXTENSION );
	$slug = 'trip-demo-' . $file;

	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'name'        => $slug,
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);

	if ( $existing ) {
		$id = $existing[0];
	} else {
		// media_handle_sideload() moves the file, so give it a copy.
		$tmp = wp_tempnam( $slug );
		copy( dirname( __DIR__ ) . "/demo/{$path}", $tmp );

		$id = media_handle_sideload(
			array(
				'name'     => "{$slug}.{$ext}",
				'tmp_name' => $tmp,
			),
			0,
			null,
			array(
				'post_name'    => $slug,
				'post_title'   => $alt,
				'post_excerpt' => $caption,
			)
		);
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( "Couldn't import {$path}: " . $id->get_error_message() );
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}

	return array(
		'id'      => $id,
		'url'     => wp_get_attachment_image_url( $id, 'large' ) ? wp_get_attachment_image_url( $id, 'large' ) : wp_get_attachment_url( $id ),
		'alt'     => $alt,
		'caption' => $caption,
	);
}

/**
 * A parsed block, in the shape serialize_blocks() expects.
 *
 * @param string $name         Block name.
 * @param array  $attrs        Attributes stored in the block comment.
 * @param array  $inner_blocks Child blocks.
 * @param string $html         The block's own saved HTML.
 * @return array
 */
function trip_itinerary_demo_block( $name, array $attrs = array(), array $inner_blocks = array(), $html = '' ) {
	return array(
		'blockName'    => $name,
		'attrs'        => $attrs,
		'innerBlocks'  => $inner_blocks,
		'innerHTML'    => $html,
		// A null marks where each child block goes.
		'innerContent' => $inner_blocks ? array_fill( 0, count( $inner_blocks ), null ) : array( $html ),
	);
}

/**
 * A core List block with one item per entry, in a block style.
 *
 * @param string[] $items Item texts.
 * @param string   $style Block style name, such as "trip-included".
 * @return array
 */
function trip_itinerary_demo_list( array $items, $style ) {
	$class       = "is-style-{$style}";
	$list_items  = array_map(
		function ( $item ) {
			$html = '<li>' . esc_html( $item ) . '</li>';
			return trip_itinerary_demo_block( 'core/list-item', array(), array(), $html );
		},
		$items
	);
	$list        = trip_itinerary_demo_block( 'core/list', array( 'className' => $class ), $list_items );
	$list['innerContent'] = array_merge(
		array( '<ul class="wp-block-list ' . $class . '">' ),
		array_fill( 0, count( $list_items ), null ),
		array( '</ul>' )
	);
	$list['innerHTML']    = '<ul class="wp-block-list ' . $class . '"></ul>';
	return $list;
}

$trip_itinerary_reset = in_array( 'reset', $args, true );
$trip_itinerary_page  = get_page_by_path( 'itinerary-demo' );

if ( $trip_itinerary_page && ! $trip_itinerary_reset ) {
	WP_CLI::log( 'The demo page already exists; leaving it as it is.' );
} else {
	$trip = json_decode( file_get_contents( dirname( __DIR__ ) . '/demo/trip.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.

	$days = array();
	foreach ( $trip['days'] as $day ) {
		$paragraph = '<p>' . esc_html( $day['text'] ) . '</p>';
		$days[]    = trip_itinerary_demo_block(
			'trip-itinerary/day',
			array(
				'title'    => $day['title'],
				'location' => $day['location'],
				'photos'   => array_map(
					function ( $photo ) {
						return trip_itinerary_demo_photo( $photo['file'], $photo['alt'] );
					},
					$day['photos']
				),
			),
			array( trip_itinerary_demo_block( 'core/paragraph', array(), array(), $paragraph ) )
		);
	}

	$content = serialize_blocks(
		array(
			trip_itinerary_demo_block(
				'trip-itinerary/itinerary',
				array(
					'title'      => $trip['title'],
					'startDate'  => $trip['startDate'],
					'travellers' => $trip['travellers'],
				),
				array_merge(
					array(
						trip_itinerary_demo_block( 'trip-itinerary/header', $trip['header'] ),
						trip_itinerary_demo_block( 'trip-itinerary/overview' ),
					),
					$days,
					array(
						trip_itinerary_demo_block(
							'trip-itinerary/price',
							array_intersect_key( $trip['price'], array_flip( array( 'amount', 'per', 'note', 'requestNote', 'requestText', 'requestUrl' ) ) ),
							array(
								trip_itinerary_demo_block( 'core/heading', array( 'level' => 3 ), array(), '<h3 class="wp-block-heading">What is included in the price?</h3>' ),
								trip_itinerary_demo_list( $trip['price']['included'], 'trip-included' ),
								trip_itinerary_demo_list( $trip['price']['excluded'], 'trip-excluded' ),
							)
						),
					)
				)
			),
			trip_itinerary_demo_block(
				'trip-itinerary/footer',
				array(
					'logo'   => trip_itinerary_demo_image( $trip['footer']['logo'] . '.png', 'Trip Itinerary' ),
					'links'  => $trip['footer']['links'],
					'credit' => $trip['footer']['credit'],
				)
			),
		)
	);

	$trip_itinerary_page_id = wp_insert_post(
		array(
			'ID'           => $trip_itinerary_page ? $trip_itinerary_page->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Itinerary demo',
			'post_name'    => 'itinerary-demo',
			'post_content' => wp_slash( $content ),
		),
		true
	);
	if ( is_wp_error( $trip_itinerary_page_id ) ) {
		WP_CLI::error( $trip_itinerary_page_id );
	}

	$trip_itinerary_page = get_post( $trip_itinerary_page_id );
	WP_CLI::log( ( $trip_itinerary_reset ? 'Rebuilt' : 'Created' ) . " the demo page (ID {$trip_itinerary_page->ID})." );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $trip_itinerary_page->ID );

WP_CLI::success( 'The demo itinerary is the homepage: ' . home_url( '/' ) );
