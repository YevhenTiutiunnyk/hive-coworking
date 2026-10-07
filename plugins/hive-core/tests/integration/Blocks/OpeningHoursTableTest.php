<?php
namespace Hive\Core\Tests\Integration\Blocks;

use Hive\Core\Blocks\OpeningHoursTable;
use Hive\Core\Content\Meta;
use Hive\Core\Tests\Integration\TestCase;

final class OpeningHoursTableTest extends TestCase {

	public function test_lists_the_week_starting_on_monday_and_marks_today(): void {
		$space    = $this->create_space();
		$location = (int) get_post_meta( $space, Meta::SPACE_LOCATION, true );

		$rows = OpeningHoursTable::rows( $location, self::local( '2030-01-08 09:00' ) );

		$this->assertCount( 7, $rows );
		$this->assertSame( 'Monday', $rows[0]['day'] );
		$this->assertSame( '08:00–20:00', $rows[0]['hours'] );
		$this->assertSame( 'Closed', $rows[6]['hours'] );
		$this->assertSame( array( false, true, false, false, false, false, false ), array_column( $rows, 'today' ) );
	}

	public function test_resolves_the_location_of_a_space(): void {
		$space = $this->create_space();

		$this->assertSame( (int) get_post_meta( $space, Meta::SPACE_LOCATION, true ), OpeningHoursTable::location_for( $space ) );
	}

	public function test_other_posts_have_no_location(): void {
		$this->assertSame( 0, OpeningHoursTable::location_for( self::factory()->post->create() ) );
	}
}
