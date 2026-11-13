import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

// What a new price block starts with: a heading and one list of each style.
const TEMPLATE = [
	[
		'core/heading',
		{
			level: 3,
			content: __( 'What is included in the price?', 'trip-itinerary' ),
		},
	],
	[
		'core/list',
		{ className: 'is-style-trip-included' },
		[
			[
				'core/list-item',
				{
					placeholder: __(
						'Included, e.g. Hotels',
						'trip-itinerary'
					),
				},
			],
		],
	],
	[
		'core/list',
		{ className: 'is-style-trip-excluded' },
		[
			[
				'core/list-item',
				{
					placeholder: __(
						'Not included, e.g. Flights',
						'trip-itinerary'
					),
				},
			],
		],
	],
];

const ALLOWED_BLOCKS = [ 'core/heading', 'core/list', 'core/paragraph' ];

export default function Edit( { attributes, setAttributes } ) {
	const { requestUrl } = attributes;

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-trip-itinerary-price__details' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE }
	);

	// An inline-editable text attribute.
	const text = ( key, props ) => (
		<RichText
			{ ...props }
			value={ attributes[ key ] }
			onChange={ ( value ) => setAttributes( { [ key ]: value } ) }
		/>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Request button', 'trip-itinerary' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="url"
						label={ __( 'Button link', 'trip-itinerary' ) }
						help={ __(
							'For example a contact page or a mailto: link. The button shows once it has text and a link.',
							'trip-itinerary'
						) }
						value={ requestUrl ?? '' }
						onChange={ ( value ) =>
							setAttributes( { requestUrl: value || undefined } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="wp-block-trip-itinerary-price__card">
					<p className="wp-block-trip-itinerary-price__amount">
						{ text( 'amount', {
							tagName: 'span',
							className: 'wp-block-trip-itinerary-price__value',
							placeholder: __( '€1,250', 'trip-itinerary' ),
							allowedFormats: [],
						} ) }
						{ text( 'per', {
							tagName: 'span',
							className: 'wp-block-trip-itinerary-price__per',
							placeholder: __( '/ person', 'trip-itinerary' ),
							allowedFormats: [],
						} ) }
					</p>
					{ text( 'note', {
						tagName: 'p',
						className: 'wp-block-trip-itinerary-price__note',
						placeholder: __(
							'A note about the price, e.g. what the estimate is based on',
							'trip-itinerary'
						),
						allowedFormats: [ 'core/bold', 'core/italic' ],
					} ) }
					<div { ...innerBlocksProps } />
				</div>

				<div className="wp-block-trip-itinerary-price__request">
					{ text( 'requestNote', {
						tagName: 'p',
						className:
							'wp-block-trip-itinerary-price__request-note',
						placeholder: __(
							'A note above the button, e.g. Free, no commitment',
							'trip-itinerary'
						),
						allowedFormats: [ 'core/bold', 'core/italic' ],
					} ) }
					{ text( 'requestText', {
						tagName: 'span',
						className: 'wp-block-trip-itinerary-price__button',
						placeholder: __( 'Button text', 'trip-itinerary' ),
						allowedFormats: [],
						withoutInteractiveFormatting: true,
					} ) }
				</div>
			</section>
		</>
	);
}
