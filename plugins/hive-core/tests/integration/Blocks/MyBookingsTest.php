<?php
namespace Hive\Core\Tests\Integration\Blocks;

use Hive\Core\Blocks\MyBookings;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Tests\Integration\TestCase;

/**
 * "Now" is Monday 2030-01-07 09:00 in Berlin. Default cancellation window: 2 hours.
 */
final class MyBookingsTest extends TestCase {

	public function test_splits_a_members_bookings_into_upcoming_and_past(): void {
		$repository = new BookingRepository();
		$space      = $this->create_space();
		$member     = self::factory()->user->create();
		$upcoming   = $repository->insert( $space, $member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );
		$soon       = $repository->insert( $space, $member, self::local( '2030-01-07 10:00' ), self::local( '2030-01-07 11:00' ) );
		$cancelled  = $repository->update_status( $repository->insert( $space, $member, self::local( '2030-01-09 10:00' ), self::local( '2030-01-09 11:00' ) ), BookingStatus::Cancelled );
		$past       = $repository->insert( $space, $member, self::local( '2030-01-01 10:00' ), self::local( '2030-01-01 11:00' ) );
		$repository->insert( $space, $member + 1, self::local( '2030-01-08 14:00' ), self::local( '2030-01-08 15:00' ) );

		$context = MyBookings::context( $member, self::local( '2030-01-07 09:00' ) );

		$this->assertSame( 'upcoming', $context['tab'] );
		$this->assertSame( array( $soon->id, $upcoming->id, $cancelled->id ), array_column( $context['upcoming'], 'id' ) );
		$this->assertSame( array( $past->id ), array_column( $context['past'], 'id' ) );

		$first = $context['upcoming'][1];
		$this->assertSame( 'Room Atlas', $first['space'] );
		$this->assertSame( get_permalink( $space ), $first['spaceUrl'] );
		$this->assertSame( 'Hive Central', $first['location'] );
		$this->assertSame( 'confirmed', $first['status'] );
		$this->assertTrue( $first['canCancel'] );
		$this->assertStringContainsString( '10:00', $first['when'] );

		$this->assertFalse( $context['upcoming'][0]['canCancel'], 'Inside the cancellation window.' );
		$this->assertFalse( $context['upcoming'][2]['canCancel'], 'Already cancelled.' );
		$this->assertFalse( $context['past'][0]['canCancel'] );
	}

	public function test_state_contains_the_rest_details_and_strings(): void {
		wp_set_current_user( self::factory()->user->create() );

		$state = MyBookings::state();

		$this->assertStringEndsWith( '/hive/v1/', $state['restUrl'] );
		$this->assertNotEmpty( $state['nonce'] );
		$this->assertNotEmpty( $state['strings']['confirmCancel'] );
		$this->assertNotEmpty( $state['strings']['cancelled'] );
	}
}
