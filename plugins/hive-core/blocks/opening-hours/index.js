/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import './style.scss';

function Edit( { attributes, context } ) {
	// On a location or space page, the server reads the post from `post_id`.
	const contextPostId = [ 'hive_location', 'hive_space' ].includes(
		context.postType
	)
		? context.postId
		: 0;

	return (
		<div { ...useBlockProps() }>
			{ attributes.locationId || contextPostId ? (
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={
						contextPostId ? { post_id: contextPostId } : {}
					}
				/>
			) : (
				<Placeholder
					icon="clock"
					label={ __( 'Opening Hours', 'hive-core' ) }
					instructions={ __(
						'Shows the hours of the location or space being viewed.',
						'hive-core'
					) }
				/>
			) }
		</div>
	);
}

registerBlockType( metadata.name, { edit: Edit } );
