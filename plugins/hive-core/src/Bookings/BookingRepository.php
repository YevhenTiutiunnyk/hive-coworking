<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;
use DateTimeZone;
use Hive\Core\Content\Meta;

// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Bookings live in a custom table; results must not be cached because availability changes constantly.

/**
 * Reads and writes bookings in the custom table. Times are stored in UTC.
 */
final class BookingRepository {

	private const DB_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Stores a new confirmed booking.
	 *
	 * @param int               $space_id Space being booked.
	 * @param int               $user_id  Member making the booking.
	 * @param DateTimeImmutable $start    Start time.
	 * @param DateTimeImmutable $end      End time.
	 * @throws \RuntimeException When the database rejects the insert.
	 */
	public function insert( int $space_id, int $user_id, DateTimeImmutable $start, DateTimeImmutable $end ): Booking {
		global $wpdb;

		$wpdb->insert(
			Schema::table(),
			array(
				'space_id'   => $space_id,
				'user_id'    => $user_id,
				'start_utc'  => self::to_db( $start ),
				'end_utc'    => self::to_db( $end ),
				'status'     => BookingStatus::Confirmed->value,
				'created_at' => self::to_db( new DateTimeImmutable( 'now' ) ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		$booking = $this->find( (int) $wpdb->insert_id );
		if ( null === $booking ) {
			throw new \RuntimeException( 'Booking could not be saved: ' . $wpdb->last_error );
		}

		return $booking;
	}

	/**
	 * Finds a booking by ID.
	 *
	 * @param int $id Booking ID.
	 */
	public function find( int $id ): ?Booking {
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', Schema::table(), $id ), ARRAY_A );

		return is_array( $row ) ? self::hydrate( $row ) : null;
	}

	/**
	 * Changes the status of a booking.
	 *
	 * @param Booking       $booking Booking to change.
	 * @param BookingStatus $status  New status.
	 */
	public function update_status( Booking $booking, BookingStatus $status ): Booking {
		global $wpdb;

		$wpdb->update( Schema::table(), array( 'status' => $status->value ), array( 'id' => $booking->id ), array( '%s' ), array( '%d' ) );

		return $booking->with_status( $status );
	}

	/**
	 * Whether a confirmed booking of the space overlaps the range.
	 *
	 * @param int               $space_id Space to check.
	 * @param DateTimeImmutable $start    Range start.
	 * @param DateTimeImmutable $end      Range end.
	 */
	public function has_overlap( int $space_id, DateTimeImmutable $start, DateTimeImmutable $end ): bool {
		global $wpdb;

		$id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM %i WHERE space_id = %d AND status = %s AND start_utc < %s AND end_utc > %s LIMIT 1',
				Schema::table(),
				$space_id,
				BookingStatus::Confirmed->value,
				self::to_db( $end ),
				self::to_db( $start )
			)
		);

		return null !== $id;
	}

	/**
	 * Confirmed bookings of a space that overlap the range, earliest first.
	 *
	 * @param int               $space_id Space to list.
	 * @param DateTimeImmutable $from     Range start.
	 * @param DateTimeImmutable $to       Range end.
	 * @return list<Booking>
	 */
	public function for_space_between( int $space_id, DateTimeImmutable $from, DateTimeImmutable $to ): array {
		global $wpdb;

		return self::hydrate_all(
			$wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %i WHERE space_id = %d AND status = %s AND start_utc < %s AND end_utc > %s ORDER BY start_utc',
					Schema::table(),
					$space_id,
					BookingStatus::Confirmed->value,
					self::to_db( $to ),
					self::to_db( $from )
				),
				ARRAY_A
			)
		);
	}

	/**
	 * A member's bookings: upcoming ones soonest first, or past ones most recent first.
	 *
	 * @param int               $user_id  Member.
	 * @param DateTimeImmutable $now      Current time.
	 * @param bool              $upcoming True for bookings that have not ended yet.
	 * @param int               $limit    Maximum number of bookings.
	 * @return list<Booking>
	 */
	public function for_user( int $user_id, DateTimeImmutable $now, bool $upcoming, int $limit = 50 ): array {
		global $wpdb;

		$query = $upcoming
			? 'SELECT * FROM %i WHERE user_id = %d AND end_utc > %s ORDER BY start_utc ASC LIMIT %d'
			: 'SELECT * FROM %i WHERE user_id = %d AND end_utc <= %s ORDER BY start_utc DESC LIMIT %d';

		return self::hydrate_all(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $query is one of two literal strings above.
			$wpdb->get_results( $wpdb->prepare( $query, Schema::table(), $user_id, self::to_db( $now ), $limit ), ARRAY_A )
		);
	}

	/**
	 * Confirmed bookings of any space that overlap the range, earliest first.
	 *
	 * @param DateTimeImmutable $from Range start.
	 * @param DateTimeImmutable $to   Range end.
	 * @return list<Booking>
	 */
	public function between( DateTimeImmutable $from, DateTimeImmutable $to ): array {
		global $wpdb;

		return self::hydrate_all(
			$wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %i WHERE status = %s AND start_utc < %s AND end_utc > %s ORDER BY start_utc',
					Schema::table(),
					BookingStatus::Confirmed->value,
					self::to_db( $to ),
					self::to_db( $from )
				),
				ARRAY_A
			)
		);
	}

	/**
	 * Bookings matching a filter: upcoming ones soonest first, otherwise most recent first.
	 * Ties are ordered by ID so pagination is stable.
	 *
	 * @param BookingFilter $filter Criteria.
	 * @param int           $limit  Page size.
	 * @param int           $offset Rows to skip.
	 * @return list<Booking>
	 */
	public function search( BookingFilter $filter, int $limit, int $offset = 0 ): array {
		global $wpdb;

		$order = BookingFilter::WHEN_UPCOMING === $filter->when ? 'ASC' : 'DESC';

		return self::hydrate_all(
			$wpdb->get_results(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- where() returns prepared SQL; $order is a literal.
					"SELECT * FROM %i WHERE {$this->where( $filter )} ORDER BY start_utc {$order}, id {$order} LIMIT %d OFFSET %d",
					Schema::table(),
					$limit,
					$offset
				),
				ARRAY_A
			)
		);
	}

	/**
	 * Number of bookings matching a filter.
	 *
	 * @param BookingFilter $filter Criteria.
	 */
	public function count( BookingFilter $filter ): int {
		global $wpdb;

		return (int) $wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- where() returns prepared SQL.
			$wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE {$this->where( $filter )}", Schema::table() )
		);
	}

	/**
	 * Prepared WHERE clause for a filter.
	 *
	 * @param BookingFilter $filter Criteria.
	 */
	private function where( BookingFilter $filter ): string {
		global $wpdb;

		$clauses = array( '1 = 1' );

		if ( null !== $filter->status ) {
			$clauses[] = $wpdb->prepare( 'status = %s', $filter->status->value );
		}

		if ( null !== $filter->location_id ) {
			$clauses[] = $wpdb->prepare(
				'space_id IN (SELECT post_id FROM %i WHERE meta_key = %s AND meta_value = %s)',
				$wpdb->postmeta,
				Meta::SPACE_LOCATION,
				(string) $filter->location_id
			);
		}

		if ( BookingFilter::WHEN_UPCOMING === $filter->when ) {
			$clauses[] = $wpdb->prepare( 'end_utc > %s', self::to_db( $filter->now ) );
		} elseif ( BookingFilter::WHEN_PAST === $filter->when ) {
			$clauses[] = $wpdb->prepare( 'end_utc <= %s', self::to_db( $filter->now ) );
		}

		return implode( ' AND ', $clauses );
	}

	/**
	 * Converts rows to bookings.
	 *
	 * @param mixed $rows Result of $wpdb->get_results().
	 * @return list<Booking>
	 */
	private static function hydrate_all( mixed $rows ): array {
		return is_array( $rows ) ? array_values( array_map( array( self::class, 'hydrate' ), $rows ) ) : array();
	}

	/**
	 * Converts one row to a booking.
	 *
	 * @param array<string, string> $row Database row.
	 */
	private static function hydrate( array $row ): Booking {
		return new Booking(
			(int) $row['id'],
			(int) $row['space_id'],
			(int) $row['user_id'],
			self::from_db( $row['start_utc'] ),
			self::from_db( $row['end_utc'] ),
			BookingStatus::from( $row['status'] ),
			self::from_db( $row['created_at'] )
		);
	}

	/**
	 * Formats a time for the database, in UTC.
	 *
	 * @param DateTimeImmutable $time Time in any timezone.
	 */
	private static function to_db( DateTimeImmutable $time ): string {
		return $time->setTimezone( new DateTimeZone( 'UTC' ) )->format( self::DB_FORMAT );
	}

	/**
	 * Parses a UTC time from the database.
	 *
	 * @param string $value Database value.
	 */
	private static function from_db( string $value ): DateTimeImmutable {
		return new DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
	}
}
