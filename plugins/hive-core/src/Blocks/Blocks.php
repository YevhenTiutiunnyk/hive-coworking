<?php
namespace Hive\Core\Blocks;

use Hive\Core\Plugin;

/**
 * Registers the plugin's blocks from the compiled build directory.
 */
final class Blocks {

	/**
	 * Hooks block registration into `init`.
	 */
	public static function register_hooks(): void {
		add_action( 'init', array( self::class, 'register_all' ) );
	}

	/**
	 * Registers every block that has been built. Run `npm run build` first.
	 */
	public static function register_all(): void {
		$metadata_files = glob( Plugin::path() . '/build/*/block.json' );

		foreach ( false === $metadata_files ? array() : $metadata_files as $metadata ) {
			$block_type = register_block_type( dirname( $metadata ) );

			// Core looks for JS translations in wp-content/languages only; add the bundled ones.
			foreach ( false === $block_type ? array() : $block_type->editor_script_handles as $handle ) {
				wp_set_script_translations( $handle, 'hive-core', Plugin::path() . '/languages' );
			}
		}
	}
}
