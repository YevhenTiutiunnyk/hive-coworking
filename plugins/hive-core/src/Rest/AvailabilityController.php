<?php
namespace Hive\Core\Rest;

use DateTimeImmutable;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingService;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * GET /hive/v1/spaces/{id}/availability?date=YYYY-MM-DD
 *
 * Public: lists a day's slots so guests can see availability before logging in.
 */
final class AvailabilityController {

	public const NAMESPACE = 'hive/v1';

	/**
	 * Creates the controller.
	 *
	 * @param BookingService $service Booking service.
	 */
	public function __construct( private readonly BookingService $service ) {}

	/**
	 * Registers the route.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/spaces/(?P<id>\d+)/availability',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_availability' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id'   => array(
						'type'     => 'integer',
						'required' => true,
					),
					'date' => array(
						'description' => __( 'Local date in the site timezone (YYYY-MM-DD).', 'hive-core' ),
						'type'        => 'string',
						'pattern'     => '^\d{4}-\d{2}-\d{2}$',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Route callback.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 */
	public function get_availability( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$date = (string) $request['date'];
		$day  = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );

		// createFromFormat() rolls 2030-02-31 over to March, so compare the round trip.
		if ( false === $day || $day->format( 'Y-m-d' ) !== $date ) {
			return new WP_Error( 'hive_invalid_date', __( 'Invalid date.', 'hive-core' ), array( 'status' => 400 ) );
		}

		try {
			$slots = $this->service->availability( (int) $request['id'], $day, current_datetime() );
		} catch ( BookingError $error ) {
			return Errors::from_booking_error( $error );
		}

		$timezone = wp_timezone();

		return new WP_REST_Response(
			array(
				'space_id'     => (int) $request['id'],
				'date'         => $date,
				'timezone'     => $timezone->getName(),
				'slot_minutes' => $this->service->settings()->slot_minutes,
				'slots'        => array_map(
					static fn( array $slot ): array => array(
						'start'     => $slot['start']->setTimezone( $timezone )->format( DATE_ATOM ),
						'end'       => $slot['end']->setTimezone( $timezone )->format( DATE_ATOM ),
						'available' => $slot['available'],
					),
					$slots
				),
			)
		);
	}
}
