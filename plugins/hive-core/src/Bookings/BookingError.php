<?php
namespace Hive\Core\Bookings;

use DomainException;

/**
 * A booking rule was broken.
 *
 * Carries a machine-readable reason only; the REST layer turns it into a translated message.
 */
final class BookingError extends DomainException {

	public const INVALID_RANGE   = 'invalid_range';
	public const TOO_SOON        = 'too_soon';
	public const TOO_LONG        = 'too_long';
	public const OUTSIDE_HOURS   = 'outside_hours';
	public const OFF_GRID        = 'off_grid';
	public const CONFLICT        = 'conflict';
	public const CANCEL_WINDOW   = 'cancel_window';
	public const NOT_CANCELLABLE = 'not_cancellable';
	public const SPACE_NOT_FOUND = 'space_not_found';
	public const BUSY            = 'busy';

	/**
	 * Why the booking was rejected; one of the class constants.
	 *
	 * @var string
	 */
	public readonly string $reason;

	/**
	 * Creates the error.
	 *
	 * @param string $reason One of the class constants.
	 */
	public function __construct( string $reason ) {
		$this->reason = $reason;
		parent::__construct( $reason );
	}
}
