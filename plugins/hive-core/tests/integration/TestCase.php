<?php
namespace Hive\Core\Tests\Integration;

use Hive\Core\Content\Meta;
use WP_UnitTestCase;

/**
 * Base class for the plugin's integration tests.
 *
 * WP_UnitTestCase unregisters every meta key after each test, so they are registered again here.
 */
abstract class TestCase extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		Meta::register_all();
	}
}
