import { __ } from '@wordpress/i18n';
import {
	InnerBlocks,
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

// Only Day blocks can go directly inside an Itinerary.
const ALLOWED_BLOCKS = [ 'trip-itinerary/day' ];

// What a freshly inserted Itinerary starts with: one empty day.
const TEMPLATE = [ [ 'trip-itinerary/day' ] ];

export default function Edit( { attributes, setAttributes } ) {
	const { title } = attributes;

	// Props for the block's outer element: class names, alignment, the
	// editor's selection and focus handling. Every block needs these.
	const blockProps = useBlockProps();

	// Props for the element that holds the child blocks. The child blocks are
	// rendered directly inside it, so the <ol> below contains the days.
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-trip-itinerary-itinerary__days' },
		{
			allowedBlocks: ALLOWED_BLOCKS,
			template: TEMPLATE,
			renderAppender: InnerBlocks.ButtonBlockAppender,
		}
	);

	return (
		<section { ...blockProps }>
			<RichText
				tagName="h2"
				className="wp-block-trip-itinerary-itinerary__title"
				value={ title }
				onChange={ ( value ) => setAttributes( { title: value } ) }
				placeholder={ __( 'Trip name', 'trip-itinerary' ) }
				allowedFormats={ [ 'core/bold', 'core/italic' ] }
			/>
			<ol { ...innerBlocksProps } />
		</section>
	);
}
