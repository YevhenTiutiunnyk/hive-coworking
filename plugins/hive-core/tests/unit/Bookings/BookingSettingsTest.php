<?php
namespace Hive\Core\Tests\Unit\Bookings;

use Hive\Core\Bookings\BookingSettings;
use PHPUnit\Framework\TestCase;

final class BookingSettingsTest extends TestCase {

	public function test_has_sensible_defaults(): void {
		$settings = new BookingSettings();

		$this->assertSame( 60, $settings->slot_minutes );
		$this->assertSame( 60, $settings->min_notice_minutes );
		$this->assertSame( 480, $settings->max_duration_minutes );
		$this->assertSame( 2, $settings->cancel_window_hours );
	}

	public function test_reads_values_from_an_option_array(): void {
		$settings = BookingSettings::from_array(
			array(
				'slot_minutes'         => '30',
				'min_notice_minutes'   => '0',
				'max_duration_minutes' => '120',
				'cancel_window_hours'  => '24',
			)
		);

		$this->assertSame( 30, $settings->slot_minutes );
		$this->assertSame( 0, $settings->min_notice_minutes );
		$this->assertSame( 120, $settings->max_duration_minutes );
		$this->assertSame( 24, $settings->cancel_window_hours );
	}

	public function test_unsupported_slot_length_falls_back_to_default(): void {
		$this->assertSame( 60, BookingSettings::from_array( array( 'slot_minutes' => 45 ) )->slot_minutes );
	}

	public function test_negative_values_are_clamped_to_zero(): void {
		$settings = BookingSettings::from_array(
			array(
				'min_notice_minutes'  => -10,
				'cancel_window_hours' => -1,
			)
		);

		$this->assertSame( 0, $settings->min_notice_minutes );
		$this->assertSame( 0, $settings->cancel_window_hours );
	}

	public function test_max_duration_is_at_least_one_slot(): void {
		$settings = BookingSettings::from_array(
			array(
				'slot_minutes'         => 30,
				'max_duration_minutes' => 10,
			)
		);

		$this->assertSame( 30, $settings->max_duration_minutes );
	}

	public function test_round_trips_through_an_array(): void {
		$settings = new BookingSettings( 15, 30, 240, 12 );

		$this->assertEquals( $settings, BookingSettings::from_array( $settings->to_array() ) );
	}
}
