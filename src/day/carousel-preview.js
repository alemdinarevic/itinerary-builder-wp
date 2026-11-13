import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

import { slidePosition, wrapIndex } from '../shared/carousel';

/**
 * The photo carousel in the editor. It uses the same markup and classes as
 * includes/carousel.php, so it looks the same, with React state in place of
 * the Interactivity API store.
 *
 * @param {Object} props
 * @param {Array}  props.photos Stored photos: id, url, alt, caption.
 */
export default function CarouselPreview( { photos } ) {
	const [ current, setCurrent ] = useState( 0 );
	const count = photos.length;

	if ( count === 0 ) {
		return null;
	}

	// Photos may have been removed since `current` was set.
	const active = Math.min( current, count - 1 );

	return (
		<div className="wp-block-trip-itinerary-carousel" data-count={ count }>
			<div className="wp-block-trip-itinerary-carousel__track">
				{ photos.map( ( photo, index ) => (
					// The slides behave like the site's, but the arrows and
					// dots below are what keyboard users should reach.
					// eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-noninteractive-element-interactions
					<figure
						key={ photo.id ?? index }
						className="wp-block-trip-itinerary-carousel__slide"
						data-position={ slidePosition( index, active, count ) }
						onClick={ () => setCurrent( index ) }
					>
						<img
							className="wp-block-trip-itinerary-carousel__image"
							src={ photo.url }
							alt={ photo.alt ?? '' }
						/>
						{ photo.caption && (
							<figcaption className="wp-block-trip-itinerary-carousel__credit">
								📸 { photo.caption }
							</figcaption>
						) }
					</figure>
				) ) }
			</div>

			{ count > 1 && (
				<div className="wp-block-trip-itinerary-carousel__controls">
					<button
						type="button"
						className="wp-block-trip-itinerary-carousel__arrow"
						aria-label={ __( 'Previous photo', 'trip-itinerary' ) }
						onClick={ () =>
							setCurrent( wrapIndex( active - 1, count ) )
						}
					>
						<svg viewBox="0 0 24 24" width="16" height="16">
							<path
								d="M15 5l-7 7 7 7"
								fill="none"
								stroke="currentColor"
								strokeWidth="2.5"
								strokeLinecap="round"
								strokeLinejoin="round"
							/>
						</svg>
					</button>
					<div className="wp-block-trip-itinerary-carousel__dots">
						{ photos.map( ( photo, index ) => (
							<button
								key={ photo.id ?? index }
								type="button"
								className="wp-block-trip-itinerary-carousel__dot"
								aria-label={ sprintf(
									/* translators: %d: photo number. */
									__( 'Show photo %d', 'trip-itinerary' ),
									index + 1
								) }
								aria-current={ index === active }
								onClick={ () => setCurrent( index ) }
							/>
						) ) }
					</div>
					<button
						type="button"
						className="wp-block-trip-itinerary-carousel__arrow"
						aria-label={ __( 'Next photo', 'trip-itinerary' ) }
						onClick={ () =>
							setCurrent( wrapIndex( active + 1, count ) )
						}
					>
						<svg viewBox="0 0 24 24" width="16" height="16">
							<path
								d="M9 5l7 7-7 7"
								fill="none"
								stroke="currentColor"
								strokeWidth="2.5"
								strokeLinecap="round"
								strokeLinejoin="round"
							/>
						</svg>
					</button>
				</div>
			) }
		</div>
	);
}
