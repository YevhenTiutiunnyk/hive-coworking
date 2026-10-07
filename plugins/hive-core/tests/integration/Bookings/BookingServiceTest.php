<?php
namespace Hive\Core\Tests\Integration\Bookings;

use DateTimeImmutable;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Bookings\BookingSettings;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Bookings\SpaceLock;
use Hive\Core\Tests\Integration\TestCase;

/**
 * "Now" is Monday 2030-01-07 09:00 in Berlin. Default settings and default opening hours.
 */
final class BookingServiceTest extends TestCase {

	private BookingService $service;

	private DateTimeImmutable $now;

	private int $space;

	public function set_up(): void {
		parent::set_up();
		$this->service = new BookingService( new BookingRepository(), new SpaceCatalog(), new SpaceLock(), new BookingSettings() );
		$this->now     = self::local( '2030-01-07 09:00' );
		$this->space   = $this->create_space();
	}

	public function test_books_a_free_slot(): void {
		$booking = $this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		$this->assertSame( $this->space, $booking->space_id );
		$this->assertSame( 5, $booking->user_id );
		$this->assertNotNull( ( new BookingRepository() )->find( $booking->id ) );
		$this->assertSame( 1, did_action( 'hive_booking_created' ) );
	}

	public function test_rejects_overlapping_bookings(): void {
		$this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		$this->assert_rejected(
			BookingError::CONFLICT,
			fn() => $this->service->book( 6, $this->space, self::local( '2030-01-08 11:00' ), self::local( '2030-01-08 13:00' ), $this->now )
		);
	}

	public function test_applies_booking_rules(): void {
		$this->assert_rejected(
			BookingError::OUTSIDE_HOURS,
			fn() => $this->service->book( 5, $this->space, self::local( '2030-01-08 19:00' ), self::local( '2030-01-08 21:00' ), $this->now )
		);
	}

	public function test_rejects_unknown_spaces(): void {
		$this->assert_rejected(
			BookingError::SPACE_NOT_FOUND,
			fn() => $this->service->book( 5, 999999, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ), $this->now )
		);
	}

	public function test_reports_busy_when_another_request_holds_the_lock(): void {
		( new SpaceLock() )->acquire( $this->space );

		$this->assert_rejected(
			BookingError::BUSY,
			fn() => $this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ), $this->now )
		);
	}

	public function test_releases_the_lock_after_a_conflict(): void {
		$this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		try {
			$this->service->book( 6, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ), $this->now );
		} catch ( BookingError $error ) {
			// Expected conflict.
			unset( $error );
		}

		$this->assertTrue( ( new SpaceLock() )->acquire( $this->space ) );
	}

	public function test_owner_cancels_a_booking(): void {
		$booking = $this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		$cancelled = $this->service->cancel( $booking, false, $this->now );

		$this->assertSame( BookingStatus::Cancelled, $cancelled->status );
		$this->assertSame( BookingStatus::Cancelled, ( new BookingRepository() )->find( $booking->id )->status );
		$this->assertSame( 1, did_action( 'hive_booking_cancelled' ) );
	}

	public function test_cancelled_slot_can_be_booked_again(): void {
		$booking = $this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );
		$this->service->cancel( $booking, false, $this->now );

		$again = $this->service->book( 6, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		$this->assertSame( 6, $again->user_id );
	}

	public function test_can_cancel_reflects_the_cancellation_window(): void {
		$booking = $this->service->book( 5, $this->space, self::local( '2030-01-07 10:00' ), self::local( '2030-01-07 11:00' ), self::local( '2030-01-07 08:00' ) );

		$this->assertFalse( $this->service->can_cancel( $booking, false, $this->now ) );
		$this->assertTrue( $this->service->can_cancel( $booking, true, $this->now ) );
	}

	public function test_availability_marks_booked_slots(): void {
		$this->service->book( 5, $this->space, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		$slots = $this->service->availability( $this->space, self::local( '2030-01-08 00:00' ), $this->now );

		$this->assertCount( 12, $slots );
		$this->assertSame( array( true, true, false, false, true ), array_column( array_slice( $slots, 0, 5 ), 'available' ) );
	}

	private function assert_rejected( string $reason, callable $action ): void {
		try {
			$action();
		} catch ( BookingError $error ) {
			$this->assertSame( $reason, $error->reason );
			return;
		}

		$this->fail( "Expected BookingError '{$reason}'." );
	}
}
