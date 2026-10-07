<?php
namespace Hive\Core\Tests\Integration\Notifications;

use Hive\Core\Bookings\BookingRepository;
use Hive\Core\Bookings\BookingStatus;
use Hive\Core\Content\Meta;
use Hive\Core\Notifications\BookingMailer;
use Hive\Core\Tests\Integration\TestCase;

final class BookingMailerTest extends TestCase {

	private int $member;

	private int $space;

	public function set_up(): void {
		parent::set_up();
		reset_phpmailer_instance();

		// The test site runs on localhost, and PHPMailer rejects wordpress@localhost as a sender.
		add_filter( 'wp_mail_from', static fn(): string => 'hello@example.com' );

		$this->member = self::factory()->user->create(
			array(
				'display_name' => 'Ada Lovelace',
				'user_email'   => 'ada@example.com',
			)
		);
		$this->space  = $this->create_space( array( 'meta_input' => array( Meta::SPACE_PRICE => 40 ) ) );
		update_post_meta( (int) get_post_meta( $this->space, Meta::SPACE_LOCATION, true ), Meta::LOCATION_ADDRESS, '12 Quay Street' );
	}

	public function tear_down(): void {
		reset_phpmailer_instance();
		parent::tear_down();
	}

	public function test_sends_a_confirmation_when_a_booking_is_made(): void {
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) );

		do_action( 'hive_booking_created', $booking );

		$mail = tests_retrieve_phpmailer_instance()->get_sent();
		$this->assertSame( 'ada@example.com', $mail->to[0][0] );
		$this->assertSame( 'Booking confirmed: Room Atlas, Tuesday 8 January 2030', $mail->subject );
		$this->assertStringContainsString( 'text/html', $mail->header );
		$this->assertStringContainsString( 'Ada Lovelace', $mail->body );
		$this->assertStringContainsString( '10:00–12:00', $mail->body );
		$this->assertStringContainsString( 'Hive Central', $mail->body );
		$this->assertStringContainsString( '12 Quay Street', $mail->body );
		$this->assertStringContainsString( '€80', $mail->body, 'Two hours at €40.' );
	}

	public function test_sends_a_notice_when_a_booking_is_cancelled(): void {
		$repository = new BookingRepository();
		$booking    = $repository->update_status(
			$repository->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 12:00' ) ),
			BookingStatus::Cancelled
		);

		do_action( 'hive_booking_cancelled', $booking );

		$mail = tests_retrieve_phpmailer_instance()->get_sent();
		$this->assertSame( 'Booking cancelled: Room Atlas, Tuesday 8 January 2030', $mail->subject );
		$this->assertStringContainsString( 'cancelled', $mail->body );
	}

	public function test_escapes_content_in_the_email(): void {
		wp_update_post(
			array(
				'ID'         => $this->space,
				'post_title' => 'Room <script>alert(1)</script>',
			)
		);
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );

		BookingMailer::send_confirmation( $booking );

		$this->assertStringNotContainsString( '<script>', tests_retrieve_phpmailer_instance()->get_sent()->body );
	}

	public function test_the_email_can_be_changed_with_a_filter(): void {
		add_filter(
			'hive_booking_email',
			static function ( array $email ): array {
				$email['subject'] = 'Custom subject';
				return $email;
			}
		);
		$booking = ( new BookingRepository() )->insert( $this->space, $this->member, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );

		BookingMailer::send_confirmation( $booking );

		$this->assertSame( 'Custom subject', tests_retrieve_phpmailer_instance()->get_sent()->subject );
	}

	public function test_does_nothing_for_deleted_users(): void {
		$booking = ( new BookingRepository() )->insert( $this->space, 999999, self::local( '2030-01-08 10:00' ), self::local( '2030-01-08 11:00' ) );

		$this->assertFalse( BookingMailer::send_confirmation( $booking ) );
		$this->assertFalse( tests_retrieve_phpmailer_instance()->get_sent() );
	}
}
