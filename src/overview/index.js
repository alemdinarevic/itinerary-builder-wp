import { registerBlockType } from '@wordpress/blocks';

import './style.scss';
import Edit from './edit';
import metadata from './block.json';

// No save function: the overview is calculated from the itinerary and
// rendered on the server (render.php), so nothing is stored for it.
registerBlockType( metadata.name, {
	edit: Edit,
} );
