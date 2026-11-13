import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	store as blockEditorStore,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

import PinIcon from '../shared/pin-icon';
import { getStops } from '../shared/stops';
import { toPlainText } from '../shared/plain-text';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { buttonText, buttonUrl } = attributes;

	// The other blocks in the same Itinerary, like the Trip overview.
	const siblings = useSelect(
		( select ) => {
			const { getBlockRootClientId, getBlocks } =
				select( blockEditorStore );
			return getBlocks( getBlockRootClientId( clientId ) );
		},
		[ clientId ]
	);

	const stops = useMemo(
		() =>
			getStops(
				siblings
					.filter( ( block ) => block.name === 'trip-itinerary/day' )
					.map( ( day ) => toPlainText( day.attributes.location ) )
			),
		[ siblings ]
	);

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Button', 'trip-itinerary' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="url"
						label={ __( 'Button link', 'trip-itinerary' ) }
						help={ __(
							'Leave empty to scroll to the Trip price block.',
							'trip-itinerary'
						) }
						value={ buttonUrl ?? '' }
						onChange={ ( value ) =>
							setAttributes( { buttonUrl: value || undefined } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<nav { ...blockProps }>
				<div className="wp-block-trip-itinerary-header__scroll">
					<PinIcon />
					{ stops.length > 0 ? (
						<ul className="wp-block-trip-itinerary-header__stops">
							{ stops.map( ( stop ) => (
								<li key={ stop.name }>
									{ /* Not a link in the editor, where it would leave the page. */ }
									<span className="wp-block-trip-itinerary-header__stop">
										{ stop.name }
									</span>
								</li>
							) ) }
						</ul>
					) : (
						<span className="wp-block-trip-itinerary-header__empty">
							{ __(
								'Add locations to the days to list the stops here.',
								'trip-itinerary'
							) }
						</span>
					) }
				</div>
				<RichText
					tagName="span"
					className="wp-block-trip-itinerary-header__button"
					value={ buttonText }
					onChange={ ( value ) =>
						setAttributes( { buttonText: value } )
					}
					placeholder={ __( 'Button text', 'trip-itinerary' ) }
					allowedFormats={ [] }
					withoutInteractiveFormatting
				/>
			</nav>
		</>
	);
}
