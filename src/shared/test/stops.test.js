import { getCoordinates, getMapStops, getStops } from '../stops';

// The same cases are checked in PHP in tests/php/HelpersTest.php.

describe( 'getStops', () => {
	it( 'lists each place once, in the order it is first visited', () => {
		expect(
			getStops( [ 'Sarajevo', 'Mostar', 'Sarajevo', 'Blagaj' ] )
		).toEqual( [
			{ name: 'Sarajevo', dayIndex: 0 },
			{ name: 'Mostar', dayIndex: 1 },
			{ name: 'Blagaj', dayIndex: 3 },
		] );
	} );

	it( 'skips days without a location', () => {
		expect( getStops( [ '', ' ', 'Mostar' ] ) ).toEqual( [
			{ name: 'Mostar', dayIndex: 2 },
		] );
	} );

	it( 'treats different capitalisation as the same place', () => {
		expect(
			getStops( [ 'Mostar', 'mostar ', 'Trebević', 'TREBEVIĆ' ] )
		).toEqual( [
			{ name: 'Mostar', dayIndex: 0 },
			{ name: 'Trebević', dayIndex: 2 },
		] );
	} );

	it( 'is empty without locations', () => {
		expect( getStops( [] ) ).toEqual( [] );
	} );
} );

describe( 'getMapStops', () => {
	it( 'numbers stops in order and places each at its first day with a position', () => {
		expect(
			getMapStops(
				[ 'Sarajevo', 'Mostar', 'Sarajevo' ],
				[ null, [ 43.34, 17.81 ], [ 43.86, 18.43 ] ]
			)
		).toEqual( [
			{
				name: 'Sarajevo',
				dayIndex: 0,
				number: 1,
				lat: 43.86,
				lng: 18.43,
			},
			{ name: 'Mostar', dayIndex: 1, number: 2, lat: 43.34, lng: 17.81 },
		] );
	} );

	it( 'leaves stops without a position off the map, keeping their numbers', () => {
		expect(
			getMapStops( [ 'Sarajevo', 'Mostar' ], [ null, [ 43.34, 17.81 ] ] )
		).toEqual( [
			{ name: 'Mostar', dayIndex: 1, number: 2, lat: 43.34, lng: 17.81 },
		] );
	} );
} );

describe( 'getCoordinates', () => {
	it( 'accepts valid positions only', () => {
		expect( getCoordinates( { lat: 43.86, lng: 18.43 } ) ).toEqual( [
			43.86, 18.43,
		] );
		expect( getCoordinates( {} ) ).toBeNull();
		expect( getCoordinates( { lat: '43', lng: 18 } ) ).toBeNull();
		expect( getCoordinates( { lat: 91, lng: 18 } ) ).toBeNull();
	} );
} );
