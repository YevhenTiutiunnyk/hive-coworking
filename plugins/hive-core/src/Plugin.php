<?php
namespace Hive\Core;

/**
 * Wires the plugin's components into WordPress.
 */
final class Plugin {

	/**
	 * Registers all hooks. Runs on `plugins_loaded`.
	 */
	public static function boot(): void {
		Bookings\Schema::maybe_upgrade();
		Content\PostTypes::register_hooks();
		Content\Meta::register_hooks();
		Roles::register_hooks();

		if ( is_admin() ) {
			Admin\SettingsPage::register_hooks();
		}

		add_action( 'rest_api_init', array( self::class, 'register_rest_routes' ) );
	}

	/**
	 * Registers the plugin's REST API routes.
	 */
	public static function register_rest_routes(): void {
		$service = Bookings\BookingService::create_default();

		( new Rest\AvailabilityController( $service ) )->register_routes();
		( new Rest\BookingsController( $service, new Bookings\BookingRepository() ) )->register_routes();
	}
}
