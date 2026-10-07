<?php
namespace Hive\Core\Blocks;

use DateTimeImmutable;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Content\Meta;
use Hive\Core\Roles;

/**
 * Server-side data for the hive/booking-widget block.
 *
 * The block renders the first day's slots on the server, then the Interactivity API
 * takes over in the browser using the same data shape.
 */
final class BookingWidget {

	public const NAMESPACE = 'hive/booking';

	private const BOOKING_HORIZON_DAYS = 60;

	/**
	 * Creates the helper.
	 *
	 * @param SpaceCatalog   $spaces  Space lookup.
	 * @param BookingService $service Booking service.
	 */
	public function __construct(
		private readonly SpaceCatalog $spaces,
		private readonly BookingService $service,
	) {}

	/**
	 * Creates the helper with default collaborators.
	 */
	public static function create_default(): self {
		return new self( new SpaceCatalog(), BookingService::create_default() );
	}

	/**
	 * Per-block context: the space, the date range and the slots of today.
	 *
	 * @param int               $space_id Space post ID.
	 * @param DateTimeImmutable $now      Current time.
	 * @return array<string, mixed>|null Null when the space cannot be booked.
	 */
	public function context( int $space_id, DateTimeImmutable $now ): ?array {
		if ( null === $this->spaces->find( $space_id ) ) {
			return null;
		}

		$today = $now->setTimezone( wp_timezone() )->setTime( 0, 0 );

		return array(
			'spaceId'      => $space_id,
			'date'         => $today->format( 'Y-m-d' ),
			'minDate'      => $today->format( 'Y-m-d' ),
			'maxDate'      => $today->modify( sprintf( '+%d days', self::BOOKING_HORIZON_DAYS ) )->format( 'Y-m-d' ),
			'hourlyPrice'  => (float) get_post_meta( $space_id, Meta::SPACE_PRICE, true ),
			'slots'        => self::format_slots( $this->service->availability( $space_id, $today, $now ) ),
			'selection'    => array(
				'start' => -1,
				'end'   => -1,
			),
			'isLoading'    => false,
			'isSubmitting' => false,
			'message'      => '',
			'messageType'  => '',
		);
	}

	/**
	 * Shared state for every booking widget on the page.
	 *
	 * @return array<string, mixed>
	 */
	public function state(): array {
		$logged_in = is_user_logged_in();

		return array(
			'restUrl'    => rest_url( 'hive/v1/' ),
			'nonce'      => $logged_in ? wp_create_nonce( 'wp_rest' ) : '',
			'isLoggedIn' => $logged_in,
			'canBook'    => current_user_can( Roles::CAP_BOOK ),
			/**
			 * Filters the ISO 4217 currency used for prices.
			 *
			 * @param string $currency Currency code.
			 */
			'currency'   => (string) apply_filters( 'hive_currency', 'EUR' ),
			'locale'     => str_replace( '_', '-', determine_locale() ),
			'accountUrl' => self::account_url(),
			'strings'    => array(
				/* translators: 1: space name, 2: date, 3: start time, 4: end time. */
				'booked'       => __( 'Booked: %1$s on %2$s, %3$s–%4$s.', 'hive-core' ),
				'error'        => __( 'Something went wrong. Please try again.', 'hive-core' ),
				'closed'       => __( 'Closed on this day.', 'hive-core' ),
				/* translators: 1: start time, 2: end time, 3: number of hours. */
				'summary'      => __( '%1$s–%2$s · %3$s h', 'hive-core' ),
				'selectPrompt' => __( 'Select one or more consecutive slots.', 'hive-core' ),
			),
		);
	}

	/**
	 * Derived state for server rendering, mirroring the getters in view.js.
	 *
	 * Closures are evaluated while WordPress processes directives and are not sent to the browser.
	 *
	 * @param string $select_prompt Text shown before a slot is selected.
	 * @return array<string, \Closure>
	 */
	public static function derived_state( string $select_prompt ): array {
		return array(
			'isClosed'       => static fn(): bool => array() === ( wp_interactivity_get_context()['slots'] ?? array() ),
			'summary'        => static fn(): string => $select_prompt,
			'isSlotSelected' => static fn(): bool => false,
			'canSubmit'      => static fn(): bool => false,
			'isError'        => static fn(): bool => false,
			'isSuccess'      => static fn(): bool => false,
		);
	}

	/**
	 * URL of the members' account page.
	 */
	public static function account_url(): string {
		/**
		 * Filters the URL of the page that lists a member's bookings.
		 *
		 * @param string $url Account page URL.
		 */
		return (string) apply_filters( 'hive_account_url', home_url( '/account/' ) );
	}

	/**
	 * Slots in the shape the view script expects, with times in the site timezone.
	 *
	 * @param list<array{start: DateTimeImmutable, end: DateTimeImmutable, available: bool}> $slots Slots.
	 * @return list<array{start: string, end: string, label: string, available: bool}>
	 */
	public static function format_slots( array $slots ): array {
		$timezone = wp_timezone();

		return array_map(
			static fn( array $slot ): array => array(
				'start'     => $slot['start']->setTimezone( $timezone )->format( DATE_ATOM ),
				'end'       => $slot['end']->setTimezone( $timezone )->format( DATE_ATOM ),
				'label'     => $slot['start']->setTimezone( $timezone )->format( 'H:i' ),
				'available' => $slot['available'],
			),
			$slots
		);
	}
}
