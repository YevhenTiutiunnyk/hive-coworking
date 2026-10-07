<?php
namespace Hive\Core\Blocks;

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
		$build = dirname( __DIR__, 2 ) . '/build';

		$metadata_files = glob( $build . '/*/block.json' );

		foreach ( false === $metadata_files ? array() : $metadata_files as $metadata ) {
			register_block_type( dirname( $metadata ) );
		}
	}
}
