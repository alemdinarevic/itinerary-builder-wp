import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

// Rendered on the server (render.php) for whoever is viewing the page.
registerBlockType( metadata.name, {
	edit: Edit,
} );
