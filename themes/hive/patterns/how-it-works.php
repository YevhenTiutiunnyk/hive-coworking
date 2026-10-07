<?php
/**
 * Title: How it works
 * Slug: hive/how-it-works
 * Categories: hive
 * Description: Three numbered steps.
 *
 * @package Hive
 */

?>
<!-- wp:group {"className":"hive-reveal","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"base-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group hive-reveal has-base-2-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
	<!-- wp:paragraph {"align":"wide","className":"hive-eyebrow"} -->
	<p class="alignwide hive-eyebrow"><?php esc_html_e( 'How it works', 'hive' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"align":"wide"} -->
	<h2 class="wp-block-heading alignwide"><?php esc_html_e( 'From idea to booked room in a minute', 'hive' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"},"margin":{"top":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--50)">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"hive-step__number"} -->
			<p class="hive-step__number">1</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":3,"fontSize":"large"} -->
			<h3 class="wp-block-heading has-large-font-size"><?php esc_html_e( 'Pick a space', 'hive' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"contrast-2"} -->
			<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Filter by location, size and what you need in the room: a screen, a whiteboard, daylight.', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"hive-step__number"} -->
			<p class="hive-step__number">2</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":3,"fontSize":"large"} -->
			<h3 class="wp-block-heading has-large-font-size"><?php esc_html_e( 'Choose your hours', 'hive' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"contrast-2"} -->
			<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Free slots are shown live. Select one or several in a row and see the price straight away.', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"hive-step__number"} -->
			<p class="hive-step__number">3</p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":3,"fontSize":"large"} -->
			<h3 class="wp-block-heading has-large-font-size"><?php esc_html_e( 'Show up and work', 'hive' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"textColor":"contrast-2"} -->
			<p class="has-contrast-2-color has-text-color"><?php esc_html_e( 'Your booking is confirmed at once. Plans change? Cancel from your account up to two hours before.', 'hive' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
