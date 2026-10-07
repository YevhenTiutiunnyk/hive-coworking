<?php
namespace Hive\Core;

use Hive\Core\Content\PostTypes;

/**
 * One-time setup that runs when the plugin is activated.
 */
final class Activation {

	/**
	 * Activation hook callback.
	 */
	public static function activate(): void {
		Roles::install();

		// Post types must exist before rewrite rules are flushed.
		PostTypes::register_all();
		flush_rewrite_rules();
	}
}
