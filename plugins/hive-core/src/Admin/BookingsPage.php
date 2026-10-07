<?php
namespace Hive\Core\Admin;

use Hive\Core\Bookings\Booking;
use Hive\Core\Bookings\BookingError;
use Hive\Core\Bookings\BookingFilter;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingService;
use Hive\Core\Roles;

/**
 * The Bookings admin screen: list, filter, cancel and export bookings.
 */
final class BookingsPage {

	public const SLUG          = 'hive-bookings';
	public const EXPORT_ACTION = 'hive_export_bookings';
	private const EXPORT_LIMIT = 5000;

	/**
	 * Hooks the screen into the admin.
	 */
	public static function register_hooks(): void {
		add_action( 'admin_menu', array( self::class, 'add_page' ) );
		add_action( 'admin_post_' . self::EXPORT_ACTION, array( self::class, 'export' ) );
	}

	/**
	 * Adds the top-level Bookings menu.
	 */
	public static function add_page(): void {
		$hook = add_menu_page(
			__( 'Bookings', 'hive-core' ),
			__( 'Bookings', 'hive-core' ),
			Roles::CAP_MANAGE,
			self::SLUG,
			array( self::class, 'render' ),
			'dashicons-calendar',
			26
		);

		// Actions redirect, so they must run before any output.
		add_action( 'load-' . $hook, array( self::class, 'handle_request' ) );
	}

	/**
	 * Nonce action for a single-row cancel link.
	 *
	 * @param int $booking_id Booking ID.
	 */
	public static function row_nonce_action( int $booking_id ): string {
		return 'hive_cancel_booking_' . $booking_id;
	}

	/**
	 * URL of the CSV export for a filter.
	 *
	 * @param BookingFilter $filter Filter to export.
	 */
	public static function export_url( BookingFilter $filter ): string {
		return wp_nonce_url(
			add_query_arg( array_merge( array( 'action' => self::EXPORT_ACTION ), $filter->to_query_args() ), admin_url( 'admin-post.php' ) ),
			self::EXPORT_ACTION
		);
	}

	/**
	 * Verifies and runs a cancel action, then redirects back with a notice.
	 */
	public static function handle_request(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonces are verified below before anything changes.
		$action = self::current_action();
		if ( 'cancel' !== $action || ! current_user_can( Roles::CAP_MANAGE ) ) {
			return;
		}

		$raw = wp_unslash( $_REQUEST['booking'] ?? array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Cast with absint() below.
		// phpcs:enable

		// A row link carries one ID; the bulk form posts an array and uses the list table's nonce.
		if ( is_array( $raw ) ) {
			check_admin_referer( 'bulk-bookings' );
			$ids = array_map( 'absint', $raw );
		} else {
			$ids = array( absint( $raw ) );
			check_admin_referer( self::row_nonce_action( $ids[0] ) );
		}

		$cancelled = self::cancel( $ids );

		wp_safe_redirect( add_query_arg( 'cancelled', $cancelled, remove_query_arg( array( 'action', 'action2', 'booking', '_wpnonce', '_wp_http_referer' ) ) ) );
		exit;
	}

	/**
	 * Cancels bookings as a manager. Returns how many were cancelled.
	 *
	 * @param array<int> $ids Booking IDs.
	 */
	public static function cancel( array $ids ): int {
		$service    = BookingService::create_default();
		$repository = new BookingRepository();
		$cancelled  = 0;

		foreach ( $ids as $id ) {
			$booking = $repository->find( $id );
			if ( null === $booking ) {
				continue;
			}

			try {
				$service->cancel( $booking, true, current_datetime() );
				++$cancelled;
			} catch ( BookingError $error ) {
				// Already cancelled: nothing to do.
				continue;
			}
		}

		return $cancelled;
	}

	/**
	 * Renders the screen.
	 */
	public static function render(): void {
		if ( ! current_user_can( Roles::CAP_MANAGE ) ) {
			return;
		}

		$table = new BookingsListTable( new BookingRepository(), self::current_filter() );
		$table->prepare_items();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display-only notice after a redirect.
		$cancelled = isset( $_GET['cancelled'] ) ? absint( $_GET['cancelled'] ) : null;
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Bookings', 'hive-core' ); ?></h1>

			<?php if ( null !== $cancelled ) : ?>
				<div class="notice notice-success is-dismissible"><p>
					<?php
					/* translators: %d: number of bookings. */
					echo esc_html( sprintf( _n( '%d booking cancelled.', '%d bookings cancelled.', $cancelled, 'hive-core' ), $cancelled ) );
					?>
				</p></div>
			<?php endif; ?>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::SLUG ); ?>" />
				<?php $table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Streams the filtered bookings as a CSV download.
	 */
	public static function export(): void {
		if ( ! current_user_can( Roles::CAP_MANAGE ) ) {
			wp_die( esc_html__( 'You are not allowed to export bookings.', 'hive-core' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( self::EXPORT_ACTION );

		$bookings = ( new BookingRepository() )->search( self::current_filter(), self::EXPORT_LIMIT );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="bookings-' . gmdate( 'Y-m-d' ) . '.csv"' );

		self::write_csv( 'php://output', $bookings );
		exit;
	}

	/**
	 * Writes the CSV to a stream.
	 *
	 * @param string    $target   Stream URI.
	 * @param Booking[] $bookings Bookings to write.
	 */
	public static function write_csv( string $target, array $bookings ): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Writing to an output stream, not the filesystem.
		$handle = fopen( $target, 'w' );
		if ( false === $handle ) {
			return;
		}

		fputcsv( $handle, BookingsCsv::header(), ',', '"', '' );
		foreach ( BookingsCsv::rows( $bookings ) as $row ) {
			fputcsv( $handle, $row, ',', '"', '' );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- See fopen() above.
		fclose( $handle );
	}

	/**
	 * Filter from the current query string.
	 */
	private static function current_filter(): BookingFilter {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter values.
		return BookingFilter::from_request( wp_unslash( $_GET ), current_datetime() );
	}

	/**
	 * Selected action from the top or bottom bulk-action dropdown, or the row action.
	 */
	private static function current_action(): string {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Only reads the action name.
		foreach ( array( 'action', 'action2' ) as $key ) {
			$value = isset( $_REQUEST[ $key ] ) ? sanitize_key( wp_unslash( $_REQUEST[ $key ] ) ) : '';
			if ( '' !== $value && '-1' !== $value ) {
				return $value;
			}
		}
		// phpcs:enable

		return '';
	}
}
