<?php
namespace Hive\Core\Tests\Integration;

use Hive\Core\Plugin;
use PO;

/**
 * The bundled Russian translation loads and covers every string.
 */
final class TranslationTest extends TestCase {

	public function tear_down(): void {
		unload_textdomain( 'hive-core' );
		unload_textdomain( 'hive' );
		parent::tear_down();
	}

	public function test_plugin_strings_are_translated(): void {
		$this->assertTrue( load_textdomain( 'hive-core', Plugin::path() . '/languages/hive-core-ru_RU.mo', 'ru_RU' ) );

		$this->assertSame( 'Пространство не найдено.', __( 'Space not found.', 'hive-core' ) );
		$this->assertSame( 'Переговорная', __( 'Meeting room', 'hive-core' ) );
	}

	public function test_plurals_use_three_russian_forms(): void {
		load_textdomain( 'hive-core', Plugin::path() . '/languages/hive-core-ru_RU.mo', 'ru_RU' );

		$this->assertSame( 'Найдено %d пространство', _n( '%d space found', '%d spaces found', 1, 'hive-core' ) );
		$this->assertSame( 'Найдено %d пространства', _n( '%d space found', '%d spaces found', 3, 'hive-core' ) );
		$this->assertSame( 'Найдено %d пространств', _n( '%d space found', '%d spaces found', 11, 'hive-core' ) );
		$this->assertSame( 'Найдено %d пространство', _n( '%d space found', '%d spaces found', 21, 'hive-core' ) );
	}

	public function test_theme_strings_are_translated(): void {
		$this->assertTrue( load_textdomain( 'hive', dirname( Plugin::path(), 2 ) . '/themes/hive/languages/ru_RU.mo', 'ru_RU' ) );

		$this->assertSame( 'Ваш стол уже ждёт.', __( 'Your desk is waiting.', 'hive' ) );
	}

	/**
	 * @return array<string, array{string, string}>
	 */
	public function catalogue_provider(): array {
		$root = dirname( __DIR__, 4 );

		return array(
			'plugin' => array( $root . '/plugins/hive-core/languages/hive-core.pot', $root . '/plugins/hive-core/languages/hive-core-ru_RU.po' ),
			'theme'  => array( $root . '/themes/hive/languages/hive.pot', $root . '/themes/hive/languages/ru_RU.po' ),
		);
	}

	/**
	 * @dataProvider catalogue_provider
	 */
	public function test_every_string_has_a_translation( string $pot_file, string $po_file ): void {
		require_once ABSPATH . WPINC . '/pomo/po.php';

		$pot = new PO();
		$pot->import_from_file( $pot_file );
		$po = new PO();
		$po->import_from_file( $po_file );

		$untranslated = array();
		foreach ( $pot->entries as $key => $entry ) {
			$translation = $po->entries[ $key ] ?? null;
			if ( null === $translation || array() === array_filter( $translation->translations ) ) {
				$untranslated[] = $entry->singular;
			}
		}

		$this->assertSame( array(), $untranslated );
	}
}
