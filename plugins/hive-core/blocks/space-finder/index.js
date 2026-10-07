/**
 * WordPress dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Internal dependencies
 */
import metadata from './block.json';
import './style.scss';

function Edit() {
	return (
		<div { ...useBlockProps() }>
			<ServerSideRender block={ metadata.name } />
		</div>
	);
}

registerBlockType( metadata.name, { edit: Edit } );
