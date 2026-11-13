import { __, _x, sprintf } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	store as blockEditorStore,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import { formatTripDate, toIsoDate, tripDate } from '../shared/dates';
import CarouselPreview from './carousel-preview';

// Core blocks an editor can use to describe a day.
const ALLOWED_BLOCKS = [
	'core/paragraph',
	'core/heading',
	'core/list',
	'core/image',
	'core/quote',
	'core/buttons',
];

/**
 * What we store for each photo picked in the media library. The site uses
 * the attachment ID for responsive images; the URL and text are a fallback,
 * and what the editor preview shows.
 *
 * @param {Object} media Attachment from the media modal.
 * @return {Object} Stored photo.
 */
function toPhoto( media ) {
	return {
		id: media.id,
		url: media.sizes?.large?.url ?? media.url,
		alt: media.alt ?? '',
		caption: media.caption ?? '',
	};
}

const TEMPLATE = [
	[
		'core/paragraph',
		{ placeholder: __( 'What happens on this day?', 'trip-itinerary' ) },
	],
];

export default function Edit( {
	attributes,
	setAttributes,
	clientId,
	context,
} ) {
	const { title, location } = attributes;
	// Blocks upgraded from an older version through `deprecated` don't get
	// defaults for attributes added since, so `photos` can be missing.
	const photos = attributes.photos ?? [];

	// Provided by the parent Itinerary (see "usesContext" in block.json).
	const startDate = context[ 'trip-itinerary/startDate' ];

	// This day's position among the days of its Itinerary. useSelect
	// re-renders the block when days are added, removed or reordered.
	const index = useSelect(
		( select ) => {
			const { getBlockRootClientId, getBlockOrder, getBlockName } =
				select( blockEditorStore );
			return getBlockOrder( getBlockRootClientId( clientId ) )
				.filter( ( id ) => getBlockName( id ) === 'trip-itinerary/day' )
				.indexOf( clientId );
		},
		[ clientId ]
	);

	const date = tripDate( startDate, index );

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-trip-itinerary-day__content' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE }
	);

	// Opens the media library to pick or reorder this day's photos.
	const photoPicker = ( renderButton ) => (
		<MediaUploadCheck>
			<MediaUpload
				allowedTypes={ [ 'image' ] }
				multiple
				gallery
				value={ photos.map( ( photo ) => photo.id ) }
				onSelect={ ( media ) =>
					setAttributes( { photos: media.map( toPhoto ) } )
				}
				render={ ( { open } ) => renderButton( open ) }
			/>
		</MediaUploadCheck>
	);
	const photoLabel =
		photos.length > 0
			? __( 'Edit photos', 'trip-itinerary' )
			: __( 'Add photos', 'trip-itinerary' );

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					{ photoPicker( ( open ) => (
						<ToolbarButton onClick={ open }>
							{ photoLabel }
						</ToolbarButton>
					) ) }
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Photos', 'trip-itinerary' ) }>
					<p>
						{ __(
							'Shown in a carousel below the day. Drag photos in the media library to change their order; captions appear as photo credits.',
							'trip-itinerary'
						) }
					</p>
					{ photoPicker( ( open ) => (
						<Button
							__next40pxDefaultSize
							variant="secondary"
							onClick={ open }
						>
							{ photoLabel }
						</Button>
					) ) }
					{ photos.length > 0 && (
						<Button
							__next40pxDefaultSize
							variant="tertiary"
							isDestructive
							onClick={ () => setAttributes( { photos: [] } ) }
						>
							{ __( 'Remove all photos', 'trip-itinerary' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<article { ...blockProps }>
				{ /* The same circle and label render.php prints on the site. They
				aren't saved. */ }
				<span
					className="wp-block-trip-itinerary-day__marker"
					aria-hidden="true"
				>
					{ index + 1 }
				</span>
				<p className="wp-block-trip-itinerary-day__label">
					<span className="wp-block-trip-itinerary-day__label-day">
						{ sprintf(
							/* translators: %d: day number, starting at 1. */
							__( 'Day %d', 'trip-itinerary' ),
							index + 1
						) }
					</span>
					{ date && (
						<>
							{ ' ' }
							<span
								className="wp-block-trip-itinerary-day__label-sep"
								aria-hidden="true"
							>
								·
							</span>{ ' ' }
							<time dateTime={ toIsoDate( date ) }>
								{ formatTripDate(
									date,
									/* translators: Date format for the day label, see https://www.php.net/manual/datetime.format.php */
									_x(
										'D, j M',
										'day label date format',
										'trip-itinerary'
									)
								) }
							</time>
						</>
					) }
				</p>
				<RichText
					tagName="h3"
					className="wp-block-trip-itinerary-day__title"
					value={ title }
					onChange={ ( value ) => setAttributes( { title: value } ) }
					placeholder={ __(
						'Day title, e.g. Arrival in Sarajevo',
						'trip-itinerary'
					) }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>
				<RichText
					tagName="p"
					className="wp-block-trip-itinerary-day__location"
					value={ location }
					onChange={ ( value ) =>
						setAttributes( { location: value } )
					}
					placeholder={ __(
						'Where? e.g. Sarajevo',
						'trip-itinerary'
					) }
					allowedFormats={ [] }
				/>
				<div { ...innerBlocksProps } />
				<CarouselPreview photos={ photos } />
			</article>
		</>
	);
}
