<?php
namespace Hive\Core;

/**
 * PSR-4 autoloader for the Hive\Core namespace.
 *
 * The plugin ships without a vendor directory, so it loads its own classes.
 */
final class Autoloader {

	private const PREFIX = 'Hive\\Core\\';

	/**
	 * Registers the autoloader with SPL.
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'load' ) );
	}

	/**
	 * Loads a class file from src/ when the class belongs to this plugin.
	 *
	 * @param string $class_name Fully qualified class name.
	 */
	public static function load( string $class_name ): void {
		if ( ! str_starts_with( $class_name, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( self::PREFIX ) );
		$file     = __DIR__ . '/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
}
