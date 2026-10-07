<?php
namespace Hive\Core\Bookings;

use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;

/**
 * Looks up bookable spaces.
 */
final class SpaceCatalog {

	/**
	 * A published space in a published location, or null.
	 *
	 * @param int $space_id Space post ID.
	 */
	public function find( int $space_id ): ?Space {
		$space = get_post( $space_id );
		if ( null === $space || PostTypes::SPACE !== $space->post_type || 'publish' !== $space->post_status ) {
			return null;
		}

		$location_id = (int) get_post_meta( $space_id, Meta::SPACE_LOCATION, true );
		$location    = get_post( $location_id );
		if ( null === $location || PostTypes::LOCATION !== $location->post_type || 'publish' !== $location->post_status ) {
			return null;
		}

		return new Space(
			$space_id,
			$space->post_title,
			$location_id,
			OpeningHours::from_meta( get_post_meta( $location_id, Meta::LOCATION_HOURS, true ), wp_timezone() )
		);
	}

	/**
	 * All bookable spaces, ordered by title.
	 *
	 * @return list<Space>
	 */
	public function all(): array {
		$posts = get_posts(
			array(
				'post_type'   => PostTypes::SPACE,
				'post_status' => 'publish',
				'numberposts' => -1,
				'orderby'     => 'title',
				'order'       => 'ASC',
			)
		);

		$spaces = array();
		foreach ( $posts as $post ) {
			$space = $this->find( $post->ID );
			if ( null !== $space ) {
				$spaces[] = $space;
			}
		}

		return $spaces;
	}
}
