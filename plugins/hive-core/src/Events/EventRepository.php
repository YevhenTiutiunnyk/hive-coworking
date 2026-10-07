<?php
namespace Hive\Core\Events;

use DateTimeImmutable;
use Exception;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use WP_Post;

/**
 * Reads events from posts.
 */
final class EventRepository {

	/**
	 * Events that have not ended yet, soonest first.
	 *
	 * Dates are compared as times rather than as meta strings, so events with different
	 * UTC offsets (e.g. either side of a daylight saving change) sort correctly.
	 *
	 * @param DateTimeImmutable $now         Current time.
	 * @param int               $limit       Maximum number of events.
	 * @param int               $location_id Only events at this location; 0 for all.
	 * @return list<Event>
	 */
	public function upcoming( DateTimeImmutable $now, int $limit, int $location_id = 0 ): array {
		$args = array(
			'post_type'   => PostTypes::EVENT,
			'post_status' => 'publish',
			'numberposts' => 100,
		);

		if ( $location_id > 0 ) {
			$args['meta_key']   = Meta::EVENT_LOCATION; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Small, capped query.
			$args['meta_value'] = $location_id; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- See above.
		}

		$events = array_filter(
			array_map( array( $this, 'to_event' ), get_posts( $args ) ),
			static fn( ?Event $event ): bool => null !== $event && $event->end > $now
		);

		usort( $events, static fn( Event $a, Event $b ): int => $a->start <=> $b->start );

		return array_slice( $events, 0, $limit );
	}

	/**
	 * A published event, or null.
	 *
	 * @param int $id Event post ID.
	 */
	public function find( int $id ): ?Event {
		$post = get_post( $id );

		if ( null === $post || PostTypes::EVENT !== $post->post_type || 'publish' !== $post->post_status ) {
			return null;
		}

		return $this->to_event( $post );
	}

	/**
	 * Converts a post, or returns null when its dates are missing or invalid.
	 *
	 * @param WP_Post $post Event post.
	 */
	private function to_event( WP_Post $post ): ?Event {
		try {
			$start = new DateTimeImmutable( (string) get_post_meta( $post->ID, Meta::EVENT_STARTS, true ), wp_timezone() );
			$end   = new DateTimeImmutable( (string) get_post_meta( $post->ID, Meta::EVENT_ENDS, true ), wp_timezone() );
		} catch ( Exception $exception ) {
			return null;
		}

		// An empty meta value parses as "now"; require real dates.
		if ( '' === get_post_meta( $post->ID, Meta::EVENT_STARTS, true ) || $end <= $start ) {
			return null;
		}

		return new Event(
			$post->ID,
			get_the_title( $post ),
			get_the_excerpt( $post ),
			(string) get_permalink( $post ),
			$post->post_name,
			$start,
			$end,
			(int) get_post_meta( $post->ID, Meta::EVENT_LOCATION, true )
		);
	}
}
