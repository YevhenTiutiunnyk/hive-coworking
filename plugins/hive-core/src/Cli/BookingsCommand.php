<?php
namespace Hive\Core\Cli;

use Hive\Core\Bookings\BookingFilter;
use Hive\Core\Bookings\BookingRepository;
use WP_CLI\Utils;

/**
 * Lists bookings.
 */
final class BookingsCommand {

	/**
	 * Lists bookings, soonest first.
	 *
	 * ## OPTIONS
	 *
	 * [--when=<when>]
	 * : Which bookings to list.
	 * ---
	 * default: upcoming
	 * options:
	 *   - upcoming
	 *   - past
	 *   - all
	 * ---
	 *
	 * [--status=<status>]
	 * : Only bookings with this status.
	 * ---
	 * options:
	 *   - confirmed
	 *   - cancelled
	 * ---
	 *
	 * [--location=<id>]
	 * : Only bookings of spaces in this location.
	 *
	 * [--limit=<number>]
	 * : Maximum number of bookings.
	 * ---
	 * default: 100
	 * ---
	 *
	 * [--format=<format>]
	 * : Output format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - json
	 *   - count
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     wp hive bookings list
	 *     wp hive bookings list --when=past --format=csv
	 *
	 * @subcommand list
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Named arguments.
	 */
	public function list_( array $args, array $assoc_args ): void {
		$filter   = BookingFilter::from_request( $assoc_args, current_datetime() );
		$bookings = ( new BookingRepository() )->search( $filter, max( 1, (int) ( $assoc_args['limit'] ?? 100 ) ) );
		$timezone = wp_timezone();

		$items = array_map(
			static function ( $booking ) use ( $timezone ): array {
				$user = get_userdata( $booking->user_id );

				return array(
					'id'     => $booking->id,
					'space'  => get_the_title( $booking->space_id ),
					'member' => false === $user ? '' : $user->user_login,
					'start'  => $booking->start->setTimezone( $timezone )->format( 'Y-m-d H:i' ),
					'end'    => $booking->end->setTimezone( $timezone )->format( 'H:i' ),
					'status' => $booking->status->value,
				);
			},
			$bookings
		);

		Utils\format_items( $assoc_args['format'] ?? 'table', $items, array( 'id', 'space', 'member', 'start', 'end', 'status' ) );
	}
}
