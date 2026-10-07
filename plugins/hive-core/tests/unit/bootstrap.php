<?php
/**
 * Bootstrap for unit tests. These tests cover pure domain code and run without WordPress.
 *
 * @package Hive\Core
 */

require dirname( __DIR__, 4 ) . '/vendor/autoload.php';
require dirname( __DIR__, 2 ) . '/src/Autoloader.php';

\Hive\Core\Autoloader::register();
