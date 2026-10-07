<?php
namespace Hive\Core\Tests\Integration;

use Hive\Core\Plugin;

final class PluginTest extends TestCase {

	public function test_boot_is_hooked_to_plugins_loaded(): void {
		$this->assertSame( 10, has_action( 'plugins_loaded', array( Plugin::class, 'boot' ) ) );
	}
}
