<?php
namespace Hive\Core\Tests\Unit\Events;

use DateTimeImmutable;
use DateTimeZone;
use Hive\Core\Events\Ics;
use PHPUnit\Framework\TestCase;

final class IcsTest extends TestCase {

	private function calendar( string $title = 'Design Systems Meetup', string $description = 'Talks and drinks.' ): string {
		$berlin = new DateTimeZone( 'Europe/Berlin' );

		return Ics::event(
			array(
				'uid'         => 'event-42@example.com',
				'title'       => $title,
				'description' => $description,
				'location'    => 'Hive Old Town, 4 Clockmaker Lane',
				'url'         => 'https://example.com/events/design-systems-meetup/',
				'start'       => new DateTimeImmutable( '2030-01-08 18:30', $berlin ),
				'end'         => new DateTimeImmutable( '2030-01-08 21:00', $berlin ),
				'stamp'       => new DateTimeImmutable( '2030-01-01 12:00', new DateTimeZone( 'UTC' ) ),
			)
		);
	}

	public function test_builds_a_calendar_with_one_event(): void {
		$lines = explode( "\r\n", $this->calendar() );

		$this->assertSame( 'BEGIN:VCALENDAR', $lines[0] );
		$this->assertContains( 'VERSION:2.0', $lines );
		$this->assertContains( 'BEGIN:VEVENT', $lines );
		$this->assertContains( 'UID:event-42@example.com', $lines );
		$this->assertContains( 'SUMMARY:Design Systems Meetup', $lines );
		$this->assertContains( 'END:VCALENDAR', $lines );
	}

	public function test_times_are_written_in_utc(): void {
		$lines = explode( "\r\n", $this->calendar() );

		$this->assertContains( 'DTSTART:20300108T173000Z', $lines );
		$this->assertContains( 'DTEND:20300108T200000Z', $lines );
		$this->assertContains( 'DTSTAMP:20300101T120000Z', $lines );
	}

	public function test_escapes_special_characters(): void {
		$ics = $this->calendar( 'Food; drinks, and fun', "Line one\nLine two \\ done" );

		$this->assertStringContainsString( 'SUMMARY:Food\; drinks\\, and fun', $ics );
		$this->assertStringContainsString( 'DESCRIPTION:Line one\\nLine two \\\\ done', $ics );
		$this->assertStringContainsString( 'LOCATION:Hive Old Town\\, 4 Clockmaker Lane', $ics );
	}

	public function test_folds_lines_longer_than_75_octets(): void {
		$ics = $this->calendar( str_repeat( 'Ä', 60 ) );

		foreach ( explode( "\r\n", $ics ) as $line ) {
			$this->assertLessThanOrEqual( 75, strlen( $line ) );
		}
		$this->assertStringContainsString( "\r\n ", $ics, 'Continuation lines start with a space.' );
		$this->assertTrue( mb_check_encoding( str_replace( "\r\n ", '', $ics ), 'UTF-8' ), 'Folding never splits a character.' );
	}

	public function test_ends_with_crlf(): void {
		$this->assertStringEndsWith( "END:VCALENDAR\r\n", $this->calendar() );
	}
}
