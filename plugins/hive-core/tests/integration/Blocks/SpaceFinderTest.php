<?php
namespace Hive\Core\Tests\Integration\Blocks;

use Hive\Core\Blocks\SpaceFinder;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;
use WP_Query;

final class SpaceFinderTest extends TestCase {

	private int $harbour;

	private int $old_town;

	/** @var array<string, int> */
	private array $spaces = array();

	public function set_up(): void {
		parent::set_up();

		$this->harbour  = self::factory()->post->create(
			array(
				'post_type'  => PostTypes::LOCATION,
				'post_title' => 'Harbour',
			)
		);
		$this->old_town = self::factory()->post->create(
			array(
				'post_type'  => PostTypes::LOCATION,
				'post_title' => 'Old Town',
			)
		);

		$this->spaces['big-room']   = $this->space( 'Big Room', $this->harbour, 'meeting_room', 12, array( 'wifi', 'projector' ) );
		$this->spaces['small-room'] = $this->space( 'Small Room', $this->harbour, 'meeting_room', 4, array( 'wifi' ) );
		$this->spaces['office']     = $this->space( 'Office', $this->old_town, 'private_office', 6, array( 'wifi', 'projector' ) );
		$this->spaces['desk']       = $this->space( 'Desk', $this->old_town, 'hot_desk', 1, array() );
	}

	public function test_reads_filters_from_the_query_string(): void {
		$filters = SpaceFinder::filters_from_request(
			array(
				SpaceFinder::PARAM_LOCATION => (string) $this->harbour,
				SpaceFinder::PARAM_TYPE     => 'meeting_room',
				SpaceFinder::PARAM_CAPACITY => '5',
				SpaceFinder::PARAM_AMENITY  => array( 'wifi', 'projector' ),
			)
		);

		$this->assertSame(
			array(
				'location'  => $this->harbour,
				'type'      => 'meeting_room',
				'capacity'  => 5,
				'amenities' => array( 'wifi', 'projector' ),
			),
			$filters
		);
	}

	public function test_ignores_invalid_filter_values(): void {
		$filters = SpaceFinder::filters_from_request(
			array(
				SpaceFinder::PARAM_LOCATION => 'harbour',
				SpaceFinder::PARAM_TYPE     => 'castle',
				SpaceFinder::PARAM_CAPACITY => '-3',
				SpaceFinder::PARAM_AMENITY  => 'wifi',
			)
		);

		$this->assertSame(
			array(
				'location'  => 0,
				'type'      => '',
				'capacity'  => 0,
				'amenities' => array( 'wifi' ),
			),
			$filters
		);
	}

	public function test_without_filters_lists_all_spaces_by_title(): void {
		$this->assertSame( array( 'Big Room', 'Desk', 'Office', 'Small Room' ), $this->titles( SpaceFinder::empty_filters() ) );
	}

	public function test_filters_by_location(): void {
		$this->assertSame( array( 'Big Room', 'Small Room' ), $this->titles( array( 'location' => $this->harbour ) + SpaceFinder::empty_filters() ) );
	}

	public function test_filters_by_type(): void {
		$this->assertSame( array( 'Office' ), $this->titles( array( 'type' => 'private_office' ) + SpaceFinder::empty_filters() ) );
	}

	public function test_filters_by_minimum_capacity(): void {
		$this->assertSame( array( 'Big Room', 'Office' ), $this->titles( array( 'capacity' => 6 ) + SpaceFinder::empty_filters() ) );
	}

	public function test_requires_every_selected_amenity(): void {
		$this->assertSame( array( 'Big Room', 'Office' ), $this->titles( array( 'amenities' => array( 'wifi', 'projector' ) ) + SpaceFinder::empty_filters() ) );
	}

	public function test_combines_filters(): void {
		$filters = array(
			'location'  => $this->harbour,
			'capacity'  => 6,
			'amenities' => array( 'projector' ),
		) + SpaceFinder::empty_filters();

		$this->assertSame( array( 'Big Room' ), $this->titles( $filters ) );
	}

	public function test_filter_parameters_do_not_turn_the_page_into_an_archive(): void {
		$page = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$this->go_to(
			add_query_arg(
				array(
					SpaceFinder::PARAM_LOCATION => $this->harbour,
					SpaceFinder::PARAM_AMENITY  => array( 'wifi' ),
				),
				get_permalink( $page )
			)
		);

		$this->assertFalse( is_404() );
		$this->assertTrue( is_page( $page ) );
	}

	public function test_location_pages_lock_the_location_filter(): void {
		$filters = SpaceFinder::with_context(
			SpaceFinder::empty_filters(),
			array(
				'postType' => PostTypes::LOCATION,
				'postId'   => $this->old_town,
			)
		);

		$this->assertSame( $this->old_town, $filters['location'] );
		$this->assertSame( array( 'Desk', 'Office' ), $this->titles( $filters ) );
	}

	public function test_other_pages_keep_the_requested_location(): void {
		$filters = SpaceFinder::with_context(
			array( 'location' => $this->harbour ) + SpaceFinder::empty_filters(),
			array(
				'postType' => 'page',
				'postId'   => 5,
			)
		);

		$this->assertSame( $this->harbour, $filters['location'] );
	}

	public function test_type_labels_are_human_readable(): void {
		$this->assertSame( 'Meeting room', SpaceFinder::type_label( 'meeting_room' ) );
		$this->assertSame( 'Hot desk', SpaceFinder::type_label( 'hot_desk' ) );
	}

	/**
	 * @param array{location: int, type: string, capacity: int, amenities: list<string>} $filters
	 * @return list<string>
	 */
	private function titles( array $filters ): array {
		return wp_list_pluck( ( new WP_Query( SpaceFinder::query_args( $filters ) ) )->posts, 'post_title' );
	}

	/**
	 * @param list<string> $amenities
	 */
	private function space( string $title, int $location, string $type, int $capacity, array $amenities ): int {
		$id = self::factory()->post->create(
			array(
				'post_type'  => PostTypes::SPACE,
				'post_title' => $title,
				'meta_input' => array(
					Meta::SPACE_LOCATION => $location,
					Meta::SPACE_TYPE     => $type,
					Meta::SPACE_CAPACITY => $capacity,
				),
			)
		);
		wp_set_object_terms( $id, $amenities, PostTypes::AMENITY );

		return $id;
	}
}
