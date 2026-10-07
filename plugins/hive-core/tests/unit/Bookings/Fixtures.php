<?php
namespace Hive\Core\Tests\Unit\Bookings;

use DateTimeImmutable;
use DateTimeZone;
use Hive\Core\Bookings\Booking;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Bookings\OpeningHours;

/**
 * Shared helpers for booking unit tests. All local times are in Europe/Berlin.
 */
trait Fixtures {

	private static function tz(): DateTimeZone {
		return new DateTimeZone( 'Europe/Berlin' );
	}

	private static function local( string $local ): DateTimeImmutable {
		return new DateTimeImmutable( $local, self::tz() );
	}

	/**
	 * Monday to Friday 08:00-20:00, Saturday 10:00-16:00, closed on Sunday.
	 */
	private static function hours(): OpeningHours {
		$weekday = array(
			'open'  => '08:00',
			'close' => '20:00',
		);

		return OpeningHours::from_meta(
			array(
				'mon' => $weekday,
				'tue' => $weekday,
				'wed' => $weekday,
				'thu' => $weekday,
				'fri' => $weekday,
				'sat' => array(
					'open'  => '10:00',
					'close' => '16:00',
				),
				'sun' => null,
			),
			self::tz()
		);
	}

	private static function booking( string $start, string $end, BookingStatus $status = BookingStatus::Confirmed ): Booking {
		return new Booking( 1, 10, 100, self::local( $start ), self::local( $end ), $status, self::local( '2030-01-01 00:00' ) );
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
