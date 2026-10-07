<?php
namespace Hive\Core\Tests\Integration\Content;

use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;
use WP_REST_Request;

final class MetaTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
	}

	public function test_space_meta_can_be_saved_through_rest(): void {
		$location = self::factory()->post->create( array( 'post_type' => PostTypes::LOCATION ) );

		$response = $this->post(
			'/wp/v2/hive-spaces',
			array(
				Meta::SPACE_LOCATION => $location,
				Meta::SPACE_TYPE     => 'private_office',
				Meta::SPACE_CAPACITY => 6,
				Meta::SPACE_PRICE    => 25.5,
			)
		);

		$this->assertSame( 201, $response->get_status() );
		$id = $response->get_data()['id'];
		$this->assertSame( $location, (int) get_post_meta( $id, Meta::SPACE_LOCATION, true ) );
		$this->assertSame( 'private_office', get_post_meta( $id, Meta::SPACE_TYPE, true ) );
		$this->assertSame( 6, (int) get_post_meta( $id, Meta::SPACE_CAPACITY, true ) );
	}

	public function test_unknown_space_type_is_rejected(): void {
		$response = $this->post( '/wp/v2/hive-spaces', array( Meta::SPACE_TYPE => 'castle' ) );

		$this->assertSame( 400, $response->get_status() );
	}

	public function test_space_capacity_must_be_positive(): void {
		$response = $this->post( '/wp/v2/hive-spaces', array( Meta::SPACE_CAPACITY => 0 ) );

		$this->assertSame( 400, $response->get_status() );
	}

	public function test_locations_default_to_standard_opening_hours(): void {
		$location = self::factory()->post->create( array( 'post_type' => PostTypes::LOCATION ) );

		$hours = get_post_meta( $location, Meta::LOCATION_HOURS, true );

		$this->assertSame(
			array(
				'open'  => '08:00',
				'close' => '20:00',
			),
			$hours['mon']
		);
		$this->assertNull( $hours['sun'] );
	}

	public function test_opening_hours_can_be_saved_through_rest(): void {
		$hours        = Meta::DEFAULT_HOURS;
		$hours['sun'] = array(
			'open'  => '12:00',
			'close' => '18:00',
		);

		$response = $this->post( '/wp/v2/hive-locations', array( Meta::LOCATION_HOURS => $hours ) );

		$this->assertSame( 201, $response->get_status() );
		$saved = get_post_meta( $response->get_data()['id'], Meta::LOCATION_HOURS, true );
		$this->assertSame( '12:00', $saved['sun']['open'] );
	}

	public function test_opening_hours_reject_invalid_times(): void {
		$hours        = Meta::DEFAULT_HOURS;
		$hours['mon'] = array(
			'open'  => '25:00',
			'close' => '20:00',
		);

		$response = $this->post( '/wp/v2/hive-locations', array( Meta::LOCATION_HOURS => $hours ) );

		$this->assertSame( 400, $response->get_status() );
	}

	public function test_plan_features_are_a_list_of_strings(): void {
		$features = array( '24/7 access', 'Meeting room credits' );

		$response = $this->post(
			'/wp/v2/hive-plans',
			array(
				Meta::PLAN_PRICE_MONTHLY => 199,
				Meta::PLAN_FEATURES      => $features,
			)
		);

		$this->assertSame( 201, $response->get_status() );
		$this->assertSame( $features, $response->get_data()['meta'][ Meta::PLAN_FEATURES ] );
	}

	/**
	 * Creates a published post through the REST API with the given meta.
	 *
	 * @param array<string, mixed> $meta
	 */
	private function post( string $route, array $meta ): \WP_REST_Response {
		$request = new WP_REST_Request( 'POST', $route );
		$request->set_body_params(
			array(
				'title'  => 'Test',
				'status' => 'publish',
				'meta'   => $meta,
			)
		);

		return rest_do_request( $request );
	}
}
