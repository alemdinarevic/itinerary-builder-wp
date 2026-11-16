<?php
/**
 * Helpers shared by the blocks' render.php templates.
 *
 * @package TripItinerary
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a block still has the static markup saved by version 0.1.
 *
 * Version 0.1 saved the Itinerary and Day as full HTML. Since then they are
 * rendered here, and the saved markup holds only their inner blocks. The
 * editor upgrades old content through the blocks' `deprecated` entries the
 * next time it's saved; until then, the old markup is shown as it was saved.
 *
 * @param WP_Block $block The block being rendered.
 * @return bool
 */
function trip_itinerary_has_legacy_markup( WP_Block $block ) {
	return '' !== trim( $block->parsed_block['innerHTML'] ?? '' );
}

/**
 * The date `$offset` days after the trip's start date.
 *
 * Mirrors tripDate() in src/shared/dates.js. Dates are calendar days, so
 * they are handled in UTC to avoid shifting by a day across time zones.
 *
 * @param mixed $start_date Trip start, as YYYY-MM-DD.
 * @param int   $offset     Days to add (0 for the first day).
 * @return DateTimeImmutable|null The date, or null without a valid start date.
 */
function trip_itinerary_date( $start_date, $offset ) {
	if ( ! is_string( $start_date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
		return null;
	}

	$date = DateTimeImmutable::createFromFormat( '!Y-m-d', $start_date, new DateTimeZone( 'UTC' ) );
	if ( ! $date ) {
		return null;
	}

	return $date->modify( sprintf( '%+d days', $offset ) );
}

/**
 * Formats a trip date in the site's language.
 *
 * @param DateTimeImmutable $date   The date.
 * @param string            $format PHP date format, for example 'D, j M'.
 * @return string
 */
function trip_itinerary_format_date( DateTimeImmutable $date, $format ) {
	return wp_date( $format, $date->getTimestamp(), new DateTimeZone( 'UTC' ) );
}

/**
 * The label above each day's title. Visually it shows the date ("Fri, 4 Dec")
 * next to the day's numbered circle; "Day 1" is there for screen readers,
 * which skip the circle. Mirrors the label in src/day/edit.js.
 *
 * @param int    $index      Zero-based position of the day in its itinerary.
 * @param string $start_date The trip's start date, if any.
 * @return string HTML.
 */
function trip_itinerary_day_label_html( $index, $start_date = '' ) {
	$html = sprintf(
		'<span class="wp-block-trip-itinerary-day__label-day">%s</span>',
		/* translators: %d: day number, starting at 1. */
		esc_html( sprintf( __( 'Day %d', 'trip-itinerary' ), $index + 1 ) )
	);
	$date = trip_itinerary_date( $start_date, $index );

	if ( $date ) {
		$html .= sprintf(
			' <span class="wp-block-trip-itinerary-day__label-sep" aria-hidden="true">·</span> <time datetime="%s">%s</time>',
			esc_attr( $date->format( 'Y-m-d' ) ),
			esc_html(
				trip_itinerary_format_date(
					$date,
					/* translators: Date format for the day label, see https://www.php.net/manual/datetime.format.php */
					_x( 'D, j M', 'day label date format', 'trip-itinerary' )
				)
			)
		);
	}

	return '<p class="wp-block-trip-itinerary-day__label">' . $html . '</p>';
}

/**
 * The numbered circle on the timeline. Hidden from screen readers, which
 * read "Day 1" in the label instead.
 *
 * @param int $index Zero-based position of the day in its itinerary.
 * @return string HTML.
 */
function trip_itinerary_day_marker_html( $index ) {
	return '<span class="wp-block-trip-itinerary-day__marker" aria-hidden="true">' . esc_html( number_format_i18n( $index + 1 ) ) . '</span>';
}

/**
 * A plain-text version of a RichText value, such as a day's location.
 *
 * @param mixed $html Stored attribute value.
 * @return string
 */
function trip_itinerary_plain_text( $html ) {
	if ( ! is_string( $html ) ) {
		return '';
	}
	return trim( html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES, 'UTF-8' ) );
}

/**
 * "13 – 15 Nov 2026", "30 Nov – 2 Dec 2026" or "30 Dec 2026 – 2 Jan 2027".
 * Mirrors formatRange() in src/shared/summary.js.
 *
 * @param DateTimeImmutable $start First day.
 * @param DateTimeImmutable $end   Last day.
 * @return string
 */
function trip_itinerary_format_range( DateTimeImmutable $start, DateTimeImmutable $end ) {
	/* translators: Date format for a single trip date, see https://www.php.net/manual/datetime.format.php */
	$full = _x( 'j M Y', 'trip date format', 'trip-itinerary' );

	if ( $start == $end ) { // phpcs:ignore Universal.Operators.StrictComparisons -- Compares the dates, not the objects.
		return trip_itinerary_format_date( $start, $full );
	}

	$start_format = $full;
	if ( $start->format( 'Y' ) === $end->format( 'Y' ) ) {
		$start_format = $start->format( 'm' ) === $end->format( 'm' )
			/* translators: Start of a date range within one month. */
			? _x( 'j', 'trip range start, same month', 'trip-itinerary' )
			/* translators: Start of a date range within one year. */
			: _x( 'j M', 'trip range start, same year', 'trip-itinerary' );
	}

	return trip_itinerary_format_date( $start, $start_format ) . ' – ' . trip_itinerary_format_date( $end, $full );
}

/**
 * The items shown in the Trip overview. Mirrors getTripSummary() in
 * src/shared/summary.js.
 *
 * @param string   $start_date Trip start, as YYYY-MM-DD.
 * @param int      $travellers Number of travellers.
 * @param string[] $locations  Each day's location as plain text, in order.
 * @return array[] Items with 'key', 'label', 'value' and optional 'detail'.
 */
function trip_itinerary_summary_items( $start_date, $travellers, array $locations ) {
	$items     = array();
	$day_count = count( $locations );

	$start = trip_itinerary_date( $start_date, 0 );
	if ( $start && $day_count > 0 ) {
		$items[] = array(
			'key'    => 'dates',
			'label'  => __( 'Dates', 'trip-itinerary' ),
			'value'  => trip_itinerary_format_range( $start, trip_itinerary_date( $start_date, $day_count - 1 ) ),
			/* translators: %d: number of days. */
			'detail' => sprintf( _n( '%d day', '%d days', $day_count, 'trip-itinerary' ), $day_count ),
		);
	}

	if ( $travellers > 0 ) {
		$items[] = array(
			'key'   => 'travellers',
			'label' => __( 'Travellers', 'trip-itinerary' ),
			/* translators: %d: number of people. */
			'value' => sprintf( _n( '%d person', '%d people', $travellers, 'trip-itinerary' ), $travellers ),
		);
	}

	// "from Sarajevo": where the trip starts, the first day with a place.
	$places = array_values( array_filter( array_map( 'trim', $locations ), 'strlen' ) );
	if ( $places ) {
		$items[] = array(
			'key'   => 'from',
			'label' => __( 'From', 'trip-itinerary' ),
			/* translators: %s: the first place of the trip. */
			'value' => sprintf( __( 'from %s', 'trip-itinerary' ), $places[0] ),
		);
	}

	return $items;
}

/**
 * The places a trip visits, for the Trip header: each day's location once,
 * in the order they're first visited. Mirrors getStops() in
 * src/shared/stops.js.
 *
 * @param string[] $locations Each day's location as plain text, in order.
 * @return array[] Stops with 'name' and 'dayIndex'.
 */
function trip_itinerary_stops( array $locations ) {
	$stops = array();
	$seen  = array();

	foreach ( array_values( $locations ) as $day_index => $location ) {
		$name = trim( (string) $location );
		$key  = mb_strtolower( $name );
		if ( '' !== $name && ! isset( $seen[ $key ] ) ) {
			$seen[ $key ] = true;
			$stops[]      = array(
				'name'     => $name,
				'dayIndex' => $day_index,
			);
		}
	}

	return $stops;
}

/**
 * A logo: the picked image, else the site logo, else the site's name.
 * Used by the Trip footer and the Page header.
 *
 * @param mixed  $logo  Stored logo: id, url, alt.
 * @param string $block Block class, such as "wp-block-trip-itinerary-footer".
 * @return string HTML.
 */
function trip_itinerary_logo_html( $logo, $block ) {
	$attributes = array( 'class' => $block . '__logo-image' );

	if ( is_array( $logo ) && ! empty( $logo['id'] ) ) {
		$image = wp_get_attachment_image( (int) $logo['id'], 'medium', false, $attributes );
		if ( $image ) {
			return $image;
		}
	}

	// Block themes store the logo in "site_logo", classic themes in "custom_logo".
	$site_logo = (int) get_option( 'site_logo' );
	if ( ! $site_logo ) {
		$site_logo = (int) get_theme_mod( 'custom_logo' );
	}
	if ( $site_logo ) {
		$image = wp_get_attachment_image( $site_logo, 'medium', false, $attributes );
		if ( $image ) {
			return $image;
		}
	}

	return '<span class="' . esc_attr( $block ) . '__site-name">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
}

/**
 * The Trip footer's logo. Mirrors the preview in src/footer/edit.js.
 *
 * @param mixed $logo Stored logo: id, url, alt.
 * @return string HTML.
 */
function trip_itinerary_footer_logo_html( $logo ) {
	return trip_itinerary_logo_html( $logo, 'wp-block-trip-itinerary-footer' );
}

/**
 * A day's map position, if it has a valid one.
 *
 * @param array $attributes Day attributes, with 'lat' and 'lng'.
 * @return float[]|null [ lat, lng ], or null.
 */
function trip_itinerary_coordinates( array $attributes ) {
	$lat = $attributes['lat'] ?? null;
	$lng = $attributes['lng'] ?? null;
	if ( ! is_numeric( $lat ) || ! is_numeric( $lng ) || abs( $lat ) > 90 || abs( $lng ) > 180 ) {
		return null;
	}
	return array( (float) $lat, (float) $lng );
}

/**
 * The trip's stops with their map positions: the stops from
 * trip_itinerary_stops(), each placed where the first day at that place with
 * a position is. Stops without one are left off the map. Mirrors
 * getMapStops() in src/shared/stops.js.
 *
 * @param string[]          $locations   Each day's location, in order.
 * @param (float[]|null)[]  $coordinates Each day's [ lat, lng ] or null, in order.
 * @return array[] Stops with 'name', 'dayIndex', 'number', 'lat' and 'lng'.
 */
function trip_itinerary_map_stops( array $locations, array $coordinates ) {
	$map_stops = array();

	foreach ( trip_itinerary_stops( $locations ) as $number => $stop ) {
		$key = mb_strtolower( $stop['name'] );
		foreach ( array_values( $locations ) as $day_index => $location ) {
			if ( mb_strtolower( trim( (string) $location ) ) === $key && ! empty( $coordinates[ $day_index ] ) ) {
				$map_stops[] = $stop + array(
					'number' => $number + 1,
					'lat'    => $coordinates[ $day_index ][0],
					'lng'    => $coordinates[ $day_index ][1],
				);
				break;
			}
		}
	}

	return $map_stops;
}

/**
 * The map tiles the Trip overview uses. OpenStreetMap's by default, which
 * suit a demo or a small site; busy sites should use their own tile server
 * or a provider, through the `trip_itinerary_map_tiles` filter.
 *
 * @see https://operations.osmfoundation.org/policies/tiles/
 *
 * @return array 'url' (a Leaflet tile URL template) and 'attribution' (HTML).
 */
function trip_itinerary_map_tiles() {
	return apply_filters(
		'trip_itinerary_map_tiles',
		array(
			'url'         => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
			'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
		)
	);
}

/**
 * The first block of a type in a list of parsed blocks, at any depth.
 *
 * @param array[] $blocks Parsed blocks, from parse_blocks().
 * @param string  $name   Block name.
 * @return array|null The block, or null.
 */
function trip_itinerary_find_block( array $blocks, $name ) {
	foreach ( $blocks as $parsed ) {
		if ( ( $parsed['blockName'] ?? '' ) === $name ) {
			return $parsed;
		}
		$inner = trip_itinerary_find_block( $parsed['innerBlocks'] ?? array(), $name );
		if ( $inner ) {
			return $inner;
		}
	}
	return null;
}

/**
 * What the Itinerary card shows about the trip on a page: read from the
 * page's Itinerary block, so nothing has to be typed twice.
 *
 * @param int $page_id The page with the itinerary.
 * @return array|null 'title', 'url', 'route', 'dates', 'facts', 'photoId'
 *                    and 'priceUrl', or null without a published itinerary.
 */
function trip_itinerary_card_data( $page_id ) {
	$page = $page_id ? get_post( $page_id ) : null;
	if ( ! $page || 'publish' !== $page->post_status ) {
		return null;
	}

	$itinerary = trip_itinerary_find_block( parse_blocks( $page->post_content ), 'trip-itinerary/itinerary' );
	if ( ! $itinerary ) {
		return null;
	}

	$attributes = $itinerary['attrs'];
	$locations  = array();
	$photo_id   = 0;
	$has_price  = false;
	foreach ( $itinerary['innerBlocks'] as $child ) {
		if ( 'trip-itinerary/day' === $child['blockName'] ) {
			$locations[] = trip_itinerary_plain_text( $child['attrs']['location'] ?? '' );
			// A day's first photo, if the overview has no country photo.
			if ( ! $photo_id && ! empty( $child['attrs']['photos'][0]['id'] ) ) {
				$photo_id = (int) $child['attrs']['photos'][0]['id'];
			}
		}
		if ( 'trip-itinerary/overview' === $child['blockName'] && ! empty( $child['attrs']['background']['id'] ) ) {
			$overview_photo = (int) $child['attrs']['background']['id'];
		}
		if ( 'trip-itinerary/price' === $child['blockName'] ) {
			$has_price = true;
		}
	}

	// "Sarajevo to Mostar", or one place for a trip that starts and ends there.
	$places = array_values( array_filter( $locations, 'strlen' ) );
	$route  = '';
	if ( $places ) {
		$from  = $places[0];
		$to    = end( $places );
		$route = $from === $to
			? $from
			/* translators: 1: where the trip starts, 2: where it ends. */
			: sprintf( __( '%1$s to %2$s', 'trip-itinerary' ), $from, $to );
	}

	$dates = '';
	$facts = array();
	foreach ( trip_itinerary_summary_items( $attributes['startDate'] ?? '', (int) ( $attributes['travellers'] ?? 0 ), $locations ) as $item ) {
		if ( 'dates' === $item['key'] ) {
			$dates   = $item['value'];
			$facts[] = $item['detail'];
		} elseif ( 'travellers' === $item['key'] ) {
			$facts[] = $item['value'];
		}
	}

	$url = get_permalink( $page );

	return array(
		'title'    => trip_itinerary_plain_text( $attributes['title'] ?? get_the_title( $page ) ),
		'url'      => $url,
		'route'    => $route,
		'dates'    => $dates,
		'facts'    => $facts,
		'photoId'  => $overview_photo ?? $photo_id,
		// The price's anchor is only predictable when the itinerary has one.
		'priceUrl' => $has_price && ! empty( $attributes['anchor'] ) ? $url . '#' . $attributes['anchor'] . '-price' : '',
	);
}

/**
 * A notification's ID, for remembering in the browser that it's been read.
 * Made from its content, so editing a notification makes it unread again,
 * as a changed message should be.
 *
 * @param array $attributes Notification attributes.
 * @return string
 */
function trip_itinerary_notification_id( array $attributes ) {
	return 'n' . substr( md5( wp_json_encode( array( $attributes['title'] ?? '', $attributes['description'] ?? '', $attributes['date'] ?? '' ) ) ), 0, 12 );
}
