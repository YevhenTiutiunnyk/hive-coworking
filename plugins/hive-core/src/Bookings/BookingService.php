<?php
namespace Hive\Core\Bookings;

use DateTimeImmutable;
use Hive\Core\Settings;

/**
 * Makes and cancels bookings, and reports availability.
 *
 * This is the only entry point that writes bookings, so every booking passes the same checks.
 */
final class BookingService {

	private readonly BookingRules $rules;

	/**
	 * Creates the service.
	 *
	 * @param BookingRepository $repository Booking storage.
	 * @param SpaceCatalog      $spaces     Space lookup.
	 * @param SpaceLock         $lock       Per-space lock.
	 * @param BookingSettings   $settings   Site-wide booking limits.
	 */
	public function __construct(
		private readonly BookingRepository $repository,
		private readonly SpaceCatalog $spaces,
		private readonly SpaceLock $lock,
		private readonly BookingSettings $settings,
	) {
		$this->rules = new BookingRules( $settings );
	}

	/**
	 * The service wired with its default collaborators and the stored settings.
	 */
	public static function create_default(): self {
		return new self( new BookingRepository(), new SpaceCatalog(), new SpaceLock(), Settings::booking() );
	}

	/**
	 * Site-wide booking limits in use.
	 */
	public function settings(): BookingSettings {
		return $this->settings;
	}

	/**
	 * Books a space for a member.
	 *
	 * @param int               $user_id  Member making the booking.
	 * @param int               $space_id Space to book.
	 * @param DateTimeImmutable $start    Start time.
	 * @param DateTimeImmutable $end      End time.
	 * @param DateTimeImmutable $now      Current time.
	 * @throws BookingError When the booking is not allowed.
	 */
	public function book( int $user_id, int $space_id, DateTimeImmutable $start, DateTimeImmutable $end, DateTimeImmutable $now ): Booking {
		$space = $this->find_space( $space_id );
		$this->rules->assert_bookable( $space->hours, $start, $end, $now );

		if ( ! $this->lock->acquire( $space_id ) ) {
			throw new BookingError( BookingError::BUSY );
		}

		try {
			if ( $this->repository->has_overlap( $space_id, $start, $end ) ) {
				throw new BookingError( BookingError::CONFLICT );
			}

			$booking = $this->repository->insert( $space_id, $user_id, $start, $end );
		} finally {
			$this->lock->release( $space_id );
		}

		/**
		 * Fires after a booking is made.
		 *
		 * @param Booking $booking The new booking.
		 */
		do_action( 'hive_booking_created', $booking );

		return $booking;
	}

	/**
	 * Cancels a booking.
	 *
	 * @param Booking           $booking    Booking to cancel.
	 * @param bool              $is_manager Whether the current user manages bookings.
	 * @param DateTimeImmutable $now        Current time.
	 * @throws BookingError When the booking cannot be cancelled.
	 */
	public function cancel( Booking $booking, bool $is_manager, DateTimeImmutable $now ): Booking {
		$this->rules->assert_cancellable( $booking, $now, $is_manager );

		$cancelled = $this->repository->update_status( $booking, BookingStatus::Cancelled );

		/**
		 * Fires after a booking is cancelled.
		 *
		 * @param Booking $cancelled The cancelled booking.
		 */
		do_action( 'hive_booking_cancelled', $cancelled );

		return $cancelled;
	}

	/**
	 * Whether the booking may be cancelled now.
	 *
	 * @param Booking           $booking    Booking to check.
	 * @param bool              $is_manager Whether the current user manages bookings.
	 * @param DateTimeImmutable $now        Current time.
	 */
	public function can_cancel( Booking $booking, bool $is_manager, DateTimeImmutable $now ): bool {
		try {
			$this->rules->assert_cancellable( $booking, $now, $is_manager );
			return true;
		} catch ( BookingError $error ) {
			return false;
		}
	}

	/**
	 * Slots of a space on a local day.
	 *
	 * @param int               $space_id Space to check.
	 * @param DateTimeImmutable $day      Midnight of the local day.
	 * @param DateTimeImmutable $now      Current time.
	 * @return list<array{start: DateTimeImmutable, end: DateTimeImmutable, available: bool}>
	 * @throws BookingError When the space does not exist.
	 */
	public function availability( int $space_id, DateTimeImmutable $day, DateTimeImmutable $now ): array {
		$space    = $this->find_space( $space_id );
		$bookings = $this->repository->for_space_between( $space_id, $day, $day->modify( '+1 day' ) );

		return ( new SlotCalculator( $this->settings ) )->slots_for_day( $space->hours, $day, $bookings, $now );
	}

	/**
	 * Looks up a bookable space.
	 *
	 * @param int $space_id Space ID.
	 * @throws BookingError When the space does not exist or is not bookable.
	 */
	private function find_space( int $space_id ): Space {
		$space = $this->spaces->find( $space_id );
		if ( null === $space ) {
			throw new BookingError( BookingError::SPACE_NOT_FOUND );
		}

		return $space;
	}
}
