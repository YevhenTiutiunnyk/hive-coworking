<?php
namespace Hive\Core\Bookings;

use DateInterval;
use DateTimeImmutable;

/**
 * Splits a day's opening hours into bookable slots.
 */
final class SlotCalculator {

	/**
	 * Creates the calculator.
	 *
	 * @param BookingSettings $settings Site-wide booking limits.
	 */
	public function __construct( private readonly BookingSettings $settings ) {}

	/**
	 * All slots on the given day and whether each can still be booked.
	 *
	 * @param OpeningHours      $hours    Opening hours of the location.
	 * @param DateTimeImmutable $day      Any time on the local day.
	 * @param Booking[]         $bookings Bookings of the space on that day.
	 * @param DateTimeImmutable $now      Current time.
	 * @return list<array{start: DateTimeImmutable, end: DateTimeImmutable, available: bool}>
	 */
	public function slots_for_day( OpeningHours $hours, DateTimeImmutable $day, array $bookings, DateTimeImmutable $now ): array {
		$window = $hours->for_date( $day );
		if ( null === $window ) {
			return array();
		}

		$step     = new DateInterval( sprintf( 'PT%dM', $this->settings->slot_minutes ) );
		$earliest = ( new BookingRules( $this->settings ) )->earliest_start( $now );
		$slots    = array();
		$start    = $window[0];
		$end      = $start->add( $step );

		while ( $end <= $window[1] ) {
			$slots[] = array(
				'start'     => $start,
				'end'       => $end,
				'available' => $start >= $earliest && ! self::is_taken( $start, $end, $bookings ),
			);

			$start = $end;
			$end   = $start->add( $step );
		}

		return $slots;
	}

	/**
	 * Whether a confirmed booking covers any part of the range.
	 *
	 * @param DateTimeImmutable $start    Range start.
	 * @param DateTimeImmutable $end      Range end.
	 * @param Booking[]         $bookings Bookings to check.
	 */
	private static function is_taken( DateTimeImmutable $start, DateTimeImmutable $end, array $bookings ): bool {
		foreach ( $bookings as $booking ) {
			if ( BookingStatus::Confirmed === $booking->status && $booking->overlaps( $start, $end ) ) {
				return true;
			}
		}

		return false;
	}
}
