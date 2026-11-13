import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

export default function Edit( { attributes, setAttributes } ) {
	const { logo, links, credit } = attributes;

	// The site's name and logo, shown when no logo is picked, as on the site.
	const site = useSelect( ( select ) => {
		const { getEntityRecord, getMedia } = select( coreStore );
		const settings = getEntityRecord( 'root', 'site' );
		const logoMedia = settings?.site_logo
			? getMedia( settings.site_logo )
			: null;
		return {
			name: settings?.title,
			logoUrl: logoMedia?.source_url,
		};
	}, [] );

	const logoPicker = ( renderButton ) => (
		<MediaUploadCheck>
			<MediaUpload
				allowedTypes={ [ 'image' ] }
				value={ logo?.id }
				onSelect={ ( media ) =>
					setAttributes( {
						logo: {
							id: media.id,
							url: media.sizes?.medium?.url ?? media.url,
							alt: media.alt ?? '',
						},
					} )
				}
				render={ ( { open } ) => renderButton( open ) }
			/>
		</MediaUploadCheck>
	);
	const logoLabel = logo
		? __( 'Replace logo', 'trip-itinerary' )
		: __( 'Choose logo', 'trip-itinerary' );

	let logoPreview = (
		<span className="wp-block-trip-itinerary-footer__site-name">
			{ site.name }
		</span>
	);
	if ( logo?.url || site.logoUrl ) {
		logoPreview = (
			<img
				className="wp-block-trip-itinerary-footer__logo-image"
				src={ logo?.url ?? site.logoUrl }
				alt={ logo?.alt ?? '' }
			/>
		);
	}

	const blockProps = useBlockProps();

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					{ logoPicker( ( open ) => (
						<ToolbarButton onClick={ open }>
							{ logoLabel }
						</ToolbarButton>
					) ) }
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Logo', 'trip-itinerary' ) }>
					<p>
						{ __(
							"Links to the homepage. Without a logo, the footer uses the site's logo, or its name.",
							'trip-itinerary'
						) }
					</p>
					{ logoPicker( ( open ) => (
						<Button
							__next40pxDefaultSize
							variant="secondary"
							onClick={ open }
						>
							{ logoLabel }
						</Button>
					) ) }
					{ logo && (
						<Button
							__next40pxDefaultSize
							variant="tertiary"
							isDestructive
							onClick={ () =>
								setAttributes( { logo: undefined } )
							}
						>
							{ __( 'Use the site logo', 'trip-itinerary' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<footer { ...blockProps }>
				<span className="wp-block-trip-itinerary-footer__logo">
					{ logoPreview }
				</span>
				<RichText
					tagName="p"
					className="wp-block-trip-itinerary-footer__links"
					value={ links }
					onChange={ ( value ) => setAttributes( { links: value } ) }
					placeholder={ __(
						'Links, e.g. Contact, Privacy policy, Terms',
						'trip-itinerary'
					) }
					allowedFormats={ [ 'core/link' ] }
				/>
				<RichText
					tagName="p"
					className="wp-block-trip-itinerary-footer__credit"
					value={ credit }
					onChange={ ( value ) => setAttributes( { credit: value } ) }
					placeholder={ __(
						'Credit, e.g. Powered by…',
						'trip-itinerary'
					) }
					allowedFormats={ [ 'core/link' ] }
				/>
			</footer>
		</>
	);
}
