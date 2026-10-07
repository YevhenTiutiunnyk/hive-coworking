<?php
/**
 * Title: Hero with honeycomb
 * Slug: hive/hero
 * Categories: hive, banner
 * Description: Headline, two buttons and the honeycomb illustration.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-hero" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|60"}}}} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"58%","className":"hive-hero__copy"} -->
		<div class="wp-block-column is-vertically-aligned-center hive-hero__copy" style="flex-basis:58%">
			<!-- wp:paragraph {"className":"hive-eyebrow"} -->
			<p class="hive-eyebrow"><?php esc_html_e( 'Coworking in three neighbourhoods', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":1,"fontSize":"display"} -->
			<h1 class="wp-block-heading has-display-font-size">
				<?php
				echo wp_kses(
					/* translators: The <em> part is highlighted. */
					__( 'Book a room. <em>Do your best work.</em>', 'hive' ),
					array( 'em' => array() )
				);
				?>
			</h1>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"fontSize":"large","textColor":"contrast-2"} -->
			<p class="has-contrast-2-color has-text-color has-large-font-size"><?php esc_html_e( 'Meeting rooms, private offices and hot desks you can book by the hour. See what is free, pick your slots and you are done in under a minute.', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
			<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/spaces/' ) ); ?>"><?php esc_html_e( 'Find a space', 'hive' ); ?></a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'See membership plans', 'hive' ); ?></a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">
			<!-- wp:html -->
			<div class="hive-hero__art" aria-hidden="true">
				<span class="hive-cell hive-cell--1"></span>
				<span class="hive-cell hive-cell--2"></span>
				<span class="hive-cell hive-cell--3"></span>
				<span class="hive-cell hive-cell--4"></span>
			</div>
			<!-- /wp:html -->
			<!-- wp:paragraph {"className":"hive-hero__stat"} -->
			<p class="hive-hero__stat"><strong>12</strong><?php esc_html_e( 'bookable spaces across 3 locations', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
