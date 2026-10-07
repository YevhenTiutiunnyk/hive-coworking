<?php
namespace Hive\Core\Tests\Unit\Bookings;

use Hive\Core\Bookings\Booking;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Bookings\OccupancyReport;
use Hive\Core\Bookings\OpeningHours;
use Hive\Core\Bookings\Space;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

/**
 * 2030-01-08 is a Tuesday: locations are open 08:00-20:00 (720 minutes).
 */
final class OccupancyReportTest extends TestCase {

	use Fixtures;

	public function test_sums_booked_and_open_minutes_per_location(): void {
		$spaces   = array( $this->space( 1, 100 ), $this->space( 2, 100 ), $this->space( 3, 200 ) );
		$bookings = array(
			$this->booking_of( 1, '2030-01-08 10:00', '2030-01-08 12:00' ),
			$this->booking_of( 2, '2030-01-08 14:00', '2030-01-08 15:00' ),
			$this->booking_of( 2, '2030-01-08 16:00', '2030-01-08 17:00', BookingStatus::Cancelled ),
		);

		$report = OccupancyReport::for_day( $spaces, $bookings, self::local( '2030-01-08' ) );

		$this->assertSame(
			array(
				'spaces'         => 2,
				'bookings'       => 2,
				'booked_minutes' => 180,
				'open_minutes'   => 1440,
			),
			$report[100]
		);
		$this->assertSame( 0, $report[200]['bookings'] );
		$this->assertSame( 13, OccupancyReport::percent( $report[100] ) );
	}

	public function test_closed_locations_report_zero_percent(): void {
		$report = OccupancyReport::for_day( array( $this->space( 1, 100 ) ), array(), self::local( '2030-01-13' ) );

		$this->assertSame( 0, $report[100]['open_minutes'] );
		$this->assertSame( 0, OccupancyReport::percent( $report[100] ) );
	}

	public function test_ignores_bookings_of_unknown_spaces(): void {
		$report = OccupancyReport::for_day(
			array( $this->space( 1, 100 ) ),
			array( $this->booking_of( 99, '2030-01-08 10:00', '2030-01-08 12:00' ) ),
			self::local( '2030-01-08' )
		);

		$this->assertSame( 0, $report[100]['bookings'] );
	}

	private function space( int $id, int $location ): Space {
		return new Space( $id, "Space {$id}", $location, self::hours() );
	}

	private function booking_of( int $space, string $start, string $end, BookingStatus $status = BookingStatus::Confirmed ): Booking {
		return new Booking( 0, $space, 1, self::local( $start ), self::local( $end ), $status, self::local( '2030-01-01' ) );
	}
}
