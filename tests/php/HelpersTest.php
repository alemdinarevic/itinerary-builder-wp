<?php
/**
 * Tests for the date and summary helpers in includes/helpers.php.
 *
 * The summary cases match src/shared/test/summary.test.js, so the PHP
 * that renders the site and the JS that renders the editor agree.
 *
 * @package TripItinerary
 */

class HelpersTest extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		// A site behind UTC, where formatting a trip date in the site's
		// time zone would show the day before.
		update_option( 'timezone_string', 'Pacific/Honolulu' );
	}

	public function test_trip_date_counts_days_across_month_and_year_boundaries() {
		$this->assertSame( '2026-12-04', trip_itinerary_date( '2026-12-04', 0 )->format( 'Y-m-d' ) );
		$this->assertSame( '2026-12-01', trip_itinerary_date( '2026-11-29', 2 )->format( 'Y-m-d' ) );
		$this->assertSame( '2027-01-02', trip_itinerary_date( '2026-12-30', 3 )->format( 'Y-m-d' ) );
		$this->assertSame( '2028-02-29', trip_itinerary_date( '2028-02-28', 1 )->format( 'Y-m-d' ) );
	}

	/**
	 * @dataProvider data_invalid_start_dates
	 *
	 * @param mixed $start_date A start date that should be rejected.
	 */
	public function test_trip_date_rejects_missing_or_malformed_dates( $start_date ) {
		$this->assertNull( trip_itinerary_date( $start_date, 0 ) );
	}

	public function data_invalid_start_dates() {
		return array(
			'null'        => array( null ),
			'empty'       => array( '' ),
			'words'       => array( '4 Dec 2026' ),
			'short day'   => array( '2026-12-4' ),
			'slashes'     => array( '2026/12/04' ),
			'not a string' => array( 20261204 ),
		);
	}

	public function test_day_label_shows_number_and_date_in_utc() {
		$this->assertSame(
			'<p class="wp-block-trip-itinerary-day__label"><span class="wp-block-trip-itinerary-day__label-day">Day 2</span> <span class="wp-block-trip-itinerary-day__label-sep" aria-hidden="true">·</span> <time datetime="2026-12-05">Sat, 5 Dec</time></p>',
			trip_itinerary_day_label_html( 1, '2026-12-04' )
		);
	}

	public function test_day_label_without_a_start_date_shows_only_the_number() {
		$this->assertSame(
			'<p class="wp-block-trip-itinerary-day__label"><span class="wp-block-trip-itinerary-day__label-day">Day 1</span></p>',
			trip_itinerary_day_label_html( 0 )
		);
	}

	public function test_summary_is_empty_when_there_is_nothing_to_summarise() {
		$this->assertSame( array(), trip_itinerary_summary_items( '', 0, array() ) );
		$this->assertSame( array(), trip_itinerary_summary_items( '2026-12-04', 0, array() ) );
	}

	public function test_summary_shows_dates_travellers_and_where_the_trip_starts() {
		$this->assertSame(
			array(
				'dates'      => '4 – 6 Dec 2026 (3 days)',
				'travellers' => '2 people',
				'from'       => 'from Sarajevo',
			),
			$this->summary( '2026-12-04', 2, array( 'Sarajevo', 'Trebević', 'Mostar' ) )
		);
	}

	/**
	 * @dataProvider data_date_ranges
	 *
	 * @param string $start_date First day.
	 * @param int    $day_count  Number of days.
	 * @param string $expected   Expected "dates" item.
	 */
	public function test_summary_formats_date_ranges( $start_date, $day_count, $expected ) {
		$summary = $this->summary( $start_date, 0, array_fill( 0, $day_count, '' ) );
		$this->assertSame( $expected, $summary['dates'] );
	}

	public function data_date_ranges() {
		return array(
			'one day'       => array( '2026-12-04', 1, '4 Dec 2026 (1 day)' ),
			'same month'    => array( '2026-12-04', 3, '4 – 6 Dec 2026 (3 days)' ),
			'two months'    => array( '2026-11-29', 4, '29 Nov – 2 Dec 2026 (4 days)' ),
			'two years'     => array( '2026-12-30', 4, '30 Dec 2026 – 2 Jan 2027 (4 days)' ),
		);
	}

	public function test_summary_uses_the_singular_for_one_traveller() {
		$this->assertSame( array( 'travellers' => '1 person' ), $this->summary( '', 1, array() ) );
	}

	public function test_day_marker_shows_the_day_number_hidden_from_screen_readers() {
		$this->assertSame(
			'<span class="wp-block-trip-itinerary-day__marker" aria-hidden="true">3</span>',
			trip_itinerary_day_marker_html( 2 )
		);
	}

	public function test_stops_list_each_place_once_in_order() {
		$this->assertSame(
			array(
				array(
					'name'     => 'Sarajevo',
					'dayIndex' => 0,
				),
				array(
					'name'     => 'Mostar',
					'dayIndex' => 1,
				),
				array(
					'name'     => 'Blagaj',
					'dayIndex' => 3,
				),
			),
			trip_itinerary_stops( array( 'Sarajevo', 'Mostar', 'Sarajevo', 'Blagaj' ) )
		);
	}

	public function test_stops_skip_empty_days_and_ignore_capitalisation() {
		$this->assertSame(
			array(
				array(
					'name'     => 'Mostar',
					'dayIndex' => 1,
				),
				array(
					'name'     => 'Trebević',
					'dayIndex' => 3,
				),
			),
			trip_itinerary_stops( array( ' ', 'Mostar', 'mostar ', 'Trebević', 'TREBEVIĆ' ) )
		);
	}

	public function test_summary_starts_from_the_first_day_with_a_place() {
		$summary = $this->summary( '', 0, array( '', ' Mostar ', 'Blagaj' ) );
		$this->assertSame( 'from Mostar', $summary['from'] );
	}

	public function test_map_stops_use_the_first_day_with_a_position() {
		$this->assertSame(
			array(
				array(
					'name'     => 'Sarajevo',
					'dayIndex' => 0,
					'number'   => 1,
					'lat'      => 43.86,
					'lng'      => 18.43,
				),
				array(
					'name'     => 'Mostar',
					'dayIndex' => 1,
					'number'   => 2,
					'lat'      => 43.34,
					'lng'      => 17.81,
				),
			),
			trip_itinerary_map_stops(
				array( 'Sarajevo', 'Mostar', 'Sarajevo' ),
				array( null, array( 43.34, 17.81 ), array( 43.86, 18.43 ) )
			)
		);
	}

	public function test_coordinates_accept_valid_positions_only() {
		$this->assertSame( array( 43.86, 18.43 ), trip_itinerary_coordinates( array( 'lat' => 43.86, 'lng' => 18.43 ) ) );
		$this->assertNull( trip_itinerary_coordinates( array() ) );
		$this->assertNull( trip_itinerary_coordinates( array( 'lat' => 91, 'lng' => 18 ) ) );
		$this->assertNull( trip_itinerary_coordinates( array( 'lat' => 'north', 'lng' => 18 ) ) );
	}

	public function test_map_tiles_can_be_changed_with_a_filter() {
		$this->assertStringContainsString( 'tile.openstreetmap.org', trip_itinerary_map_tiles()['url'] );

		$tiles = static function () {
			return array(
				'url'         => 'https://tiles.example.com/{z}/{x}/{y}.png',
				'attribution' => 'Example',
			);
		};
		add_filter( 'trip_itinerary_map_tiles', $tiles );
		$this->assertSame( 'https://tiles.example.com/{z}/{x}/{y}.png', trip_itinerary_map_tiles()['url'] );
		remove_filter( 'trip_itinerary_map_tiles', $tiles );
	}

	public function test_plain_text_strips_markup_and_decodes_entities() {
		$this->assertSame( 'Rock & Roll café', trip_itinerary_plain_text( '<strong>Rock &amp; Roll</strong> caf&eacute;' ) );
		$this->assertSame( '', trip_itinerary_plain_text( null ) );
	}

	/**
	 * The summary as key => "value (detail)", like values() in the JS test.
	 *
	 * @param string   $start_date Trip start.
	 * @param int      $travellers Travellers.
	 * @param string[] $locations  Day locations.
	 * @return array
	 */
	private function summary( $start_date, $travellers, array $locations ) {
		$values = array();
		foreach ( trip_itinerary_summary_items( $start_date, $travellers, $locations ) as $item ) {
			$values[ $item['key'] ] = isset( $item['detail'] ) ? "{$item['value']} ({$item['detail']})" : $item['value'];
		}
		return $values;
	}
}
