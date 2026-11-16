import { __, sprintf } from '@wordpress/i18n';
import { PanelBody, Button, TextControl, Notice } from '@wordpress/components';
import { useState } from '@wordpress/element';

import { toPlainText } from '../shared/plain-text';

/**
 * Looks a place up with OpenStreetMap's Nominatim search. Runs only in the
 * editor, when someone clicks "Find on map", never on the site.
 *
 * @see https://operations.osmfoundation.org/policies/nominatim/
 *
 * @param {string} query Place name, such as "Mostar".
 * @return {Promise<{lat: number, lng: number, label: string}|null>} The best match, or null.
 */
async function findPlace( query ) {
	const url = new URL( 'https://nominatim.openstreetmap.org/search' );
	url.search = new URLSearchParams( {
		q: query,
		format: 'jsonv2',
		limit: '1',
	} );
	const response = await window.fetch( url, {
		headers: { 'Accept-Language': document.documentElement.lang || 'en' },
	} );
	if ( ! response.ok ) {
		throw new Error( response.statusText );
	}
	const [ match ] = await response.json();
	return match
		? {
				lat: Number( match.lat ),
				lng: Number( match.lon ),
				label: match.display_name,
			}
		: null;
}

/**
 * Rounds to 5 decimals, about a metre: plenty for a map pin.
 *
 * @param {number} value
 * @return {number} Rounded value.
 */
const round = ( value ) => Math.round( value * 1e5 ) / 1e5;

/**
 * The Day's "Map" panel: where its stop goes on the Trip overview's map.
 *
 * @param {Object}   props
 * @param {Object}   props.attributes    The Day's attributes.
 * @param {Function} props.setAttributes Updates them.
 */
export default function MapPanel( { attributes, setAttributes } ) {
	const { lat, lng } = attributes;
	const location = toPlainText( attributes.location ).trim();
	const [ status, setStatus ] = useState( null );

	const find = async () => {
		setStatus( {
			type: 'info',
			message: __( 'Searching…', 'trip-itinerary' ),
		} );
		try {
			const place = await findPlace( location );
			if ( ! place ) {
				setStatus( {
					type: 'warning',
					message: sprintf(
						/* translators: %s: place name. */
						__( 'Couldn’t find “%s”.', 'trip-itinerary' ),
						location
					),
				} );
				return;
			}
			setAttributes( {
				lat: round( place.lat ),
				lng: round( place.lng ),
			} );
			setStatus( { type: 'success', message: place.label } );
		} catch {
			setStatus( {
				type: 'error',
				message: __(
					'The search didn’t work. Try again, or enter the coordinates.',
					'trip-itinerary'
				),
			} );
		}
	};

	const coordinate = ( key, label ) => (
		<TextControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			type="number"
			step="any"
			label={ label }
			value={ attributes[ key ] ?? '' }
			onChange={ ( value ) => {
				const number = parseFloat( value );
				setAttributes( {
					[ key ]: Number.isFinite( number ) ? number : undefined,
				} );
			} }
		/>
	);

	return (
		<PanelBody
			title={ __( 'Map', 'trip-itinerary' ) }
			initialOpen={ false }
		>
			<p>
				{ __(
					'Where this day’s place goes on the Trip overview’s map.',
					'trip-itinerary'
				) }
			</p>
			<Button
				__next40pxDefaultSize
				variant="secondary"
				onClick={ find }
				disabled={ ! location }
				accessibleWhenDisabled
			>
				{ location
					? sprintf(
							/* translators: %s: place name. */
							__( 'Find “%s” on the map', 'trip-itinerary' ),
							location
						)
					: __( 'Add a location to find it', 'trip-itinerary' ) }
			</Button>
			{ status && (
				<Notice status={ status.type } isDismissible={ false }>
					{ status.message }
				</Notice>
			) }
			<div style={ { display: 'grid', gap: 12, marginTop: 16 } }>
				{ coordinate( 'lat', __( 'Latitude', 'trip-itinerary' ) ) }
				{ coordinate( 'lng', __( 'Longitude', 'trip-itinerary' ) ) }
			</div>
			{ lat !== undefined && lng !== undefined && (
				<Button
					variant="link"
					isDestructive
					onClick={ () => {
						setAttributes( { lat: undefined, lng: undefined } );
						setStatus( null );
					} }
				>
					{ __( 'Remove from the map', 'trip-itinerary' ) }
				</Button>
			) }
		</PanelBody>
	);
}
