import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import metadata from './block.json';

// Rendered on the server (render.php); styled by the Notifications block.
registerBlockType( metadata.name, {
	edit: Edit,
} );
