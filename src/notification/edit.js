import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { title, description, date, url } = attributes;
	const blockProps = useBlockProps( { className: 'is-unread' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Notification', 'trip-itinerary' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="date"
						label={ __( 'Date', 'trip-itinerary' ) }
						value={ date ?? '' }
						onChange={ ( value ) =>
							setAttributes( { date: value || undefined } )
						}
					/>
					<div style={ { height: 16 } } />
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="url"
						label={ __( 'Link', 'trip-itinerary' ) }
						help={ __(
							'Where the title goes, for example the trip it’s about.',
							'trip-itinerary'
						) }
						value={ url ?? '' }
						onChange={ ( value ) =>
							setAttributes( { url: value || undefined } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<li { ...blockProps }>
				<span
					className="wp-block-trip-itinerary-notification__dot"
					aria-hidden="true"
				/>
				<div className="wp-block-trip-itinerary-notification__body">
					{ date && (
						<p className="wp-block-trip-itinerary-notification__date">
							{ date }
						</p>
					) }
					<RichText
						tagName="p"
						className="wp-block-trip-itinerary-notification__title"
						value={ title }
						onChange={ ( value ) =>
							setAttributes( { title: value } )
						}
						placeholder={ __(
							'Title, e.g. Your quote is ready',
							'trip-itinerary'
						) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>
					<RichText
						tagName="p"
						className="wp-block-trip-itinerary-notification__description"
						value={ description }
						onChange={ ( value ) =>
							setAttributes( { description: value } )
						}
						placeholder={ __( 'Description', 'trip-itinerary' ) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>
				</div>
			</li>
		</>
	);
}
