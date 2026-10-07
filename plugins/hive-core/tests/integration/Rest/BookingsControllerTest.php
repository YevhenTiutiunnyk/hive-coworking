<?php
namespace Hive\Core\Tests\Integration\Rest;

use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Roles;
use Hive\Core\Tests\Integration\TestCase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Dates are in 2030 so they are always in the future; 2030-01-08 is a Tuesday.
 */
final class BookingsControllerTest extends TestCase {

	private int $space;

	private int $member;

	public function set_up(): void {
		parent::set_up();
		$this->space  = $this->create_space();
		$this->member = self::factory()->user->create( array( 'role' => Roles::MEMBER ) );
	}

	public function test_guests_must_log_in_to_book(): void {
		$response = $this->create_booking( '2030-01-08T10:00:00', '2030-01-08T11:00:00' );

		$this->assertSame( 401, $response->get_status() );
	}

	public function test_users_without_the_capability_cannot_book(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$response = $this->create_booking( '2030-01-08T10:00:00', '2030-01-08T11:00:00' );

		$this->assertSame( 403, $response->get_status() );
	}

	public function test_member_books_a_space(): void {
		wp_set_current_user( $this->member );

		$response = $this->create_booking( '2030-01-08T10:00:00', '2030-01-08T12:00:00' );
		$data     = $response->get_data();

		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( $this->space, $data['space_id'] );
		$this->assertSame( 'Room Atlas', $data['space_title'] );
		$this->assertSame( $this->member, $data['user_id'] );
		$this->assertSame( '2030-01-08T10:00:00+01:00', $data['start'] );
		$this->assertSame( '2030-01-08T12:00:00+01:00', $data['end'] );
		$this->assertSame( 'confirmed', $data['status'] );
		$this->assertTrue( $data['can_cancel'] );
	}

	public function test_accepts_times_with_an_explicit_offset(): void {
		wp_set_current_user( $this->member );

		$response = $this->create_booking( '2030-01-08T09:00:00Z', '2030-01-08T10:00:00Z' );

		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( '2030-01-08T10:00:00+01:00', $response->get_data()['start'] );
	}

	public function test_overlapping_booking_is_a_conflict(): void {
		wp_set_current_user( $this->member );
		$this->create_booking( '2030-01-08T10:00:00', '2030-01-08T12:00:00' );

		$response = $this->create_booking( '2030-01-08T11:00:00', '2030-01-08T13:00:00' );

		$this->assertSame( 409, $response->get_status() );
		$this->assertSame( 'hive_conflict', $response->get_data()['code'] );
		$this->assertNotEmpty( $response->get_data()['message'] );
	}

	public function test_rule_violations_are_bad_requests(): void {
		wp_set_current_user( $this->member );

		$response = $this->create_booking( '2030-01-08T19:00:00', '2030-01-08T21:00:00' );

		$this->assertSame( 400, $response->get_status() );
		$this->assertSame( 'hive_outside_hours', $response->get_data()['code'] );
	}

	public function test_rejects_malformed_times(): void {
		wp_set_current_user( $this->member );

		$this->assertSame( 400, $this->create_booking( 'tomorrow', '2030-01-08T12:00:00' )->get_status() );
	}

	public function test_lists_only_the_current_users_bookings(): void {
		$repository = new BookingRepository();
		$mine       = $repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$repository->insert( $this->space, $this->member + 1, self::local( '2030-01-08 12:00' ), self::local( '2030-01-08 13:00' ) );
		wp_set_current_user( $this->member );

		$response = rest_do_request( new WP_REST_Request( 'GET', '/hive/v1/bookings' ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( array( $mine->id ), array_column( $response->get_data(), 'id' ) );
	}

	public function test_listing_requires_login(): void {
		$this->assertSame( 401, rest_do_request( new WP_REST_Request( 'GET', '/hive/v1/bookings' ) )->get_status() );
	}

	public function test_past_scope_lists_finished_bookings(): void {
		$repository = new BookingRepository();
		$past       = $repository->insert( $this->space, $this->member, self::local( '2020-01-07 10:00' ), self::local( '2020-01-07 11:00' ) );
		$repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		wp_set_current_user( $this->member );

		$request = new WP_REST_Request( 'GET', '/hive/v1/bookings' );
		$request->set_query_params( array( 'scope' => 'past' ) );

		$this->assertSame( array( $past->id ), array_column( rest_do_request( $request )->get_data(), 'id' ) );
	}

	public function test_member_reads_own_booking(): void {
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		wp_set_current_user( $this->member );

		$response = rest_do_request( new WP_REST_Request( 'GET', "/hive/v1/bookings/{$booking->id}" ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( $booking->id, $response->get_data()['id'] );
	}

	public function test_other_members_bookings_are_not_found(): void {
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member + 1, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		wp_set_current_user( $this->member );

		$this->assertSame( 404, rest_do_request( new WP_REST_Request( 'GET', "/hive/v1/bookings/{$booking->id}" ) )->get_status() );
		$this->assertSame( 404, $this->cancel_booking( $booking->id )->get_status() );
	}

	public function test_member_cancels_own_booking(): void {
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		wp_set_current_user( $this->member );

		$response = $this->cancel_booking( $booking->id );

		$this->assertSame( 200, $response->get_status() );
		$this->assertSame( 'cancelled', $response->get_data()['status'] );
		$this->assertFalse( $response->get_data()['can_cancel'] );
	}

	public function test_member_cannot_cancel_inside_the_window(): void {
		$soon    = current_datetime()->modify( '+1 hour' );
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, $soon, $soon->modify( '+1 hour' ) );
		wp_set_current_user( $this->member );

		$response = $this->cancel_booking( $booking->id );

		$this->assertSame( 403, $response->get_status() );
		$this->assertSame( 'hive_cancel_window', $response->get_data()['code'] );
	}

	public function test_manager_cancels_any_booking(): void {
		$soon    = current_datetime()->modify( '+1 hour' );
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, $soon, $soon->modify( '+1 hour' ) );
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$this->assertSame( 200, $this->cancel_booking( $booking->id )->get_status() );
	}

	public function test_only_cancellation_is_allowed(): void {
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		wp_set_current_user( $this->member );

		$request = new WP_REST_Request( 'PATCH', "/hive/v1/bookings/{$booking->id}" );
		$request->set_body_params( array( 'status' => 'confirmed' ) );

		$this->assertSame( 400, rest_do_request( $request )->get_status() );
	}

	public function test_route_publishes_a_schema(): void {
		$response = rest_do_request( new WP_REST_Request( 'OPTIONS', '/hive/v1/bookings' ) );

		$this->assertSame( 'hive-booking', $response->get_data()['schema']['title'] );
	}

	private function create_booking( string $start, string $end ): WP_REST_Response {
		$request = new WP_REST_Request( 'POST', '/hive/v1/bookings' );
		$request->set_body_params(
			array(
				'space_id' => $this->space,
				'start'    => $start,
				'end'      => $end,
			)
		);

		return rest_do_request( $request );
	}

	private function cancel_booking( int $id ): WP_REST_Response {
		$request = new WP_REST_Request( 'PATCH', "/hive/v1/bookings/{$id}" );
		$request->set_body_params( array( 'status' => 'cancelled' ) );

		return rest_do_request( $request );
	}
}
