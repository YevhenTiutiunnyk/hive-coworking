<?php
namespace Hive\Core\Tests\Integration\Bookings;

use Hive\Core\Bookings\SpaceLock;
use Hive\Core\Tests\Integration\TestCase;

final class SpaceLockTest extends TestCase {

	public function test_only_one_request_holds_the_lock(): void {
		$lock = new SpaceLock();

		$this->assertTrue( $lock->acquire( 10 ) );
		$this->assertFalse( $lock->acquire( 10 ) );
		$this->assertTrue( $lock->acquire( 11 ), 'Locks are per space.' );
	}

	public function test_released_lock_can_be_acquired_again(): void {
		$lock = new SpaceLock();
		$lock->acquire( 10 );

		$lock->release( 10 );

		$this->assertTrue( $lock->acquire( 10 ) );
	}

	public function test_expired_lock_is_taken_over(): void {
		add_option( 'hive_lock_space_10', (string) ( time() - 1 ), '', false );

		$this->assertTrue( ( new SpaceLock() )->acquire( 10 ) );
	}
}
