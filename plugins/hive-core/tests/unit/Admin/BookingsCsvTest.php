<?php
namespace Hive\Core\Tests\Unit\Admin;

use Hive\Core\Admin\BookingsCsv;
use PHPUnit\Framework\TestCase;

final class BookingsCsvTest extends TestCase {

	/**
	 * @return array<string, array{string, string}>
	 */
	public function cell_provider(): array {
		return array(
			'plain text'   => array( 'Room Atlas', 'Room Atlas' ),
			'empty'        => array( '', '' ),
			'formula'      => array( '=HYPERLINK("http://evil")', "'=HYPERLINK(\"http://evil\")" ),
			'plus'         => array( '+1', "'+1" ),
			'minus'        => array( '-1', "'-1" ),
			'at sign'      => array( '@SUM(A1)', "'@SUM(A1)" ),
			'tab'          => array( "\tx", "'\tx" ),
			'inner equals' => array( 'a=b', 'a=b' ),
		);
	}

	/**
	 * @dataProvider cell_provider
	 */
	public function test_escapes_formula_cells( string $value, string $expected ): void {
		$this->assertSame( $expected, BookingsCsv::escape_cell( $value ) );
	}
}
