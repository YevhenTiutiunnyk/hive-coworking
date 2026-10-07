<?php
/**
 * Server render of hive/my-bookings.
 *
 * @package Hive\Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

use Hive\Core\Blocks\MyBookings;
use Hive\Core\Content\PostTypes;

$hive_wrapper = get_block_wrapper_attributes( array( 'class' => 'hive-account' ) );

if ( ! is_user_logged_in() ) {
	printf(
		'<div %1$s><p class="hive-account__login">%2$s <a href="%3$s">%4$s</a></p></div>',
		$hive_wrapper, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core.
		esc_html__( 'Your bookings appear here once you are logged in.', 'hive-core' ),
		esc_url( wp_login_url( (string) get_permalink() ) ),
		esc_html__( 'Log in', 'hive-core' )
	);
	return;
}

$hive_context = MyBookings::context( get_current_user_id(), current_datetime() );
$hive_spaces  = get_post_type_archive_link( PostTypes::SPACE );

wp_interactivity_state( MyBookings::NAMESPACE, array_merge( MyBookings::state(), MyBookings::derived_state() ) );
?>
<div
	<?php echo $hive_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
	data-wp-interactive="hive/account"
	<?php echo wp_interactivity_data_wp_context( $hive_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
>
	<div class="hive-account__tabs" role="group" aria-label="<?php esc_attr_e( 'Bookings to show', 'hive-core' ); ?>">
		<button type="button" data-wp-bind--aria-pressed="state.isUpcomingTab" data-wp-on--click="actions.showUpcoming">
			<?php esc_html_e( 'Upcoming', 'hive-core' ); ?>
		</button>
		<button type="button" data-wp-bind--aria-pressed="state.isPastTab" data-wp-on--click="actions.showPast">
			<?php esc_html_e( 'Past', 'hive-core' ); ?>
		</button>
	</div>

	<p class="hive-account__message" role="status" data-wp-text="context.message" data-wp-class--is-error="state.isError"></p>

	<ul class="hive-account__list" data-wp-bind--hidden="!state.isUpcomingTab">
		<template data-wp-each--booking="context.upcoming" data-wp-each-key="context.booking.id">
			<li class="hive-account__item" data-wp-class--is-cancelled="state.isCancelled">
				<div class="hive-account__details">
					<a class="hive-account__space" data-wp-bind--href="context.booking.spaceUrl" data-wp-text="context.booking.space"></a>
					<p class="hive-account__when" data-wp-text="context.booking.when"></p>
					<p class="hive-account__location" data-wp-text="context.booking.location"></p>
				</div>
				<span class="hive-account__status" data-wp-text="context.booking.statusLabel"></span>
				<button
					type="button"
					class="hive-account__cancel"
					data-wp-bind--hidden="!context.booking.canCancel"
					data-wp-bind--disabled="state.isPending"
					data-wp-on--click="actions.cancel"
				>
					<?php esc_html_e( 'Cancel', 'hive-core' ); ?>
				</button>
			</li>
		</template>
	</ul>

	<ul class="hive-account__list" hidden data-wp-bind--hidden="!state.isPastTab">
		<template data-wp-each--booking="context.past" data-wp-each-key="context.booking.id">
			<li class="hive-account__item" data-wp-class--is-cancelled="state.isCancelled">
				<div class="hive-account__details">
					<a class="hive-account__space" data-wp-bind--href="context.booking.spaceUrl" data-wp-text="context.booking.space"></a>
					<p class="hive-account__when" data-wp-text="context.booking.when"></p>
					<p class="hive-account__location" data-wp-text="context.booking.location"></p>
				</div>
				<span class="hive-account__status" data-wp-text="context.booking.statusLabel"></span>
			</li>
		</template>
	</ul>

	<p class="hive-account__empty" data-wp-bind--hidden="!state.showEmptyUpcoming">
		<?php esc_html_e( 'You have no upcoming bookings.', 'hive-core' ); ?>
		<?php if ( $hive_spaces ) : ?>
			<a href="<?php echo esc_url( $hive_spaces ); ?>"><?php esc_html_e( 'Find a space', 'hive-core' ); ?></a>
		<?php endif; ?>
	</p>
	<p class="hive-account__empty" hidden data-wp-bind--hidden="!state.showEmptyPast">
		<?php esc_html_e( 'No past bookings yet.', 'hive-core' ); ?>
	</p>
</div>
