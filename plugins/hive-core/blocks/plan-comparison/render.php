<?php
/**
 * Server render of hive/plan-comparison.
 *
 * @package Hive\Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

use Hive\Core\Blocks\BookingWidget;
use Hive\Core\Blocks\Plans;
use Hive\Core\Blocks\SpaceFinder;

$hive_plans = Plans::all();
if ( array() === $hive_plans ) {
	return;
}

$hive_cta_url     = '' !== ( $attributes['ctaUrl'] ?? '' ) ? (string) $attributes['ctaUrl'] : BookingWidget::account_url();
$hive_max_savings = max( array_column( $hive_plans, 'savings' ) );

wp_interactivity_state(
	'hive/plans',
	array(
		'isMonthly' => static fn(): bool => 'monthly' === ( wp_interactivity_get_context()['billing'] ?? 'monthly' ),
		'isYearly'  => static fn(): bool => 'yearly' === ( wp_interactivity_get_context()['billing'] ?? 'monthly' ),
	)
);
?>
<div
	<?php echo get_block_wrapper_attributes( array( 'class' => 'hive-plans' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by core. ?>
	data-wp-interactive="hive/plans"
	data-wp-context='{ "billing": "monthly" }'
>
	<div class="hive-plans__toggle" role="group" aria-label="<?php esc_attr_e( 'Billing period', 'hive-core' ); ?>">
		<button type="button" data-wp-bind--aria-pressed="state.isMonthly" data-wp-on--click="actions.showMonthly">
			<?php esc_html_e( 'Monthly', 'hive-core' ); ?>
		</button>
		<button type="button" data-wp-bind--aria-pressed="state.isYearly" data-wp-on--click="actions.showYearly">
			<?php esc_html_e( 'Yearly', 'hive-core' ); ?>
			<?php if ( $hive_max_savings > 0 ) : ?>
				<span class="hive-plans__save">
					<?php
					/* translators: %d: percentage. */
					echo esc_html( sprintf( __( 'Save up to %d%%', 'hive-core' ), $hive_max_savings ) );
					?>
				</span>
			<?php endif; ?>
		</button>
	</div>

	<ul class="hive-plans__grid">
		<?php foreach ( $hive_plans as $hive_plan ) : ?>
			<li class="hive-plans__plan<?php echo $hive_plan['featured'] ? ' is-featured' : ''; ?>">
				<?php if ( $hive_plan['featured'] ) : ?>
					<p class="hive-plans__flag"><?php esc_html_e( 'Most popular', 'hive-core' ); ?></p>
				<?php endif; ?>

				<h3 class="hive-plans__name"><?php echo esc_html( $hive_plan['title'] ); ?></h3>
				<?php if ( '' !== $hive_plan['description'] ) : ?>
					<p class="hive-plans__description"><?php echo esc_html( $hive_plan['description'] ); ?></p>
				<?php endif; ?>

				<p class="hive-plans__price">
					<span data-wp-bind--hidden="state.isYearly">
						<?php
						/* translators: %s: price. */
						echo wp_kses( sprintf( __( '%s / month', 'hive-core' ), '<strong>' . esc_html( SpaceFinder::format_price( $hive_plan['monthly'] ) ) . '</strong>' ), array( 'strong' => array() ) );
						?>
					</span>
					<span hidden data-wp-bind--hidden="state.isMonthly">
						<?php
						/* translators: %s: price. */
						echo wp_kses( sprintf( __( '%s / year', 'hive-core' ), '<strong>' . esc_html( SpaceFinder::format_price( $hive_plan['yearly'] ) ) . '</strong>' ), array( 'strong' => array() ) );
						?>
					</span>
				</p>

				<?php if ( array() !== $hive_plan['features'] ) : ?>
					<ul class="hive-plans__features">
						<?php foreach ( $hive_plan['features'] as $hive_feature ) : ?>
							<li><?php echo esc_html( $hive_feature ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<a class="wp-element-button hive-plans__cta" href="<?php echo esc_url( $hive_cta_url ); ?>">
					<?php
					/* translators: %s: plan name. */
					echo esc_html( sprintf( __( 'Choose %s', 'hive-core' ), $hive_plan['title'] ) );
					?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
