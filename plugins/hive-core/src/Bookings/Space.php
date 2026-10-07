<?php
namespace Hive\Core\Bookings;

/**
 * The parts of a space that booking needs.
 */
final class Space {

	/**
	 * Creates the value object.
	 *
	 * @param int          $id          Space post ID.
	 * @param string       $title       Space name.
	 * @param int          $location_id Location post ID.
	 * @param OpeningHours $hours       Opening hours of the location.
	 */
	public function __construct(
		public readonly int $id,
		public readonly string $title,
		public readonly int $location_id,
		public readonly OpeningHours $hours,
	) {}
}
