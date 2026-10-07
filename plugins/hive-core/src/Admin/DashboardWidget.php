<?php
namespace Hive\Core\Admin;

use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\OccupancyReport;
use Hive\Core\Bookings\SpaceCatalog;
use Hive\Core\Roles;

/**
 * Dashboard widget with today's bookings and occupancy per location.
 */
final class DashboardWidget {

	/**
	 * Hooks the widget into the dashboard.
	 */
	public static function register_hooks(): void {
		add_action( 'wp_dashboard_setup', array( self::class, 'add' ) );
	}

	/**
	 * Adds the widget for users who manage bookings.
	 */
	public static function add(): void {
		if ( current_user_can( Roles::CAP_MANAGE ) ) {
			wp_add_dashboard_widget( 'hive_today', __( 'Bookings today', 'hive-core' ), array( self::class, 'render' ) );
		}
	}

	/**
	 * Renders the widget.
	 */
	public static function render(): void {
		$today    = current_datetime()->setTime( 0, 0 );
		$bookings = ( new BookingRepository() )->between( $today, $today->modify( '+1 day' ) );
		$report   = OccupancyReport::for_day( ( new SpaceCatalog() )->all(), $bookings, $today );

		if ( array() === $report ) {
			echo '<p>' . esc_html__( 'No bookable spaces yet.', 'hive-core' ) . '</p>';
			return;
		}
		?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Location', 'hive-core' ); ?></th>
					<th><?php esc_html_e( 'Bookings', 'hive-core' ); ?></th>
					<th><?php esc_html_e( 'Occupancy', 'hive-core' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $report as $location_id => $row ) : ?>
					<?php $percent = OccupancyReport::percent( $row ); ?>
					<tr>
						<td><?php echo esc_html( get_the_title( $location_id ) ); ?></td>
						<td><?php echo (int) $row['bookings']; ?></td>
						<td>
							<?php
							echo 0 === $row['open_minutes']
								? esc_html__( 'Closed', 'hive-core' )
								: esc_html( sprintf( '%d%%', $percent ) );
							?>
							<progress max="100" value="<?php echo (int) $percent; ?>" style="width: 100%;"></progress>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=' . BookingsPage::SLUG ) ); ?>"><?php esc_html_e( 'View all bookings', 'hive-core' ); ?></a></p>
		<?php
	}
}
