<?php
namespace Hive\Core\Bookings;

// phpcs:disable WordPress.DB.DirectDatabaseQuery -- The lock must bypass the object cache to be atomic.

/**
 * A short-lived lock per space, so two requests cannot book the same slot at the same moment.
 *
 * Uses an INSERT into the options table, which fails if the row already exists. This is atomic
 * on both MySQL and SQLite (WordPress Playground), unlike SELECT ... FOR UPDATE or GET_LOCK().
 * WordPress core uses the same technique for its upgrader lock.
 */
final class SpaceLock {

	/**
	 * Creates the lock helper.
	 *
	 * @param int $ttl_seconds How long a lock is valid if its holder never releases it.
	 */
	public function __construct( private readonly int $ttl_seconds = 10 ) {}

	/**
	 * Tries to take the lock. Returns false if another request holds it.
	 *
	 * @param int $space_id Space to lock.
	 */
	public function acquire( int $space_id ): bool {
		global $wpdb;

		$name    = self::option_name( $space_id );
		$expires = (string) ( time() + $this->ttl_seconds );

		$inserted = $wpdb->query(
			$wpdb->prepare( "INSERT IGNORE INTO %i (option_name, option_value, autoload) VALUES (%s, %s, 'off')", $wpdb->options, $name, $expires )
		);
		if ( $inserted ) {
			return true;
		}

		// The lock exists. Take it over only if its holder died without releasing it.
		$current = $wpdb->get_var( $wpdb->prepare( 'SELECT option_value FROM %i WHERE option_name = %s', $wpdb->options, $name ) );
		if ( null === $current || (int) $current > time() ) {
			return false;
		}

		// Compare-and-swap, so only one of several waiting requests wins the takeover.
		$updated = $wpdb->query(
			$wpdb->prepare( 'UPDATE %i SET option_value = %s WHERE option_name = %s AND option_value = %s', $wpdb->options, $expires, $name, $current )
		);

		return 1 === $updated;
	}

	/**
	 * Releases the lock.
	 *
	 * @param int $space_id Space to unlock.
	 */
	public function release( int $space_id ): void {
		global $wpdb;

		$wpdb->delete( $wpdb->options, array( 'option_name' => self::option_name( $space_id ) ) );
	}

	/**
	 * Option name used for a space's lock.
	 *
	 * @param int $space_id Space ID.
	 */
	private static function option_name( int $space_id ): string {
		return 'hive_lock_space_' . $space_id;
	}
}
