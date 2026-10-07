<?php
namespace Hive\Core\Tests\Unit\Bookings;

use DateTimeZone;
use Hive\Core\Bookings\OpeningHours;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

final class OpeningHoursTest extends TestCase {

	use Fixtures;

	public function test_returns_open_and_close_times_for_the_day(): void {
		// 2030-01-08 is a Tuesday.
		$window = self::hours()->for_date( self::local( '2030-01-08 13:37' ) );

		$this->assertEquals( self::local( '2030-01-08 08:00' ), $window[0] );
		$this->assertEquals( self::local( '2030-01-08 20:00' ), $window[1] );
	}

	public function test_returns_null_on_closed_days(): void {
		$this->assertNull( self::hours()->for_date( self::local( '2030-01-13 12:00' ) ) );
	}

	public function test_converts_the_moment_to_the_location_timezone(): void {
		// Saturday 23:30 UTC is already Sunday in Berlin, when the location is closed.
		$moment = new \DateTimeImmutable( '2030-01-12 23:30', new DateTimeZone( 'UTC' ) );

		$this->assertNull( self::hours()->for_date( $moment ) );
	}

	public function test_invalid_days_are_treated_as_closed(): void {
		$hours = OpeningHours::from_meta(
			array(
				'mon' => array(
					'open'  => '9am',
					'close' => '17:00',
				),
				'tue' => array(
					'open'  => '18:00',
					'close' => '09:00',
				),
				'wed' => 'always',
			),
			self::tz()
		);

		$this->assertNull( $hours->for_date( self::local( '2030-01-07 12:00' ) ) );
		$this->assertNull( $hours->for_date( self::local( '2030-01-08 12:00' ) ) );
		$this->assertNull( $hours->for_date( self::local( '2030-01-09 12:00' ) ) );
	}

	public function test_non_array_meta_means_always_closed(): void {
		$hours = OpeningHours::from_meta( '', self::tz() );

		$this->assertNull( $hours->for_date( self::local( '2030-01-08 12:00' ) ) );
	}
}
