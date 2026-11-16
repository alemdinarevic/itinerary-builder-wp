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
	$source = dirname( __DIR__ ) . "/demo/{$path}";
	$file   = pathinfo( $path, PATHINFO_FILENAME );
	$ext    = pathinfo( $path, PATHINFO_EXTENSION );
	$slug   = 'trip-demo-' . $file;

	// The file name includes part of the image's hash, so an image that has
	// changed gets a new URL instead of browsers showing a cached old one.
	$hash = substr( md5_file( $source ), 0, 8 );

	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'name'        => $slug,
			'fields'      => 'ids',
			'numberposts' => 1,
		)
	);

	// Replace an imported copy of an older version of the image.
	if ( $existing && get_post_meta( $existing[0], '_trip_itinerary_demo_hash', true ) !== $hash ) {
		wp_delete_attachment( $existing[0], true );
		$existing = array();
	}

	if ( $existing ) {
		$id = $existing[0];
	} else {
		// media_handle_sideload() moves the file, so give it a copy.
		$tmp = wp_tempnam( $slug );
		copy( $source, $tmp );

		$id = media_handle_sideload(
			array(
				'name'     => "{$slug}-{$hash}.{$ext}",
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
		update_post_meta( $id, '_trip_itinerary_demo_hash', $hash );
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
 * A block whose children sit inside wrapper markup, such as a List's <ul>
 * or Buttons' <div class="wp-block-buttons">.
 *
 * @param string  $name   Block name.
 * @param array   $attrs  Attributes.
 * @param array[] $inner  Child blocks.
 * @param string  $open   Opening wrapper tag.
 * @param string  $close  Closing wrapper tag.
 * @return array
 */
function trip_itinerary_demo_wrapped( $name, array $attrs, array $inner, $open, $close ) {
	$block                 = trip_itinerary_demo_block( $name, $attrs, $inner );
	$block['innerContent'] = array_merge( array( $open ), array_fill( 0, count( $inner ), null ), array( $close ) );
	$block['innerHTML']    = $open . $close;
	return $block;
}

/**
 * A core List block with one item per entry, in a block style.
 *
 * @param string[] $items Item texts.
 * @param string   $style Block style name, such as "trip-included".
 * @return array
 */
function trip_itinerary_demo_list( array $items, $style ) {
	$class = "is-style-{$style}";
	$items = array_map(
		function ( $item ) {
			return trip_itinerary_demo_block( 'core/list-item', array(), array(), '<li>' . esc_html( $item ) . '</li>' );
		},
		$items
	);
	return trip_itinerary_demo_wrapped( 'core/list', array( 'className' => $class ), $items, '<ul class="wp-block-list ' . $class . '">', '</ul>' );
}

/**
 * Creates or updates a demo page, unless it exists and this isn't a reset.
 *
 * @param string  $slug    Page slug.
 * @param string  $title   Page title.
 * @param array[] $blocks  The page's blocks.
 * @param bool    $reset   Whether to rebuild an existing page.
 * @return WP_Post The page.
 */
function trip_itinerary_demo_page( $slug, $title, array $blocks, $reset ) {
	$page = get_page_by_path( $slug );
	if ( $page && ! $reset ) {
		WP_CLI::log( "The {$title} page already exists; leaving it as it is." );
		return $page;
	}

	$id = wp_insert_post(
		array(
			'ID'           => $page ? $page->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => wp_slash( serialize_blocks( $blocks ) ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id );
	}

	WP_CLI::log( ( $page ? 'Rebuilt' : 'Created' ) . " the {$title} page (ID {$id})." );
	return get_post( $id );
}

/**
 * A heading and a short description, introducing a section of the
 * Components page.
 *
 * @param string $title       Heading.
 * @param string $description Paragraph.
 * @return array[] Two blocks.
 */
function trip_itinerary_demo_section( $title, $description ) {
	return array(
		trip_itinerary_demo_block( 'core/heading', array( 'className' => 'trip-components__heading' ), array(), '<h2 class="wp-block-heading trip-components__heading">' . esc_html( $title ) . '</h2>' ),
		trip_itinerary_demo_block( 'core/paragraph', array(), array(), '<p>' . esc_html( $description ) . '</p>' ),
	);
}

/**
 * A core Button block in one of the plugin's styles.
 *
 * @param string $style Style name, such as "trip-primary".
 * @param string $text  Button text.
 * @return array
 */
function trip_itinerary_demo_button( $style, $text ) {
	$class = "is-style-{$style}";
	return trip_itinerary_demo_block(
		'core/button',
		array( 'className' => $class ),
		array(),
		'<div class="wp-block-button ' . $class . '"><a class="wp-block-button__link wp-element-button">' . esc_html( $text ) . '</a></div>'
	);
}

$trip_itinerary_reset = in_array( 'reset', $args, true );
$trip                 = json_decode( file_get_contents( dirname( __DIR__ ) . '/demo/trip.json' ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.

// The trip's blocks, used on both pages.
$days = array();
foreach ( $trip['days'] as $day ) {
	$paragraph = '<p>' . esc_html( $day['text'] ) . '</p>';
	$days[]    = trip_itinerary_demo_block(
		'trip-itinerary/day',
		array(
			'title'    => $day['title'],
			'location' => $day['location'],
			'lat'      => $day['lat'],
			'lng'      => $day['lng'],
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

$header   = trip_itinerary_demo_block( 'trip-itinerary/header', $trip['header'] );
$overview = trip_itinerary_demo_block(
	'trip-itinerary/overview',
	array( 'background' => trip_itinerary_demo_image( $trip['overview']['photo'] . '.jpg', $trip['overview']['alt'] ) )
);
$price    = trip_itinerary_demo_block(
	'trip-itinerary/price',
	array_intersect_key( $trip['price'], array_flip( array( 'amount', 'per', 'note', 'requestNote', 'requestText', 'requestUrl' ) ) ),
	array(
		trip_itinerary_demo_block( 'core/heading', array( 'level' => 3 ), array(), '<h3 class="wp-block-heading">What is included in the price?</h3>' ),
		trip_itinerary_demo_list( $trip['price']['included'], 'trip-included' ),
		trip_itinerary_demo_list( $trip['price']['excluded'], 'trip-excluded' ),
	)
);
$logo     = trip_itinerary_demo_image( $trip['footer']['logo'] . '.png', 'Trip Itinerary' );
$footer   = trip_itinerary_demo_block(
	'trip-itinerary/footer',
	array(
		'logo'   => $logo,
		'links'  => $trip['footer']['links'],
		'credit' => $trip['footer']['credit'],
	)
);

/**
 * An Itinerary with the demo trip's details around the given blocks.
 *
 * @param array[] $inner      Child blocks.
 * @param array   $attributes Extra Itinerary attributes.
 * @return array
 */
$itinerary = static function ( array $inner, array $attributes = array() ) use ( $trip ) {
	return trip_itinerary_demo_block(
		'trip-itinerary/itinerary',
		array_merge(
			array(
				'startDate'  => $trip['startDate'],
				'travellers' => $trip['travellers'],
			),
			$attributes
		),
		$inner
	);
};

// The demo trip. The anchor gives stable links, such as the Itinerary card's
// link to the price.
$trip_itinerary_page = trip_itinerary_demo_page(
	'itinerary-demo',
	'Itinerary demo',
	array(
		$itinerary(
			array_merge( array( $header, $overview ), $days, array( $price ) ),
			array(
				'title'  => $trip['title'],
				'anchor' => 'bosnia',
			)
		),
		$footer,
	),
	$trip_itinerary_reset
);

// A bell with three notifications, and a user menu, for the Components page.
$notifications = trip_itinerary_demo_block(
	'trip-itinerary/notifications',
	array(),
	array(
		trip_itinerary_demo_block(
			'trip-itinerary/notification',
			array(
				'title'       => 'Your quote is ready',
				'description' => 'A long weekend in Bosnia: €640 per person. Have a look and let us know.',
				'date'        => '2026-11-12',
				'url'         => get_permalink( $trip_itinerary_page ) . '#bosnia-price',
			)
		),
		trip_itinerary_demo_block(
			'trip-itinerary/notification',
			array(
				'title'       => 'New message from your agent',
				'description' => 'Would you like a later train to Mostar on Sunday?',
				'date'        => '2026-11-10',
			)
		),
		trip_itinerary_demo_block(
			'trip-itinerary/notification',
			array(
				'title'       => 'Itinerary updated',
				'description' => 'We added the Tunnel of Hope museum to day 2.',
				'date'        => '2026-11-08',
			)
		),
	)
);
$user_menu = trip_itinerary_demo_block(
	'trip-itinerary/user-menu',
	array(
		'links' => sprintf(
			'<a href="%s">My itineraries</a> <a href="%s">Components</a> <a href="%s">Profile</a>',
			esc_url( home_url( '/' ) ),
			esc_url( home_url( '/components/' ) ),
			esc_url( admin_url( 'profile.php' ) )
		),
	)
);

// The Components page: each block on its own. The trip blocks need the
// days' data, so they sit in "Summary only" itineraries, which keep the days
// but don't show them.
$summary          = array( 'className' => 'is-style-summary' );
$components_links = sprintf(
	'<a href="%s">Itinerary demo</a> <a href="%s">Components</a> <a href="https://github.com/alemdinarevic/itinerary-builder-wp">GitHub</a>',
	esc_url( home_url( '/' ) ),
	esc_url( home_url( '/components/' ) )
);

trip_itinerary_demo_page(
	'components',
	'Components',
	array_merge(
		trip_itinerary_demo_section( 'Page header', 'A logo linking home, links and a button on a translucent bar that stays at the top. On phones the links fold into a menu.' ),
		array(
			trip_itinerary_demo_block(
				'trip-itinerary/page-header',
				array(
					'logo'       => $logo,
					'links'      => $components_links,
					'buttonText' => 'Plan a trip',
					'buttonUrl'  => home_url( '/' ),
					'sticky'     => false,
					'align'      => 'wide',
				),
				array( $notifications, $user_menu )
			),
		),
		trip_itinerary_demo_section( 'Notifications', 'A bell with the unread count. Its panel lists notifications written as blocks; each visitor’s browser remembers which they’ve read. Click outside or press Escape to close it.' ),
		array( $notifications ),
		trip_itinerary_demo_section( 'User menu', 'For a logged-in visitor: their initial, opening a menu with their name and email, links and Log out. Logged-out visitors see a Log in button.' ),
		array( $user_menu ),
		trip_itinerary_demo_section( 'Buttons', 'Styles for the core Button block, in the Styles panel of any button. The tinted ones fill with their colour on hover.' ),
		array(
			trip_itinerary_demo_wrapped(
				'core/buttons',
				array(),
				array(
					trip_itinerary_demo_button( 'trip-primary', 'Primary' ),
					trip_itinerary_demo_button( 'trip-primary-outline', 'Primary outline' ),
					trip_itinerary_demo_button( 'trip-secondary', 'Secondary' ),
					trip_itinerary_demo_button( 'trip-secondary-dark', 'Secondary dark' ),
					trip_itinerary_demo_button( 'trip-tertiary', 'Tertiary' ),
					trip_itinerary_demo_button( 'trip-quaternary', 'Quaternary' ),
				),
				'<div class="wp-block-buttons">',
				'</div>'
			),
		),
		trip_itinerary_demo_section( 'Fields', 'The Field block: a labelled input for text, email, phone or a password, which has a button to show what was typed.' ),
		array(
			trip_itinerary_demo_block(
				'trip-itinerary/field',
				array(
					'label'       => 'Full name',
					'placeholder' => 'Alem Dinarević',
					'name'        => 'name',
				)
			),
			trip_itinerary_demo_block(
				'trip-itinerary/field',
				array(
					'label'       => 'Email',
					'type'        => 'email',
					'placeholder' => 'you@example.com',
					'name'        => 'email',
					'required'    => true,
				)
			),
			trip_itinerary_demo_block(
				'trip-itinerary/field',
				array(
					'label'    => 'Password',
					'type'     => 'password',
					'name'     => 'password',
					'required' => true,
					'help'     => 'At least 12 characters.',
				)
			),
		),
		trip_itinerary_demo_section( 'Itinerary card', 'A card for the trip on another page, filled in from that page’s itinerary: here, the Itinerary demo.' ),
		array( trip_itinerary_demo_block( 'trip-itinerary/card', array( 'pageId' => $trip_itinerary_page->ID ) ) ),
		trip_itinerary_demo_section( 'Trip header', 'Sticks to the top while the days scroll by, with a link to each place and a button that scrolls to the price.' ),
		array( $itinerary( array_merge( array( $header ), $days, array( $price ) ), $summary ) ),
		trip_itinerary_demo_section( 'Trip overview', 'The dates, length, travellers and starting place over a photo of the country, then each stop with a map.' ),
		array( $itinerary( array_merge( array( $overview ), $days ), $summary ) ),
		trip_itinerary_demo_section( 'Day', 'One day: its number and date, title, place, content and a carousel of photos.' ),
		array( $itinerary( array( $days[0] ) ) ),
		trip_itinerary_demo_section( 'Trip price', 'The price, what is and isn’t included, and a button to request a quote.' ),
		array( $itinerary( array_merge( $days, array( $price ) ), $summary ) ),
		trip_itinerary_demo_section( 'Trip footer', 'A logo linking home, links and a credit. Without a logo it uses the site’s.' ),
		array( $footer )
	),
	$trip_itinerary_reset
);

// The demo uses the plugin's font, Montserrat, for the whole site.
update_option( 'trip_itinerary_site_font', true );

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $trip_itinerary_page->ID );

WP_CLI::success( 'The demo itinerary is the homepage: ' . home_url( '/' ) . ' — components: ' . home_url( '/components/' ) );
