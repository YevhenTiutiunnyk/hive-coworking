<?php
namespace Hive\Core\Tests\Integration\Admin;

use Hive\Core\Admin\BookingsCsv;
use Hive\Core\Admin\BookingsListTable;
use Hive\Core\Admin\BookingsPage;
use Hive\Core\Admin\DashboardWidget;
use Hive\Core\Bookings\BookingFilter;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Content\Meta;
use Hive\Core\Tests\Integration\TestCase;

final class BookingsAdminTest extends TestCase {

	private BookingRepository $repository;

	private int $space;

	private int $member;

	public function set_up(): void {
		parent::set_up();
		$this->repository = new BookingRepository();
		$this->space      = $this->create_space();
		$this->member     = self::factory()->user->create(
			array(
				'display_name' => 'Ada Lovelace',
				'user_email'   => 'ada@example.com',
			)
		);
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'toplevel_page_' . BookingsPage::SLUG );
	}

	public function test_search_filters_by_status_location_and_time(): void {
		$other_space = $this->create_space();
		$upcoming    = $this->repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$past        = $this->repository->insert( $this->space, $this->member, self::local( '2020-01-08 10:00' ), self::local( '2020-01-08 11:00' ) );
		$cancelled   = $this->repository->update_status(
			$this->repository->insert( $this->space, $this->member, self::local( '2030-01-09 10:00' ), self::local( '2030-01-09 11:00' ) ),
			BookingStatus::Cancelled
		);
		$elsewhere   = $this->repository->insert( $other_space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$location    = (int) get_post_meta( $this->space, Meta::SPACE_LOCATION, true );
		$now         = self::local( '2026-01-01 00:00' );
		$ids         = fn( BookingFilter $filter ) => array_map( fn( $booking ) => $booking->id, $this->repository->search( $filter, 50 ) );

		$this->assertSame( array( $upcoming->id, $elsewhere->id, $cancelled->id ), $ids( new BookingFilter( $now ) ) );
		$this->assertSame( array( $past->id ), $ids( new BookingFilter( $now, BookingFilter::WHEN_PAST ) ) );
		$this->assertSame( array( $cancelled->id ), $ids( new BookingFilter( $now, BookingFilter::WHEN_ALL, BookingStatus::Cancelled ) ) );
		$this->assertSame( array( $upcoming->id, $cancelled->id ), $ids( new BookingFilter( $now, BookingFilter::WHEN_UPCOMING, null, $location ) ) );
		$this->assertSame( 4, $this->repository->count( new BookingFilter( $now, BookingFilter::WHEN_ALL ) ) );
	}

	public function test_list_table_shows_bookings_with_row_actions(): void {
		$this->repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$table = new BookingsListTable( $this->repository, new BookingFilter( current_datetime() ) );

		$table->prepare_items();
		ob_start();
		$table->display();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Room Atlas', $html );
		$this->assertStringContainsString( 'Hive Central', $html );
		$this->assertStringContainsString( 'Ada Lovelace', $html );
		$this->assertStringContainsString( 'action=cancel', $html );
		$this->assertStringContainsString( 'Export CSV', $html );
	}

	public function test_cancel_cancels_confirmed_bookings_and_skips_others(): void {
		$first  = $this->repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$second = $this->repository->update_status(
			$this->repository->insert( $this->space, $this->member, self::local( '2030-01-08 12:00' ), self::local( '2030-01-08 13:00' ) ),
			BookingStatus::Cancelled
		);

		$this->assertSame( 1, BookingsPage::cancel( array( $first->id, $second->id, 999999 ) ) );
		$this->assertSame( BookingStatus::Cancelled, $this->repository->find( $first->id )->status );
	}

	public function test_csv_contains_a_row_per_booking(): void {
		$booking = $this->repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );
		$file    = wp_tempnam( 'bookings.csv' );

		BookingsPage::write_csv( $file, array( $booking ) );
		$lines = array_map( 'str_getcsv', file( $file, FILE_IGNORE_NEW_LINES ) );
		wp_delete_file( $file );

		$this->assertSame( BookingsCsv::header(), $lines[0] );
		$this->assertSame(
			array( (string) $booking->id, 'Room Atlas', 'Hive Central', 'Ada Lovelace', 'ada@example.com', '2030-01-08 10:00', '2030-01-08 11:00', 'confirmed' ),
			array_slice( $lines[1], 0, 8 )
		);
	}

	public function test_export_url_keeps_the_filter_and_has_a_nonce(): void {
		$url = BookingsPage::export_url( new BookingFilter( current_datetime(), BookingFilter::WHEN_PAST ) );

		$this->assertStringContainsString( 'action=hive_export_bookings', $url );
		$this->assertStringContainsString( 'when=past', $url );
		$this->assertStringContainsString( '_wpnonce=', $url );
	}

	public function test_dashboard_widget_reports_todays_occupancy(): void {
		$today = current_datetime()->setTime( 0, 0 );
		$open  = Meta::DEFAULT_HOURS[ strtolower( $today->format( 'D' ) ) ];

		ob_start();
		DashboardWidget::render();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'Hive Central', $html );
		$this->assertStringContainsString( null === $open ? 'Closed' : '0%', $html );
	}
}
