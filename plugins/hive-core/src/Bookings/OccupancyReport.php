<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;

/**
 * How busy each location is on a given day.
 */
final class OccupancyReport {

	/**
	 * Totals per location: number of spaces and bookings, booked minutes and bookable minutes.
	 *
	 * @param Space[]           $spaces   All bookable spaces.
	 * @param Booking[]         $bookings Bookings on that day.
	 * @param DateTimeImmutable $day      Any time on the local day.
	 * @return array<int, array{spaces: int, bookings: int, booked_minutes: int, open_minutes: int}>
	 */
	public static function for_day( array $spaces, array $bookings, DateTimeImmutable $day ): array {
		$report   = array();
		$location = array();

		foreach ( $spaces as $space ) {
			$location[ $space->id ] = $space->location_id;

			$report[ $space->location_id ] ??= array(
				'spaces'         => 0,
				'bookings'       => 0,
				'booked_minutes' => 0,
				'open_minutes'   => 0,
			);

			$window = $space->hours->for_date( $day );

			++$report[ $space->location_id ]['spaces'];
			$report[ $space->location_id ]['open_minutes'] += null === $window ? 0 : self::minutes( $window[0], $window[1] );
		}

		foreach ( $bookings as $booking ) {
			if ( BookingStatus::Confirmed !== $booking->status || ! isset( $location[ $booking->space_id ] ) ) {
				continue;
			}

			$location_id = $location[ $booking->space_id ];
			++$report[ $location_id ]['bookings'];
			$report[ $location_id ]['booked_minutes'] += self::minutes( $booking->start, $booking->end );
		}

		return $report;
	}

	/**
	 * Booked share of bookable time, as a whole percentage.
	 *
	 * @param array{booked_minutes: int, open_minutes: int} $row One location's totals.
	 */
	public static function percent( array $row ): int {
		return $row['open_minutes'] > 0 ? (int) round( 100 * $row['booked_minutes'] / $row['open_minutes'] ) : 0;
	}

	/**
	 * Minutes between two times.
	 *
	 * @param DateTimeImmutable $start Start.
	 * @param DateTimeImmutable $end   End.
	 */
	private static function minutes( DateTimeImmutable $start, DateTimeImmutable $end ): int {
		return intdiv( $end->getTimestamp() - $start->getTimestamp(), 60 );
	}
}
