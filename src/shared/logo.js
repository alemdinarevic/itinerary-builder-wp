/**
 * The logo picker and preview used by the Trip footer and the Page header.
 * The preview falls back like trip_itinerary_logo_html() in PHP: the picked
 * image, else the site logo, else the site's name.
 */
import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

/**
 * @param {Object} props
 * @param {Object} props.logo  The stored logo: id, url, alt.
 * @param {string} props.block The block's class, such as "wp-block-trip-itinerary-footer".
 */
export function LogoPreview( { logo, block } ) {
	const site = useSelect( ( select ) => {
		const { getEntityRecord, getMedia } = select( coreStore );
		const settings = getEntityRecord( 'root', 'site' );
		const media = settings?.site_logo
			? getMedia( settings.site_logo )
			: null;
		return { name: settings?.title, logoUrl: media?.source_url };
	}, [] );

	const url = logo?.url ?? site.logoUrl;
	if ( url ) {
		return (
			<img
				className={ `${ block }__logo-image` }
				src={ url }
				alt={ logo?.alt ?? '' }
			/>
		);
	}
	return <span className={ `${ block }__site-name` }>{ site.name }</span>;
}

/**
 * Opens the media library to pick a logo.
 *
 * @param {Object}   props
 * @param {Object}   props.logo     The stored logo.
 * @param {Function} props.onChange Called with the new logo.
 * @param {Function} props.children Renders the button, given an `open` function.
 */
export function LogoPicker( { logo, onChange, children } ) {
	return (
		<MediaUploadCheck>
			<MediaUpload
				allowedTypes={ [ 'image' ] }
				value={ logo?.id }
				onSelect={ ( media ) =>
					onChange( {
						id: media.id,
						url: media.sizes?.medium?.url ?? media.url,
						alt: media.alt ?? '',
					} )
				}
				render={ ( { open } ) => children( open ) }
			/>
		</MediaUploadCheck>
	);
}

/**
 * @param {Object} logo The stored logo, if any.
 * @return {string} The picker button's label.
 */
export function logoLabel( logo ) {
	return logo
		? __( 'Replace logo', 'trip-itinerary' )
		: __( 'Choose logo', 'trip-itinerary' );
}
