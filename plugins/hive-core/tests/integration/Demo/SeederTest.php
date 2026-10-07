<?php
namespace Hive\Core\Tests\Integration\Demo;

use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Demo\Seeder;
use Hive\Core\Roles;
use Hive\Core\Tests\Integration\TestCase;

final class SeederTest extends TestCase {

	public function test_creates_the_demo_catalogue(): void {
		$counts = ( new Seeder() )->run( self::local( '2030-01-07 09:00' ) );

		$this->assertSame( 3, $counts['locations'] );
		$this->assertSame( 12, $counts['spaces'] );
		$this->assertSame( 3, $counts['plans'] );
		$this->assertSame( 4, $counts['events'] );
		$this->assertSame( 2, $counts['pages'] );
		$this->assertStringContainsString( 'wp:hive/my-bookings', get_page_by_path( 'account' )->post_content );
		$this->assertCount( 12, ( new SpaceCatalog() )->all(), 'Every space is bookable.' );
		$this->assertNotEmpty(
			get_terms(
				array(
					'taxonomy'   => PostTypes::AMENITY,
					'hide_empty' => true,
				)
			)
		);
	}

	public function test_locations_have_their_own_opening_hours(): void {
		( new Seeder() )->run( self::local( '2030-01-07 09:00' ) );

		$harbour = get_page_by_path( 'hive-harbour', OBJECT, PostTypes::LOCATION );
		$hours   = get_post_meta( $harbour->ID, Meta::LOCATION_HOURS, true );

		$this->assertSame( '07:00', $hours['mon']['open'] );
		$this->assertNotNull( $hours['sun'] );
	}

	public function test_events_are_in_the_future(): void {
		$now = self::local( '2030-01-07 09:00' );
		( new Seeder() )->run( $now );

		foreach ( get_posts(
			array(
				'post_type'   => PostTypes::EVENT,
				'numberposts' => -1,
			)
		) as $event ) {
			$this->assertGreaterThan( $now, new \DateTimeImmutable( get_post_meta( $event->ID, Meta::EVENT_STARTS, true ) ) );
		}
	}

	public function test_creates_a_demo_member_with_bookings(): void {
		$now = self::local( '2030-01-07 09:00' );
		( new Seeder() )->run( $now, 'demo-pass' );

		$user = get_user_by( 'login', Seeder::DEMO_USER );

		$this->assertInstanceOf( \WP_User::class, $user );
		$this->assertTrue( user_can( $user, Roles::CAP_BOOK ) );
		$this->assertTrue( wp_check_password( 'demo-pass', $user->user_pass, $user->ID ) );
		$this->assertCount( 3, ( new BookingRepository() )->for_user( $user->ID, $now, true ) );
		$this->assertCount( 2, ( new BookingRepository() )->for_user( $user->ID, $now, false ) );
	}

	public function test_running_twice_does_not_duplicate_anything(): void {
		$now = self::local( '2030-01-07 09:00' );
		( new Seeder() )->run( $now );

		$second = ( new Seeder() )->run( $now );

		$this->assertSame( 0, array_sum( $second ) );
		$this->assertCount(
			12,
			get_posts(
				array(
					'post_type'   => PostTypes::SPACE,
					'numberposts' => -1,
				)
			)
		);

		$member     = get_user_by( 'login', Seeder::DEMO_USER )->ID;
		$repository = new BookingRepository();
		$this->assertCount( 5, array_merge( $repository->for_user( $member, $now, true ), $repository->for_user( $member, $now, false ) ) );
	}
}
