<?php
/**
 * Removes the plugin's roles, bookings table and settings when it is deleted.
 *
 * Posts are kept: they belong to the site owner, not to the plugin.
 *
 * @package Hive\Core
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/src/Autoloader.php';
\Hive\Core\Autoloader::register();

\Hive\Core\Roles::uninstall();
\Hive\Core\Bookings\Schema::uninstall();
delete_option( \Hive\Core\Settings::OPTION );
