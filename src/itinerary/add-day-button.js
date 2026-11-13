import { __ } from '@wordpress/i18n';
import { createBlock } from '@wordpress/blocks';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { plus } from '@wordpress/icons';

/**
 * The button below an Itinerary's blocks. It adds a Day after the last day,
 * so new days go before the Trip price rather than after it.
 *
 * @param {Object} props
 * @param {string} props.clientId The Itinerary's client ID.
 */
export default function AddDayButton( { clientId } ) {
	// Where the new day goes: after the last day, or before the price when
	// there are no days yet, or at the end.
	const index = useSelect(
		( select ) => {
			const { getBlockOrder, getBlockName } = select( blockEditorStore );
			const order = getBlockOrder( clientId );
			const names = order.map( ( id ) => getBlockName( id ) );

			const lastDay = names.lastIndexOf( 'trip-itinerary/day' );
			if ( lastDay !== -1 ) {
				return lastDay + 1;
			}
			const price = names.indexOf( 'trip-itinerary/price' );
			return price !== -1 ? price : order.length;
		},
		[ clientId ]
	);
	const { insertBlock } = useDispatch( blockEditorStore );

	return (
		<Button
			__next40pxDefaultSize
			className="wp-block-trip-itinerary-itinerary__add-day"
			variant="secondary"
			icon={ plus }
			onClick={ () =>
				insertBlock(
					createBlock( 'trip-itinerary/day' ),
					index,
					clientId
				)
			}
		>
			{ __( 'Add day', 'trip-itinerary' ) }
		</Button>
	);
}
