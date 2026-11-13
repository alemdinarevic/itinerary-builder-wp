import { getSettings, setSettings } from '@wordpress/date';

import { formatTripDate, toIsoDate, tripDate } from '../dates';

// The test script also runs Jest with the computer's clock 13 hours ahead
// of UTC, where building dates in local time would land on the wrong day.
describe( 'tripDate', () => {
	it( 'returns the start date for the first day', () => {
		expect( toIsoDate( tripDate( '2026-12-04', 0 ) ) ).toBe( '2026-12-04' );
	} );

	it( 'counts days across month and year boundaries', () => {
		expect( toIsoDate( tripDate( '2026-11-29', 2 ) ) ).toBe( '2026-12-01' );
		expect( toIsoDate( tripDate( '2026-12-30', 3 ) ) ).toBe( '2027-01-02' );
	} );

	it( 'handles leap years', () => {
		expect( toIsoDate( tripDate( '2028-02-28', 1 ) ) ).toBe( '2028-02-29' );
		expect( toIsoDate( tripDate( '2027-02-28', 1 ) ) ).toBe( '2027-03-01' );
	} );

	it.each( [ undefined, '', '4 Dec 2026', '2026-12-4', '2026/12/04' ] )(
		'returns null for a missing or malformed start date (%p)',
		( startDate ) => {
			expect( tripDate( startDate, 0 ) ).toBeNull();
		}
	);
} );

describe( 'formatTripDate', () => {
	// A site set to a time zone behind UTC. Formatting a trip date in the
	// site's time zone would show the day before.
	const original = getSettings();
	beforeAll( () => {
		setSettings( {
			...original,
			timezone: { offset: -10, string: 'Pacific/Honolulu', abbr: 'HST' },
		} );
	} );
	afterAll( () => setSettings( original ) );

	it( "formats the calendar date regardless of the site's time zone", () => {
		expect( formatTripDate( tripDate( '2026-12-04', 0 ), 'D, j M' ) ).toBe(
			'Fri, 4 Dec'
		);
		expect( formatTripDate( tripDate( '2026-12-31', 0 ), 'j M Y' ) ).toBe(
			'31 Dec 2026'
		);
	} );
} );
