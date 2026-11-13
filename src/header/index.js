import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

// No save function: the stops come from the days and are rendered on the
// server (render.php); only the button's text and link are stored.
registerBlockType( metadata.name, {
	edit: Edit,
} );
