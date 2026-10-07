<?php
namespace Hive\Core\Bookings;

/**
 * Lifecycle state of a booking.
 */
enum BookingStatus: string {
	case Confirmed = 'confirmed';
	case Cancelled = 'cancelled';
}
