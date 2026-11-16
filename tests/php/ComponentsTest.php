<?php
/**
 * The generic components: button styles, the Field, Itinerary card and
 * Page header blocks, the Itinerary's "Summary only" style and the font.
 *
 * @package TripItinerary
 */

class ComponentsTest extends WP_UnitTestCase {

	public function test_button_styles_are_registered_for_the_core_button() {
		$styles = WP_Block_Styles_Registry::get_instance()->get_registered_styles_for_block( 'core/button' );
		foreach ( array_keys( trip_itinerary_button_styles() ) as $name ) {
			$this->assertArrayHasKey( $name, $styles, "{$name} is a Button style" );
		}
	}

	public function test_text_field_has_a_label_tied_to_its_input() {
		$html = do_blocks( '<!-- wp:trip-itinerary/field {"label":"Email","type":"email","name":"email","required":true,"help":"We reply here."} /-->' );

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( 'label' ) );
		$for = $processor->get_attribute( 'for' );
		$this->assertTrue( $processor->next_tag( 'input' ) );
		$this->assertSame( $for, $processor->get_attribute( 'id' ) );
		$this->assertSame( 'email', $processor->get_attribute( 'type' ) );
		$this->assertSame( 'email', $processor->get_attribute( 'autocomplete' ) );
		$this->assertTrue( $processor->get_attribute( 'required' ) );
		$this->assertSame( "{$for}-help", $processor->get_attribute( 'aria-describedby' ) );

		$this->assertStringNotContainsString( 'field__toggle', $html, 'Only passwords get a show button' );
		$this->assertStringNotContainsString( 'data-wp-interactive', $html );
	}

	public function test_password_field_has_a_working_show_button() {
		$html = do_blocks( '<!-- wp:trip-itinerary/field {"label":"Password","type":"password"} /-->' );

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-field' ) ) );
		$this->assertSame( 'trip-itinerary/field', $processor->get_attribute( 'data-wp-interactive' ) );

		$this->assertTrue( $processor->next_tag( 'input' ) );
		$this->assertSame( 'password', $processor->get_attribute( 'type' ) );
		$this->assertSame( 'current-password', $processor->get_attribute( 'autocomplete' ) );
		$input = $processor->get_attribute( 'id' );

		$this->assertTrue( $processor->next_tag( 'button' ) );
		$this->assertSame( $input, $processor->get_attribute( 'aria-controls' ) );
		$this->assertSame( 'false', $processor->get_attribute( 'aria-pressed' ) );
		$this->assertSame( 'Show password', $processor->get_attribute( 'aria-label' ) );
	}

	public function test_field_rejects_unknown_types_and_escapes_text() {
		$html = do_blocks( '<!-- wp:trip-itinerary/field {"label":"<script>x</script>Name","type":"checkbox","placeholder":"\\u0022 onfocus=\\u0022alert(1)"} /-->' );

		$this->assertStringContainsString( 'type="text"', $html );
		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringNotContainsString( '" onfocus="', $html );
	}

	public function test_card_shows_the_trip_from_the_chosen_page() {
		$photo = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );
		$page  = $this->trip_page( $photo );

		$html = do_blocks( '<!-- wp:trip-itinerary/card {"pageId":' . $page . '} /-->' );

		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-card__title">A weekend</span>', $html );
		$this->assertStringContainsString( 'Sarajevo to Mostar', $html );
		$this->assertStringContainsString( '4 – 5 Dec 2026', $html );
		$this->assertStringContainsString( '2 days · 2 people', $html );
		$this->assertStringContainsString( 'canola', $html, 'Uses the overview photo' );
		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $page ) ) . '"', $html );
		$this->assertStringContainsString( 'href="' . esc_url( get_permalink( $page ) . '#bosnia-price' ) . '"', $html );
	}

	public function test_card_without_a_published_itinerary_renders_nothing() {
		$draft   = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'draft',
				'post_content' => '<!-- wp:trip-itinerary/itinerary /-->',
			)
		);
		$no_trip = self::factory()->post->create( array( 'post_type' => 'page' ) );

		foreach ( array( 0, $draft, $no_trip, 999999 ) as $page ) {
			$this->assertSame( '', trim( do_blocks( '<!-- wp:trip-itinerary/card {"pageId":' . $page . '} /-->' ) ), "Page {$page}" );
		}
	}

	public function test_page_header_has_logo_links_menu_and_button() {
		update_option( 'blogname', 'Bosnia Trips' );
		$html = do_blocks(
			'<!-- wp:trip-itinerary/page-header ' . wp_json_encode(
				array(
					'links'      => '<a href="/trips/">Trips</a><script>alert(1)</script>',
					'buttonText' => 'Plan a trip',
					'buttonUrl'  => 'https://example.com/plan',
				)
			) . ' /-->'
		);

		$this->assertStringContainsString( 'is-sticky', $html );
		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-page-header__site-name">Bosnia Trips</span>', $html );
		$this->assertStringContainsString( '<a href="/trips/">Trips</a>', $html );
		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringContainsString( 'href="https://example.com/plan"', $html );

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-page-header__menu-button' ) ) );
		$this->assertSame( 'false', $processor->get_attribute( 'aria-expanded' ) );
		$menu = $processor->get_attribute( 'aria-controls' );
		$this->assertTrue( $processor->next_tag( 'nav' ) );
		$this->assertSame( $menu, $processor->get_attribute( 'id' ) );
	}

	public function test_page_header_without_links_has_no_menu() {
		$html = do_blocks( '<!-- wp:trip-itinerary/page-header {"sticky":false} /-->' );
		$this->assertStringNotContainsString( 'menu-button', $html );
		$this->assertStringNotContainsString( 'is-sticky', $html );
	}

	public function test_summary_only_keeps_the_trip_blocks_but_not_the_days() {
		$html = do_blocks(
			'<!-- wp:trip-itinerary/itinerary {"className":"is-style-summary","startDate":"2026-12-04"} -->'
			. '<!-- wp:trip-itinerary/header /-->'
			. '<!-- wp:trip-itinerary/overview /-->'
			. $this->day( 'Sarajevo' ) . $this->day( 'Mostar' )
			. '<!-- /wp:trip-itinerary/itinerary -->'
		);

		$this->assertStringNotContainsString( '<article', $html, 'No days' );
		$this->assertStringContainsString( '4 – 5 Dec 2026', $html, 'The overview still counts them' );
		// Nothing to link to: the stops are plain text.
		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-header__stop">Sarajevo</span>', $html );
		$this->assertStringNotContainsString( 'href="#', $html );
	}

	public function test_site_font_is_off_until_turned_on() {
		$variable = '--wp--preset--font-family--trip-montserrat';

		delete_option( 'trip_itinerary_site_font' );
		wp_clean_theme_json_cache();
		$this->assertStringNotContainsString( $variable, wp_get_global_stylesheet() );

		update_option( 'trip_itinerary_site_font', true );
		wp_clean_theme_json_cache();
		$this->assertStringContainsString( $variable, wp_get_global_stylesheet() );
		$this->assertSame( "var({$variable})", wp_get_global_styles( array( 'typography', 'fontFamily' ) ) );
	}

	public function test_notifications_render_a_bell_with_the_unread_count() {
		$html = do_blocks(
			'<!-- wp:trip-itinerary/notifications -->'
			. '<!-- wp:trip-itinerary/notification {"title":"Your quote is ready","description":"Have a look.","date":"2026-11-12","url":"https://example.com/trip"} /-->'
			. '<!-- wp:trip-itinerary/notification {"title":"New message"} /-->'
			. '<!-- /wp:trip-itinerary/notifications -->'
		);

		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-notifications' ) ) );
		$context = json_decode( $processor->get_attribute( 'data-wp-context' ), true );
		$this->assertSame(
			array(
				trip_itinerary_notification_id( array( 'title' => 'Your quote is ready', 'description' => 'Have a look.', 'date' => '2026-11-12' ) ),
				trip_itinerary_notification_id( array( 'title' => 'New message' ) ),
			),
			$context['ids']
		);

		$this->assertTrue( $processor->next_tag( 'button' ) );
		$this->assertSame( 'Notifications, 2 unread', $processor->get_attribute( 'aria-label' ) );
		$panel = $processor->get_attribute( 'aria-controls' );
		$this->assertTrue( $processor->next_tag( array( 'class_name' => 'wp-block-trip-itinerary-notifications__panel' ) ) );
		$this->assertSame( $panel, $processor->get_attribute( 'id' ) );
		$this->assertTrue( $processor->get_attribute( 'hidden' ), 'Closed until the bell is clicked' );

		$this->assertStringContainsString( '<time datetime="2026-11-12">12 Nov 2026</time>', $html );
		$this->assertStringContainsString( 'href="https://example.com/trip"', $html );
		$this->assertSame( 2, substr_count( $html, 'class="wp-block-trip-itinerary-notification__mark"' ) );
	}

	public function test_notifications_without_any_show_an_empty_state() {
		$html = do_blocks( '<!-- wp:trip-itinerary/notifications /-->' );
		$this->assertStringContainsString( 'No new notifications', $html );
		$this->assertStringContainsString( 'aria-label="Notifications"', $html );
	}

	public function test_a_notification_is_unread_again_once_edited() {
		$original = array( 'title' => 'Your quote is ready', 'date' => '2026-11-12' );
		$this->assertSame( trip_itinerary_notification_id( $original ), trip_itinerary_notification_id( $original ) );
		$this->assertNotSame(
			trip_itinerary_notification_id( $original ),
			trip_itinerary_notification_id( array( 'title' => 'Your new quote is ready', 'date' => '2026-11-12' ) )
		);
	}

	public function test_user_menu_offers_log_in_to_visitors() {
		wp_set_current_user( 0 );
		$html = do_blocks( '<!-- wp:trip-itinerary/user-menu /-->' );

		$this->assertStringContainsString( 'is-logged-out', $html );
		$this->assertStringContainsString( 'Log in', $html );
		$this->assertStringContainsString( wp_login_url(), html_entity_decode( $html ) );
		$this->assertStringNotContainsString( 'Log out', $html );
	}

	public function test_user_menu_shows_the_logged_in_user() {
		$user = self::factory()->user->create(
			array(
				'first_name' => 'Alem',
				'last_name'  => 'Dinarević',
				'user_email' => 'alem@example.com',
			)
		);
		wp_set_current_user( $user );

		$html = do_blocks( '<!-- wp:trip-itinerary/user-menu {"links":"<a href=\u0022/profile/\u0022>Profile</a>"} /-->' );

		$this->assertStringContainsString( 'aria-label="Account menu for Alem Dinarević"', $html );
		$this->assertStringContainsString( '<span class="wp-block-trip-itinerary-user-menu__initial" aria-hidden="true">A</span>', $html );
		$this->assertStringContainsString( 'alem@example.com', $html );
		$this->assertStringContainsString( '<a href="/profile/">Profile</a>', $html );
		$this->assertStringContainsString( 'action=logout', $html );
		$this->assertStringContainsString( '_wpnonce=', $html, 'A real, nonce-protected log-out link' );
	}

	public function test_page_header_puts_notifications_and_user_menu_on_the_bar() {
		$html = do_blocks(
			'<!-- wp:trip-itinerary/page-header -->'
			. '<!-- wp:trip-itinerary/notifications /-->'
			. '<!-- wp:trip-itinerary/user-menu /-->'
			. '<!-- /wp:trip-itinerary/page-header -->'
		);

		$this->assertStringContainsString( 'has-actions', $html );
		$this->assertMatchesRegularExpression( '/page-header__actions">\s*<div[^>]*wp-block-trip-itinerary-notifications/', $html );
		$this->assertStringContainsString( 'wp-block-trip-itinerary-user-menu', $html );
	}

	/**
	 * A published page with a two-day trip.
	 *
	 * @param int $photo Overview photo.
	 * @return int Page ID.
	 */
	private function trip_page( $photo ) {
		return self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:trip-itinerary/itinerary {"title":"A weekend","startDate":"2026-12-04","travellers":2,"anchor":"bosnia"} -->'
					. '<!-- wp:trip-itinerary/overview {"background":{"id":' . $photo . '}} /-->'
					. $this->day( 'Sarajevo' ) . $this->day( 'Mostar' )
					. '<!-- wp:trip-itinerary/price {"amount":"€640"} /-->'
					. '<!-- /wp:trip-itinerary/itinerary -->',
			)
		);
	}

	/**
	 * Day block markup.
	 *
	 * @param string $location Day location.
	 * @return string
	 */
	private function day( $location ) {
		return '<!-- wp:trip-itinerary/day ' . wp_json_encode( array( 'location' => $location ) ) . " -->\n<!-- wp:paragraph -->\n<p>Text</p>\n<!-- /wp:paragraph -->\n<!-- /wp:trip-itinerary/day -->";
	}
}
