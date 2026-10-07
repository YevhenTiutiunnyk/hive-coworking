<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;

/**
 * Business rules for making and cancelling bookings.
 *
 * Checks that do not need the database live here, so they can be unit tested without WordPress.
 * Overlap with other bookings is checked by BookingService, which has the data.
 */
final class BookingRules {

	/**
	 * Creates the rules.
	 *
	 * @param BookingSettings $settings Site-wide booking limits.
	 */
	public function __construct( private readonly BookingSettings $settings ) {}

	/**
	 * Throws unless a booking for the given range may be made.
	 *
	 * @param OpeningHours      $hours Opening hours of the space's location.
	 * @param DateTimeImmutable $start Requested start.
	 * @param DateTimeImmutable $end   Requested end.
	 * @param DateTimeImmutable $now   Current time.
	 * @throws BookingError When a rule is broken.
	 */
	public function assert_bookable( OpeningHours $hours, DateTimeImmutable $start, DateTimeImmutable $end, DateTimeImmutable $now ): void {
		if ( $end <= $start ) {
			throw new BookingError( BookingError::INVALID_RANGE );
		}

		if ( $start < $this->earliest_start( $now ) ) {
			throw new BookingError( BookingError::TOO_SOON );
		}

		$minutes = ( $end->getTimestamp() - $start->getTimestamp() ) / 60;
		if ( $minutes > $this->settings->max_duration_minutes ) {
			throw new BookingError( BookingError::TOO_LONG );
		}

		$window = $hours->for_date( $start );
		if ( null === $window || $start < $window[0] || $end > $window[1] ) {
			throw new BookingError( BookingError::OUTSIDE_HOURS );
		}

		if ( ! $this->is_on_grid( $start, $window[0] ) || ! $this->is_on_grid( $end, $window[0] ) ) {
			throw new BookingError( BookingError::OFF_GRID );
		}
	}

	/**
	 * Throws unless the booking may be cancelled now.
	 *
	 * @param Booking           $booking    Booking to cancel.
	 * @param DateTimeImmutable $now        Current time.
	 * @param bool              $is_manager Managers may cancel at any time.
	 * @throws BookingError When the booking cannot be cancelled.
	 */
	public function assert_cancellable( Booking $booking, DateTimeImmutable $now, bool $is_manager ): void {
		if ( BookingStatus::Confirmed !== $booking->status ) {
			throw new BookingError( BookingError::NOT_CANCELLABLE );
		}

		$deadline = $booking->start->modify( sprintf( '-%d hours', $this->settings->cancel_window_hours ) );
		if ( ! $is_manager && $now > $deadline ) {
			throw new BookingError( BookingError::CANCEL_WINDOW );
		}
	}

	/**
	 * The earliest time a new booking may start.
	 *
	 * @param DateTimeImmutable $now Current time.
	 */
	public function earliest_start( DateTimeImmutable $now ): DateTimeImmutable {
		return $now->modify( sprintf( '+%d minutes', $this->settings->min_notice_minutes ) );
	}

	/**
	 * Whether a time lies a whole number of slots after opening.
	 *
	 * @param DateTimeImmutable $time  Time to check.
	 * @param DateTimeImmutable $opens Opening time that day.
	 */
	private function is_on_grid( DateTimeImmutable $time, DateTimeImmutable $opens ): bool {
		return 0 === ( $time->getTimestamp() - $opens->getTimestamp() ) % ( $this->settings->slot_minutes * 60 );
	}
}
