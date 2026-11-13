import { slidePosition, wrapIndex } from '../carousel';

// Every photo's position, in order, for a carousel of `count` photos.
const positions = ( current, count ) =>
	Array.from( { length: count }, ( _, index ) =>
		slidePosition( index, current, count )
	);

describe( 'slidePosition', () => {
	it( 'shows a single photo in the centre', () => {
		expect( positions( 0, 1 ) ).toEqual( [ 'center' ] );
	} );

	it( 'puts the second of two photos on the right', () => {
		expect( positions( 0, 2 ) ).toEqual( [ 'center', 'right' ] );
		expect( positions( 1, 2 ) ).toEqual( [ 'right', 'center' ] );
	} );

	it( 'puts one neighbour on each side with three photos', () => {
		expect( positions( 0, 3 ) ).toEqual( [ 'center', 'right', 'left' ] );
	} );

	it( 'fills both sides with four photos, favouring the right', () => {
		expect( positions( 0, 4 ) ).toEqual( [
			'center',
			'right',
			'right-far',
			'left',
		] );
	} );

	it( 'shows two on each side and hides the rest', () => {
		expect( positions( 0, 7 ) ).toEqual( [
			'center',
			'right',
			'right-far',
			'hidden',
			'hidden',
			'left-far',
			'left',
		] );
	} );

	it( 'wraps around when the current photo is near the end', () => {
		expect( positions( 4, 5 ) ).toEqual( [
			'right',
			'right-far',
			'left-far',
			'left',
			'center',
		] );
	} );
} );

describe( 'wrapIndex', () => {
	it( 'goes from the first photo back to the last, and on to the first', () => {
		expect( wrapIndex( -1, 4 ) ).toBe( 3 );
		expect( wrapIndex( 4, 4 ) ).toBe( 0 );
		expect( wrapIndex( 2, 4 ) ).toBe( 2 );
	} );
} );
