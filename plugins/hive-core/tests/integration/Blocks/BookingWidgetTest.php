<?php
namespace Hive\Core\Tests\Integration\Blocks;

use Hive\Core\Blocks\BookingWidget;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Bookings\BookingSettings;
use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Bookings\SpaceLock;
use Hive\Core\Content\Meta;
use Hive\Core\Roles;
use Hive\Core\Tests\Integration\TestCase;

/**
 * "Now" is Monday 2030-01-07 09:00 in Berlin.
 */
final class BookingWidgetTest extends TestCase {

	private BookingWidget $widget;

	public function set_up(): void {
		parent::set_up();
		$this->widget = new BookingWidget(
			new SpaceCatalog(),
			new BookingService( new BookingRepository(), new SpaceCatalog(), new SpaceLock(), new BookingSettings() )
		);
	}

	public function test_context_contains_todays_slots(): void {
		$space = $this->create_space( array( 'meta_input' => array( Meta::SPACE_PRICE => 40 ) ) );

		$context = $this->widget->context( $space, self::local( '2030-01-07 09:00' ) );

		$this->assertSame( $space, $context['spaceId'] );
		$this->assertSame( '2030-01-07', $context['date'] );
		$this->assertSame( '2030-01-07', $context['minDate'] );
		$this->assertSame( '2030-03-08', $context['maxDate'] );
		$this->assertSame( 40.0, $context['hourlyPrice'] );
		$this->assertCount( 12, $context['slots'] );
		$this->assertSame(
			array(
				'start'     => '2030-01-07T08:00:00+01:00',
				'end'       => '2030-01-07T09:00:00+01:00',
				'label'     => '08:00',
				'available' => false,
			),
			$context['slots'][0]
		);
		$this->assertTrue( $context['slots'][2]['available'], '10:00 is after the one-hour notice.' );
		$this->assertSame(
			array(
				'start' => -1,
				'end'   => -1,
			),
			$context['selection']
		);
	}

	public function test_context_is_null_for_unknown_spaces(): void {
		$this->assertNull( $this->widget->context( 999999, self::local( '2030-01-07 09:00' ) ) );
	}

	public function test_state_tells_guests_to_log_in(): void {
		$state = $this->widget->state();

		$this->assertFalse( $state['isLoggedIn'] );
		$this->assertFalse( $state['canBook'] );
		$this->assertStringEndsWith( '/hive/v1/', $state['restUrl'] );
		$this->assertNotEmpty( $state['strings']['booked'] );
	}

	public function test_state_lets_members_book(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => Roles::MEMBER ) ) );

		$state = $this->widget->state();

		$this->assertTrue( $state['isLoggedIn'] );
		$this->assertTrue( $state['canBook'] );
		$this->assertNotEmpty( $state['nonce'] );
	}
}
