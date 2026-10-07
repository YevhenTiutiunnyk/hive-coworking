<?php
namespace Hive\Core\Rest;

use DateTimeImmutable;
use Exception;
use Hive\Core\Bookings\Booking;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Content\Meta;
use Hive\Core\Roles;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Bookings of the current user.
 *
 * POST  /hive/v1/bookings       Book a space.
 * GET   /hive/v1/bookings       List upcoming or past bookings.
 * GET   /hive/v1/bookings/{id}  Read one booking.
 * PATCH /hive/v1/bookings/{id}  Cancel a booking.
 */
final class BookingsController {

	/**
	 * Creates the controller.
	 *
	 * @param BookingService    $service    Booking service.
	 * @param BookingRepository $repository Booking storage, for reads.
	 */
	public function __construct(
		private readonly BookingService $service,
		private readonly BookingRepository $repository,
	) {}

	/**
	 * Registers the routes.
	 */
	public function register_routes(): void {
		$time_arg = array(
			'type'     => 'string',
			'format'   => 'date-time',
			'required' => true,
		);

		register_rest_route(
			AvailabilityController::NAMESPACE,
			'/bookings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'check_logged_in' ),
					'args'                => array(
						'scope' => array(
							'type'    => 'string',
							'enum'    => array( 'upcoming', 'past' ),
							'default' => 'upcoming',
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'check_can_book' ),
					'args'                => array(
						'space_id' => array(
							'type'     => 'integer',
							'minimum'  => 1,
							'required' => true,
						),
						'start'    => $time_arg,
						'end'      => $time_arg,
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			AvailabilityController::NAMESPACE,
			'/bookings/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_logged_in' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'check_logged_in' ),
					'args'                => array(
						'status' => array(
							'description' => __( 'Only cancellation is supported.', 'hive-core' ),
							'type'        => 'string',
							'enum'        => array( BookingStatus::Cancelled->value ),
							'required'    => true,
						),
					),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	}

	/**
	 * Permission callback: any logged-in user.
	 */
	public function check_logged_in(): bool|WP_Error {
		if ( is_user_logged_in() ) {
			return true;
		}

		return new WP_Error( 'rest_not_logged_in', __( 'You need to log in.', 'hive-core' ), array( 'status' => 401 ) );
	}

	/**
	 * Permission callback: users who may book spaces.
	 */
	public function check_can_book(): bool|WP_Error {
		$logged_in = $this->check_logged_in();
		if ( true !== $logged_in ) {
			return $logged_in;
		}

		if ( current_user_can( Roles::CAP_BOOK ) ) {
			return true;
		}

		return new WP_Error( 'rest_forbidden', __( 'You are not allowed to book spaces.', 'hive-core' ), array( 'status' => 403 ) );
	}

	/**
	 * Lists the current user's bookings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 */
	public function get_items( WP_REST_Request $request ): WP_REST_Response {
		$bookings = $this->repository->for_user( get_current_user_id(), current_datetime(), 'upcoming' === $request['scope'] );

		return new WP_REST_Response( array_map( array( $this, 'prepare' ), $bookings ) );
	}

	/**
	 * Books a space.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 */
	public function create_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		try {
			$start = new DateTimeImmutable( (string) $request['start'], wp_timezone() );
			$end   = new DateTimeImmutable( (string) $request['end'], wp_timezone() );
		} catch ( Exception $exception ) {
			return new WP_Error( 'rest_invalid_param', __( 'Invalid start or end time.', 'hive-core' ), array( 'status' => 400 ) );
		}

		try {
			$booking = $this->service->book( get_current_user_id(), (int) $request['space_id'], $start, $end, current_datetime() );
		} catch ( BookingError $error ) {
			return Errors::from_booking_error( $error );
		}

		return new WP_REST_Response( $this->prepare( $booking ), 201 );
	}

	/**
	 * Reads one booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 */
	public function get_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$booking = $this->find_visible( (int) $request['id'] );

		return null === $booking ? Errors::booking_not_found() : new WP_REST_Response( $this->prepare( $booking ) );
	}

	/**
	 * Cancels a booking.
	 *
	 * @param WP_REST_Request $request Request.
	 * @phpstan-param WP_REST_Request<array<string, mixed>> $request
	 */
	public function update_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$booking = $this->find_visible( (int) $request['id'] );
		if ( null === $booking ) {
			return Errors::booking_not_found();
		}

		try {
			$cancelled = $this->service->cancel( $booking, current_user_can( Roles::CAP_MANAGE ), current_datetime() );
		} catch ( BookingError $error ) {
			return Errors::from_booking_error( $error );
		}

		return new WP_REST_Response( $this->prepare( $cancelled ) );
	}

	/**
	 * JSON schema of a booking, published for API discovery (OPTIONS requests).
	 *
	 * @return array<string, mixed>
	 */
	public function get_item_schema(): array {
		$time = array(
			'type'   => 'string',
			'format' => 'date-time',
		);

		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'hive-booking',
			'type'       => 'object',
			'properties' => array(
				'id'          => array( 'type' => 'integer' ),
				'space_id'    => array( 'type' => 'integer' ),
				'space_title' => array( 'type' => 'string' ),
				'location_id' => array( 'type' => 'integer' ),
				'user_id'     => array( 'type' => 'integer' ),
				'start'       => $time,
				'end'         => $time,
				'status'      => array(
					'type' => 'string',
					'enum' => array_column( BookingStatus::cases(), 'value' ),
				),
				'created_at'  => $time,
				'can_cancel'  => array( 'type' => 'boolean' ),
			),
		);
	}

	/**
	 * A booking the current user owns or manages, or null.
	 *
	 * Bookings of other members are reported as not found rather than forbidden,
	 * so IDs cannot be probed.
	 *
	 * @param int $id Booking ID.
	 */
	private function find_visible( int $id ): ?Booking {
		$booking = $this->repository->find( $id );
		if ( null === $booking ) {
			return null;
		}

		$visible = get_current_user_id() === $booking->user_id || current_user_can( Roles::CAP_MANAGE );

		return $visible ? $booking : null;
	}

	/**
	 * Shapes a booking for the response. Times are in the site timezone.
	 *
	 * @param Booking $booking Booking.
	 * @return array<string, mixed>
	 */
	private function prepare( Booking $booking ): array {
		$timezone = wp_timezone();
		$space    = get_post( $booking->space_id );

		return array(
			'id'          => $booking->id,
			'space_id'    => $booking->space_id,
			'space_title' => null === $space ? '' : $space->post_title,
			'location_id' => (int) get_post_meta( $booking->space_id, Meta::SPACE_LOCATION, true ),
			'user_id'     => $booking->user_id,
			'start'       => $booking->start->setTimezone( $timezone )->format( DATE_ATOM ),
			'end'         => $booking->end->setTimezone( $timezone )->format( DATE_ATOM ),
			'status'      => $booking->status->value,
			'created_at'  => $booking->created_at->setTimezone( $timezone )->format( DATE_ATOM ),
			'can_cancel'  => $this->service->can_cancel( $booking, current_user_can( Roles::CAP_MANAGE ), current_datetime() ),
		);
	}
}
