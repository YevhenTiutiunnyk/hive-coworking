<?php
namespace Hive\Core;

use Hive\Core\Bookings\BookingSettings;

/**
 * Access to the plugin's stored settings.
 */
final class Settings {

	public const OPTION = 'hive_core_settings';

	/**
	 * Current booking settings, with defaults for anything not set.
	 */
	public static function booking(): BookingSettings {
		$stored = get_option( self::OPTION, array() );

		return BookingSettings::from_array( is_array( $stored ) ? $stored : array() );
	}
}
