<?php
namespace Hive\Core;

/**
 * The member role and the capabilities that control booking.
 */
final class Roles {

	public const MEMBER     = 'hive_member';
	public const CAP_BOOK   = 'hive_book_spaces';
	public const CAP_MANAGE = 'hive_manage_bookings';

	/**
	 * Hooks role behaviour into WordPress.
	 */
	public static function register_hooks(): void {
		add_filter( 'pre_option_default_role', array( self::class, 'default_role' ) );
		add_filter( 'show_admin_bar', array( self::class, 'show_admin_bar' ) );
	}

	/**
	 * Hides the admin toolbar from members, who never need wp-admin.
	 *
	 * @param bool $show Whether WordPress would show the toolbar.
	 */
	public static function show_admin_bar( bool $show ): bool {
		return $show && current_user_can( 'edit_posts' );
	}

	/**
	 * New registrations become members, without changing the stored site option.
	 */
	public static function default_role(): string {
		return self::MEMBER;
	}

	/**
	 * Creates the member role and grants capabilities to administrators. Safe to run repeatedly.
	 */
	public static function install(): void {
		// Recreate the role so capability changes in new versions are applied.
		remove_role( self::MEMBER );
		add_role(
			self::MEMBER,
			'Hive Member',
			array(
				'read'         => true,
				self::CAP_BOOK => true,
			)
		);

		$admin = get_role( 'administrator' );
		if ( null !== $admin ) {
			$admin->add_cap( self::CAP_BOOK );
			$admin->add_cap( self::CAP_MANAGE );
		}
	}

	/**
	 * Removes the role and capabilities.
	 */
	public static function uninstall(): void {
		remove_role( self::MEMBER );

		$admin = get_role( 'administrator' );
		if ( null !== $admin ) {
			$admin->remove_cap( self::CAP_BOOK );
			$admin->remove_cap( self::CAP_MANAGE );
		}
	}
}
