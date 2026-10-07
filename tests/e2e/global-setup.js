const { execSync } = require( 'node:child_process' );
const { MEMBER_PASSWORD } = require( './config' );

/**
 * Resets the tests site to a known state: plugin and theme active, pretty permalinks,
 * no bookings and fresh demo content.
 */
module.exports = async () => {
	const wp = ( command ) =>
		execSync( `npx wp-env run tests-cli wp ${ command }`, {
			stdio: 'pipe',
		} ).toString();

	wp( 'plugin activate hive-core' );
	wp( 'theme activate hive' );
	wp( 'rewrite structure /%postname%/ --hard' );
	wp( 'option update timezone_string Europe/Amsterdam' );
	wp(
		`eval 'global $wpdb; $wpdb->query( "DELETE FROM " . Hive\\Core\\Bookings\\Schema::table() );'`
	);
	wp( `hive seed --demo-password=${ MEMBER_PASSWORD }` );
};
