/**
 * The location pin at the start of the Trip header. render.php in
 * src/header prints the same SVG.
 */
export default function PinIcon() {
	return (
		<svg
			className="wp-block-trip-itinerary-header__pin"
			viewBox="0 0 24 24"
			width="28"
			height="28"
			aria-hidden="true"
			focusable="false"
		>
			<path
				d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11z"
				fill="none"
				stroke="currentColor"
				strokeWidth="1.8"
				strokeLinejoin="round"
			/>
			<circle
				cx="12"
				cy="10"
				r="2.4"
				fill="none"
				stroke="currentColor"
				strokeWidth="1.8"
			/>
		</svg>
	);
}
