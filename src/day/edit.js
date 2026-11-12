import { __ } from '@wordpress/i18n';
import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

// Core blocks an editor can use to describe a day. Stage 2 adds our own
// Stop block to this list.
const ALLOWED_BLOCKS = [
	'core/paragraph',
	'core/heading',
	'core/list',
	'core/image',
	'core/quote',
	'core/buttons',
];

const TEMPLATE = [
	[
		'core/paragraph',
		{ placeholder: __( 'What happens on this day?', 'trip-itinerary' ) },
	],
];

export default function Edit( { attributes, setAttributes } ) {
	const { title } = attributes;

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-trip-itinerary-day__content' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE }
	);

	// A day is an <li>, because the Itinerary renders days inside an <ol>.
	return (
		<li { ...blockProps }>
			<RichText
				tagName="h3"
				className="wp-block-trip-itinerary-day__title"
				value={ title }
				onChange={ ( value ) => setAttributes( { title: value } ) }
				placeholder={ __(
					'Day title, e.g. Arrival in Sarajevo',
					'trip-itinerary'
				) }
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
			/>
			<div { ...innerBlocksProps } />
		</li>
	);
}
