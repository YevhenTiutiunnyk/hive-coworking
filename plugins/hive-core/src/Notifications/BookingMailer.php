<?php
namespace Hive\Core\Notifications;

use Hive\Core\Blocks\BookingWidget;
use Hive\Core\Blocks\SpaceFinder;
use Hive\Core\Bookings\Booking;
use Hive\Core\Content\Meta;

/**
 * Emails members when a booking is made or cancelled.
 */
final class BookingMailer {

	private const TEMPLATE = 'emails/booking.php';

	/**
	 * Hooks the emails to the booking events.
	 */
	public static function register_hooks(): void {
		add_action( 'hive_booking_created', array( self::class, 'on_created' ) );
		add_action( 'hive_booking_cancelled', array( self::class, 'on_cancelled' ) );
	}

	/**
	 * Action callback for hive_booking_created.
	 *
	 * @param Booking $booking New booking.
	 */
	public static function on_created( Booking $booking ): void {
		self::send_confirmation( $booking );
	}

	/**
	 * Action callback for hive_booking_cancelled.
	 *
	 * @param Booking $booking Cancelled booking.
	 */
	public static function on_cancelled( Booking $booking ): void {
		self::send_cancellation( $booking );
	}

	/**
	 * Sends the confirmation email.
	 *
	 * @param Booking $booking New booking.
	 */
	public static function send_confirmation( Booking $booking ): bool {
		return self::send( $booking, 'confirmed' );
	}

	/**
	 * Sends the cancellation email.
	 *
	 * @param Booking $booking Cancelled booking.
	 */
	public static function send_cancellation( Booking $booking ): bool {
		return self::send( $booking, 'cancelled' );
	}

	/**
	 * Builds and sends an email in the member's language.
	 *
	 * @param Booking $booking Booking.
	 * @param string  $type    "confirmed" or "cancelled".
	 */
	private static function send( Booking $booking, string $type ): bool {
		$user = get_userdata( $booking->user_id );
		if ( false === $user ) {
			return false;
		}

		$switched = switch_to_user_locale( $user->ID );

		try {
			$details = self::details( $booking );
			$subject = 'confirmed' === $type
				/* translators: 1: space name, 2: date. */
				? sprintf( __( 'Booking confirmed: %1$s, %2$s', 'hive-core' ), $details['space'], $details['date'] )
				/* translators: 1: space name, 2: date. */
				: sprintf( __( 'Booking cancelled: %1$s, %2$s', 'hive-core' ), $details['space'], $details['date'] );

			/**
			 * Filters a booking email before it is sent.
			 *
			 * @param array{to: string, subject: string, body: string, headers: list<string>} $email   Email.
			 * @param Booking                                                               $booking Booking.
			 * @param string                                                                $type    "confirmed" or "cancelled".
			 */
			$email = apply_filters(
				'hive_booking_email',
				array(
					'to'      => $user->user_email,
					'subject' => $subject,
					'body'    => self::render(
						array(
							'type'        => $type,
							'name'        => $user->display_name,
							'details'     => $details,
							'account_url' => BookingWidget::account_url(),
							'site_name'   => get_bloginfo( 'name' ),
						)
					),
					'headers' => array( 'Content-Type: text/html; charset=UTF-8' ),
				),
				$booking,
				$type
			);

			return wp_mail( $email['to'], $email['subject'], $email['body'], $email['headers'] );
		} finally {
			if ( $switched ) {
				restore_previous_locale();
			}
		}
	}

	/**
	 * Human-readable booking details, in the site timezone.
	 *
	 * @param Booking $booking Booking.
	 * @return array{space: string, location: string, address: string, date: string, time: string, price: string}
	 */
	private static function details( Booking $booking ): array {
		$location = (int) get_post_meta( $booking->space_id, Meta::SPACE_LOCATION, true );
		$hours    = ( $booking->end->getTimestamp() - $booking->start->getTimestamp() ) / 3600;
		$price    = (float) get_post_meta( $booking->space_id, Meta::SPACE_PRICE, true );

		return array(
			'space'    => wp_strip_all_tags( get_the_title( $booking->space_id ) ),
			'location' => $location ? wp_strip_all_tags( get_the_title( $location ) ) : '',
			'address'  => $location ? (string) get_post_meta( $location, Meta::LOCATION_ADDRESS, true ) : '',
			'date'     => wp_date( _x( 'l j F Y', 'email date', 'hive-core' ), $booking->start->getTimestamp() ),
			'time'     => wp_date( 'H:i', $booking->start->getTimestamp() ) . '–' . wp_date( 'H:i', $booking->end->getTimestamp() ),
			'price'    => SpaceFinder::format_price( $hours * $price ),
		);
	}

	/**
	 * Renders the email template. A theme can override it with hive/emails/booking.php.
	 *
	 * @param array<string, mixed> $hive_email Template variables.
	 */
	private static function render( array $hive_email ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Used by the included template.
		$template = locate_template( 'hive/' . self::TEMPLATE );
		if ( '' === $template ) {
			$template = dirname( __DIR__, 2 ) . '/templates/' . self::TEMPLATE;
		}

		ob_start();
		include $template;

		return (string) ob_get_clean();
	}
}
