<?php
namespace Hive\Core\Tests\Unit\Bookings;

use DateTimeImmutable;
use Hive\Core\Bookings\BookingSettings;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Bookings\SlotCalculator;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures.php';

final class SlotCalculatorTest extends TestCase {

	use Fixtures;

	private SlotCalculator $calculator;

	private DateTimeImmutable $now;

	protected function setUp(): void {
		$this->calculator = new SlotCalculator( new BookingSettings() );
		$this->now        = self::local( '2030-01-07 09:00' );
	}

	public function test_splits_opening_hours_into_slots(): void {
		$slots = $this->calculator->slots_for_day( self::hours(), self::local( '2030-01-08' ), array(), $this->now );

		$this->assertCount( 12, $slots );
		$this->assertEquals( self::local( '2030-01-08 08:00' ), $slots[0]['start'] );
		$this->assertEquals( self::local( '2030-01-08 09:00' ), $slots[0]['end'] );
		$this->assertEquals( self::local( '2030-01-08 20:00' ), $slots[11]['end'] );
		$this->assertTrue( $slots[0]['available'] );
	}

	public function test_marks_booked_slots_unavailable(): void {
		$bookings = array( self::booking( '2030-01-08 10:00', '2030-01-08 12:00' ) );

		$slots = $this->calculator->slots_for_day( self::hours(), self::local( '2030-01-08' ), $bookings, $this->now );

		$this->assertSame(
			array( true, true, false, false, true ),
			array_column( array_slice( $slots, 0, 5 ), 'available' )
		);
	}

	public function test_ignores_cancelled_bookings(): void {
		$bookings = array( self::booking( '2030-01-08 10:00', '2030-01-08 12:00', BookingStatus::Cancelled ) );

		$slots = $this->calculator->slots_for_day( self::hours(), self::local( '2030-01-08' ), $bookings, $this->now );

		$this->assertNotContains( false, array_column( $slots, 'available' ) );
	}

	public function test_slots_inside_the_notice_period_are_unavailable(): void {
		$now = self::local( '2030-01-08 09:10' );

		$slots = $this->calculator->slots_for_day( self::hours(), self::local( '2030-01-08' ), array(), $now );

		// 08:00, 09:00 and 10:00 start before 10:10; 11:00 is the first bookable slot.
		$this->assertSame(
			array( false, false, false, true ),
			array_column( array_slice( $slots, 0, 4 ), 'available' )
		);
	}

	public function test_closed_days_have_no_slots(): void {
		$this->assertSame( array(), $this->calculator->slots_for_day( self::hours(), self::local( '2030-01-13' ), array(), $this->now ) );
	}

	public function test_slot_length_follows_the_settings(): void {
		$calculator = new SlotCalculator( new BookingSettings( slot_minutes: 30 ) );

		$this->assertCount( 24, $calculator->slots_for_day( self::hours(), self::local( '2030-01-08' ), array(), $this->now ) );
	}
}
