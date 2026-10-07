<?php
namespace Hive\Core\Rest;

use Hive\Core\Bookings\BookingError;
use WP_Error;

/**
 * Turns domain errors into REST errors with translated messages and HTTP status codes.
 */
final class Errors {

	/**
	 * Converts a booking error.
	 *
	 * @param BookingError $error Domain error.
	 */
	public static function from_booking_error( BookingError $error ): WP_Error {
		list( $status, $message ) = match ( $error->reason ) {
			BookingError::INVALID_RANGE   => array( 400, __( 'The end time must be after the start time.', 'hive-core' ) ),
			BookingError::TOO_SOON        => array( 400, __( 'This time is too soon to book. Please choose a later slot.', 'hive-core' ) ),
			BookingError::TOO_LONG        => array( 400, __( 'This booking is longer than the maximum allowed.', 'hive-core' ) ),
			BookingError::OUTSIDE_HOURS   => array( 400, __( 'The location is closed at this time.', 'hive-core' ) ),
			BookingError::OFF_GRID        => array( 400, __( 'Bookings must start and end on a slot boundary.', 'hive-core' ) ),
			BookingError::CONFLICT        => array( 409, __( 'This space is already booked for part of that time.', 'hive-core' ) ),
			BookingError::CANCEL_WINDOW   => array( 403, __( 'It is too late to cancel this booking.', 'hive-core' ) ),
			BookingError::NOT_CANCELLABLE => array( 409, __( 'This booking has already been cancelled.', 'hive-core' ) ),
			BookingError::SPACE_NOT_FOUND => array( 404, __( 'Space not found.', 'hive-core' ) ),
			BookingError::BUSY            => array( 503, __( 'Another booking for this space is being processed. Please try again.', 'hive-core' ) ),
			default                       => array( 500, __( 'The booking could not be processed.', 'hive-core' ) ),
		};

		return new WP_Error( 'hive_' . $error->reason, $message, array( 'status' => $status ) );
	}

	/**
	 * Error for a booking that does not exist or that the user may not see.
	 */
	public static function booking_not_found(): WP_Error {
		return new WP_Error( 'hive_booking_not_found', __( 'Booking not found.', 'hive-core' ), array( 'status' => 404 ) );
	}
}
