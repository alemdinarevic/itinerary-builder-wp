import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

const TYPES = [
	{ value: 'text', label: __( 'Text', 'trip-itinerary' ) },
	{ value: 'email', label: __( 'Email', 'trip-itinerary' ) },
	{ value: 'tel', label: __( 'Phone', 'trip-itinerary' ) },
	{ value: 'password', label: __( 'Password', 'trip-itinerary' ) },
];

export default function Edit( { attributes, setAttributes } ) {
	const { label, type, placeholder, name, help, required } = attributes;
	const isPassword = type === 'password';

	const blockProps = useBlockProps( {
		className: isPassword ? 'is-password' : undefined,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Field', 'trip-itinerary' ) }>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Type', 'trip-itinerary' ) }
						value={ type }
						options={ TYPES }
						onChange={ ( value ) =>
							setAttributes( { type: value } )
						}
					/>
					<div style={ { height: 16 } } />
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Placeholder', 'trip-itinerary' ) }
						value={ placeholder ?? '' }
						onChange={ ( value ) =>
							setAttributes( { placeholder: value || undefined } )
						}
					/>
					<div style={ { height: 16 } } />
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Name', 'trip-itinerary' ) }
						help={ __(
							'The name the value is sent under when the field is part of a form.',
							'trip-itinerary'
						) }
						value={ name ?? '' }
						onChange={ ( value ) =>
							setAttributes( { name: value || undefined } )
						}
					/>
					<div style={ { height: 16 } } />
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Required', 'trip-itinerary' ) }
						checked={ required }
						onChange={ ( value ) =>
							setAttributes( { required: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<span className="wp-block-trip-itinerary-field__label">
					<RichText
						tagName="span"
						value={ label }
						onChange={ ( value ) =>
							setAttributes( { label: value } )
						}
						placeholder={ __( 'Label', 'trip-itinerary' ) }
						allowedFormats={ [] }
					/>
					{ required && (
						<span
							className="wp-block-trip-itinerary-field__required"
							aria-hidden="true"
						>
							*
						</span>
					) }
				</span>
				<div className="wp-block-trip-itinerary-field__control">
					{ /* Read-only in the editor, where typing would edit the page. */ }
					<input
						className="wp-block-trip-itinerary-field__input"
						type={ isPassword ? 'password' : type }
						placeholder={ placeholder }
						readOnly
						tabIndex={ -1 }
						aria-hidden="true"
					/>
					{ isPassword && (
						<span
							className="wp-block-trip-itinerary-field__toggle"
							aria-hidden="true"
						>
							<svg viewBox="0 0 24 24" width="20" height="20">
								<path
									d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"
									fill="none"
									stroke="currentColor"
									strokeWidth="1.8"
								/>
								<circle
									cx="12"
									cy="12"
									r="3"
									fill="none"
									stroke="currentColor"
									strokeWidth="1.8"
								/>
							</svg>
						</span>
					) }
				</div>
				<RichText
					tagName="p"
					className="wp-block-trip-itinerary-field__help"
					value={ help }
					onChange={ ( value ) => setAttributes( { help: value } ) }
					placeholder={ __(
						'Help text (optional)',
						'trip-itinerary'
					) }
					allowedFormats={ [ 'core/link' ] }
				/>
			</div>
		</>
	);
}
