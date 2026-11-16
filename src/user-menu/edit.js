import { __ } from '@wordpress/i18n';
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

// In the editor the menu is shown open, for the current user, so its links
// can be written in place. Visitors see it for themselves (render.php).
export default function Edit( { attributes, setAttributes } ) {
	const { links } = attributes;
	const name = useSelect(
		( select ) => select( coreStore ).getCurrentUser()?.name ?? '',
		[]
	);
	const initial = name.slice( 0, 1 ).toUpperCase();

	const blockProps = useBlockProps( { className: 'is-open is-preview' } );

	return (
		<div { ...blockProps }>
			<span className="wp-block-trip-itinerary-user-menu__button">
				<span className="wp-block-trip-itinerary-user-menu__initial">
					{ initial }
				</span>
			</span>
			<div className="wp-block-trip-itinerary-user-menu__panel">
				<div className="wp-block-trip-itinerary-user-menu__user">
					<span className="wp-block-trip-itinerary-user-menu__initial">
						{ initial }
					</span>
					<span>
						<span className="wp-block-trip-itinerary-user-menu__name">
							{ name }
						</span>
						<span className="wp-block-trip-itinerary-user-menu__email">
							{ __(
								'Shown to each visitor for their own account',
								'trip-itinerary'
							) }
						</span>
					</span>
				</div>
				<RichText
					tagName="div"
					className="wp-block-trip-itinerary-user-menu__links"
					value={ links }
					onChange={ ( value ) => setAttributes( { links: value } ) }
					placeholder={ __(
						'Links, e.g. Profile, My itineraries, Chat',
						'trip-itinerary'
					) }
					allowedFormats={ [ 'core/link' ] }
				/>
				<span className="wp-block-trip-itinerary-user-menu__logout">
					{ __( 'Log out', 'trip-itinerary' ) }
				</span>
			</div>
		</div>
	);
}
