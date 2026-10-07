<?php
namespace Hive\Core;

/**
 * Wires the plugin's components into WordPress.
 */
final class Plugin {

	/**
	 * Registers all hooks. Runs on `plugins_loaded`.
	 */
	public static function boot(): void {
		// Components register their hooks here as they are added.
	}
}
