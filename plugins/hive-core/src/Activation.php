<?php
namespace Hive\Core;

/**
 * One-time setup that runs when the plugin is activated.
 */
final class Activation {

	/**
	 * Activation hook callback.
	 */
	public static function activate(): void {
		flush_rewrite_rules();
	}
}
