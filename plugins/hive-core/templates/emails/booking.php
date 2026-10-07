<?php
/**
 * Booking email. Copy to your-theme/hive/emails/booking.php to customise.
 *
 * @package Hive\Core
 *
 * @var array{type: string, name: string, details: array{space: string, location: string, address: string, date: string, time: string, price: string}, account_url: string, site_name: string} $hive_email
 */

defined( 'ABSPATH' ) || exit;

$hive_details   = $hive_email['details'];
$hive_confirmed = 'confirmed' === $hive_email['type'];
$hive_rows      = array(
	__( 'Space', 'hive-core' )    => $hive_details['space'],
	__( 'Location', 'hive-core' ) => trim( $hive_details['location'] . ', ' . $hive_details['address'], ', ' ),
	__( 'Date', 'hive-core' )     => $hive_details['date'],
	__( 'Time', 'hive-core' )     => $hive_details['time'],
);
if ( $hive_confirmed ) {
	$hive_rows[ __( 'Price', 'hive-core' ) ] = $hive_details['price'];
}
?>
<!doctype html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( $hive_details['space'] ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f3ecdd;font-family:Helvetica,Arial,sans-serif;color:#211b14;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3ecdd;padding:32px 16px;">
		<tr>
			<td align="center">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fbf7ef;border-radius:16px;overflow:hidden;">
					<tr>
						<td style="background:<?php echo $hive_confirmed ? '#e8a317' : '#211b14'; ?>;padding:24px 32px;color:<?php echo $hive_confirmed ? '#211b14' : '#fbf7ef'; ?>;font-size:14px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">
							<?php echo esc_html( $hive_email['site_name'] ); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:32px;">
							<h1 style="margin:0 0 16px;font-family:Georgia,serif;font-size:28px;line-height:1.2;">
								<?php echo esc_html( $hive_confirmed ? __( 'Your booking is confirmed', 'hive-core' ) : __( 'Your booking was cancelled', 'hive-core' ) ); ?>
							</h1>
							<p style="margin:0 0 24px;font-size:16px;line-height:1.5;">
								<?php
								/* translators: %s: member name. */
								echo esc_html( sprintf( __( 'Hi %s,', 'hive-core' ), $hive_email['name'] ) );
								echo ' ';
								echo esc_html(
									$hive_confirmed
										? __( 'your room is booked. Here are the details.', 'hive-core' )
										: __( 'this booking has been cancelled and the room is free again.', 'hive-core' )
								);
								?>
							</p>
							<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #ddd3c2;font-size:15px;">
								<?php foreach ( $hive_rows as $hive_label => $hive_value ) : ?>
									<tr>
										<td style="padding:10px 0;border-bottom:1px solid #ddd3c2;color:#5e554a;"><?php echo esc_html( $hive_label ); ?></td>
										<td style="padding:10px 0;border-bottom:1px solid #ddd3c2;text-align:right;font-weight:bold;"><?php echo esc_html( $hive_value ); ?></td>
									</tr>
								<?php endforeach; ?>
							</table>
							<p style="margin:32px 0 0;">
								<a href="<?php echo esc_url( $hive_email['account_url'] ); ?>" style="display:inline-block;padding:14px 28px;border-radius:999px;background:#211b14;color:#fbf7ef;font-weight:bold;text-decoration:none;">
									<?php esc_html_e( 'View my bookings', 'hive-core' ); ?>
								</a>
							</p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
