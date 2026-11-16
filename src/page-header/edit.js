import { __ } from '@wordpress/i18n';
import {
	BlockControls,
	InnerBlocks,
	InspectorControls,
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextControl,
	ToggleControl,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';

import { LogoPicker, LogoPreview, logoLabel } from '../shared/logo';

const BLOCK = 'wp-block-trip-itinerary-page-header';

// The blocks that can sit on the bar, like Offtryp's bell and avatar.
const ACTIONS = [ 'trip-itinerary/notifications', 'trip-itinerary/user-menu' ];

export default function Edit( { attributes, setAttributes } ) {
	const { logo, links, buttonText, buttonUrl, sticky } = attributes;
	const setLogo = ( value ) => setAttributes( { logo: value } );

	// Not sticky in the editor, where it would cover the blocks below it.
	const blockProps = useBlockProps( { className: 'has-links' } );
	const actionsProps = useInnerBlocksProps(
		{ className: `${ BLOCK }__actions` },
		{
			allowedBlocks: ACTIONS,
			orientation: 'horizontal',
			renderAppender: InnerBlocks.ButtonBlockAppender,
		}
	);

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
				<PanelBody title={ __( 'Page header', 'trip-itinerary' ) }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Stay at the top while scrolling',
							'trip-itinerary'
						) }
						checked={ sticky }
						onChange={ ( value ) =>
							setAttributes( { sticky: value } )
						}
					/>
					<div style={ { height: 16 } } />
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="url"
						label={ __( 'Button link', 'trip-itinerary' ) }
						help={ __(
							'The button shows on the site once it has text and a link.',
							'trip-itinerary'
						) }
						value={ buttonUrl ?? '' }
						onChange={ ( value ) =>
							setAttributes( { buttonUrl: value || undefined } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Logo', 'trip-itinerary' ) }>
					<p>
						{ __(
							"Links to the homepage. Without a logo, the header uses the site's logo, or its name.",
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
							onClick={ () => setLogo( undefined ) }
						>
							{ __( 'Use the site logo', 'trip-itinerary' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<header { ...blockProps }>
				<span className={ `${ BLOCK }__logo` }>
					<LogoPreview logo={ logo } block={ BLOCK } />
				</span>
				<div className={ `${ BLOCK }__nav` }>
					<RichText
						tagName="p"
						className={ `${ BLOCK }__links` }
						value={ links }
						onChange={ ( value ) =>
							setAttributes( { links: value } )
						}
						placeholder={ __(
							'Links, e.g. Trips, About, Contact',
							'trip-itinerary'
						) }
						allowedFormats={ [ 'core/link' ] }
					/>
				</div>
				<div { ...actionsProps } />
				<div
					className={ `wp-block-button is-style-trip-secondary-dark ${ BLOCK }__button` }
				>
					<RichText
						tagName="span"
						className="wp-block-button__link wp-element-button"
						value={ buttonText }
						onChange={ ( value ) =>
							setAttributes( { buttonText: value } )
						}
						placeholder={ __( 'Button', 'trip-itinerary' ) }
						allowedFormats={ [] }
						withoutInteractiveFormatting
					/>
				</div>
			</header>
		</>
	);
}
