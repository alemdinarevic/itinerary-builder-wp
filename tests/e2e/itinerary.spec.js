/**
 * End-to-end tests: build trips in the real editor, then check what
 * visitors see on the site.
 */
const path = require( 'path' );
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

const PHOTOS = path.join( __dirname, '../../demo/photos' );

test.describe( 'Trip itinerary', () => {
	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPages();
		await requestUtils.deleteAllPosts();
		await requestUtils.deleteAllMedia();
	} );

	test( 'builds a trip in the editor and shows it on the site', async ( {
		admin,
		editor,
		page,
	} ) => {
		await admin.createNewPost( { postType: 'page', title: 'E2E trip' } );

		await editor.insertBlock( {
			name: 'trip-itinerary/itinerary',
			attributes: { title: 'Weekend away' },
			innerBlocks: [
				{ name: 'trip-itinerary/overview' },
				{
					name: 'trip-itinerary/day',
					attributes: { title: 'Arrival', location: 'Sarajevo' },
				},
				{
					name: 'trip-itinerary/day',
					attributes: { title: 'Day trip', location: 'Mostar' },
				},
			],
		} );

		// Set the trip details in the block sidebar, as an editor would.
		await editor.selectBlocks(
			editor.canvas.locator( '[data-type="trip-itinerary/itinerary"]' )
		);
		await editor.openDocumentSettingsSidebar();
		await page.getByLabel( 'Start date' ).fill( '2026-12-30' );
		await page.getByLabel( 'Travellers' ).fill( '2' );

		// The editor shows the dates and overview straight away.
		const labels = editor.canvas.locator(
			'.wp-block-trip-itinerary-day__label'
		);
		await expect( labels ).toHaveText( [
			'Day 1 · Wed, 30 Dec',
			'Day 2 · Thu, 31 Dec',
		] );
		const overview = editor.canvas.locator(
			'.wp-block-trip-itinerary-overview'
		);
		await expect( overview ).toContainText( '30 – 31 Dec 2026' );
		await expect(
			overview.locator( '.wp-block-trip-itinerary-overview__place' )
		).toHaveText( [ 'Sarajevo', 'Mostar' ] );

		// The "Add day" button adds a day after the last one.
		await editor.canvas
			.locator( '.wp-block-trip-itinerary-itinerary__days' )
			.getByRole( 'button', { name: 'Add day' } )
			.click();
		await expect( labels ).toHaveCount( 3 );
		await expect( labels.last() ).toHaveText( 'Day 3 · Fri, 1 Jan' );
		await expect( overview ).toContainText( '30 Dec 2026 – 1 Jan 2027' );

		const pageId = await editor.publishPost();
		await page.goto( `/?page_id=${ pageId }` );

		await expect(
			page.locator( '.wp-block-trip-itinerary-day__label' )
		).toHaveText( [
			'Day 1 · Wed, 30 Dec',
			'Day 2 · Thu, 31 Dec',
			'Day 3 · Fri, 1 Jan',
		] );
		const siteOverview = page.locator(
			'.wp-block-trip-itinerary-overview'
		);
		await expect( siteOverview ).toContainText(
			'30 Dec 2026 – 1 Jan 2027'
		);
		await expect( siteOverview ).toContainText( '2 people' );
		await expect(
			siteOverview.locator( '.wp-block-trip-itinerary-overview__place' )
		).toHaveText( [ 'Sarajevo', 'Mostar', 'Day 3' ] );
	} );

	test( 'the header links to each stop and its button to the price', async ( {
		page,
		requestUtils,
	} ) => {
		const day = ( title, location ) =>
			`<!-- wp:trip-itinerary/day ${ JSON.stringify( {
				title,
				location,
			} ) } -->\n<!-- wp:paragraph -->\n<p>${ 'Text. '.repeat(
				120
			) }</p>\n<!-- /wp:paragraph -->\n<!-- /wp:trip-itinerary/day -->`;

		const post = await requestUtils.createPost( {
			title: 'Header',
			status: 'publish',
			content: [
				'<!-- wp:trip-itinerary/itinerary {"anchor":"bosnia"} -->',
				'<!-- wp:trip-itinerary/header {"buttonText":"Request a quote"} /-->',
				day( 'Old town', 'Sarajevo' ),
				day( 'Mountain', 'Trebević' ),
				day( 'Old Bridge', 'Mostar' ),
				'<!-- wp:trip-itinerary/price {"amount":"€640","per":"/ person"} /-->',
				'<!-- /wp:trip-itinerary/itinerary -->',
				'<!-- wp:trip-itinerary/footer {"credit":"Built with Trip Itinerary"} /-->',
			].join( '\n' ),
		} );

		await page.goto( post.link );

		const header = page.getByRole( 'navigation', { name: 'Trip stops' } );
		await expect( header.getByRole( 'link' ) ).toHaveText( [
			'Sarajevo',
			'Trebević',
			'Mostar',
			'Request a quote →',
		] );

		// A stop scrolls its day into view, below the header that stays on top.
		await header.getByRole( 'link', { name: 'Mostar' } ).click();
		await expect( page ).toHaveURL( /#bosnia-day-3$/ );
		const day3 = page.locator( '#bosnia-day-3' );
		await expect( day3 ).toBeInViewport();
		const headerBox = await header.boundingBox();
		await expect
			.poll( async () => ( await day3.boundingBox() ).y )
			.toBeGreaterThanOrEqual( headerBox.y + headerBox.height );

		// The button goes to the price, which is highlighted on arrival. The
		// tests run with reduced motion, where the highlight is a steady ring
		// rather than an animation.
		await header.getByRole( 'link', { name: /Request a quote/ } ).click();
		await expect( page ).toHaveURL( /#bosnia-price$/ );
		const price = page.locator( '#bosnia-price' );
		await expect( price ).toBeInViewport();
		await expect(
			price.locator( '.wp-block-trip-itinerary-price__card' )
		).toHaveCSS( 'box-shadow', /0px 0px 0px 3px/ );

		await expect(
			page.locator( '.wp-block-trip-itinerary-footer' )
		).toContainText( 'Built with Trip Itinerary' );
	} );

	test( 'the photo carousel responds to arrows, dots, clicks and keys', async ( {
		page,
		requestUtils,
	} ) => {
		const photos = [];
		for ( const file of [
			'trebevic-1',
			'trebevic-2',
			'trebevic-3',
			'trebevic-4',
		] ) {
			const media = await requestUtils.uploadMedia(
				path.join( PHOTOS, `${ file }.jpg` )
			);
			photos.push( { id: media.id, url: media.source_url, alt: file } );
		}

		const day = `<!-- wp:trip-itinerary/day ${ JSON.stringify( {
			title: 'Mostar',
			photos,
		} ) } -->\n<!-- wp:paragraph -->\n<p>Text</p>\n<!-- /wp:paragraph -->\n<!-- /wp:trip-itinerary/day -->`;
		const post = await requestUtils.createPost( {
			title: 'Carousel',
			status: 'publish',
			content: `<!-- wp:trip-itinerary/itinerary -->\n${ day }\n<!-- /wp:trip-itinerary/itinerary -->`,
		} );

		await page.goto( post.link );

		const carousel = page.getByRole( 'region', {
			name: 'Photos from day 1',
		} );
		const slides = carousel.locator(
			'.wp-block-trip-itinerary-carousel__slide'
		);
		const positions = () =>
			slides.evaluateAll( ( elements ) =>
				elements.map( ( element ) => element.dataset.position )
			);

		await expect
			.poll( positions )
			.toEqual( [ 'center', 'right', 'right-far', 'left' ] );

		await carousel.getByRole( 'button', { name: 'Next photo' } ).click();
		await expect
			.poll( positions )
			.toEqual( [ 'left', 'center', 'right', 'right-far' ] );
		await expect(
			carousel.getByRole( 'button', { name: 'Show photo 2' } )
		).toHaveAttribute( 'aria-current', 'true' );

		await carousel.getByRole( 'button', { name: 'Show photo 4' } ).click();
		await expect( slides.nth( 3 ) ).toHaveAttribute(
			'data-position',
			'center'
		);

		// Clicking a photo at the side brings it to the centre. Most of it is
		// behind the centre photo, so click the part that shows, on the right.
		const side = await slides.nth( 0 ).boundingBox();
		await slides.nth( 0 ).click( {
			position: { x: side.width - 8, y: side.height / 2 },
		} );
		await expect( slides.nth( 0 ) ).toHaveAttribute(
			'data-position',
			'center'
		);

		// Arrow keys work while focus is in the carousel, and wrap around.
		await carousel.getByRole( 'button', { name: 'Show photo 1' } ).focus();
		await page.keyboard.press( 'ArrowLeft' );
		await expect( slides.nth( 3 ) ).toHaveAttribute(
			'data-position',
			'center'
		);
	} );

	test( 'upgrades content saved by version 0.1 without errors', async ( {
		admin,
		editor,
		page,
		requestUtils,
	} ) => {
		const legacy = `<!-- wp:trip-itinerary/itinerary -->
<section class="wp-block-trip-itinerary-itinerary"><h2 class="wp-block-trip-itinerary-itinerary__title">Old trip</h2><ol class="wp-block-trip-itinerary-itinerary__days"><!-- wp:trip-itinerary/day -->
<li class="wp-block-trip-itinerary-day"><h3 class="wp-block-trip-itinerary-day__title">Old day</h3><div class="wp-block-trip-itinerary-day__content"><!-- wp:paragraph -->
<p>Text</p>
<!-- /wp:paragraph --></div></li>
<!-- /wp:trip-itinerary/day --></ol></section>
<!-- /wp:trip-itinerary/itinerary -->`;
		const post = await requestUtils.createPost( {
			title: 'Legacy',
			status: 'publish',
			content: legacy,
		} );

		const errors = [];
		page.on( 'pageerror', ( error ) => errors.push( error.message ) );

		await admin.editPost( post.id );

		// No "This block contains unexpected or invalid content" warnings,
		// and the titles were read from the old markup.
		await expect(
			editor.canvas.locator( '.block-editor-warning' )
		).toHaveCount( 0 );
		await expect(
			editor.canvas.locator( '.wp-block-trip-itinerary-day__title' )
		).toHaveText( 'Old day' );

		const blocks = await editor.getBlocks();
		expect( blocks[ 0 ].attributes.title ).toBe( 'Old trip' );
		expect( blocks[ 0 ].innerBlocks[ 0 ].attributes.title ).toBe(
			'Old day'
		);
		expect( errors ).toEqual( [] );
	} );
} );
