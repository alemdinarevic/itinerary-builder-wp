import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

// No save function: rendered on the server (render.php), which can fall
// back to the site's logo and name.
registerBlockType( metadata.name, {
	edit: Edit,
} );
