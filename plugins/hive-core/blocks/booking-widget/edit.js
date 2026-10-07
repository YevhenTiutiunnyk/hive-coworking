/**
 * WordPress dependencies
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Placeholder, SelectControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes, setAttributes, context } ) {
	const { spaceId } = attributes;
	const isSpaceTemplate = context.postType === 'hive_space';
	const effectiveSpaceId =
		spaceId || ( isSpaceTemplate && context.postId ) || 0;

	const spaces = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'hive_space', {
				per_page: 100,
				status: 'publish',
				orderby: 'title',
				order: 'asc',
				_fields: 'id,title',
			} ) ?? [],
		[]
	);

	const options = [
		{
			label: isSpaceTemplate
				? __( 'Current space', 'hive-core' )
				: __( 'Select a space', 'hive-core' ),
			value: 0,
		},
		...spaces.map( ( space ) => ( {
			label: space.title.rendered,
			value: space.id,
		} ) ),
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Space', 'hive-core' ) }>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Space to book', 'hive-core' ) }
						value={ spaceId }
						options={ options }
						onChange={ ( value ) =>
							setAttributes( { spaceId: parseInt( value, 10 ) } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				{ effectiveSpaceId ? (
					<ServerSideRender
						block="hive/booking-widget"
						attributes={ {
							...attributes,
							spaceId: effectiveSpaceId,
						} }
					/>
				) : (
					<Placeholder
						icon="calendar-alt"
						label={ __( 'Space Booking', 'hive-core' ) }
						instructions={
							isSpaceTemplate
								? __(
										'Shows the booking form for the space being viewed.',
										'hive-core'
									)
								: __(
										'Choose a space in the block settings.',
										'hive-core'
									)
						}
					/>
				) }
			</div>
		</>
	);
}
