<?php
namespace Hive\Core\Blocks;

use DateTimeImmutable;
use Hive\Core\Bookings\OpeningHours;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;

/**
 * Data for the hive/opening-hours block.
 */
final class OpeningHoursTable {

	/**
	 * The location of a post: itself for a location, its location for a space, otherwise 0.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function location_for( int $post_id ): int {
		return match ( get_post_type( $post_id ) ) {
			PostTypes::LOCATION => $post_id,
			PostTypes::SPACE    => (int) get_post_meta( $post_id, Meta::SPACE_LOCATION, true ),
			default             => 0,
		};
	}

	/**
	 * One row per weekday, Monday first.
	 *
	 * @param int               $location_id Location ID.
	 * @param DateTimeImmutable $now         Current time; its weekday is marked as today.
	 * @return list<array{day: string, hours: string, today: bool}>
	 */
	public static function rows( int $location_id, DateTimeImmutable $now ): array {
		$hours  = OpeningHours::from_meta( get_post_meta( $location_id, Meta::LOCATION_HOURS, true ), wp_timezone() );
		$today  = $now->setTimezone( wp_timezone() );
		$monday = $today->modify( 'monday this week' )->setTime( 12, 0 );
		$rows   = array();

		for ( $offset = 0; $offset < 7; $offset++ ) {
			$day    = $monday->modify( sprintf( '+%d days', $offset ) );
			$window = $hours->for_date( $day );

			$rows[] = array(
				'day'   => wp_date( 'l', $day->getTimestamp() ),
				'hours' => null === $window ? __( 'Closed', 'hive-core' ) : $window[0]->format( 'H:i' ) . '–' . $window[1]->format( 'H:i' ),
				'today' => $day->format( 'Y-m-d' ) === $today->format( 'Y-m-d' ),
			);
		}

		return $rows;
	}
}
