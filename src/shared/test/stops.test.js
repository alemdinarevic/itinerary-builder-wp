import { getStops } from '../stops';

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
