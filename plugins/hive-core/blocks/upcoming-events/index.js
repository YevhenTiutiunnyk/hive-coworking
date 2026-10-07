/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	const locations = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'hive_location', {
				per_page: 100,
				_fields: 'id,title',
			} ) ?? [],
		[]
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Events', 'hive-core' ) }>
					<RangeControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Number of events', 'hive-core' ) }
						min={ 1 }
						max={ 12 }
						value={ attributes.count }
						onChange={ ( count ) => setAttributes( { count } ) }
					/>
					<SelectControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Location', 'hive-core' ) }
						value={ attributes.locationId }
						options={ [
							{
								label: __( 'All locations', 'hive-core' ),
								value: 0,
							},
							...locations.map( ( location ) => ( {
								label: location.title.rendered,
								value: location.id,
							} ) ),
						] }
						onChange={ ( value ) =>
							setAttributes( {
								locationId: parseInt( value, 10 ),
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
				/>
			</div>
		</>
	);
}

registerBlockType( metadata.name, { edit: Edit } );
