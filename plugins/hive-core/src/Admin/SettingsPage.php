<?php
namespace Hive\Core\Admin;

use Hive\Core\Bookings\BookingSettings;
use Hive\Core\Settings;

/**
 * Settings → Hive Coworking: booking limits, built on the Settings API.
 */
final class SettingsPage {

	public const SLUG  = 'hive-settings';
	public const GROUP = 'hive_core';

	/**
	 * Hooks the page into the admin.
	 */
	public static function register_hooks(): void {
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
		add_action( 'admin_init', array( self::class, 'register_fields' ) );
		add_action( 'admin_menu', array( self::class, 'add_page' ) );
	}

	/**
	 * Registers the option with its sanitizer, so every write is validated.
	 */
	public static function register_settings(): void {
		register_setting(
			self::GROUP,
			Settings::OPTION,
			array(
				'type'              => 'object',
				'sanitize_callback' => array( self::class, 'sanitize' ),
				'default'           => ( new BookingSettings() )->to_array(),
			)
		);
	}

	/**
	 * Keeps only known keys and replaces invalid values with safe ones.
	 *
	 * @param mixed $input Submitted value.
	 * @return array<string, int>
	 */
	public static function sanitize( mixed $input ): array {
		return BookingSettings::from_array( is_array( $input ) ? $input : array() )->to_array();
	}

	/**
	 * Adds the page under Settings.
	 */
	public static function add_page(): void {
		add_options_page(
			__( 'Hive Coworking', 'hive-core' ),
			__( 'Hive Coworking', 'hive-core' ),
			'manage_options',
			self::SLUG,
			array( self::class, 'render' )
		);
	}

	/**
	 * Registers the section and fields.
	 */
	public static function register_fields(): void {
		add_settings_section(
			'hive_booking',
			__( 'Booking rules', 'hive-core' ),
			static function (): void {
				echo '<p>' . esc_html__( 'These limits apply to every space and are enforced by the booking API.', 'hive-core' ) . '</p>';
			},
			self::SLUG
		);

		add_settings_field(
			'slot_minutes',
			__( 'Slot length', 'hive-core' ),
			array( self::class, 'render_slot_field' ),
			self::SLUG,
			'hive_booking',
			array( 'label_for' => 'hive-slot_minutes' )
		);

		$number_fields = array(
			'min_notice_minutes'   => array( __( 'Minimum notice', 'hive-core' ), __( 'minutes before the start', 'hive-core' ) ),
			'max_duration_minutes' => array( __( 'Maximum duration', 'hive-core' ), __( 'minutes per booking', 'hive-core' ) ),
			'cancel_window_hours'  => array( __( 'Cancellation deadline', 'hive-core' ), __( 'hours before the start; managers can always cancel', 'hive-core' ) ),
		);

		foreach ( $number_fields as $key => list( $label, $unit ) ) {
			add_settings_field(
				$key,
				$label,
				array( self::class, 'render_number_field' ),
				self::SLUG,
				'hive_booking',
				array(
					'label_for' => 'hive-' . $key,
					'key'       => $key,
					'unit'      => $unit,
				)
			);
		}
	}

	/**
	 * Renders the page.
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::GROUP );
				do_settings_sections( self::SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the slot length dropdown.
	 */
	public static function render_slot_field(): void {
		$current = Settings::booking()->slot_minutes;

		printf( '<select id="hive-slot_minutes" name="%s[slot_minutes]">', esc_attr( Settings::OPTION ) );
		foreach ( BookingSettings::SLOT_CHOICES as $minutes ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $minutes,
				selected( $current, $minutes, false ),
				/* translators: %d: number of minutes. */
				esc_html( sprintf( _n( '%d minute', '%d minutes', $minutes, 'hive-core' ), $minutes ) )
			);
		}
		echo '</select>';
	}

	/**
	 * Renders a number input.
	 *
	 * @param array{key: string, unit: string} $args Field arguments.
	 */
	public static function render_number_field( array $args ): void {
		$values = Settings::booking()->to_array();

		printf(
			'<input type="number" min="0" step="1" class="small-text" id="hive-%1$s" name="%2$s[%1$s]" value="%3$d" /> %4$s',
			esc_attr( $args['key'] ),
			esc_attr( Settings::OPTION ),
			(int) ( $values[ $args['key'] ] ?? 0 ),
			esc_html( $args['unit'] )
		);
	}
}
