<?php
namespace Hive\Core\Events;

use DateTimeImmutable;

/**
 * A published event.
 */
final class Event {

	/**
	 * Creates the value object.
	 *
	 * @param int               $id          Event post ID.
	 * @param string            $title       Title.
	 * @param string            $excerpt     Short description.
	 * @param string            $url         Permalink.
	 * @param string            $slug        Post slug.
	 * @param DateTimeImmutable $start       Start time.
	 * @param DateTimeImmutable $end         End time.
	 * @param int               $location_id Location post ID, or 0.
	 */
	public function __construct(
		public readonly int $id,
		public readonly string $title,
		public readonly string $excerpt,
		public readonly string $url,
		public readonly string $slug,
		public readonly DateTimeImmutable $start,
		public readonly DateTimeImmutable $end,
		public readonly int $location_id,
	) {}
}
