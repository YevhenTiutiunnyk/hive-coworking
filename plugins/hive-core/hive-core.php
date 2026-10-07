<?php
/**
 * Plugin Name:       Hive Core
 * Plugin URI:        https://github.com/YevhenTiutiunnyk/hive-coworking
 * Description:       Locations, spaces, plans, events and room booking for Hive Coworking.
 * Version:           0.1.0
 * Requires at least: 6.8
 * Requires PHP:      8.1
 * Author:            Yevhen Tiutiunnyk
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hive-core
 * Domain Path:       /languages
 *
 * @package Hive\Core
 */

namespace Hive\Core;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/src/Autoloader.php';
Autoloader::register();

register_activation_hook( __FILE__, array( Activation::class, 'activate' ) );
add_action( 'plugins_loaded', array( Plugin::class, 'boot' ) );
