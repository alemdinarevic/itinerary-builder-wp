import { registerBlockStyle, registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	// The price is rendered on the server (render.php); only the lists of
	// what's included and excluded are saved, as ordinary blocks.
	save: () => <InnerBlocks.Content />,
} );

// Two styles for the core List block, used for what the price includes and
// excludes. Editors pick them in the List block's Styles panel.
registerBlockStyle( 'core/list', {
	name: 'trip-included',
	label: __( 'Included (✓)', 'trip-itinerary' ),
} );
registerBlockStyle( 'core/list', {
	name: 'trip-excluded',
	label: __( 'Excluded (✗)', 'trip-itinerary' ),
} );
