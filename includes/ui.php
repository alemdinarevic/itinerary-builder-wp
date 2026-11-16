<?php
/**
 * The generic UI: design tokens and button styles for the core Button block.
 *
 * @package TripItinerary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The button styles, as name => label. Each becomes an "is-style-{name}"
 * class on the Button block; assets/ui.css styles them.
 *
 * @return string[]
 */
function trip_itinerary_button_styles() {
	return array(
		'trip-primary'         => __( 'Primary', 'trip-itinerary' ),
		'trip-primary-outline' => __( 'Primary outline', 'trip-itinerary' ),
		'trip-secondary'       => __( 'Secondary', 'trip-itinerary' ),
		'trip-secondary-dark'  => __( 'Secondary dark', 'trip-itinerary' ),
		'trip-tertiary'        => __( 'Tertiary', 'trip-itinerary' ),
		'trip-quaternary'      => __( 'Quaternary', 'trip-itinerary' ),
	);
}

/**
 * Registers the stylesheet and the Button block styles. Registering styles
 * in PHP makes them appear in the editor's Styles panel without any JS.
 */
function trip_itinerary_register_ui() {
	wp_register_style(
		'trip-itinerary-ui',
		plugins_url( 'assets/ui.css', dirname( __DIR__ ) . '/trip-itinerary.php' ),
		array(),
		(string) filemtime( dirname( __DIR__ ) . '/assets/ui.css' )
	);

	foreach ( trip_itinerary_button_styles() as $name => $label ) {
		register_block_style(
			'core/button',
			array(
				'name'  => $name,
				'label' => $label,
			)
		);
	}
}
add_action( 'init', 'trip_itinerary_register_ui' );

/**
 * Loads the stylesheet on the site and inside the editor's canvas, where the
 * tokens are also used by the plugin's blocks.
 */
function trip_itinerary_enqueue_ui() {
	wp_enqueue_style( 'trip-itinerary-ui' );
}
add_action( 'enqueue_block_assets', 'trip_itinerary_enqueue_ui' );

/**
 * Whether the whole site uses the plugin's font, not just its blocks. Off by
 * default, since a plugin shouldn't change a site's typography unasked; the
 * demo turns it on (bin/setup-demo.php).
 *
 * @return bool
 */
function trip_itinerary_uses_site_font() {
	return (bool) get_option( 'trip_itinerary_site_font', false );
}

/**
 * Adds Montserrat to the theme's fonts and makes it the site's text and
 * heading font, when trip_itinerary_uses_site_font() is on. Done through
 * theme.json data, so it also shows in the Site Editor's typography settings.
 *
 * @param WP_Theme_JSON_Data $theme_json The theme's theme.json data.
 * @return WP_Theme_JSON_Data
 */
function trip_itinerary_site_font( $theme_json ) {
	if ( ! trip_itinerary_uses_site_font() ) {
		return $theme_json;
	}

	$font = 'var(--wp--preset--font-family--trip-montserrat)';

	return $theme_json->update_with(
		array(
			'version'  => 3,
			'settings' => array(
				'typography' => array(
					'fontFamilies' => array(
						array(
							'name'       => 'Montserrat',
							'slug'       => 'trip-montserrat',
							'fontFamily' => 'Montserrat, system-ui, sans-serif',
						),
					),
				),
			),
			'styles'   => array(
				'typography' => array( 'fontFamily' => $font ),
				'elements'   => array(
					'heading' => array( 'typography' => array( 'fontFamily' => $font ) ),
					'button'  => array( 'typography' => array( 'fontFamily' => $font ) ),
				),
			),
		)
	);
}
add_filter( 'wp_theme_json_data_theme', 'trip_itinerary_site_font' );
