<?php
/**
 * Server render of hive/booking-widget.
 *
 * @package Hive\Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var WP_Block             $block      Block instance.
 */

use Hive\Core\Blocks\BookingWidget;

$hive_space_id = (int) ( $attributes['spaceId'] ?? 0 );
if ( 0 === $hive_space_id && 'hive_space' === ( $block->context['postType'] ?? '' ) ) {
	$hive_space_id = (int) ( $block->context['postId'] ?? 0 );
}

$hive_widget  = BookingWidget::create_default();
$hive_context = $hive_widget->context( $hive_space_id, current_datetime() );

if ( null === $hive_context ) {
	return;
}

$hive_state = $hive_widget->state();
wp_interactivity_state( BookingWidget::NAMESPACE, array_merge( $hive_state, BookingWidget::derived_state( $hive_state['strings']['selectPrompt'] ) ) );

$hive_id = wp_unique_id( 'hive-booking-' );
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'hive-booking' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
	data-wp-interactive="hive/booking"
	<?php echo wp_interactivity_data_wp_context( $hive_context ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
>
	<div class="hive-booking__header">
		<label class="hive-booking__label" for="<?php echo esc_attr( $hive_id ); ?>-date"><?php esc_html_e( 'Date', 'hive-core' ); ?></label>
		<input
			id="<?php echo esc_attr( $hive_id ); ?>-date"
			class="hive-booking__date"
			type="date"
			value="<?php echo esc_attr( $hive_context['date'] ); ?>"
			min="<?php echo esc_attr( $hive_context['minDate'] ); ?>"
			max="<?php echo esc_attr( $hive_context['maxDate'] ); ?>"
			data-wp-bind--value="context.date"
			data-wp-on--change="actions.changeDate"
		/>
	</div>

	<p class="hive-booking__closed" data-wp-bind--hidden="!state.isClosed">
		<?php esc_html_e( 'Closed on this day.', 'hive-core' ); ?>
	</p>

	<ul
		class="hive-booking__slots"
		aria-label="<?php esc_attr_e( 'Time slots', 'hive-core' ); ?>"
		data-wp-bind--aria-busy="context.isLoading"
	>
		<template data-wp-each--slot="context.slots" data-wp-each-key="context.slot.start">
			<li>
				<button
					type="button"
					class="hive-booking__slot"
					data-wp-bind--disabled="!context.slot.available"
					data-wp-bind--aria-pressed="state.isSlotSelected"
					data-wp-class--is-selected="state.isSlotSelected"
					data-wp-on--click="actions.toggleSlot"
					data-wp-text="context.slot.label"
				></button>
			</li>
		</template>
	</ul>

	<div class="hive-booking__footer">
		<p class="hive-booking__summary" data-wp-text="state.summary"></p>

		<?php if ( ! is_user_logged_in() ) : ?>
			<a class="wp-element-button hive-booking__submit" href="<?php echo esc_url( wp_login_url( (string) get_permalink() ) ); ?>">
				<?php esc_html_e( 'Log in to book', 'hive-core' ); ?>
			</a>
		<?php elseif ( current_user_can( \Hive\Core\Roles::CAP_BOOK ) ) : ?>
			<button
				type="button"
				class="wp-element-button hive-booking__submit"
				disabled
				data-wp-bind--disabled="!state.canSubmit"
				data-wp-on--click="actions.book"
			>
				<?php esc_html_e( 'Book', 'hive-core' ); ?>
			</button>
		<?php else : ?>
			<p class="hive-booking__note"><?php esc_html_e( 'Your account cannot book spaces.', 'hive-core' ); ?></p>
		<?php endif; ?>
	</div>

	<p
		class="hive-booking__message"
		role="status"
		data-wp-text="context.message"
		data-wp-class--is-error="state.isError"
		data-wp-class--is-success="state.isSuccess"
	></p>
</div>
