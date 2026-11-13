/**
 * Converts the HTML a RichText stores into plain text.
 *
 * DOMParser doesn't run scripts or load images, unlike setting innerHTML
 * on an element, so it's safe for content an editor typed.
 *
 * @param {string|undefined} html
 * @return {string} The text.
 */
export function toPlainText( html ) {
	return (
		new window.DOMParser().parseFromString( html ?? '', 'text/html' ).body
			.textContent ?? ''
	);
}
