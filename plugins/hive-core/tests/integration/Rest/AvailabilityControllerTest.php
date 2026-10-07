<?php
namespace Hive\Core\Tests\Integration\Rest;

use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Tests\Integration\TestCase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Dates are in 2030 so they are always in the future; 2030-01-08 is a Tuesday.
 */
final class AvailabilityControllerTest extends TestCase {

	public function test_lists_slots_for_a_day(): void {
		$space = $this->create_space();
		( new BookingRepository() )->insert( $space, 1, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );

		$response = $this->get_availability( $space, '2030-01-08' );
		$data     = $response->get_data();

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $space, $data['space_id'] );
		$this->assertSame( '2030-01-08', $data['date'] );
		$this->assertSame( 'Europe/Berlin', $data['timezone'] );
		$this->assertSame( 60, $data['slot_minutes'] );
		$this->assertCount( 12, $data['slots'] );
		$this->assertSame(
			array(
				'start'     => '2030-01-08T08:00:00+01:00',
				'end'       => '2030-01-08T09:00:00+01:00',
				'available' => true,
			),
			$data['slots'][0]
		);
		$this->assertFalse( $data['slots'][2]['available'] );
	}

	public function test_is_public(): void {
		wp_set_current_user( 0 );

		$this->assertSame( 200, $this->get_availability( $this->create_space(), '2030-01-08' )->get_status() );
	}

	public function test_closed_days_have_no_slots(): void {
		$data = $this->get_availability( $this->create_space(), '2030-01-13' )->get_data();

		$this->assertSame( array(), $data['slots'] );
	}

	public function test_unknown_space_is_not_found(): void {
		$response = $this->get_availability( 999999, '2030-01-08' );

		$this->assertSame( 404, $response->get_status() );
		$this->assertSame( 'hive_space_not_found', $response->get_data()['code'] );
	}

	public function test_rejects_malformed_dates(): void {
		$this->assertSame( 400, $this->get_availability( $this->create_space(), '08.01.2030' )->get_status() );
	}

	public function test_rejects_impossible_dates(): void {
		$response = $this->get_availability( $this->create_space(), '2030-02-31' );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'hive_invalid_date', $response->get_data()['code'] );
	}

	private function get_availability( int $space, string $date ): WP_REST_Response {
		$request = new WP_REST_Request( 'GET', "/hive/v1/spaces/{$space}/availability" );
		$request->set_query_params( array( 'date' => $date ) );

		return rest_do_request( $request );
	}
}
