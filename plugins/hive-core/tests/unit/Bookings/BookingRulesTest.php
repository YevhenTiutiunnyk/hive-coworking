<?php
namespace Hive\Core\Tests\Unit\Bookings;

use DateTimeImmutable;
use DateTimeZone;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingRules;
use Hive\Core\Bookings\BookingSettings;
use Hive\Core\Bookings\BookingStatus;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

/**
 * Default settings: 60 minute slots, 60 minutes notice, 8 hours maximum, 2 hour cancellation window.
 * "Now" is Monday 2030-01-07 09:00 in Berlin.
 */
final class BookingRulesTest extends TestCase {

	use Fixtures;

	private BookingRules $rules;

	private DateTimeImmutable $now;

	protected function setUp(): void {
		$this->rules = new BookingRules( new BookingSettings() );
		$this->now   = self::local( '2030-01-07 09:00' );
	}

	public function test_accepts_a_valid_booking(): void {
		$this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ), $this->now );

		$this->addToAssertionCount( 1 );
	}

	public function test_accepts_times_given_in_another_timezone(): void {
		// 09:00 UTC is 10:00 in Berlin in winter.
		$utc = new DateTimeZone( 'UTC' );

		$this->rules->assert_bookable(
			self::hours(),
			new DateTimeImmutable( '2030-01-08 09:00', $utc ),
			new DateTimeImmutable( '2030-01-08 10:00', $utc ),
			$this->now
		);

		$this->addToAssertionCount( 1 );
	}

	public function test_accepts_a_booking_that_fills_the_whole_day_up_to_the_limit(): void {
		$this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 08:00' ), self::local( '2030-01-08 16:00' ), $this->now );

		$this->addToAssertionCount( 1 );
	}

	public function test_rejects_end_before_start(): void {
		$this->assert_rejected(
			BookingError::INVALID_RANGE,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 12:00' ), self::local( '2030-01-08 10:00' ), $this->now )
		);
	}

	public function test_rejects_zero_length_bookings(): void {
		$this->assert_rejected(
			BookingError::INVALID_RANGE,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 10:00' ), $this->now )
		);
	}

	public function test_rejects_bookings_in_the_past(): void {
		$this->assert_rejected(
			BookingError::TOO_SOON,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-07 08:00' ), self::local( '2030-01-07 09:00' ), $this->now )
		);
	}

	public function test_rejects_bookings_inside_the_notice_period(): void {
		$this->assert_rejected(
			BookingError::TOO_SOON,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-07 09:30' ), self::local( '2030-01-07 10:30' ), $this->now )
		);
	}

	public function test_rejects_bookings_longer_than_the_maximum(): void {
		$this->assert_rejected(
			BookingError::TOO_LONG,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 08:00' ), self::local( '2030-01-08 17:00' ), $this->now )
		);
	}

	public function test_rejects_closed_days(): void {
		$this->assert_rejected(
			BookingError::OUTSIDE_HOURS,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-13 10:00' ), self::local( '2030-01-13 11:00' ), $this->now )
		);
	}

	public function test_rejects_bookings_that_run_past_closing_time(): void {
		$this->assert_rejected(
			BookingError::OUTSIDE_HOURS,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 19:00' ), self::local( '2030-01-08 21:00' ), $this->now )
		);
	}

	public function test_rejects_bookings_that_start_before_opening(): void {
		$this->assert_rejected(
			BookingError::OUTSIDE_HOURS,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 07:00' ), self::local( '2030-01-08 09:00' ), $this->now )
		);
	}

	public function test_rejects_times_off_the_slot_grid(): void {
		$this->assert_rejected(
			BookingError::OFF_GRID,
			fn() => $this->rules->assert_bookable( self::hours(), self::local( '2030-01-08 10:30' ), self::local( '2030-01-08 11:30' ), $this->now )
		);
	}

	public function test_slot_grid_follows_the_settings(): void {
		$rules = new BookingRules( new BookingSettings( slot_minutes: 30 ) );

		$rules->assert_bookable( self::hours(), self::local( '2030-01-08 10:30' ), self::local( '2030-01-08 11:00' ), $this->now );

		$this->addToAssertionCount( 1 );
	}

	public function test_owner_can_cancel_before_the_window(): void {
		$booking = self::booking( '2030-01-07 11:00', '2030-01-07 12:00' );

		$this->rules->assert_cancellable( $booking, $this->now, false );

		$this->addToAssertionCount( 1 );
	}

	public function test_owner_cannot_cancel_inside_the_window(): void {
		$booking = self::booking( '2030-01-07 10:00', '2030-01-07 11:00' );

		$this->assert_rejected(
			BookingError::CANCEL_WINDOW,
			fn() => $this->rules->assert_cancellable( $booking, $this->now, false )
		);
	}

	public function test_manager_can_cancel_inside_the_window(): void {
		$booking = self::booking( '2030-01-07 10:00', '2030-01-07 11:00' );

		$this->rules->assert_cancellable( $booking, $this->now, true );

		$this->addToAssertionCount( 1 );
	}

	public function test_cancelled_bookings_cannot_be_cancelled_again(): void {
		$booking = self::booking( '2030-01-08 10:00', '2030-01-08 11:00', BookingStatus::Cancelled );

		$this->assert_rejected(
			BookingError::NOT_CANCELLABLE,
			fn() => $this->rules->assert_cancellable( $booking, $this->now, true )
		);
	}
}
