<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;

/**
 * Criteria for listing bookings in the admin, CSV export and WP-CLI.
 */
final class BookingFilter {

	public const WHEN_UPCOMING = 'upcoming';
	public const WHEN_PAST     = 'past';
	public const WHEN_ALL      = 'all';

	/**
	 * Creates the filter.
	 *
	 * @param DateTimeImmutable  $now         Current time, splits upcoming from past.
	 * @param string             $when        One of the WHEN_* constants.
	 * @param BookingStatus|null $status      Only bookings with this status, or any.
	 * @param int|null           $location_id Only bookings of spaces in this location, or any.
	 */
	public function __construct(
		public readonly DateTimeImmutable $now,
		public readonly string $when = self::WHEN_UPCOMING,
		public readonly ?BookingStatus $status = null,
		public readonly ?int $location_id = null,
	) {}

	/**
	 * Builds a filter from query-string values, ignoring anything invalid.
	 *
	 * @param array<string, mixed> $query Raw query values (`when`, `status`, `location`).
	 * @param DateTimeImmutable    $now   Current time.
	 */
	public static function from_request( array $query, DateTimeImmutable $now ): self {
		$when = is_string( $query['when'] ?? null ) ? $query['when'] : '';
		if ( ! in_array( $when, array( self::WHEN_UPCOMING, self::WHEN_PAST, self::WHEN_ALL ), true ) ) {
			$when = self::WHEN_UPCOMING;
		}

		$status   = is_string( $query['status'] ?? null ) ? BookingStatus::tryFrom( $query['status'] ) : null;
		$location = is_numeric( $query['location'] ?? null ) ? (int) $query['location'] : 0;

		return new self( $now, $when, $status, $location > 0 ? $location : null );
	}

	/**
	 * Query-string values that recreate this filter.
	 *
	 * @return array<string, string|int>
	 */
	public function to_query_args(): array {
		return array_filter(
			array(
				'when'     => $this->when,
				'status'   => $this->status?->value,
				'location' => $this->location_id,
			),
			static fn( $value ): bool => null !== $value
		);
	}
}
