import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { title } = attributes;

	const blockProps = useBlockProps.save();
	const innerBlocksProps = useInnerBlocksProps.save( {
		className: 'wp-block-trip-itinerary-day__content',
	} );

	return (
		<li { ...blockProps }>
			<RichText.Content
				tagName="h3"
				className="wp-block-trip-itinerary-day__title"
				value={ title }
			/>
			<div { ...innerBlocksProps } />
		</li>
	);
}
