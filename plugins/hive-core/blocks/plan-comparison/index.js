/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, setAttributes } ) {
	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Button', 'hive-core' ) }>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="url"
						label={ __( 'Button link', 'hive-core' ) }
						help={ __(
							'Leave empty to link to the account page.',
							'hive-core'
						) }
						value={ attributes.ctaUrl }
						onChange={ ( ctaUrl ) => setAttributes( { ctaUrl } ) }
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
