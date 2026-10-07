<?php
namespace Hive\Core\Blocks;

use DateTimeImmutable;
use Hive\Core\Bookings\Booking;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Content\Meta;
use Hive\Core\Roles;

/**
 * Data for the hive/my-bookings block.
 */
final class MyBookings {

	public const NAMESPACE = 'hive/account';

	/**
	 * Per-block context: the member's upcoming and past bookings.
	 *
	 * @param int               $user_id Member.
	 * @param DateTimeImmutable $now     Current time.
	 * @return array{tab: string, upcoming: list<array<string, mixed>>, past: list<array<string, mixed>>, pendingId: int, message: string, messageType: string}
	 */
	public static function context( int $user_id, DateTimeImmutable $now ): array {
		$repository = new BookingRepository();
		$service    = BookingService::create_default();
		$is_manager = user_can( $user_id, Roles::CAP_MANAGE );
		$present    = static fn( Booking $booking ): array => self::present( $booking, $service->can_cancel( $booking, $is_manager, $now ) );

		return array(
			'tab'         => 'upcoming',
			'upcoming'    => array_map( $present, $repository->for_user( $user_id, $now, true ) ),
			'past'        => array_map(
				static fn( Booking $booking ): array => self::present( $booking, false ),
				$repository->for_user( $user_id, $now, false )
			),
			'pendingId'   => 0,
			'message'     => '',
			'messageType' => '',
		);
	}

	/**
	 * Shared state: REST details and translated strings.
	 *
	 * @return array<string, mixed>
	 */
	public static function state(): array {
		return array(
			'restUrl' => rest_url( 'hive/v1/' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'strings' => array(
				'confirmCancel' => __( 'Cancel this booking?', 'hive-core' ),
				'cancelled'     => __( 'Booking cancelled.', 'hive-core' ),
				'statusLabel'   => __( 'Cancelled', 'hive-core' ),
				'error'         => __( 'Something went wrong. Please try again.', 'hive-core' ),
			),
		);
	}

	/**
	 * Derived state for server rendering, mirroring the getters in view.js.
	 *
	 * @return array<string, \Closure>
	 */
	public static function derived_state(): array {
		$context = static fn(): array => wp_interactivity_get_context();

		return array(
			'isUpcomingTab'     => static fn(): bool => 'upcoming' === ( $context()['tab'] ?? 'upcoming' ),
			'isPastTab'         => static fn(): bool => 'past' === ( $context()['tab'] ?? 'upcoming' ),
			'showEmptyUpcoming' => static fn(): bool => 'upcoming' === ( $context()['tab'] ?? 'upcoming' ) && array() === ( $context()['upcoming'] ?? array() ),
			'showEmptyPast'     => static fn(): bool => 'past' === ( $context()['tab'] ?? 'upcoming' ) && array() === ( $context()['past'] ?? array() ),
			'isCancelled'       => static fn(): bool => 'cancelled' === ( $context()['booking']['status'] ?? '' ),
			'isPending'         => static fn(): bool => false,
			'isError'           => static fn(): bool => false,
		);
	}

	/**
	 * A booking as shown in the list.
	 *
	 * @param Booking $booking    Booking.
	 * @param bool    $can_cancel Whether the member can still cancel it.
	 * @return array{id: int, space: string, spaceUrl: string, location: string, when: string, status: string, statusLabel: string, canCancel: bool}
	 */
	private static function present( Booking $booking, bool $can_cancel ): array {
		$location = (int) get_post_meta( $booking->space_id, Meta::SPACE_LOCATION, true );
		$start    = $booking->start->getTimestamp();

		return array(
			'id'          => $booking->id,
			'space'       => html_entity_decode( get_the_title( $booking->space_id ), ENT_QUOTES, 'UTF-8' ),
			'spaceUrl'    => (string) get_permalink( $booking->space_id ),
			'location'    => $location ? html_entity_decode( get_the_title( $location ), ENT_QUOTES, 'UTF-8' ) : '',
			'when'        => sprintf(
				/* translators: 1: date, 2: start time, 3: end time. */
				__( '%1$s · %2$s–%3$s', 'hive-core' ),
				wp_date( 'D, j M Y', $start ),
				wp_date( 'H:i', $start ),
				wp_date( 'H:i', $booking->end->getTimestamp() )
			),
			'status'      => $booking->status->value,
			'statusLabel' => BookingStatus::Confirmed === $booking->status ? __( 'Confirmed', 'hive-core' ) : __( 'Cancelled', 'hive-core' ),
			'canCancel'   => $can_cancel,
		);
	}
}
