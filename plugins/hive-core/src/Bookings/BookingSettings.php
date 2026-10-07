<?php
namespace Hive\Core\Bookings;

/**
 * Site-wide booking limits.
 */
final class BookingSettings {

	public const SLOT_CHOICES = array( 15, 30, 60 );

	/**
	 * Creates the settings.
	 *
	 * @param int $slot_minutes         Length of one bookable slot.
	 * @param int $min_notice_minutes   How far ahead a booking must start.
	 * @param int $max_duration_minutes Longest allowed booking.
	 * @param int $cancel_window_hours  Members cannot cancel later than this before the start.
	 */
	public function __construct(
		public readonly int $slot_minutes = 60,
		public readonly int $min_notice_minutes = 60,
		public readonly int $max_duration_minutes = 480,
		public readonly int $cancel_window_hours = 2,
	) {}

	/**
	 * Builds settings from a stored option, replacing invalid values with safe ones.
	 *
	 * @param array<string, mixed> $values Raw option values.
	 */
	public static function from_array( array $values ): self {
		$defaults = new self();

		$slot = (int) ( $values['slot_minutes'] ?? $defaults->slot_minutes );
		if ( ! in_array( $slot, self::SLOT_CHOICES, true ) ) {
			$slot = $defaults->slot_minutes;
		}

		return new self(
			slot_minutes: $slot,
			min_notice_minutes: max( 0, (int) ( $values['min_notice_minutes'] ?? $defaults->min_notice_minutes ) ),
			max_duration_minutes: max( $slot, (int) ( $values['max_duration_minutes'] ?? $defaults->max_duration_minutes ) ),
			cancel_window_hours: max( 0, (int) ( $values['cancel_window_hours'] ?? $defaults->cancel_window_hours ) ),
		);
	}

	/**
	 * Converts the settings to an array suitable for storing as an option.
	 *
	 * @return array{slot_minutes: int, min_notice_minutes: int, max_duration_minutes: int, cancel_window_hours: int}
	 */
	public function to_array(): array {
		return array(
			'slot_minutes'         => $this->slot_minutes,
			'min_notice_minutes'   => $this->min_notice_minutes,
			'max_duration_minutes' => $this->max_duration_minutes,
			'cancel_window_hours'  => $this->cancel_window_hours,
		);
	}
}
