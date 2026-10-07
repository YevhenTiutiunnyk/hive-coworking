<?php
namespace Hive\Core\Tests\Unit\Bookings;

use Hive\Core\Bookings\BookingStatus;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

final class BookingTest extends TestCase {

	use Fixtures;

	public function test_overlaps_a_range_that_intersects_it(): void {
		$booking = self::booking( '2030-01-08 10:00', '2030-01-08 12:00' );

		$this->assertTrue( $booking->overlaps( self::local( '2030-01-08 11:00' ), self::local( '2030-01-08 13:00' ) ) );
		$this->assertTrue( $booking->overlaps( self::local( '2030-01-08 09:00' ), self::local( '2030-01-08 13:00' ) ) );
		$this->assertTrue( $booking->overlaps( self::local( '2030-01-08 10:30' ), self::local( '2030-01-08 11:00' ) ) );
	}

	public function test_touching_ranges_do_not_overlap(): void {
		$booking = self::booking( '2030-01-08 10:00', '2030-01-08 12:00' );

		$this->assertFalse( $booking->overlaps( self::local( '2030-01-08 12:00' ), self::local( '2030-01-08 13:00' ) ) );
		$this->assertFalse( $booking->overlaps( self::local( '2030-01-08 09:00' ), self::local( '2030-01-08 10:00' ) ) );
	}

	public function test_with_status_returns_a_changed_copy(): void {
		$booking   = self::booking( '2030-01-08 10:00', '2030-01-08 12:00' );
		$cancelled = $booking->with_status( BookingStatus::Cancelled );

		$this->assertSame( BookingStatus::Confirmed, $booking->status );
		$this->assertSame( BookingStatus::Cancelled, $cancelled->status );
		$this->assertSame( $booking->id, $cancelled->id );
	}
}
