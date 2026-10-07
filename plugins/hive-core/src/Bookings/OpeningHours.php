<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Weekly opening hours of a location, in the location's timezone.
 */
final class OpeningHours {

	private const DAYS = array( 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun' );

	private const TIME_PATTERN = '/^([01][0-9]|2[0-3]):[0-5][0-9]$/';

	/**
	 * Use from_meta() to create an instance.
	 *
	 * @param array<string, array{open: string, close: string}|null> $week    Hours per day; null when closed.
	 * @param DateTimeZone                                           $timezone Timezone the hours are in.
	 */
	private function __construct(
		private readonly array $week,
		private readonly DateTimeZone $timezone,
	) {}

	/**
	 * Parses the `hive_opening_hours` meta value. Days with invalid data are treated as closed.
	 *
	 * @param mixed        $value    Raw meta value.
	 * @param DateTimeZone $timezone Timezone the hours are in.
	 */
	public static function from_meta( mixed $value, DateTimeZone $timezone ): self {
		$week = array();

		foreach ( self::DAYS as $day ) {
			$hours        = is_array( $value ) ? ( $value[ $day ] ?? null ) : null;
			$week[ $day ] = self::is_valid_day( $hours ) ? array(
				'open'  => $hours['open'],
				'close' => $hours['close'],
			) : null;
		}

		return new self( $week, $timezone );
	}

	/**
	 * Opening and closing time on the local day of the given moment, or null when closed.
	 *
	 * @param DateTimeImmutable $moment Any time on the day in question, in any timezone.
	 * @return array{0: DateTimeImmutable, 1: DateTimeImmutable}|null
	 */
	public function for_date( DateTimeImmutable $moment ): ?array {
		$local = $moment->setTimezone( $this->timezone );
		$hours = $this->week[ strtolower( $local->format( 'D' ) ) ] ?? null;

		if ( null === $hours ) {
			return null;
		}

		return array( self::time_on( $local, $hours['open'] ), self::time_on( $local, $hours['close'] ) );
	}

	/**
	 * Whether a day entry has valid times with closing after opening.
	 *
	 * @param mixed $hours Raw day entry.
	 * @phpstan-assert-if-true array{open: string, close: string} $hours
	 */
	private static function is_valid_day( mixed $hours ): bool {
		return is_array( $hours )
			&& is_string( $hours['open'] ?? null )
			&& is_string( $hours['close'] ?? null )
			&& preg_match( self::TIME_PATTERN, $hours['open'] )
			&& preg_match( self::TIME_PATTERN, $hours['close'] )
			&& $hours['close'] > $hours['open'];
	}

	/**
	 * The given HH:MM time on the same day as $day.
	 *
	 * @param DateTimeImmutable $day  Day to use.
	 * @param string            $time Time as HH:MM.
	 */
	private static function time_on( DateTimeImmutable $day, string $time ): DateTimeImmutable {
		list( $hours, $minutes ) = array_map( 'intval', explode( ':', $time ) );

		return $day->setTime( $hours, $minutes );
	}
}
