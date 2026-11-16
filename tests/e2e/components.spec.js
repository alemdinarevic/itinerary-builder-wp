/**
 * End-to-end tests for the generic components: the password field, the Page
 * header's menu on a phone, and the Itinerary card.
 */
const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Components', () => {
	test.afterAll( async ( { requestUtils } ) => {
		await requestUtils.deleteAllPages();
		await requestUtils.deleteAllPosts();
	} );

	test( 'the password field shows and hides what was typed', async ( {
		page,
		requestUtils,
	} ) => {
		const post = await requestUtils.createPost( {
			title: 'Field',
			status: 'publish',
			content:
				'<!-- wp:trip-itinerary/field {"label":"Password","type":"password"} /-->',
		} );
		await page.goto( post.link );

		const input = page.getByLabel( 'Password', { exact: true } );
		const toggle = page.getByRole( 'button', { name: 'Show password' } );

		await input.fill( 'correct horse' );
		await expect( input ).toHaveAttribute( 'type', 'password' );

		await toggle.click();
		await expect( input ).toHaveAttribute( 'type', 'text' );
		await expect(
			page.getByRole( 'button', { name: 'Hide password' } )
		).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( input ).toHaveValue( 'correct horse' );

		await page.getByRole( 'button', { name: 'Hide password' } ).click();
		await expect( input ).toHaveAttribute( 'type', 'password' );
	} );

	test( 'the page header folds its links into a menu on a phone', async ( {
		page,
		requestUtils,
	} ) => {
		await page.setViewportSize( { width: 375, height: 700 } );

		const post = await requestUtils.createPost( {
			title: 'Header',
			status: 'publish',
			content: `<!-- wp:trip-itinerary/page-header ${ JSON.stringify( {
				links: '<a href="#trips">Trips</a> <a href="#about">About</a>',
				buttonText: 'Plan a trip',
				buttonUrl: '#plan',
			} ) } /-->`,
		} );
		await page.goto( post.link );

		// Inside the block: the theme has its own "Open menu" button too.
		const header = page.locator( '.wp-block-trip-itinerary-page-header' );
		const menu = header.getByRole( 'button', { name: 'Open menu' } );
		const trips = header.getByRole( 'link', { name: 'Trips' } );

		await expect( trips ).toBeHidden();
		await menu.click();
		await expect( trips ).toBeVisible();
		await expect(
			header.getByRole( 'button', { name: 'Close menu' } )
		).toHaveAttribute( 'aria-expanded', 'true' );

		// Escape closes it and puts focus back on the button.
		await page.keyboard.press( 'Escape' );
		await expect( trips ).toBeHidden();
		await expect(
			header.getByRole( 'button', { name: 'Open menu' } )
		).toBeFocused();

		// The button stays on the bar.
		await expect(
			header.getByRole( 'link', { name: 'Plan a trip' } )
		).toBeVisible();
	} );

	test( 'notifications count down as they are read, and stay read', async ( {
		page,
		requestUtils,
	} ) => {
		const notification = ( title ) =>
			`<!-- wp:trip-itinerary/notification ${ JSON.stringify( {
				title,
				date: '2026-11-12',
			} ) } /-->`;
		const post = await requestUtils.createPost( {
			title: 'Notifications',
			status: 'publish',
			content: [
				'<!-- wp:trip-itinerary/notifications -->',
				notification( 'Your quote is ready' ),
				notification( 'New message' ),
				notification( 'Itinerary updated' ),
				'<!-- /wp:trip-itinerary/notifications -->',
			].join( '\n' ),
		} );
		await page.goto( post.link );

		const bell = page.getByRole( 'button', {
			name: 'Notifications, 3 unread',
		} );
		const panel = page.getByRole( 'region', { name: 'Notifications' } );

		await expect( panel ).toBeHidden();
		await bell.click();
		await expect( panel ).toBeVisible();

		await panel
			.getByRole( 'button', { name: 'Mark as read' } )
			.first()
			.click();
		await expect(
			page.getByRole( 'button', { name: 'Notifications, 2 unread' } )
		).toBeVisible();

		// A click elsewhere closes the panel.
		await page.locator( 'body' ).click( { position: { x: 5, y: 5 } } );
		await expect( panel ).toBeHidden();

		// Read stays read after a reload: it's remembered in the browser.
		await page.reload();
		await page
			.getByRole( 'button', { name: 'Notifications, 2 unread' } )
			.click();
		await panel.getByRole( 'button', { name: 'Mark all as read' } ).click();
		await expect(
			page.getByRole( 'button', { name: 'Notifications', exact: true } )
		).toBeVisible();
		await expect(
			panel.getByRole( 'button', { name: 'Mark as read' } )
		).toHaveCount( 0 );

		// Escape closes it and puts focus back on the bell.
		await page.keyboard.press( 'Escape' );
		await expect( panel ).toBeHidden();
		await expect(
			page.getByRole( 'button', { name: 'Notifications', exact: true } )
		).toBeFocused();
	} );

	test( 'the user menu shows the logged-in user and logs out', async ( {
		page,
		requestUtils,
	} ) => {
		const post = await requestUtils.createPost( {
			title: 'User menu',
			status: 'publish',
			content:
				'<!-- wp:trip-itinerary/user-menu {"links":"<a href=\\u0022#trips\\u0022>My itineraries</a>"} /-->',
		} );
		await page.goto( post.link );

		// The tests run logged in as admin. Inside the block: the admin
		// bar has its own "Log Out" link.
		const menu = page.locator( '.wp-block-trip-itinerary-user-menu' );
		await menu
			.getByRole( 'button', { name: 'Account menu for admin' } )
			.click();
		await expect( menu.getByText( 'wordpress@example.com' ) ).toBeVisible();
		await expect(
			menu.getByRole( 'link', { name: 'My itineraries' } )
		).toBeVisible();

		const logOut = menu.getByRole( 'link', { name: 'Log out' } );
		await expect( logOut ).toHaveAttribute( 'href', /action=logout/ );

		await page.keyboard.press( 'Escape' );
		await expect( logOut ).toBeHidden();
	} );

	test( 'the itinerary card shows a trip and links to it', async ( {
		page,
		requestUtils,
	} ) => {
		const day = ( location ) =>
			`<!-- wp:trip-itinerary/day ${ JSON.stringify( {
				location,
			} ) } -->\n<!-- wp:paragraph -->\n<p>Text</p>\n<!-- /wp:paragraph -->\n<!-- /wp:trip-itinerary/day -->`;

		const trip = await requestUtils.createPage( {
			title: 'Trip',
			status: 'publish',
			content: [
				'<!-- wp:trip-itinerary/itinerary {"title":"A weekend","startDate":"2026-12-04","travellers":2,"anchor":"bosnia"} -->',
				day( 'Sarajevo' ),
				day( 'Mostar' ),
				'<!-- wp:trip-itinerary/price {"amount":"€640"} /-->',
				'<!-- /wp:trip-itinerary/itinerary -->',
			].join( '\n' ),
		} );
		const listing = await requestUtils.createPage( {
			title: 'Trips',
			status: 'publish',
			content: `<!-- wp:trip-itinerary/card {"pageId":${ trip.id }} /-->`,
		} );

		await page.goto( listing.link );

		const card = page.locator( '.wp-block-trip-itinerary-card' );
		await expect( card ).toContainText( 'A weekend' );
		await expect( card ).toContainText( 'Sarajevo to Mostar' );
		await expect( card ).toContainText( '4 – 5 Dec 2026' );
		await expect( card ).toContainText( '2 days · 2 people' );

		await card.getByRole( 'link', { name: 'Request quotation' } ).click();
		await expect( page ).toHaveURL( /#bosnia-price$/ );
		await expect( page.locator( '#bosnia-price' ) ).toBeVisible();
	} );
} );
