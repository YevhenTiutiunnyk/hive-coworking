<?php
/**
 * Minimal WP-CLI declarations for PHPStan.
 *
 * php-stubs/wp-cli-stubs does not support WordPress 7 stubs yet, so only the
 * symbols this project uses are declared here. Never loaded at runtime.
 *
 * @package Hive\Core
 */

// phpcs:ignoreFile

namespace {
	class WP_CLI {
		/**
		 * @param callable|class-string|object $callable
		 * @param array<string, mixed>         $args
		 */
		public static function add_command( string $name, $callable, array $args = array() ): bool {}
		public static function success( string $message ): void {}
		public static function log( string $message ): void {}
		public static function warning( string $message ): void {}
		/** @return never */
		public static function error( string $message ) {}
	}
}

namespace WP_CLI\Utils {
	/**
	 * @param array<int, array<string, mixed>> $items
	 * @param list<string>|string              $fields
	 */
	function format_items( string $format, array $items, $fields ): void {}

	/**
	 * @param array<string, mixed> $assoc_args
	 * @param mixed                $default
	 * @return mixed
	 */
	function get_flag_value( array $assoc_args, string $flag, $default = null ) {}
}
