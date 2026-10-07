<?php
namespace Hive\Core\Admin;

use Hive\Core\Bookings\Booking;
use Hive\Core\Bookings\BookingFilter;
use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Content\Meta;
use Hive\Core\Content\PostTypes;
use WP_List_Table;

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * The bookings table on the Bookings admin screen.
 */
final class BookingsListTable extends WP_List_Table {

	private const PER_PAGE = 20;

	/**
	 * Creates the table.
	 *
	 * @param BookingRepository $repository Booking storage.
	 * @param BookingFilter     $filter     Current filter.
	 */
	public function __construct(
		private readonly BookingRepository $repository,
		private readonly BookingFilter $filter,
	) {
		parent::__construct(
			array(
				'singular' => 'booking',
				'plural'   => 'bookings',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Column headings.
	 *
	 * @return array<string, string>
	 */
	public function get_columns(): array {
		return array(
			'cb'       => '<input type="checkbox" />',
			'space'    => __( 'Space', 'hive-core' ),
			'location' => __( 'Location', 'hive-core' ),
			'member'   => __( 'Member', 'hive-core' ),
			'time'     => __( 'Time', 'hive-core' ),
			'status'   => __( 'Status', 'hive-core' ),
		);
	}

	/**
	 * Loads the current page of bookings.
	 */
	public function prepare_items(): void {
		$this->_column_headers = array( $this->get_columns(), array(), array(), 'space' );

		$this->items = $this->repository->search( $this->filter, self::PER_PAGE, ( $this->get_pagenum() - 1 ) * self::PER_PAGE );

		$this->set_pagination_args(
			array(
				'total_items' => $this->repository->count( $this->filter ),
				'per_page'    => self::PER_PAGE,
			)
		);
	}

	/**
	 * Bulk actions.
	 *
	 * @return array<string, string>
	 */
	protected function get_bulk_actions(): array {
		return array( 'cancel' => __( 'Cancel', 'hive-core' ) );
	}

	/**
	 * Message shown when nothing matches.
	 */
	public function no_items(): void {
		esc_html_e( 'No bookings found.', 'hive-core' );
	}

	/**
	 * Checkbox column.
	 *
	 * @param Booking $item Booking.
	 */
	protected function column_cb( $item ): string {
		return sprintf( '<input type="checkbox" name="booking[]" value="%d" />', $item->id );
	}

	/**
	 * Space column with row actions.
	 *
	 * @param Booking $item Booking.
	 */
	protected function column_space( $item ): string {
		$actions = array();

		if ( BookingStatus::Confirmed === $item->status ) {
			$url = wp_nonce_url(
				add_query_arg(
					array(
						'action'  => 'cancel',
						'booking' => $item->id,
					)
				),
				BookingsPage::row_nonce_action( $item->id )
			);

			$actions['cancel'] = sprintf( '<a href="%s" class="submitdelete">%s</a>', esc_url( $url ), esc_html__( 'Cancel', 'hive-core' ) );
		}

		$title = sprintf( '<strong>%s</strong>', esc_html( get_the_title( $item->space_id ) ) );

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Remaining columns.
	 *
	 * @param Booking $item        Booking.
	 * @param string  $column_name Column key.
	 */
	protected function column_default( $item, $column_name ): string {
		switch ( $column_name ) {
			case 'location':
				$location = (int) get_post_meta( $item->space_id, Meta::SPACE_LOCATION, true );
				return $location ? esc_html( get_the_title( $location ) ) : '—';

			case 'member':
				$user = get_userdata( $item->user_id );
				if ( false === $user ) {
					return '—';
				}
				return sprintf(
					'%s<br /><a href="mailto:%2$s">%2$s</a>',
					esc_html( $user->display_name ),
					esc_html( $user->user_email )
				);

			case 'time':
				return esc_html(
					sprintf(
						/* translators: 1: date, 2: start time, 3: end time. */
						__( '%1$s, %2$s–%3$s', 'hive-core' ),
						wp_date( get_option( 'date_format' ), $item->start->getTimestamp() ),
						wp_date( get_option( 'time_format' ), $item->start->getTimestamp() ),
						wp_date( get_option( 'time_format' ), $item->end->getTimestamp() )
					)
				);

			case 'status':
				return BookingStatus::Confirmed === $item->status
					? esc_html__( 'Confirmed', 'hive-core' )
					: esc_html__( 'Cancelled', 'hive-core' );
		}

		return '';
	}

	/**
	 * Filter dropdowns and the export link above the table.
	 *
	 * @param string $which Position: top or bottom.
	 */
	protected function extra_tablenav( $which ): void {
		if ( 'top' !== $which ) {
			return;
		}

		$locations = get_posts(
			array(
				'post_type'   => PostTypes::LOCATION,
				'numberposts' => -1,
				'orderby'     => 'title',
				'order'       => 'ASC',
			)
		);
		?>
		<div class="alignleft actions">
			<label class="screen-reader-text" for="hive-filter-when"><?php esc_html_e( 'Filter by time', 'hive-core' ); ?></label>
			<select name="when" id="hive-filter-when">
				<option value="upcoming" <?php selected( $this->filter->when, BookingFilter::WHEN_UPCOMING ); ?>><?php esc_html_e( 'Upcoming', 'hive-core' ); ?></option>
				<option value="past" <?php selected( $this->filter->when, BookingFilter::WHEN_PAST ); ?>><?php esc_html_e( 'Past', 'hive-core' ); ?></option>
				<option value="all" <?php selected( $this->filter->when, BookingFilter::WHEN_ALL ); ?>><?php esc_html_e( 'All dates', 'hive-core' ); ?></option>
			</select>

			<label class="screen-reader-text" for="hive-filter-status"><?php esc_html_e( 'Filter by status', 'hive-core' ); ?></label>
			<select name="status" id="hive-filter-status">
				<option value=""><?php esc_html_e( 'All statuses', 'hive-core' ); ?></option>
				<option value="confirmed" <?php selected( $this->filter->status?->value, BookingStatus::Confirmed->value ); ?>><?php esc_html_e( 'Confirmed', 'hive-core' ); ?></option>
				<option value="cancelled" <?php selected( $this->filter->status?->value, BookingStatus::Cancelled->value ); ?>><?php esc_html_e( 'Cancelled', 'hive-core' ); ?></option>
			</select>

			<label class="screen-reader-text" for="hive-filter-location"><?php esc_html_e( 'Filter by location', 'hive-core' ); ?></label>
			<select name="location" id="hive-filter-location">
				<option value=""><?php esc_html_e( 'All locations', 'hive-core' ); ?></option>
				<?php foreach ( $locations as $location ) : ?>
					<option value="<?php echo (int) $location->ID; ?>" <?php selected( $this->filter->location_id, $location->ID ); ?>><?php echo esc_html( get_the_title( $location ) ); ?></option>
				<?php endforeach; ?>
			</select>

			<?php submit_button( __( 'Filter', 'hive-core' ), '', 'filter_action', false ); ?>

			<a class="button" href="<?php echo esc_url( BookingsPage::export_url( $this->filter ) ); ?>"><?php esc_html_e( 'Export CSV', 'hive-core' ); ?></a>
		</div>
		<?php
	}
}
