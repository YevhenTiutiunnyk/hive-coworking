<?php
namespace Hive\Core\Tests\Integration;

use DateTimeImmutable;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use WP_UnitTestCase;

/**
 * Base class for the plugin's integration tests.
 *
 * WP_UnitTestCase unregisters every meta key after each test, so they are registered again here.
 * The site timezone is Europe/Berlin so that local and UTC times differ.
 */
abstract class TestCase extends WP_UnitTestCase {

	public function set_up(): void {
		parent::set_up();
		Meta::register_all();
		update_option( 'timezone_string', 'Europe/Berlin' );
	}

	/**
	 * Creates a published location with default opening hours and a space in it.
	 *
	 * @param array<string, mixed> $space_args Extra arguments for the space post.
	 * @return int Space ID.
	 */
	protected function create_space( array $space_args = array() ): int {
		$location = self::factory()->post->create(
			array(
				'post_type'   => PostTypes::LOCATION,
				'post_status' => 'publish',
				'post_title'  => 'Hive Central',
			)
		);

		return self::factory()->post->create(
			array_merge(
				array(
					'post_type'   => PostTypes::SPACE,
					'post_status' => 'publish',
					'post_title'  => 'Room Atlas',
					'meta_input'  => array( Meta::SPACE_LOCATION => $location ),
				),
				$space_args
			)
		);
	}

	/**
	 * A time in the site timezone.
	 */
	protected static function local( string $time ): DateTimeImmutable {
		return new DateTimeImmutable( $time, wp_timezone() );
	}
}
