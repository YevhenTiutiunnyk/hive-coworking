<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;

/**
 * An immutable booking of one space for a time range.
 */
final class Booking {

	/**
	 * Creates a booking value object.
	 *
	 * @param int               $id         Database ID.
	 * @param int               $space_id   Booked space (hive_space post ID).
	 * @param int               $user_id    Member who made the booking.
	 * @param DateTimeImmutable $start      Start time (inclusive).
	 * @param DateTimeImmutable $end        End time (exclusive).
	 * @param BookingStatus     $status     Current status.
	 * @param DateTimeImmutable $created_at When the booking was made.
	 */
	public function __construct(
		public readonly int $id,
		public readonly int $space_id,
		public readonly int $user_id,
		public readonly DateTimeImmutable $start,
		public readonly DateTimeImmutable $end,
		public readonly BookingStatus $status,
		public readonly DateTimeImmutable $created_at,
	) {}

	/**
	 * Whether this booking shares any time with the given range. Touching ranges do not overlap.
	 *
	 * @param DateTimeImmutable $start Range start.
	 * @param DateTimeImmutable $end   Range end.
	 */
	public function overlaps( DateTimeImmutable $start, DateTimeImmutable $end ): bool {
		return $this->start < $end && $this->end > $start;
	}

	/**
	 * Returns a copy with a different status.
	 *
	 * @param BookingStatus $status New status.
	 */
	public function with_status( BookingStatus $status ): self {
		return new self( $this->id, $this->space_id, $this->user_id, $this->start, $this->end, $status, $this->created_at );
	}
}
