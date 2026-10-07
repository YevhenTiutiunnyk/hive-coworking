<?php
namespace Hive\Core\Tests\Integration\Bookings;

use DateTimeZone;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Bookings\Schema;
use Hive\Core\Tests\Integration\TestCase;

final class BookingRepositoryTest extends TestCase {

	private BookingRepository $repository;

	public function set_up(): void {
		parent::set_up();
		$this->repository = new BookingRepository();
	}

	public function test_table_is_created_on_activation(): void {
		global $wpdb;

		$this->assertSame( Schema::table(), $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', Schema::table() ) ) );
		$this->assertSame( Schema::VERSION, get_option( Schema::VERSION_OPTION ) );
	}

	public function test_inserted_booking_can_be_found(): void {
		$booking = $this->repository->insert( 10, 20, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );

		$found = $this->repository->find( $booking->id );

		$this->assertNotNull( $found );
		$this->assertSame( 10, $found->space_id );
		$this->assertSame( 20, $found->user_id );
		$this->assertSame( BookingStatus::Confirmed, $found->status );
		$this->assertEquals( self::local( '2030-01-08 10:00' ), $found->start );
		$this->assertEquals( self::local( '2030-01-08 12:00' ), $found->end );
	}

	public function test_times_are_stored_in_utc(): void {
		global $wpdb;

		$booking = $this->repository->insert( 10, 20, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );

		$this->assertSame(
			'2030-01-08 09:00:00',
			$wpdb->get_var( $wpdb->prepare( 'SELECT start_utc FROM %i WHERE id = %d', Schema::table(), $booking->id ) )
		);
		$this->assertEquals( new DateTimeZone( 'UTC' ), $this->repository->find( $booking->id )->start->getTimezone() );
	}

	public function test_find_returns_null_for_unknown_ids(): void {
		$this->assertNull( $this->repository->find( 999999 ) );
	}

	public function test_detects_overlapping_confirmed_bookings(): void {
		$this->repository->insert( 10, 20, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );

		$this->assertTrue( $this->repository->has_overlap( 10, self::local( '2030-01-08 11:00' ), self::local( '2030-01-08 13:00' ) ) );
		$this->assertFalse( $this->repository->has_overlap( 10, self::local( '2030-01-08 12:00' ), self::local( '2030-01-08 13:00' ) ), 'Touching ranges do not overlap.' );
		$this->assertFalse( $this->repository->has_overlap( 11, self::local( '2030-01-08 11:00' ), self::local( '2030-01-08 13:00' ) ), 'Other spaces are independent.' );
	}

	public function test_cancelled_bookings_do_not_overlap(): void {
		$booking = $this->repository->insert( 10, 20, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );
		$this->repository->update_status( $booking, BookingStatus::Cancelled );

		$this->assertFalse( $this->repository->has_overlap( 10, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) ) );
		$this->assertSame( BookingStatus::Cancelled, $this->repository->find( $booking->id )->status );
	}

	public function test_lists_confirmed_bookings_of_a_space_in_a_range(): void {
		$inside    = $this->repository->insert( 10, 20, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$cancelled = $this->repository->insert( 10, 20, self::local( '2030-01-08 12:00' ), self::local( '2030-01-08 13:00' ) );
		$this->repository->update_status( $cancelled, BookingStatus::Cancelled );
		$this->repository->insert( 10, 20, self::local( '2030-01-09 10:00' ), self::local( '2030-01-09 11:00' ) );
		$this->repository->insert( 11, 20, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );

		$found = $this->repository->for_space_between( 10, self::local( '2030-01-08 00:00' ), self::local( '2030-01-09 00:00' ) );

		$this->assertSame( array( $inside->id ), array_map( fn( $booking ) => $booking->id, $found ) );
	}

	public function test_lists_a_users_upcoming_and_past_bookings(): void {
		$now   = self::local( '2030-01-08 12:00' );
		$past  = $this->repository->insert( 10, 20, self::local( '2030-01-07 10:00' ), self::local( '2030-01-07 11:00' ) );
		$later = $this->repository->insert( 10, 20, self::local( '2030-01-10 10:00' ), self::local( '2030-01-10 11:00' ) );
		$soon  = $this->repository->insert( 10, 20, self::local( '2030-01-09 10:00' ), self::local( '2030-01-09 11:00' ) );
		$this->repository->insert( 10, 21, self::local( '2030-01-09 12:00' ), self::local( '2030-01-09 13:00' ) );

		$ids = fn( array $bookings ) => array_map( fn( $booking ) => $booking->id, $bookings );

		$this->assertSame( array( $soon->id, $later->id ), $ids( $this->repository->for_user( 20, $now, true ) ) );
		$this->assertSame( array( $past->id ), $ids( $this->repository->for_user( 20, $now, false ) ) );
	}
}
