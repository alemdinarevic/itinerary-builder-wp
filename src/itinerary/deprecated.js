import {
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

/**
 * Earlier versions of the block's saved markup, newest first.
 *
 * When saved content doesn't match the current `save`, the editor tries each
 * entry here. The first one whose `save` reproduces the stored HTML is used
 * to read the attributes, and the block is then saved in the current format.
 */

// 0.1: the whole block was static HTML, with the title read back from the <h2>.
const v1 = {
	attributes: {
		title: {
			type: 'string',
			source: 'html',
			selector: '.wp-block-trip-itinerary-itinerary__title',
		},
	},
	supports: {
		html: false,
		anchor: true,
		align: [ 'wide', 'full' ],
	},
	save( { attributes } ) {
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
	},
};

export default [ v1 ];
