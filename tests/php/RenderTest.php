<?php
/**
 * Renders real block markup through WordPress and checks the HTML that
 * visitors get: day labels, the overview, the carousel, escaping, and
 * content saved by version 0.1.
 *
 * @package TripItinerary
 */

class RenderTest extends WP_UnitTestCase {

	public function test_blocks_are_registered() {
		$registry = WP_Block_Type_Registry::get_instance();
		foreach ( array( 'itinerary', 'day', 'overview' ) as $name ) {
			$this->assertTrue( $registry->is_registered( "trip-itinerary/{$name}" ), "trip-itinerary/{$name} is registered" );
		}
	}

	public function test_days_are_numbered_and_dated_in_order() {
		$html = do_blocks(
			$this->itinerary(
				array( 'startDate' => '2026-12-30' ),
				$this->day( array( 'title' => 'First' ) )
				. $this->day( array( 'title' => 'Second' ) )
				. $this->day( array( 'title' => 'Third' ) )
			)
		);

		$this->assertSame(
			array( 'Day 1 · Wed, 30 Dec', 'Day 2 · Thu, 31 Dec', 'Day 3 · Fri, 1 Jan' ),
			$this->texts( $html, 'wp-block-trip-itinerary-day__label' )
		);
		$this->assertSame( array( 'First', 'Second', 'Third' ), $this->texts( $html, 'wp-block-trip-itinerary-day__title' ) );
	}

	public function test_the_overview_summarises_the_trip_and_maps_its_stops() {
		$html = do_blocks(
			$this->itinerary(
				array(
					'startDate'  => '2026-12-04',
					'travellers' => 2,
				),
				'<!-- wp:trip-itinerary/overview /-->'
				. $this->day( array( 'location' => 'Sarajevo', 'lat' => 43.86, 'lng' => 18.43 ) )
				. $this->day( array( 'location' => '<em>Trebević</em>' ) )
				. $this->day( array( 'location' => 'Sarajevo' ) )
				. $this->day( array( 'location' => 'Mostar', 'lat' => 43.34, 'lng' => 17.81 ) )
			)
		);

		$this->assertSame( array( '4 – 7 Dec 2026' ), $this->texts( $html, 'wp-block-trip-itinerary-overview__dates' ) );
		$this->assertSame( array( '4 days · 2 people · from Sarajevo' ), $this->texts( $html, 'wp-block-trip-itinerary-overview__facts' ) );

		// Each place once, one below the other, linking to its first day.
		$this->assertSame(
			array( '1 Sarajevo Fri 4 Dec', '2 Trebević Sat 5 Dec', '3 Mostar Mon 7 Dec' ),
			$this->texts( $html, 'wp-block-trip-itinerary-overview__link' )
		);
		$document = new DOMDocument();
		libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="utf-8"?>' . $html );
		libxml_clear_errors();
		$xpath = new DOMXPath( $document );
		foreach ( $xpath->query( "//a[contains(@class, 'wp-block-trip-itinerary-overview__link')]" ) as $link ) {
			$target = substr( $link->getAttribute( 'href' ), 1 );
			$this->assertSame( 1, $xpath->query( "//article[@id='{$target}']" )->length, "{$target} is a day on the page" );
		}

		// The map gets the stops with a position, numbered as in the list.
		$map   = $xpath->query( "//*[contains(@class, 'wp-block-trip-itinerary-overview__map')]" )->item( 0 );
		$stops = json_decode( $map->getAttribute( 'data-stops' ), true );
		$this->assertSame( array( 'Sarajevo', 'Mostar' ), wp_list_pluck( $stops, 'name' ) );
		$this->assertSame( array( 1, 3 ), wp_list_pluck( $stops, 'number' ) );
		$this->assertSame( array( 43.86, 18.43 ), array( $stops[0]['lat'], $stops[0]['lng'] ) );
		$tiles = json_decode( $map->getAttribute( 'data-tiles' ), true );
		$this->assertSame( trip_itinerary_map_tiles()['url'], $tiles['url'] );
		$this->assertStringContainsString( 'OpenStreetMap', $tiles['attribution'] );
	}

	public function test_the_overview_has_no_map_without_positions() {
		$html = do_blocks( $this->itinerary( array(), '<!-- wp:trip-itinerary/overview /-->' . $this->day( array( 'location' => 'Sarajevo' ) ) ) );
		$this->assertStringContainsString( 'wp-block-trip-itinerary-overview__list', $html );
		$this->assertStringNotContainsString( 'wp-block-trip-itinerary-overview__map', $html );
	}

	public function test_the_overview_shows_the_country_photo_behind_the_facts() {
		$photo = $this->photo( 'Mountains', '' );
		$html  = do_blocks(
			$this->itinerary(
				array( 'travellers' => 2 ),
				'<!-- wp:trip-itinerary/overview ' . wp_json_encode( array( 'background' => array( 'id' => $photo ) ) ) . ' /-->' . $this->day()
			)
		);

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-overview' ) ) );
		$this->assertTrue( $processor->has_class( 'has-photo' ) );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-overview__photo' ) ) );
		$this->assertSame( '', $processor->get_attribute( 'alt' ), 'The photo is decorative' );
		$this->assertSame( 'eager', $processor->get_attribute( 'loading' ) );
	}

	public function test_the_overview_lists_stops_only_once_days_have_places() {
		$html = do_blocks( $this->itinerary( array( 'travellers' => 2 ), '<!-- wp:trip-itinerary/overview /-->' . $this->day() ) );
		$this->assertStringContainsString( 'wp-block-trip-itinerary-overview__facts', $html );
		$this->assertStringNotContainsString( 'wp-block-trip-itinerary-overview__stops', $html );
	}

	public function test_an_empty_overview_renders_nothing() {
		$html = do_blocks( $this->itinerary( array(), '<!-- wp:trip-itinerary/overview /-->' . $this->day() ) );
		$this->assertStringNotContainsString( 'wp-block-trip-itinerary-overview', $html );
	}

	public function test_titles_are_escaped() {
		$html = do_blocks(
			$this->itinerary(
				array( 'title' => 'Trip<script>alert(1)</script>' ),
				$this->day( array( 'title' => '<strong>Day</strong><img src=x onerror=alert(1)>' ) )
			)
		);

		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringNotContainsString( 'onerror', $html );
		$this->assertStringContainsString( '<strong>Day</strong>', $html );
	}

	public function test_the_carousel_renders_photos_from_the_media_library() {
		$first  = $this->photo( 'Old Bridge', 'Photo: Ana' );
		$second = $this->photo( 'River', '' );

		$html = do_blocks(
			$this->itinerary(
				array(),
				$this->day(
					array(
						'photos' => array(
							array( 'id' => $first ),
							array( 'id' => $second ),
							// Deleted from the media library, with nothing to fall back on.
							array( 'id' => 999999 ),
						),
					)
				)
			)
		);

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-carousel' ) ) );
		$this->assertSame( 'trip-itinerary/carousel', $processor->get_attribute( 'data-wp-interactive' ) );
		$this->assertSame( '2', $processor->get_attribute( 'data-count' ) );
		$this->assertSame( 'Photos from day 1', $processor->get_attribute( 'aria-label' ) );

		$positions = array();
		while ( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-carousel__slide' ) ) ) {
			$positions[] = $processor->get_attribute( 'data-position' );
		}
		$this->assertSame( array( 'center', 'right' ), $positions );

		$this->assertStringContainsString( 'alt="Old Bridge"', $html );
		$this->assertStringContainsString( 'srcset=', $html );
		$this->assertStringContainsString( '📸 Photo: Ana', $html );
		$this->assertCount( 2, $this->texts( $html, 'wp-block-trip-itinerary-carousel__dots', 'button' ) );
	}

	public function test_carousel_photos_load_at_the_centre_size() {
		$photo = $this->photo( 'Old Bridge', '' );
		$html  = apply_filters(
			'the_content',
			$this->itinerary( array(), $this->day( array( 'photos' => array( array( 'id' => $photo ) ) ) ) )
		);

		// WordPress would add "auto", making a side photo load a small file
		// that looks blurry once it moves to the centre.
		$this->assertStringContainsString( 'sizes="(max-width: 600px) 240px, 360px"', $html );
		$this->assertStringNotContainsString( 'sizes="auto', $html );
	}

	public function test_a_day_without_photos_has_no_carousel() {
		$html = do_blocks( $this->itinerary( array(), $this->day() ) );
		$this->assertStringNotContainsString( 'wp-block-trip-itinerary-carousel', $html );
	}

	/**
	 * @dataProvider data_slide_positions
	 *
	 * @param int      $current  The photo in the centre.
	 * @param int      $count    Number of photos.
	 * @param string[] $expected Each photo's position. Same cases as the JS test.
	 */
	public function test_slide_positions_match_the_editor( $current, $count, $expected ) {
		$positions = array();
		for ( $index = 0; $index < $count; $index++ ) {
			$positions[] = trip_itinerary_slide_position( $index, $current, $count );
		}
		$this->assertSame( $expected, $positions );
	}

	public function data_slide_positions() {
		return array(
			'one'     => array( 0, 1, array( 'center' ) ),
			'two'     => array( 0, 2, array( 'center', 'right' ) ),
			'three'   => array( 0, 3, array( 'center', 'right', 'left' ) ),
			'four'    => array( 0, 4, array( 'center', 'right', 'right-far', 'left' ) ),
			'seven'   => array( 0, 7, array( 'center', 'right', 'right-far', 'hidden', 'hidden', 'left-far', 'left' ) ),
			'wrapped' => array( 4, 5, array( 'right', 'right-far', 'left-far', 'left', 'center' ) ),
		);
	}

	public function test_content_saved_by_version_0_1_renders_as_it_was_saved() {
		$legacy = '<!-- wp:trip-itinerary/itinerary -->
<section class="wp-block-trip-itinerary-itinerary"><h2 class="wp-block-trip-itinerary-itinerary__title">Old trip</h2><ol class="wp-block-trip-itinerary-itinerary__days"><!-- wp:trip-itinerary/day -->
<li class="wp-block-trip-itinerary-day"><h3 class="wp-block-trip-itinerary-day__title">Old day</h3><div class="wp-block-trip-itinerary-day__content"><!-- wp:paragraph -->
<p>Text</p>
<!-- /wp:paragraph --></div></li>
<!-- /wp:trip-itinerary/day --></ol></section>
<!-- /wp:trip-itinerary/itinerary -->';

		$html = do_blocks( $legacy );

		$this->assertStringContainsString( '<h2 class="wp-block-trip-itinerary-itinerary__title">Old trip</h2>', $html );
		$this->assertStringContainsString( '<li class="wp-block-trip-itinerary-day"><h3 class="wp-block-trip-itinerary-day__title">Old day</h3>', $html );
		$this->assertStringNotContainsString( '<article', $html );
	}

	/**
	 * Itinerary block markup.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $inner      Inner blocks.
	 * @return string
	 */
	private function itinerary( array $attributes, $inner ) {
		return $this->block( 'trip-itinerary/itinerary', $attributes, $inner );
	}

	/**
	 * Day block markup, with one paragraph.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	private function day( array $attributes = array() ) {
		return $this->block( 'trip-itinerary/day', $attributes, "<!-- wp:paragraph -->\n<p>Text</p>\n<!-- /wp:paragraph -->" );
	}

	/**
	 * Serialized block markup.
	 *
	 * @param string $name       Block name.
	 * @param array  $attributes Block attributes.
	 * @param string $inner      Inner blocks.
	 * @return string
	 */
	private function block( $name, array $attributes, $inner ) {
		$json = $attributes ? ' ' . serialize_block_attributes( $attributes ) : '';
		return "<!-- wp:{$name}{$json} -->\n{$inner}\n<!-- /wp:{$name} -->\n";
	}

	/**
	 * An image in the media library.
	 *
	 * @param string $alt     Alternative text.
	 * @param string $caption Caption, shown as the photo credit.
	 * @return int Attachment ID.
	 */
	private function photo( $alt, $caption ) {
		$id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
		wp_update_post(
			array(
				'ID'           => $id,
				'post_excerpt' => $caption,
			)
		);
		return $id;
	}

	/**
	 * The text of every element with a class, in order.
	 *
	 * @param string $html  HTML to search.
	 * @param string $class Class name, or the parent's class when $tag is set.
	 * @param string $tag   Optional tag inside elements with $class.
	 * @return string[]
	 */
	private function texts( $html, $class, $tag = '' ) {
		$document = new DOMDocument();
		libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="utf-8"?>' . $html );
		libxml_clear_errors();

		$query = "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]" . ( $tag ? "//{$tag}" : '' );
		$texts = array();
		foreach ( ( new DOMXPath( $document ) )->query( $query ) as $node ) {
			$texts[] = trim( preg_replace( '/\s+/u', ' ', $node->textContent ) );
		}
		return $texts;
	}
}
