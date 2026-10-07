<?php
namespace Hive\Core\Tests\Integration\Content;

use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;

final class PostTypesTest extends TestCase {

	/**
	 * @return array<string, array{string, string}>
	 */
	public function post_type_provider(): array {
		return array(
			'location' => array( PostTypes::LOCATION, 'hive-locations' ),
			'space'    => array( PostTypes::SPACE, 'hive-spaces' ),
			'plan'     => array( PostTypes::PLAN, 'hive-plans' ),
			'event'    => array( PostTypes::EVENT, 'hive-events' ),
		);
	}

	/**
	 * @dataProvider post_type_provider
	 */
	public function test_post_type_is_registered_in_rest( string $post_type, string $rest_base ): void {
		$object = get_post_type_object( $post_type );

		$this->assertNotNull( $object );
		$this->assertTrue( $object->show_in_rest );
		$this->assertSame( $rest_base, $object->rest_base );
		$this->assertTrue( post_type_supports( $post_type, 'custom-fields' ) );
	}

	public function test_plans_have_no_public_pages(): void {
		$this->assertFalse( get_post_type_object( PostTypes::PLAN )->publicly_queryable );
		$this->assertTrue( get_post_type_object( PostTypes::PLAN )->show_ui );
	}

	public function test_amenities_belong_to_spaces(): void {
		$this->assertTrue( taxonomy_exists( PostTypes::AMENITY ) );
		$this->assertTrue( is_object_in_taxonomy( PostTypes::SPACE, PostTypes::AMENITY ) );
		$this->assertTrue( get_taxonomy( PostTypes::AMENITY )->show_in_rest );
	}
}
