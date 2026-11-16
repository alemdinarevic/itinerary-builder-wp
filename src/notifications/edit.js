import { __ } from '@wordpress/i18n';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'trip-itinerary/notification' ];
const TEMPLATE = [ [ 'trip-itinerary/notification' ] ];

// In the editor the panel is always open, so the notifications can be
// written in place; the bell is just a preview.
export default function Edit() {
	const blockProps = useBlockProps( { className: 'is-open' } );
	const innerBlocksProps = useInnerBlocksProps(
		// Notification blocks are list items, inside the <ul> below.
		{ className: 'wp-block-trip-itinerary-notifications__list' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE }
	);

	return (
		<div { ...blockProps }>
			<span className="wp-block-trip-itinerary-notifications__bell">
				<svg
					viewBox="0 0 24 24"
					width="24"
					height="24"
					aria-hidden="true"
				>
					<path
						d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15zM10 20.5a2 2 0 0 0 4 0"
						fill="currentColor"
					/>
				</svg>
			</span>
			<div className="wp-block-trip-itinerary-notifications__panel">
				<div className="wp-block-trip-itinerary-notifications__header">
					<h2 className="wp-block-trip-itinerary-notifications__heading">
						{ __( 'Notifications', 'trip-itinerary' ) }
					</h2>
				</div>
				<ul { ...innerBlocksProps } />
			</div>
		</div>
	);
}
