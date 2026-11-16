import { __, _x } from '@wordpress/i18n';
import {
	BlockControls,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	store as blockEditorStore,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	ToolbarButton,
	ToolbarGroup,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { Fragment, useEffect, useMemo, useRef } from '@wordpress/element';

import { formatTripDate, toIsoDate, tripDate } from '../shared/dates';
import { getTripSummary } from '../shared/summary';
import { getCoordinates, getMapStops, getStops } from '../shared/stops';
import { drawStopMap } from '../shared/stop-map';
import { toPlainText } from '../shared/plain-text';

/**
 * The map, redrawn when the stops change. Not draggable in the editor,
 * where dragging would fight with selecting and moving blocks.
 *
 * @param {Object} props
 * @param {Array}  props.stops Stops from getMapStops().
 */
function MapPreview( { stops } ) {
	const ref = useRef();
	const key = JSON.stringify( stops );

	useEffect( () => {
		const map = drawStopMap( ref.current, JSON.parse( key ), {
			interactive: false,
		} );
		return () => map.remove();
	}, [ key ] );

	return (
		<div
			ref={ ref }
			className="wp-block-trip-itinerary-overview__map"
			aria-hidden="true"
		/>
	);
}

export default function Edit( {
	attributes,
	setAttributes,
	clientId,
	context,
} ) {
	const { background } = attributes;
	const startDate = context[ 'trip-itinerary/startDate' ];

	// The other blocks in the same Itinerary. getBlocks returns the same
	// array until something changes, so this only re-renders when needed.
	const siblings = useSelect(
		( select ) => {
			const { getBlockRootClientId, getBlocks } =
				select( blockEditorStore );
			return getBlocks( getBlockRootClientId( clientId ) );
		},
		[ clientId ]
	);

	const days = useMemo(
		() =>
			siblings.filter( ( block ) => block.name === 'trip-itinerary/day' ),
		[ siblings ]
	);
	const locations = useMemo(
		() =>
			days.map( ( day ) =>
				toPlainText( day.attributes.location ).trim()
			),
		[ days ]
	);
	const stops = useMemo( () => getStops( locations ), [ locations ] );
	const mapStops = useMemo(
		() =>
			getMapStops(
				locations,
				days.map( ( day ) => getCoordinates( day.attributes ) )
			),
		[ locations, days ]
	);

	// The dates on their own line, the rest below, as in render.php.
	const { dates, facts } = useMemo( () => {
		const result = { dates: '', facts: [] };
		getTripSummary( {
			startDate,
			travellers: context[ 'trip-itinerary/travellers' ],
			locations,
		} ).forEach( ( item ) => {
			if ( item.key === 'dates' ) {
				result.dates = item.value;
				result.facts.push( item.detail );
			} else {
				result.facts.push( item.value );
			}
		} );
		return result;
	}, [ startDate, context, locations ] );

	const photoPicker = ( renderButton ) => (
		<MediaUploadCheck>
			<MediaUpload
				allowedTypes={ [ 'image' ] }
				value={ background?.id }
				onSelect={ ( media ) =>
					setAttributes( {
						background: {
							id: media.id,
							url: media.sizes?.large?.url ?? media.url,
						},
					} )
				}
				render={ ( { open } ) => renderButton( open ) }
			/>
		</MediaUploadCheck>
	);
	const photoLabel = background
		? __( 'Replace photo', 'trip-itinerary' )
		: __( 'Add country photo', 'trip-itinerary' );

	const blockProps = useBlockProps( {
		className: background?.url ? 'has-photo' : undefined,
	} );
	const hero = dates || facts.length > 0 || background?.url;

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
				<PanelBody title={ __( 'Country photo', 'trip-itinerary' ) }>
					<p>
						{ __(
							'Shown behind the dates and travellers. Wide landscape photos work best.',
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
					{ background && (
						<Button
							__next40pxDefaultSize
							variant="tertiary"
							isDestructive
							onClick={ () =>
								setAttributes( { background: undefined } )
							}
						>
							{ __( 'Remove photo', 'trip-itinerary' ) }
						</Button>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ ! hero && stops.length === 0 && (
					<p className="wp-block-trip-itinerary-overview__empty">
						{ __(
							'Set a start date or travellers in the Itinerary settings, or add places to the days, to fill in the overview.',
							'trip-itinerary'
						) }
					</p>
				) }

				{ hero && (
					<div className="wp-block-trip-itinerary-overview__hero">
						{ background?.url && (
							<img
								className="wp-block-trip-itinerary-overview__photo"
								src={ background.url }
								alt=""
							/>
						) }
						<div className="wp-block-trip-itinerary-overview__summary">
							{ dates && (
								<p className="wp-block-trip-itinerary-overview__dates">
									{ dates }
								</p>
							) }
							{ facts.length > 0 && (
								<p className="wp-block-trip-itinerary-overview__facts">
									{ facts.map( ( fact, index ) => (
										<Fragment key={ index }>
											{ index > 0 && (
												<span
													className="wp-block-trip-itinerary-overview__separator"
													aria-hidden="true"
												>
													·
												</span>
											) }
											<span>{ fact }</span>
										</Fragment>
									) ) }
								</p>
							) }
						</div>
					</div>
				) }

				{ stops.length > 0 && (
					<div
						className={
							'wp-block-trip-itinerary-overview__stops' +
							( mapStops.length > 0 ? ' has-map' : '' )
						}
					>
						<ol
							className="wp-block-trip-itinerary-overview__list"
							aria-label={ __( 'Stops', 'trip-itinerary' ) }
						>
							{ stops.map( ( stop, index ) => {
								const date = tripDate(
									startDate,
									stop.dayIndex
								);
								return (
									<li
										key={ stop.name }
										className="wp-block-trip-itinerary-overview__stop"
									>
										{ /* Not a link in the editor, where it would leave the page. */ }
										<span className="wp-block-trip-itinerary-overview__link">
											<span
												className="wp-block-trip-itinerary-overview__node"
												aria-hidden="true"
											>
												{ index + 1 }
											</span>
											<span className="wp-block-trip-itinerary-overview__place">
												{ stop.name }
											</span>{ ' ' }
											{ date && (
												<time
													className="wp-block-trip-itinerary-overview__date"
													dateTime={ toIsoDate(
														date
													) }
												>
													{ formatTripDate(
														date,
														/* translators: Date format beside each stop in the trip overview, see https://www.php.net/manual/datetime.format.php */
														_x(
															'D j M',
															'overview stop date format',
															'trip-itinerary'
														)
													) }
												</time>
											) }
										</span>
									</li>
								);
							} ) }
						</ol>
						{ mapStops.length > 0 && (
							<MapPreview stops={ mapStops } />
						) }
					</div>
				) }
			</div>
		</>
	);
}
