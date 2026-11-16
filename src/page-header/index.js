import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

// Rendered on the server (render.php), which can fall back to the site's
// logo and name. Only the blocks on the bar (notifications, user menu) are
// saved, as blocks.
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
} );
