<?php
namespace Hive\Core\Tests\Integration\Blocks;

use Hive\Core\Blocks\Bindings;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;

/**
 * "Now" is Tuesday 2030-01-08 09:00 in Berlin.
 */
final class BindingsTest extends TestCase {

	private int $space;

	private int $location;

	public function set_up(): void {
		parent::set_up();

		$this->space    = $this->create_space(
			array(
				'meta_input' => array(
					Meta::SPACE_TYPE     => 'meeting_room',
					Meta::SPACE_CAPACITY => 8,
					Meta::SPACE_PRICE    => 40,
				),
			)
		);
		$this->location = (int) get_post_meta( $this->space, Meta::SPACE_LOCATION, true );
		update_post_meta( $this->location, Meta::LOCATION_ADDRESS, '12 Quay Street' );
		wp_insert_term( 'Projector', PostTypes::AMENITY, array( 'slug' => 'projector' ) );
		wp_set_object_terms( $this->space, array( 'projector' ), PostTypes::AMENITY );
	}

	public function test_space_fields(): void {
		$this->assertSame( 'Meeting room', $this->value( 'space-type', $this->space ) );
		$this->assertSame( 'Up to 8 people', $this->value( 'space-capacity', $this->space ) );
		$this->assertSame( '€40 / hour', $this->value( 'space-price', $this->space ) );
		$this->assertSame( 'Hive Central', $this->value( 'space-location', $this->space ) );
		$this->assertSame( 'Projector', $this->value( 'space-amenities', $this->space ) );
	}

	public function test_location_fields(): void {
		$this->assertSame( '12 Quay Street', $this->value( 'location-address', $this->location ) );
		$this->assertSame( 'Open today 08:00–20:00', $this->value( 'location-today', $this->location ) );
		$this->assertSame( 'Closed today', $this->value( 'location-today', $this->location, '2030-01-13 09:00' ) );
	}

	public function test_space_pages_can_show_their_locations_fields(): void {
		$this->assertSame( '12 Quay Street', $this->value( 'location-address', $this->space ) );
	}

	public function test_event_fields(): void {
		$event = self::factory()->post->create(
			array(
				'post_type'  => PostTypes::EVENT,
				'meta_input' => array(
					Meta::EVENT_STARTS   => '2030-01-10T18:30:00+01:00',
					Meta::EVENT_ENDS     => '2030-01-10T21:00:00+01:00',
					Meta::EVENT_LOCATION => $this->location,
				),
			)
		);

		$this->assertSame( 'Thursday 10 January, 18:30–21:00', $this->value( 'event-when', $event ) );
		$this->assertSame( 'Hive Central', $this->value( 'event-location', $event ) );
		$this->assertStringEndsWith( "/hive/v1/events/{$event}/ics", $this->value( 'event-ics-url', $event ) );
	}

	public function test_unknown_keys_and_wrong_post_types_return_null(): void {
		$this->assertNull( $this->value( 'nope', $this->space ) );
		$this->assertNull( $this->value( 'space-price', self::factory()->post->create() ) );
	}

	public function test_source_is_registered(): void {
		$this->assertNotNull( get_block_bindings_source( Bindings::SOURCE ) );
	}

	private function value( string $key, int $post_id, string $now = '2030-01-08 09:00' ): ?string {
		return Bindings::value( $key, $post_id, self::local( $now ) );
	}
}
