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
		Content\PostTypes::register_hooks();
		Content\Meta::register_hooks();
		Roles::register_hooks();
	}
}
