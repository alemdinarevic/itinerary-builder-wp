<?php
/**
 * Renders the Trip header, Trip price and Trip footer through WordPress:
 * stop links point at real days, the header button finds the price, and the
 * footer falls back to the site's logo and name.
 *
 * @package TripItinerary
 */

class HeaderPriceFooterTest extends WP_UnitTestCase {

	public function test_header_links_each_stop_to_the_first_day_there() {
		$html = do_blocks(
			$this->trip(
				'<!-- wp:trip-itinerary/header /-->'
				. $this->day( 'Sarajevo' )
				. $this->day( 'Mostar' )
				. $this->day( 'Sarajevo' )
			)
		);

		$stops = $this->links( $html, 'wp-block-trip-itinerary-header__stop' );
		$this->assertSame( array( 'Sarajevo', 'Mostar' ), array_keys( $stops ) );

		// Every link points at a day on the page: the first and second day.
		$day_ids = $this->ids( $html, 'wp-block-trip-itinerary-day' );
		$this->assertCount( 3, $day_ids );
		$this->assertSame( '#' . $day_ids[0], $stops['Sarajevo'] );
		$this->assertSame( '#' . $day_ids[1], $stops['Mostar'] );
	}

	public function test_two_itineraries_on_one_page_get_different_day_ids() {
		$html = do_blocks( $this->trip( $this->day( 'Sarajevo' ) ) . $this->trip( $this->day( 'Mostar' ) ) );

		$ids = $this->ids( $html, 'wp-block-trip-itinerary-day' );
		$this->assertCount( 2, $ids );
		$this->assertNotSame( $ids[0], $ids[1] );
	}

	public function test_day_ids_use_the_itinerary_anchor_when_set() {
		$html = do_blocks( $this->trip( $this->day( 'Sarajevo' ), array( 'anchor' => 'bosnia' ) ) );
		$this->assertSame( array( 'bosnia-day-1' ), $this->ids( $html, 'wp-block-trip-itinerary-day' ) );
	}

	public function test_header_button_scrolls_to_the_price_without_its_own_link() {
		$html = do_blocks(
			$this->trip(
				'<!-- wp:trip-itinerary/header {"buttonText":"Request a quote"} /-->'
				. $this->day( 'Sarajevo' )
				. '<!-- wp:trip-itinerary/price {"amount":"€640"} /-->'
			)
		);

		$button   = $this->links( $html, 'wp-block-trip-itinerary-header__button' );
		$price_id = $this->ids( $html, 'wp-block-trip-itinerary-price' );

		$this->assertCount( 1, $price_id );
		$this->assertSame( array( 'Request a quote →' => '#' . $price_id[0] ), $button );
	}

	public function test_header_button_uses_its_own_link_when_set() {
		$html   = do_blocks(
			$this->trip(
				'<!-- wp:trip-itinerary/header {"buttonText":"Book","buttonUrl":"https://example.com/book"} /-->'
				. '<!-- wp:trip-itinerary/price {"amount":"€640"} /-->'
			)
		);
		$button = $this->links( $html, 'wp-block-trip-itinerary-header__button' );
		$this->assertSame( array( 'Book →' => 'https://example.com/book' ), $button );
	}

	public function test_header_button_is_hidden_with_nowhere_to_go() {
		$html = do_blocks( $this->trip( '<!-- wp:trip-itinerary/header {"buttonText":"Request a quote"} /-->' . $this->day( 'Sarajevo' ) ) );
		$this->assertStringNotContainsString( 'wp-block-trip-itinerary-header__button', $html );
		$this->assertStringContainsString( 'wp-block-trip-itinerary-header__stop', $html );
	}

	public function test_price_renders_amount_lists_and_request_button() {
		$html = do_blocks(
			$this->trip(
				'<!-- wp:trip-itinerary/price {"amount":"€640","per":"/ person","note":"For <strong>two</strong>","requestText":"Request","requestUrl":"mailto:hello@example.com"} -->'
				. "\n<!-- wp:list {\"className\":\"is-style-trip-included\"} -->\n<ul class=\"wp-block-list is-style-trip-included\"><!-- wp:list-item -->\n<li>Hotels</li>\n<!-- /wp:list-item --></ul>\n<!-- /wp:list -->\n"
				. '<!-- /wp:trip-itinerary/price -->'
			)
		);

		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-price__value">€640</span>', $html );
		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-price__per">/ person</span>', $html );
		$this->assertStringContainsString( 'For <strong>two</strong>', $html );
		$this->assertStringContainsString( '<li>Hotels</li>', $html );
		$this->assertSame( array( 'Request' => 'mailto:hello@example.com' ), $this->links( $html, 'wp-block-trip-itinerary-price__button' ) );
	}

	public function test_price_escapes_its_text() {
		$html = do_blocks(
			$this->trip( '<!-- wp:trip-itinerary/price {"amount":"<script>alert(1)</script>€1","requestText":"Go","requestUrl":"javascript:alert(1)"} /-->' )
		);

		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringNotContainsString( 'javascript:', $html );
	}

	public function test_footer_shows_the_picked_logo_linking_home() {
		$logo = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$html = do_blocks( '<!-- wp:trip-itinerary/footer ' . wp_json_encode( array( 'logo' => array( 'id' => $logo ) ) ) . ' /-->' );

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-footer__logo' ) ) );
		$this->assertSame( home_url( '/' ), $processor->get_attribute( 'href' ) );
		$this->assertTrue( $processor->next_tag( 'img' ) );
		$this->assertStringContainsString( 'canola', $processor->get_attribute( 'src' ) );
	}

	public function test_footer_falls_back_to_the_site_logo_then_the_site_name() {
		update_option( 'blogname', 'Bosnia Trips' );
		$html = do_blocks( '<!-- wp:trip-itinerary/footer /-->' );
		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-footer__site-name">Bosnia Trips</span>', $html );

		$site_logo = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		update_option( 'site_logo', $site_logo );
		$html = do_blocks( '<!-- wp:trip-itinerary/footer /-->' );
		$this->assertStringNotContainsString( 'footer__site-name', $html );
		$this->assertStringContainsString( 'canola', $html );
	}

	public function test_footer_keeps_links_and_strips_scripts() {
		$html = do_blocks(
			'<!-- wp:trip-itinerary/footer ' . wp_json_encode(
				array(
					'links'  => '<a href="https://example.com/contact">Contact</a><script>alert(1)</script>',
					'credit' => 'Built with Trip Itinerary',
				)
			) . ' /-->'
		);

		$this->assertSame( array( 'Contact' => 'https://example.com/contact' ), $this->links( $html, 'wp-block-trip-itinerary-footer__links', true ) );
		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringContainsString( 'Built with Trip Itinerary', $html );
	}

	/**
	 * Itinerary block markup.
	 *
	 * @param string $inner      Inner blocks.
	 * @param array  $attributes Itinerary attributes.
	 * @return string
	 */
	private function trip( $inner, array $attributes = array() ) {
		$json = $attributes ? ' ' . wp_json_encode( $attributes ) : '';
		return "<!-- wp:trip-itinerary/itinerary{$json} -->\n{$inner}\n<!-- /wp:trip-itinerary/itinerary -->\n";
	}

	/**
	 * Day block markup at a location.
	 *
	 * @param string $location Day location.
	 * @return string
	 */
	private function day( $location ) {
		return '<!-- wp:trip-itinerary/day ' . wp_json_encode( array( 'location' => $location ) ) . " -->\n<!-- wp:paragraph -->\n<p>Text</p>\n<!-- /wp:paragraph -->\n<!-- /wp:trip-itinerary/day -->\n";
	}

	/**
	 * Links with a class, as text => href. With $inside, the links inside
	 * elements with that class.
	 *
	 * @param string $html   HTML.
	 * @param string $class  Class name.
	 * @param bool   $inside Whether $class is on a container.
	 * @return array
	 */
	private function links( $html, $class, $inside = false ) {
		$query = "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]" . ( $inside ? '//a' : '' );
		$links = array();
		foreach ( $this->query( $html, $query ) as $node ) {
			$links[ trim( preg_replace( '/\s+/u', ' ', $node->textContent ) ) ] = $node->getAttribute( 'href' );
		}
		return $links;
	}

	/**
	 * The ids of elements with a class, in order.
	 *
	 * @param string $html  HTML.
	 * @param string $class Class name.
	 * @return string[]
	 */
	private function ids( $html, $class ) {
		$ids = array();
		foreach ( $this->query( $html, "//*[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')][@id]" ) as $node ) {
			$ids[] = $node->getAttribute( 'id' );
		}
		return $ids;
	}

	/**
	 * Runs an XPath query on an HTML fragment.
	 *
	 * @param string $html  HTML.
	 * @param string $query XPath.
	 * @return DOMNodeList
	 */
	private function query( $html, $query ) {
		$document = new DOMDocument();
		libxml_use_internal_errors( true );
		$document->loadHTML( '<?xml encoding="utf-8"?>' . $html );
		libxml_clear_errors();
		return ( new DOMXPath( $document ) )->query( $query );
	}
}
