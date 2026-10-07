<?php
namespace Hive\Core\Tests\Integration;

use Hive\Core\Roles;

final class RolesTest extends TestCase {

	public function test_members_can_book_but_not_manage(): void {
		$member = self::factory()->user->create_and_get( array( 'role' => Roles::MEMBER ) );

		$this->assertTrue( user_can( $member, Roles::CAP_BOOK ) );
		$this->assertFalse( user_can( $member, Roles::CAP_MANAGE ) );
	}

	public function test_administrators_can_book_and_manage(): void {
		$admin = self::factory()->user->create_and_get( array( 'role' => 'administrator' ) );

		$this->assertTrue( user_can( $admin, Roles::CAP_BOOK ) );
		$this->assertTrue( user_can( $admin, Roles::CAP_MANAGE ) );
	}

	public function test_subscribers_cannot_book(): void {
		$subscriber = self::factory()->user->create_and_get( array( 'role' => 'subscriber' ) );

		$this->assertFalse( user_can( $subscriber, Roles::CAP_BOOK ) );
	}

	public function test_new_users_become_members(): void {
		$this->assertSame( Roles::MEMBER, get_option( 'default_role' ) );
	}

	public function test_uninstall_removes_role_and_capabilities(): void {
		Roles::uninstall();

		try {
			$this->assertNull( get_role( Roles::MEMBER ) );
			$this->assertFalse( get_role( 'administrator' )->has_cap( Roles::CAP_MANAGE ) );
		} finally {
			Roles::install();
		}
	}
}
