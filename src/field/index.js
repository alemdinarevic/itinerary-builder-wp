import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

// Rendered on the server (render.php); nothing but attributes is saved.
registerBlockType( metadata.name, {
	edit: Edit,
} );
