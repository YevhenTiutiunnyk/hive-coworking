<?php
namespace Hive\Core\Tests\Integration\Events;

use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use Hive\Core\Events\EventRepository;
use Hive\Core\Tests\Integration\TestCase;

final class EventRepositoryTest extends TestCase {

	public function test_lists_upcoming_events_soonest_first(): void {
		$later = $this->event( 'Later', '2030-01-20T18:00:00+01:00', '2030-01-20T20:00:00+01:00' );
		$soon  = $this->event( 'Soon', '2030-01-10T18:00:00+01:00', '2030-01-10T20:00:00+01:00' );
		$this->event( 'Past', '2030-01-01T18:00:00+01:00', '2030-01-01T20:00:00+01:00' );

		$events = ( new EventRepository() )->upcoming( self::local( '2030-01-07 09:00' ), 10 );

		$this->assertSame( array( $soon, $later ), array_map( fn( $event ) => $event->id, $events ) );
		$this->assertEquals( self::local( '2030-01-10 18:00' ), $events[0]->start );
		$this->assertSame( 'Soon', $events[0]->title );
	}

	public function test_includes_events_that_are_in_progress(): void {
		$now_running = $this->event( 'Running', '2030-01-07T08:00:00+01:00', '2030-01-07T10:00:00+01:00' );

		$events = ( new EventRepository() )->upcoming( self::local( '2030-01-07 09:00' ), 10 );

		$this->assertSame( array( $now_running ), array_map( fn( $event ) => $event->id, $events ) );
	}

	public function test_limits_and_filters_by_location(): void {
		$location = self::factory()->post->create( array( 'post_type' => PostTypes::LOCATION ) );
		$here     = $this->event( 'Here', '2030-01-10T18:00:00+01:00', '2030-01-10T20:00:00+01:00', $location );
		$this->event( 'Elsewhere', '2030-01-09T18:00:00+01:00', '2030-01-09T20:00:00+01:00' );
		$this->event( 'Here later', '2030-01-11T18:00:00+01:00', '2030-01-11T20:00:00+01:00', $location );

		$events = ( new EventRepository() )->upcoming( self::local( '2030-01-07 09:00' ), 1, $location );

		$this->assertSame( array( $here ), array_map( fn( $event ) => $event->id, $events ) );
	}

	public function test_skips_events_without_valid_dates(): void {
		self::factory()->post->create( array( 'post_type' => PostTypes::EVENT ) );

		$this->assertSame( array(), ( new EventRepository() )->upcoming( self::local( '2030-01-07 09:00' ), 10 ) );
	}

	private function event( string $title, string $starts, string $ends, int $location = 0 ): int {
		return self::factory()->post->create(
			array(
				'post_type'  => PostTypes::EVENT,
				'post_title' => $title,
				'meta_input' => array(
					Meta::EVENT_STARTS   => $starts,
					Meta::EVENT_ENDS     => $ends,
					Meta::EVENT_LOCATION => $location,
				),
			)
		);
	}
}
