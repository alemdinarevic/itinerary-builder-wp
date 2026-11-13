import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

import AddDayButton from './add-day-button';

// The blocks that can go directly inside an Itinerary.
const ALLOWED_BLOCKS = [
	'trip-itinerary/header',
	'trip-itinerary/overview',
	'trip-itinerary/day',
	'trip-itinerary/price',
];

// What a freshly inserted Itinerary starts with.
const TEMPLATE = [
	[ 'trip-itinerary/header' ],
	[ 'trip-itinerary/overview' ],
	[ 'trip-itinerary/day' ],
	[ 'trip-itinerary/price' ],
];

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { title, startDate, travellers } = attributes;

	// Props for the block's outer element: class names, alignment, the
	// editor's selection and focus handling. Every block needs these.
	const blockProps = useBlockProps();

	// Props for the element that holds the child blocks. The child blocks are
	// rendered directly inside it.
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-trip-itinerary-itinerary__days' },
		{
			allowedBlocks: ALLOWED_BLOCKS,
			template: TEMPLATE,
			// An "Add day" button instead of the usual "+", which would ask
			// which block to add and put it after the price. The other
			// blocks are in the block inserter.
			renderAppender: () => <AddDayButton clientId={ clientId } />,
		}
	);

	return (
		<>
			{ /* Settings in the block sidebar. Days read these through block
			context, so they update as soon as the values change here. */ }
			<InspectorControls>
				<PanelBody title={ __( 'Trip details', 'trip-itinerary' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="date"
						label={ __( 'Start date', 'trip-itinerary' ) }
						help={ __(
							"Each day's date is counted from this.",
							'trip-itinerary'
						) }
						value={ startDate ?? '' }
						onChange={ ( value ) =>
							setAttributes( { startDate: value || undefined } )
						}
					/>
					<div style={ { height: 16 } } />
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="number"
						min={ 1 }
						label={ __( 'Travellers', 'trip-itinerary' ) }
						value={ travellers ?? '' }
						onChange={ ( value ) => {
							const number = parseInt( value, 10 );
							setAttributes( {
								travellers: number > 0 ? number : undefined,
							} );
						} }
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<RichText
					tagName="h2"
					className="wp-block-trip-itinerary-itinerary__title"
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					placeholder={ __( 'Trip name', 'trip-itinerary' ) }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>
				<div { ...innerBlocksProps } />
			</section>
		</>
	);
}
