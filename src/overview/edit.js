import { __, _x, sprintf } from '@wordpress/i18n';
import {
	store as blockEditorStore,
	useBlockProps,
} from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { Fragment, useMemo } from '@wordpress/element';

import { formatTripDate, toIsoDate, tripDate } from '../shared/dates';
import { getTripSummary } from '../shared/summary';
import { toPlainText } from '../shared/plain-text';

export default function Edit( { clientId, context } ) {
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

	const locations = useMemo(
		() =>
			siblings
				.filter( ( block ) => block.name === 'trip-itinerary/day' )
				.map( ( day ) =>
					toPlainText( day.attributes.location ).trim()
				),
		[ siblings ]
	);

	// "4 – 6 Dec 2026", "3 days", "2 people", as in render.php.
	const facts = useMemo( () => {
		const items = getTripSummary( {
			startDate,
			travellers: context[ 'trip-itinerary/travellers' ],
			locations,
		} );
		return items.flatMap( ( item ) => [
			{ text: item.value, strong: item.key === 'dates' },
			...( item.detail ? [ { text: item.detail, strong: false } ] : [] ),
		] );
	}, [ startDate, context, locations ] );

	// As in render.php: the route line only once there are places or dates.
	const showRoute =
		locations.length > 0 &&
		( locations.some( Boolean ) || tripDate( startDate, 0 ) !== null );

	const blockProps = useBlockProps();

	if ( facts.length === 0 && ! showRoute ) {
		return (
			<div { ...blockProps }>
				<p className="wp-block-trip-itinerary-overview__empty">
					{ __(
						'Set a start date or travellers in the Itinerary settings, or add places to the days, to fill in the overview.',
						'trip-itinerary'
					) }
				</p>
			</div>
		);
	}

	return (
		<div { ...blockProps }>
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
							<span
								className={
									'wp-block-trip-itinerary-overview__fact' +
									( fact.strong ? ' is-strong' : '' )
								}
							>
								{ fact.text }
							</span>
						</Fragment>
					) ) }
				</p>
			) }

			{ showRoute && (
				<ol
					className="wp-block-trip-itinerary-overview__route"
					aria-label={ __( 'Days', 'trip-itinerary' ) }
				>
					{ locations.map( ( place, index ) => {
						const date = tripDate( startDate, index );
						const dayName = sprintf(
							/* translators: %d: day number, starting at 1. */
							__( 'Day %d', 'trip-itinerary' ),
							index + 1
						);
						return (
							<li
								key={ index }
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
									{ place && (
										<span className="wp-block-trip-itinerary-overview__day">
											{ `${ dayName }: ` }
										</span>
									) }
									<span className="wp-block-trip-itinerary-overview__place">
										{ place || dayName }
									</span>
									{ /* Read as "Sarajevo Fri 4 Dec", not "SarajevoFri 4 Dec". */ }{ ' ' }
									{ date && (
										<time
											className="wp-block-trip-itinerary-overview__date"
											dateTime={ toIsoDate( date ) }
										>
											{ formatTripDate(
												date,
												/* translators: Date format under each stop in the trip overview, see https://www.php.net/manual/datetime.format.php */
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
			) }
		</div>
	);
}
