<?php
namespace Hive\Core\Tests\Integration\Admin;

use Hive\Core\Admin\SettingsPage;
use Hive\Core\Settings;
use Hive\Core\Tests\Integration\TestCase;

final class SettingsPageTest extends TestCase {

	public function set_up(): void {
		parent::set_up();
		SettingsPage::register_settings();
	}

	public function test_saved_values_are_sanitized(): void {
		update_option(
			Settings::OPTION,
			array(
				'slot_minutes'         => '45',
				'min_notice_minutes'   => '-5',
				'max_duration_minutes' => '120',
				'cancel_window_hours'  => '24',
				'unexpected'           => '<script>',
			)
		);

		$this->assertSame(
			array(
				'slot_minutes'         => 60,
				'min_notice_minutes'   => 0,
				'max_duration_minutes' => 120,
				'cancel_window_hours'  => 24,
			),
			get_option( Settings::OPTION )
		);
	}

	public function test_booking_settings_are_read_from_the_option(): void {
		update_option( Settings::OPTION, array( 'slot_minutes' => 30 ) );

		$this->assertSame( 30, Settings::booking()->slot_minutes );
	}

	public function test_page_renders_a_field_for_each_setting(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
		SettingsPage::register_fields();

		ob_start();
		SettingsPage::render();
		$html = ob_get_clean();

		$this->assertStringContainsString( 'name="hive_core_settings[slot_minutes]"', $html );
		$this->assertStringContainsString( 'name="hive_core_settings[min_notice_minutes]"', $html );
		$this->assertStringContainsString( 'name="hive_core_settings[max_duration_minutes]"', $html );
		$this->assertStringContainsString( 'name="hive_core_settings[cancel_window_hours]"', $html );
		$this->assertStringContainsString( 'option_page', $html, 'The form posts through options.php with a nonce.' );
	}

	public function test_page_is_hidden_from_users_who_cannot_manage_options(): void {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'editor' ) ) );

		ob_start();
		SettingsPage::render();

		$this->assertSame( '', ob_get_clean() );
	}
}
