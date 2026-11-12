import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

// The HTML stored in the post. It must match what this function returns for
// the same attributes, or the editor reports the block as invalid. Change it
// later only together with a `deprecated` entry (stage 7).
export default function save( { attributes } ) {
	const { title } = attributes;

	const blockProps = useBlockProps.save();
	const innerBlocksProps = useInnerBlocksProps.save( {
		className: 'wp-block-trip-itinerary-itinerary__days',
	} );

	return (
		<section { ...blockProps }>
			{ ! RichText.isEmpty( title ) && (
				<RichText.Content
					tagName="h2"
					className="wp-block-trip-itinerary-itinerary__title"
					value={ title }
				/>
			) }
			<ol { ...innerBlocksProps } />
		</section>
	);
}
