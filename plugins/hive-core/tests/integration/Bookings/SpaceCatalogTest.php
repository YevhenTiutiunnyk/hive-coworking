<?php
namespace Hive\Core\Tests\Integration\Bookings;

use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;

final class SpaceCatalogTest extends TestCase {

	public function test_finds_a_published_space_with_its_location_hours(): void {
		$id = $this->create_space();

		$space = ( new SpaceCatalog() )->find( $id );

		$this->assertNotNull( $space );
		$this->assertSame( $id, $space->id );
		$this->assertSame( 'Room Atlas', $space->title );
		$this->assertNotNull( $space->hours->for_date( self::local( '2030-01-08 12:00' ) ), 'Default hours: open on Tuesday.' );
		$this->assertNull( $space->hours->for_date( self::local( '2030-01-13 12:00' ) ), 'Default hours: closed on Sunday.' );
	}

	public function test_ignores_unpublished_spaces(): void {
		$this->assertNull( ( new SpaceCatalog() )->find( $this->create_space( array( 'post_status' => 'draft' ) ) ) );
	}

	public function test_ignores_other_post_types(): void {
		$this->assertNull( ( new SpaceCatalog() )->find( self::factory()->post->create() ) );
	}

	public function test_ignores_spaces_without_a_published_location(): void {
		$id = self::factory()->post->create(
			array(
				'post_type'   => PostTypes::SPACE,
				'post_status' => 'publish',
				'meta_input'  => array( Meta::SPACE_LOCATION => 0 ),
			)
		);

		$this->assertNull( ( new SpaceCatalog() )->find( $id ) );
	}
}
