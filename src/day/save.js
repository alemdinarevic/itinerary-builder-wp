import { InnerBlocks } from '@wordpress/block-editor';

// The Day is rendered on the server (render.php), so only its child blocks
// are saved. The old static markup is handled in deprecated.js.
export default function save() {
	return <InnerBlocks.Content />;
}
