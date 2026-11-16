import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { ComboboxControl, PanelBody, Placeholder } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Picks the page whose itinerary the card shows. Searches as you type, so
 * it works on sites with many pages.
 *
 * @param {Object}   props
 * @param {number}   props.value    The chosen page's ID.
 * @param {Function} props.onChange Called with the new page's ID.
 */
function PagePicker( { value, onChange } ) {
	const pages = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'page', {
				per_page: 100,
				status: 'publish',
				orderby: 'title',
				order: 'asc',
				_fields: 'id,title',
			} ),
		[]
	);

	return (
		<ComboboxControl
			__next40pxDefaultSize
			__nextHasNoMarginBottom
			label={ __( 'Page with the itinerary', 'trip-itinerary' ) }
			value={ value ?? null }
			options={ ( pages ?? [] ).map( ( page ) => ( {
				value: page.id,
				label:
					page.title.rendered || __( '(no title)', 'trip-itinerary' ),
			} ) ) }
			onChange={ ( id ) => onChange( id ? Number( id ) : undefined ) }
		/>
	);
}

export default function Edit( { attributes, setAttributes } ) {
	const { pageId } = attributes;
	const blockProps = useBlockProps();
	const choose = ( id ) => setAttributes( { pageId: id } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Trip', 'trip-itinerary' ) }>
					<PagePicker value={ pageId } onChange={ choose } />
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ pageId ? (
					// The same HTML as on the site, rendered by render.php
					// through the REST API, so there's nothing to keep in sync.
					<ServerSideRender
						block="trip-itinerary/card"
						attributes={ attributes }
						skipBlockSupportAttributes
					/>
				) : (
					<Placeholder
						icon="id-alt"
						label={ __( 'Itinerary card', 'trip-itinerary' ) }
						instructions={ __(
							'Choose the page with the trip. The card fills itself in from that page’s itinerary.',
							'trip-itinerary'
						) }
					>
						<PagePicker value={ pageId } onChange={ choose } />
					</Placeholder>
				) }
			</div>
		</>
	);
}
