import { getTripSummary } from '../summary';

// The same cases are checked for the PHP version in
// tests/php/SummaryTest.php, so the editor and the site agree.

const values = ( items ) =>
	Object.fromEntries(
		items.map( ( { key, value, detail } ) => [
			key,
			detail ? `${ value } (${ detail })` : value,
		] )
	);

describe( 'getTripSummary', () => {
	it( 'is empty when there is nothing to summarise', () => {
		expect( getTripSummary( { locations: [] } ) ).toEqual( [] );
		expect(
			getTripSummary( { startDate: '2026-12-04', locations: [] } )
		).toEqual( [] );
	} );

	it( 'shows dates, travellers and where the trip starts', () => {
		const items = getTripSummary( {
			startDate: '2026-12-04',
			travellers: 2,
			locations: [ 'Sarajevo', 'Trebević', 'Mostar' ],
		} );

		expect( items.map( ( item ) => item.key ) ).toEqual( [
			'dates',
			'travellers',
			'from',
		] );
		expect( values( items ) ).toEqual( {
			dates: '4 – 6 Dec 2026 (3 days)',
			travellers: '2 people',
			from: 'from Sarajevo',
		} );
	} );

	it.each( [
		[ '2026-12-04', 1, '4 Dec 2026 (1 day)' ],
		[ '2026-12-04', 3, '4 – 6 Dec 2026 (3 days)' ],
		[ '2026-11-29', 4, '29 Nov – 2 Dec 2026 (4 days)' ],
		[ '2026-12-30', 4, '30 Dec 2026 – 2 Jan 2027 (4 days)' ],
	] )(
		'formats the dates of a trip starting %s with %d days',
		( startDate, dayCount, expected ) => {
			const items = getTripSummary( {
				startDate,
				locations: Array( dayCount ).fill( '' ),
			} );
			expect( values( items ).dates ).toBe( expected );
		}
	);

	it( 'uses the singular for one traveller', () => {
		const items = getTripSummary( { travellers: 1, locations: [] } );
		expect( values( items ).travellers ).toBe( '1 person' );
	} );

	it( 'starts from the first day with a place', () => {
		const items = getTripSummary( {
			locations: [ '', ' Mostar ', 'Blagaj' ],
		} );
		expect( values( items ).from ).toBe( 'from Mostar' );
	} );
} );
