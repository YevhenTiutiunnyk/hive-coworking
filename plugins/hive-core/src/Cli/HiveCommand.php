<?php
namespace Hive\Core\Cli;

use Hive\Core\Demo\Seeder;
use WP_CLI;

/**
 * Manages Hive Coworking.
 */
final class HiveCommand {

	/**
	 * Fills the site with demo locations, spaces, plans, events and a demo member.
	 *
	 * Safe to run repeatedly: existing items are kept.
	 *
	 * ## OPTIONS
	 *
	 * [--demo-password=<password>]
	 * : Password for the `demo` member. A random password is used if omitted.
	 *
	 * ## EXAMPLES
	 *
	 *     wp hive seed
	 *     wp hive seed --demo-password=demo
	 *
	 * @param string[]              $args       Positional arguments.
	 * @param array<string, string> $assoc_args Named arguments.
	 */
	public function seed( array $args, array $assoc_args ): void {
		$password = $assoc_args['demo-password'] ?? null;
		$counts   = ( new Seeder() )->run( current_datetime(), $password );

		foreach ( $counts as $kind => $count ) {
			WP_CLI::log( sprintf( '%-10s %d created', $kind, $count ) );
		}

		WP_CLI::success( 'Demo content is ready.' );
	}
}
