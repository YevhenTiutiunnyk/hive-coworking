<?php
namespace Hive\Core\Tests\Unit\Bookings;

use DateTimeImmutable;
use Hive\Core\Bookings\BookingFilter;
use Hive\Core\Bookings\BookingStatus;
use PHPUnit\Framework\TestCase;

final class BookingFilterTest extends TestCase {

	public function test_defaults_to_all_upcoming_bookings(): void {
		$filter = BookingFilter::from_request( array(), new DateTimeImmutable() );

		$this->assertSame( BookingFilter::WHEN_UPCOMING, $filter->when );
		$this->assertNull( $filter->status );
		$this->assertNull( $filter->location_id );
	}

	public function test_reads_valid_query_values(): void {
		$filter = BookingFilter::from_request(
			array(
				'when'     => 'past',
				'status'   => 'cancelled',
				'location' => '12',
			),
			new DateTimeImmutable()
		);

		$this->assertSame( BookingFilter::WHEN_PAST, $filter->when );
		$this->assertSame( BookingStatus::Cancelled, $filter->status );
		$this->assertSame( 12, $filter->location_id );
	}

	public function test_ignores_invalid_query_values(): void {
		$filter = BookingFilter::from_request(
			array(
				'when'     => 'yesterday',
				'status'   => 'deleted',
				'location' => 'abc',
			),
			new DateTimeImmutable()
		);

		$this->assertSame( BookingFilter::WHEN_UPCOMING, $filter->when );
		$this->assertNull( $filter->status );
		$this->assertNull( $filter->location_id );
	}

	public function test_converts_back_to_query_args(): void {
		$filter = new BookingFilter( new DateTimeImmutable(), BookingFilter::WHEN_ALL, BookingStatus::Confirmed, 7 );

		$this->assertSame(
			array(
				'when'     => 'all',
				'status'   => 'confirmed',
				'location' => 7,
			),
			$filter->to_query_args()
		);
	}
}
