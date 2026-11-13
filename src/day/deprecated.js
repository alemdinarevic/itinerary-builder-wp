import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

/**
 * Earlier versions of the block's saved markup, newest first. See the
 * Itinerary's deprecated.js for how the editor uses these.
 */

// 0.1: a static <li>, with the title read back from the <h3>.
const v1 = {
	attributes: {
		title: {
			type: 'string',
			source: 'html',
			selector: '.wp-block-trip-itinerary-day__title',
		},
	},
	supports: {
		html: false,
		reusable: false,
	},
	save( { attributes } ) {
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
	},
};

export default [ v1 ];
