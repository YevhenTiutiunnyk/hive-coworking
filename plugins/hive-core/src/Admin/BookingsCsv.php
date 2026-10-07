<?php
namespace Hive\Core\Admin;

use Hive\Core\Bookings\Booking;
use Hive\Core\Content\Meta;

/**
 * Builds the bookings CSV export.
 */
final class BookingsCsv {

	/**
	 * Column headings.
	 *
	 * @return list<string>
	 */
	public static function header(): array {
		return array(
			__( 'ID', 'hive-core' ),
			__( 'Space', 'hive-core' ),
			__( 'Location', 'hive-core' ),
			__( 'Member', 'hive-core' ),
			__( 'Email', 'hive-core' ),
			__( 'Start', 'hive-core' ),
			__( 'End', 'hive-core' ),
			__( 'Status', 'hive-core' ),
			__( 'Created', 'hive-core' ),
		);
	}

	/**
	 * One row per booking. Times are in the site timezone.
	 *
	 * @param Booking[] $bookings Bookings to export.
	 * @return list<list<string>>
	 */
	public static function rows( array $bookings ): array {
		$timezone = wp_timezone();
		$format   = 'Y-m-d H:i';
		$rows     = array();

		foreach ( $bookings as $booking ) {
			$user     = get_userdata( $booking->user_id );
			$location = (int) get_post_meta( $booking->space_id, Meta::SPACE_LOCATION, true );

			$rows[] = array_map(
				array( self::class, 'escape_cell' ),
				array(
					(string) $booking->id,
					get_the_title( $booking->space_id ),
					$location ? get_the_title( $location ) : '',
					false === $user ? '' : $user->display_name,
					false === $user ? '' : $user->user_email,
					$booking->start->setTimezone( $timezone )->format( $format ),
					$booking->end->setTimezone( $timezone )->format( $format ),
					$booking->status->value,
					$booking->created_at->setTimezone( $timezone )->format( $format ),
				)
			);
		}

		return $rows;
	}

	/**
	 * Prevents CSV formula injection: spreadsheet apps run cells that start with =, +, - or @.
	 *
	 * @param string $value Cell value.
	 */
	public static function escape_cell( string $value ): string {
		return '' !== $value && str_contains( "=+-@\t\r", $value[0] ) ? "'" . $value : $value;
	}
}
