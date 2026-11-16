import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';

import { LogoPicker, LogoPreview, logoLabel } from '../shared/logo';

export default function Edit( { attributes, setAttributes } ) {
	const { logo, links, credit } = attributes;
	const setLogo = ( value ) => setAttributes( { logo: value } );

	const blockProps = useBlockProps();

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<LogoPicker logo={ logo } onChange={ setLogo }>
						{ ( open ) => (
							<ToolbarButton onClick={ open }>
								{ logoLabel( logo ) }
							</ToolbarButton>
						) }
					</LogoPicker>
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
					<LogoPicker logo={ logo } onChange={ setLogo }>
						{ ( open ) => (
							<Button
								__next40pxDefaultSize
								variant="secondary"
								onClick={ open }
							>
								{ logoLabel( logo ) }
							</Button>
						) }
					</LogoPicker>
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
					<LogoPreview
						logo={ logo }
						block="wp-block-trip-itinerary-footer"
					/>
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
