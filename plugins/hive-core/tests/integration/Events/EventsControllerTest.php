<?php
namespace Hive\Core\Tests\Integration\Events;

use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Tests\Integration\TestCase;
use WP_REST_Request;

final class EventsControllerTest extends TestCase {

	public function test_downloads_an_event_as_icalendar(): void {
		$location = self::factory()->post->create(
			array(
				'post_type'  => PostTypes::LOCATION,
				'post_title' => 'Hive Old Town',
				'meta_input' => array( Meta::LOCATION_ADDRESS => '4 Clockmaker Lane' ),
			)
		);
		$event    = self::factory()->post->create(
			array(
				'post_type'    => PostTypes::EVENT,
				'post_title'   => 'Design Systems Meetup',
				'post_name'    => 'design-systems-meetup',
				'post_excerpt' => 'Talks and drinks.',
				'meta_input'   => array(
					Meta::EVENT_STARTS   => '2030-01-08T18:30:00+01:00',
					Meta::EVENT_ENDS     => '2030-01-08T21:00:00+01:00',
					Meta::EVENT_LOCATION => $location,
				),
			)
		);

		$response = rest_do_request( new WP_REST_Request( 'GET', "/hive/v1/events/{$event}/ics" ) );

		$this->assertSame( 200, $response->get_status() );
		$this->assertStringStartsWith( 'text/calendar', $response->get_headers()['Content-Type'] );
		$this->assertStringContainsString( 'design-systems-meetup.ics', $response->get_headers()['Content-Disposition'] );
		$this->assertStringContainsString( 'SUMMARY:Design Systems Meetup', $response->get_data() );
		$this->assertStringContainsString( 'DTSTART:20300108T173000Z', $response->get_data() );
		$this->assertStringContainsString( 'LOCATION:Hive Old Town\\, 4 Clockmaker Lane', $response->get_data() );
	}

	public function test_unknown_events_are_not_found(): void {
		$this->assertSame( 404, rest_do_request( new WP_REST_Request( 'GET', '/hive/v1/events/999999/ics' ) )->get_status() );
	}

	public function test_unpublished_events_are_not_found(): void {
		$event = self::factory()->post->create(
			array(
				'post_type'   => PostTypes::EVENT,
				'post_status' => 'draft',
				'meta_input'  => array(
					Meta::EVENT_STARTS => '2030-01-08T18:30:00+01:00',
					Meta::EVENT_ENDS   => '2030-01-08T21:00:00+01:00',
				),
			)
		);

		$this->assertSame( 404, rest_do_request( new WP_REST_Request( 'GET', "/hive/v1/events/{$event}/ics" ) )->get_status() );
	}
}
