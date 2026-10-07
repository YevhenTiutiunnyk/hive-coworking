<?php
namespace Hive\Core\Tests\Unit;

use Hive\Core\Plugin;
use PHPUnit\Framework\TestCase;

final class AutoloaderTest extends TestCase {

	public function test_loads_plugin_classes(): void {
		$this->assertTrue( class_exists( Plugin::class ) );
	}

	public function test_ignores_other_namespaces(): void {
		$this->assertFalse( class_exists( 'Acme\\Missing\\Thing' ) );
	}
}
