<?php
namespace Hive\Core\Events;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Builds iCalendar (RFC 5545) files.
 */
final class Ics {

	private const LINE_LIMIT = 75;

	/**
	 * A calendar containing one event.
	 *
	 * @param array{uid: string, title: string, description: string, location: string, url: string, start: DateTimeImmutable, end: DateTimeImmutable, stamp: DateTimeImmutable} $event Event data.
	 */
	public static function event( array $event ): string {
		$lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//Hive Coworking//Events//EN',
			'CALSCALE:GREGORIAN',
			'METHOD:PUBLISH',
			'BEGIN:VEVENT',
			'UID:' . self::escape( $event['uid'] ),
			'DTSTAMP:' . self::utc( $event['stamp'] ),
			'DTSTART:' . self::utc( $event['start'] ),
			'DTEND:' . self::utc( $event['end'] ),
			'SUMMARY:' . self::escape( $event['title'] ),
			'DESCRIPTION:' . self::escape( $event['description'] ),
			'LOCATION:' . self::escape( $event['location'] ),
			'URL:' . $event['url'],
			'END:VEVENT',
			'END:VCALENDAR',
		);

		return implode( "\r\n", array_map( array( self::class, 'fold' ), $lines ) ) . "\r\n";
	}

	/**
	 * A time in UTC basic format, e.g. 20300108T173000Z.
	 *
	 * @param DateTimeImmutable $time Time in any timezone.
	 */
	private static function utc( DateTimeImmutable $time ): string {
		return $time->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
	}

	/**
	 * Escapes a text value: backslash, semicolon, comma and newlines.
	 *
	 * @param string $text Text.
	 */
	private static function escape( string $text ): string {
		return str_replace(
			array( '\\', ';', ',', "\r\n", "\n" ),
			array( '\\\\', '\;', '\\,', '\\n', '\\n' ),
			$text
		);
	}

	/**
	 * Splits a line into 75-octet chunks joined by CRLF + space, without splitting UTF-8 characters.
	 *
	 * @param string $line Unfolded line.
	 */
	private static function fold( string $line ): string {
		$chunks = array();
		$chunk  = '';
		$limit  = self::LINE_LIMIT;

		foreach ( mb_str_split( $line ) as $character ) {
			if ( strlen( $chunk ) + strlen( $character ) > $limit ) {
				$chunks[] = $chunk;
				$chunk    = '';
				$limit    = self::LINE_LIMIT - 1; // Continuation lines start with a space.
			}
			$chunk .= $character;
		}
		$chunks[] = $chunk;

		return implode( "\r\n ", $chunks );
	}
}
