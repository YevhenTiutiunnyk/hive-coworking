<?php
namespace Hive\Core\Bookings;

/**
 * The bookings table and its migrations.
 */
final class Schema {

	public const VERSION        = '1';
	public const VERSION_OPTION = 'hive_core_db_version';

	/**
	 * Full table name including the site prefix.
	 */
	public static function table(): string {
		global $wpdb;

		return $wpdb->prefix . 'hive_bookings';
	}

	/**
	 * Creates or updates the table. dbDelta only applies the differences, so this is safe to rerun.
	 */
	public static function install(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table   = self::table();
		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE {$table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				space_id bigint(20) unsigned NOT NULL,
				user_id bigint(20) unsigned NOT NULL,
				start_utc datetime NOT NULL,
				end_utc datetime NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'confirmed',
				created_at datetime NOT NULL,
				PRIMARY KEY  (id),
				KEY space_time (space_id,start_utc,end_utc),
				KEY user_time (user_id,start_utc)
			) {$charset};"
		);

		update_option( self::VERSION_OPTION, self::VERSION );
	}

	/**
	 * Runs the migration when the stored schema version is out of date, e.g. after a plugin update.
	 */
	public static function maybe_upgrade(): void {
		if ( self::VERSION !== get_option( self::VERSION_OPTION ) ) {
			self::install();
		}
	}

	/**
	 * Drops the table and its version option.
	 */
	public static function uninstall(): void {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Removing the plugin's own table.
		$wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', self::table() ) );
		delete_option( self::VERSION_OPTION );
	}
}
